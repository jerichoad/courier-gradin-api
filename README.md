# Courier Master API

REST API for courier master data, built with Laravel. Backend only: no authentication, no authorization, no frontend. Every endpoint returns JSON.


## Requirements

- PHP 8.3 or newer (`composer.json` requires `^8.3`)
- Composer
- MySQL or PostgreSQL
- `pdo_sqlite` + `sqlite3` PHP extensions, for running the test suite in memory

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Set the database connection in `.env`, then:

```bash
php artisan migrate
php artisan db:seed --class=CourierSeeder   # optional, 50 dummy couriers
php artisan serve
```

The API is then available at `http://localhost:8000/api/couriers`.

## Database

Table `m_courier`, primary key `courier_id`.

| Column | Type | Nullable | Notes |
|--------|------|----------|-------|
| courier_id | BIGINT | No | Primary key |
| courier_code | VARCHAR(20) | No | Unique |
| courier_name | VARCHAR(150) | No | Indexed |
| courier_phone | VARCHAR(30) | Yes | |
| courier_email | VARCHAR(100) | Yes | |
| courier_level | TINYINT | No | 1 to 5, indexed |
| courier_address | TEXT | Yes | |
| is_active | BOOLEAN | No | Defaults to true |
| created_at | TIMESTAMP | No | |
| updated_at | TIMESTAMP | No | |

Naming follows the convention in the PRD: `m_` prefix for master tables, `<entity>_id` for primary keys.

## Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/couriers` | List couriers |
| GET | `/api/couriers/{courier_id}` | Courier detail |
| POST | `/api/couriers` | Create courier |
| PUT | `/api/couriers/{courier_id}` | Update courier |
| DELETE | `/api/couriers/{courier_id}` | Delete courier |

### List query parameters

| Parameter | Example | Behaviour |
|-----------|---------|-----------|
| `page` | `?page=2` | Laravel pagination |
| `per_page` | `?per_page=25` | 1 to 100, defaults to 15 |
| `search` | `?search=budi agung` | Each keyword matched against `courier_name` with `AND` |
| `level` | `?level=2,3` | `WHERE courier_level IN (2,3)`, values 1 to 5 |
| `sort` | `?sort=-created_at` | Prefix `-` means DESC, defaults to `courier_name` ASC |

Search is tokenized, so `search=budi agung` matches `Budiono Hadi Agung`:

```sql
WHERE courier_name LIKE '%budi%' AND courier_name LIKE '%agung%'
```

Sortable columns: `courier_id`, `courier_code`, `courier_name`, `courier_level`, `created_at`, `updated_at`. Anything else falls back to the default sort.

## Validation

| Field | Rule |
|-------|------|
| courier_code | required, string, max 20, unique (ignores itself on update) |
| courier_name | required, string, min 3, max 150 |
| courier_phone | nullable, string, max 30 |
| courier_email | nullable, email, max 100 |
| courier_level | required, integer, between 1 and 5 |
| courier_address | nullable, string |
| is_active | boolean |

Query parameters on the list endpoint are validated too, and return the same error envelope.

## Response format

Success:

```json
{
  "success": true,
  "message": "Courier created successfully.",
  "data": {}
}
```

Validation error, HTTP 422:

```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {}
}
```

Not found, HTTP 404:

```json
{
  "success": false,
  "message": "Courier not found."
}
```

On the list endpoint, `data` holds the Laravel paginator payload, so the records sit in `data.data` alongside `data.current_page`, `data.per_page`, and `data.total`.

## Project structure

```
app/
├── Http/
│   ├── Controllers/
│   │      CourierController.php
│   ├── Requests/
│   │      ApiFormRequest.php          # shared authorize + 422 envelope
│   │      IndexCourierRequest.php     # query parameter validation
│   │      StoreCourierRequest.php
│   │      UpdateCourierRequest.php
│   └── Resources/
│          CourierResource.php         # response field shape
└── Models/
       Courier.php                     # custom primary key, search + level scopes

database/
├── factories/CourierFactory.php
├── migrations/
└── seeders/CourierSeeder.php

documentation-api/                     # Bruno API collection
tests/Feature/CourierApiTest.php
```

## Tests

```bash
php artisan test
```

The suite runs against an in-memory SQLite database, configured in `phpunit.xml`.

## API collection

`documentation-api/` is a [Bruno](https://usebruno.com) collection in the OpenCollection YAML format. Open the folder in Bruno, or run it from the CLI:

```bash
cd documentation-api
npx @usebruno/cli run Courier --env Local
```

Requests are sequenced so the folder runs end to end: the create request stores `courierId` as a runtime variable, later requests reuse it, and the delete requests clean up afterwards. `environments/Local.yml` points at `http://localhost:8000`; fill in `environments/Production.yml` before using it.

## Notes

- Frontend scaffolding (`resources/`, Vite, `package.json`) was removed, since the project is API only. `routes/web.php` holds no routes; the health check at `/up` still works.
- `documentation-api/ai-instructions.md` documents the Bruno YAML format used by the collection.
