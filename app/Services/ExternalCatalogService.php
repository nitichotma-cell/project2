<?php

namespace App\Services;

use App\Exceptions\ExternalCatalogException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class ExternalCatalogService
{
    public function search(string $type, string $query): array
    {
        if ($type === 'book') {
            $response = $this->request(
                fn () => Http::acceptJson()->timeout(10)->get(
                    'https://www.googleapis.com/books/v1/volumes',
                    ['q' => $query, 'maxResults' => 20]
                )
            );

            return collect($response->json('items', []))
                ->map(fn (array $item) => $this->bookData($item['id'], $item['volumeInfo'] ?? []))
                ->all();
        }

        $response = $this->request(
            fn () => Http::acceptJson()->timeout(10)->get('https://www.omdbapi.com/', [
                'apikey' => $this->omdbKey(),
                's' => $query,
                'type' => 'movie',
            ])
        );

        $this->assertOmdbSuccess($response);

        return collect($response->json('Search', []))
            ->map(fn (array $movie) => $this->movieData($movie))
            ->all();
    }

    public function find(string $type, string $externalId): array
    {
        if ($type === 'book') {
            $response = $this->request(
                fn () => Http::acceptJson()->timeout(10)->get(
                    'https://www.googleapis.com/books/v1/volumes/'.rawurlencode($externalId)
                )
            );
            $book = $response->json();

            return $this->bookData($book['id'], $book['volumeInfo'] ?? []);
        }

        $response = $this->request(
            fn () => Http::acceptJson()->timeout(10)->get('https://www.omdbapi.com/', [
                'apikey' => $this->omdbKey(),
                'i' => $externalId,
                'plot' => 'full',
            ])
        );
        $this->assertOmdbSuccess($response);

        return $this->movieData($response->json());
    }

    private function request(callable $send): Response
    {
        try {
            $response = $send();
        } catch (ConnectionException $exception) {
            throw new ExternalCatalogException(
                'ไม่สามารถเชื่อมต่อบริการข้อมูลภายนอกได้',
                0,
                $exception
            );
        }

        if (! $response->successful()) {
            throw new ExternalCatalogException('บริการข้อมูลภายนอกตอบกลับข้อผิดพลาด');
        }

        return $response;
    }

    private function omdbKey(): string
    {
        $key = config('services.omdb.key');

        if (! is_string($key) || $key === '') {
            throw new ExternalCatalogException('กรุณากำหนด OMDB_API_KEY ในไฟล์ .env');
        }

        return $key;
    }

    private function assertOmdbSuccess(Response $response): void
    {
        if ($response->json('Response') !== 'True') {
            $message = $response->json('Error', 'OMDb API request failed');

            throw new ExternalCatalogException($message);
        }
    }

    private function bookData(string $id, array $info): array
    {
        $year = null;
        if (preg_match('/^\d{4}/', $info['publishedDate'] ?? '', $matches)) {
            $year = (int) $matches[0];
        }

        return [
            'type' => 'book',
            'external_source' => 'google_books',
            'external_id' => $id,
            'title' => $info['title'] ?? 'Untitled',
            'description' => $info['description'] ?? null,
            'creator' => isset($info['authors']) ? implode(', ', $info['authors']) : null,
            'published_year' => $year,
            'cover_url' => $info['imageLinks']['thumbnail'] ?? null,
        ];
    }

    private function movieData(array $movie): array
    {
        $year = null;
        if (preg_match('/^\d{4}/', $movie['Year'] ?? '', $matches)) {
            $year = (int) $matches[0];
        }

        return [
            'type' => 'movie',
            'external_source' => 'omdb',
            'external_id' => $movie['imdbID'],
            'title' => $movie['Title'],
            'description' => ($movie['Plot'] ?? null) === 'N/A' ? null : ($movie['Plot'] ?? null),
            'creator' => ($movie['Director'] ?? null) === 'N/A' ? null : ($movie['Director'] ?? null),
            'published_year' => $year,
            'cover_url' => ($movie['Poster'] ?? null) === 'N/A' ? null : ($movie['Poster'] ?? null),
        ];
    }
}
