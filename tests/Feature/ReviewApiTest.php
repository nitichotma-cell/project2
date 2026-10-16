<?php

namespace Tests\Feature;

use App\Models\MediaItem;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\ReviewDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_login_create_review_and_revoke_token(): void
    {
        $credentials = [
            'name' => 'Test',
            'surname' => 'Reader',
            'email' => 'reader@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $registration = $this->postJson('/api/register', $credentials)
            ->assertCreated()
            ->assertJsonStructure(['token', 'user' => ['id', 'email']])
            ->assertJsonMissingPath('user.password');
        $token = $registration->json('token');

        $item = $this->withToken($token)->postJson('/api/items', [
            'type' => 'book',
            'title' => 'Test book',
        ])->assertCreated()->json('data');

        $this->withToken($token)->postJson("/api/items/{$item['id']}/reviews", [
            'rating' => 4.5,
            'body' => 'Very good',
        ])->assertCreated();
        $this->withToken($token)->postJson("/api/items/{$item['id']}/reviews", [
            'rating' => 4,
        ])->assertUnprocessable();

        $this->getJson("/api/items/{$item['id']}")
            ->assertOk()
            ->assertJsonPath('data.reviews_count', 1)
            ->assertJsonPath('data.reviews_avg_rating', 4.5);

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $registration->json('user.id'),
        ]);

        $this->postJson('/api/login', [
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_items_support_get_all_get_by_id_and_authenticated_crud(): void
    {
        $user = User::factory()->create();
        $item = MediaItem::create(['type' => 'movie', 'title' => 'Original title']);

        $this->getJson('/api/items')->assertOk()->assertJsonPath('data.0.title', 'Original title');
        $this->getJson("/api/items/{$item->id}")->assertOk()->assertJsonPath('data.id', $item->id);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/items/{$item->id}", ['title' => 'Updated title'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Updated title');

        $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/items/{$item->id}")
            ->assertOk();

        $this->assertDatabaseMissing('media_items', ['id' => $item->id]);
    }

    public function test_users_can_only_update_or_delete_their_own_reviews(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $item = MediaItem::create(['type' => 'book', 'title' => 'Reviewed book']);
        $review = Review::create([
            'media_item_id' => $item->id,
            'user_id' => $owner->id,
            'rating' => 3,
            'body' => 'Initial review',
        ]);

        $this->actingAs($otherUser, 'sanctum')
            ->patchJson("/api/reviews/{$review->id}", ['rating' => 1])
            ->assertForbidden();
        $this->actingAs($otherUser, 'sanctum')
            ->deleteJson("/api/reviews/{$review->id}")
            ->assertForbidden();

        $this->actingAs($owner, 'sanctum')
            ->patchJson("/api/reviews/{$review->id}", ['rating' => 5])
            ->assertOk()
            ->assertJsonPath('data.rating', '5.0');
    }

    public function test_google_books_can_be_searched_and_imported(): void
    {
        Http::fake([
            'www.googleapis.com/books/v1/volumes/book-123' => Http::response([
                'id' => 'book-123',
                'volumeInfo' => [
                    'title' => 'External Book',
                    'authors' => ['An Author'],
                    'publishedDate' => '2024-01-01',
                ],
            ]),
            'www.googleapis.com/books/v1/volumes*' => Http::response([
                'items' => [[
                    'id' => 'book-123',
                    'volumeInfo' => [
                        'title' => 'External Book',
                        'authors' => ['An Author'],
                        'publishedDate' => '2024-01-01',
                    ],
                ]],
            ]),
        ]);

        $this->getJson('/api/external/search?type=book&q=external')
            ->assertOk()
            ->assertJsonPath('data.0.external_id', 'book-123');

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/external/import', [
                'type' => 'book',
                'external_id' => 'book-123',
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'External Book');

        $this->assertDatabaseHas('media_items', [
            'external_source' => 'google_books',
            'external_id' => 'book-123',
            'title' => 'External Book',
        ]);
    }

    public function test_omdb_movies_can_be_searched_and_imported(): void
    {
        config(['services.omdb.key' => 'test-key']);
        Http::fake([
            'www.omdbapi.com/*i=tt1234567*' => Http::response([
                'Response' => 'True',
                'imdbID' => 'tt1234567',
                'Title' => 'External Movie',
                'Year' => '2023',
                'Plot' => 'A movie plot',
                'Director' => 'A Director',
                'Poster' => 'https://example.com/poster.jpg',
            ]),
            'www.omdbapi.com/*s=inception*' => Http::response([
                'Response' => 'True',
                'Search' => [[
                    'imdbID' => 'tt1234567',
                    'Title' => 'External Movie',
                    'Year' => '2023',
                    'Poster' => 'https://example.com/poster.jpg',
                ]],
            ]),
        ]);

        $this->getJson('/api/external/search?type=movie&q=inception')
            ->assertOk()
            ->assertJsonPath('data.0.external_id', 'tt1234567');

        $this->actingAs(User::factory()->create(), 'sanctum')
            ->postJson('/api/external/import', [
                'type' => 'movie',
                'external_id' => 'tt1234567',
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'External Movie')
            ->assertJsonPath('data.creator', 'A Director');
    }

    public function test_application_keeps_only_review_tables_in_its_data_page(): void
    {
        foreach ([
            'students',
            'groups',
            'majors',
            'marks',
            'subjects',
            'subj_teaches',
            'teaches',
            'failed_jobs',
            'password_resets',
        ] as $table) {
            $this->assertFalse(Schema::hasTable($table), "Legacy table [{$table}] should not exist.");
        }

        $this->get('/data')
            ->assertOk()
            ->assertSee('หนังสือและภาพยนตร์')
            ->assertSee('รีวิว')
            ->assertDontSee('นักเรียน');
    }

    public function test_demo_seeder_creates_ten_movies_and_ten_users_idempotently(): void
    {
        $this->seed(ReviewDemoSeeder::class);
        $this->seed(ReviewDemoSeeder::class);

        $this->assertDatabaseCount('users', 10);
        $this->assertDatabaseCount('media_items', 10);
        $this->assertDatabaseHas('users', ['email' => 'reader01@example.test']);
        $this->assertDatabaseHas('media_items', [
            'type' => 'movie',
            'external_source' => 'review_demo',
            'external_id' => 'movie-01',
        ]);
        $demoReviews = Review::whereIn(
            'media_item_id',
            MediaItem::where('external_source', 'review_demo')->select('id')
        );
        $this->assertGreaterThanOrEqual(30, (clone $demoReviews)->count());
        $this->assertLessThanOrEqual(50, (clone $demoReviews)->count());
        $this->assertSame(0, (clone $demoReviews)
            ->whereNotIn('user_id', User::where('email', 'like', 'reader%@example.test')->select('id'))
            ->count());
        $this->assertSame(0, (clone $demoReviews)
            ->select('media_item_id', 'user_id')
            ->groupBy('media_item_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count());
        $this->assertTrue(Hash::check(
            'password123',
            User::where('email', 'reader01@example.test')->value('password')
        ));
    }

    public function test_review_page_and_api_show_newest_reviews_first(): void
    {
        $user = User::factory()->create();
        $item = MediaItem::create(['type' => 'movie', 'title' => 'Review order test']);
        $olderDate = now()->subDay();

        Review::create([
            'media_item_id' => $item->id,
            'user_id' => $user->id,
            'rating' => 3,
            'body' => 'older-review-marker',
            'created_at' => $olderDate,
            'updated_at' => $olderDate,
        ]);

        $newerUser = User::factory()->create();
        Review::create([
            'media_item_id' => $item->id,
            'user_id' => $newerUser->id,
            'rating' => 5,
            'body' => 'newer-review-marker',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getJson("/api/items/{$item->id}/reviews")
            ->assertOk()
            ->assertSeeInOrder(['newer-review-marker', 'older-review-marker']);

        $this->get('/data')
            ->assertOk()
            ->assertSeeInOrder(['newer-review-marker', 'older-review-marker']);
    }

    public function test_user_review_endpoint_returns_movies_reviewed_by_user_id(): void
    {
        $user = User::factory()->create([
            'name' => 'Review',
            'surname' => 'Reader',
            'email' => 'review-reader@example.com',
        ]);
        $movie = MediaItem::create([
            'type' => 'movie',
            'title' => 'Movie reviewed by user',
            'creator' => 'Movie Director',
            'published_year' => 2024,
        ]);
        Review::create([
            'media_item_id' => $movie->id,
            'user_id' => $user->id,
            'rating' => 4.5,
            'body' => 'My movie review',
        ]);

        $this->getJson("/api/users/{$user->id}/reviews")
            ->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonMissingPath('user.email')
            ->assertJsonPath('data.0.user_id', $user->id)
            ->assertJsonPath('data.0.body', 'My movie review')
            ->assertJsonPath('data.0.media_item.id', $movie->id)
            ->assertJsonPath('data.0.media_item.title', 'Movie reviewed by user')
            ->assertJsonPath('data.0.media_item.type', 'movie');
    }

    public function test_all_users_endpoint_requires_token_and_hides_secrets(): void
    {
        $this->getJson('/api/users')->assertUnauthorized();

        $user = User::factory()->create([
            'name' => 'API',
            'surname' => 'Reader',
            'email' => 'api-reader@example.com',
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonPath('data.0.id', $user->id)
            ->assertJsonPath('data.0.email', 'api-reader@example.com')
            ->assertJsonMissingPath('data.0.password')
            ->assertJsonMissingPath('data.0.remember_token')
            ->assertJsonMissingPath('data.0.tokens');
    }

    public function test_single_user_endpoint_requires_token_and_returns_safe_user_fields(): void
    {
        $this->getJson('/api/users/1')->assertUnauthorized();

        $requester = User::factory()->create();
        $user = User::factory()->create([
            'name' => 'Single',
            'surname' => 'Reader',
            'email' => 'single-reader@example.com',
        ]);

        $this->actingAs($requester, 'sanctum')
            ->getJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Single')
            ->assertJsonPath('data.email', 'single-reader@example.com')
            ->assertJsonMissingPath('data.password')
            ->assertJsonMissingPath('data.remember_token');

        $this->actingAs($requester, 'sanctum')
            ->getJson('/api/users/999999')
            ->assertNotFound();
    }
}
