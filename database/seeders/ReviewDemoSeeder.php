<?php

namespace Database\Seeders;

use App\Models\MediaItem;
use App\Models\Review;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ReviewDemoSeeder extends Seeder
{
    public function run()
    {
        $movieFaker = Faker::create();
        $reviewFaker = Faker::create('th_TH');
        $reviewBodies = [
            'เนื้อเรื่องสนุก ดูเพลิน และน่าติดตาม',
            'นักแสดงถ่ายทอดอารมณ์ได้ดี ประทับใจมาก',
            'ภาพสวยและบรรยากาศของเรื่องทำได้ดี',
            'ช่วงต้นสนุกมาก แต่ช่วงท้ายดำเนินเรื่องช้าไปนิด',
            'เป็นหนังที่ดูแล้วรู้สึกคุ้มค่า อยากแนะนำให้ลองดู',
            'พล็อตน่าสนใจ มีจุดหักมุมที่คาดไม่ถึง',
            'โดยรวมทำได้ดี เพลงประกอบเข้ากับเรื่องมาก',
            'ดูสนุกกว่าที่คิด เป็นอีกเรื่องที่ชอบ',
            'เนื้อเรื่องอบอุ่นและทำให้ประทับใจ',
            'การดำเนินเรื่องดี ดูต่อเนื่องจนจบ',
        ];
        $users = collect();
        $movies = collect();

        for ($number = 1; $number <= 10; $number++) {
            $suffix = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

            $generatedUser = User::factory()->make();

            $user = User::firstOrCreate(
                ['email' => "reader{$suffix}@example.test"],
                [
                    'name' => $generatedUser->name,
                    'surname' => $generatedUser->surname,
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );
            $users->push($user);

            $movie = MediaItem::firstOrCreate(
                [
                    'external_source' => 'review_demo',
                    'external_id' => "movie-{$suffix}",
                ],
                [
                    'type' => 'movie',
                    'title' => Str::title($movieFaker->unique()->words(3, true)),
                    'description' => $movieFaker->paragraph(),
                    'creator' => $movieFaker->name(),
                    'published_year' => $movieFaker->numberBetween(1980, (int) date('Y')),
                ]
            );
            $movies->push($movie);
        }

        foreach ($movies as $movie) {
            $movieNumber = (int) substr($movie->external_id, -2);
            $reviewerCount = 3 + ($movieNumber % 3);
            $startIndex = (($movieNumber - 1) * 3) % $users->count();

            for ($offset = 0; $offset < $reviewerCount; $offset++) {
                $user = $users[($startIndex + $offset) % $users->count()];

                Review::firstOrCreate(
                    [
                        'media_item_id' => $movie->id,
                        'user_id' => $user->id,
                    ],
                    [
                        'rating' => $reviewFaker->numberBetween(20, 50) / 10,
                        'body' => $reviewFaker->randomElement($reviewBodies),
                    ]
                );
            }
        }
    }
}
