# Book and movie review API

All endpoints are under `/api`. Send and receive JSON. Endpoints that change data require a Sanctum bearer token:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

## Authentication

Register a user (registration returns a token):

```http
POST /api/register
```

```json
{
  "name": "Somchai",
  "surname": "Jaidee",
  "email": "somchai@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Log in with `POST /api/login` and `{ "email": "...", "password": "..." }`. Both registration and login return `token` and `user`. Use the token as a bearer token for protected endpoints. `POST /api/logout` revokes the current token.

## Items and reviews

| Method | Endpoint | Access | Description |
| --- | --- | --- | --- |
| GET | `/api/items` | Public | List all books and movies with `reviews_count` and `reviews_avg_rating` |
| GET | `/api/items/{id}` | Public | Get an item and its reviews |
| POST | `/api/items` | Token | Add an item |
| PUT/PATCH | `/api/items/{id}` | Token | Update an item |
| DELETE | `/api/items/{id}` | Token | Delete an item and its reviews |
| GET | `/api/items/{id}/reviews` | Public | List reviews for an item |
| GET | `/api/users/{id}/reviews` | Public | List reviews written by a user, including each item's details |
| GET | `/api/users` | Token | List user IDs, names, surnames, and emails |
| GET | `/api/users/{id}` | Token | Get one user's ID, name, surname, and email |
| POST | `/api/items/{id}/reviews` | Token | Add your review |
| PUT/PATCH | `/api/reviews/{id}` | Owner token | Update your review |
| DELETE | `/api/reviews/{id}` | Owner token | Delete your review |

An item accepts `type` (`book` or `movie`), `title`, and optional `description`, `creator`, `published_year`, and `cover_url`. A review accepts `rating` from 1 to 5 and an optional `body`. Each user can review an item once; they can update or delete only their own review.

Example item:

```json
{
  "type": "movie",
  "title": "Example Movie",
  "description": "A short synopsis",
  "creator": "Example Director",
  "published_year": 2025
}
```

## External catalogs

Search Google Books with `GET /api/external/search?type=book&q=harry+potter`. Search OMDb with `GET /api/external/search?type=movie&q=inception`. Search results include `external_id` values. Import a result with the authenticated endpoint:

```http
POST /api/external/import
```

```json
{
  "type": "book",
  "external_id": "Google-Books-volume-id"
}
```

Google Books search is public. OMDb requires an API key: obtain one from OMDb and set `OMDB_API_KEY` in the local `.env` file, then clear cached configuration with `php artisan config:clear`. Never commit the key.

## Setup

From the Laravel project directory, run `php artisan migrate` to create the review tables. The migrations `2026_10_15_000003_drop_legacy_student_tables` and `2026_10_15_000004_drop_unused_framework_tables` permanently drop the old `students`, `groups`, `majors`, `marks`, `subjects`, `subj_teaches`, `teaches`, `failed_jobs`, and `password_resets` tables and their data. Back up the database before applying them. User accounts, Sanctum tokens, and the review tables are retained. The `migrations` table is retained for Laravel. Do not use `migrate:refresh` on a database containing data you want to keep.

To add ten reusable demo movie records, ten demo reader accounts, and three to five randomized reviews per demo movie without deleting existing records, run `php artisan db:seed`. Demo reviews are linked to those users and movies, have ratings from 2.0 to 5.0, and the seeder will not create duplicate user/movie reviews when run again. The demo accounts use `reader01@example.test` through `reader10@example.test`; each has the password `password123`. Change these passwords before using the accounts outside a local demo.
