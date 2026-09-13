# PRD - Courier Master API

## Document Information

| Item | Value |
|------|-------|
| Project | Courier Master API |
| Version | 1.0 |
| Author | Jericho |
| Framework | Laravel 12 |
| Database | MySQL / PostgreSQL |
| API Type | REST API |
| Authentication | None |
| UI | None |

---

# 1. Overview

Membangun REST API sederhana untuk mengelola **Master Data Courier**.

Project ini hanya berfokus pada backend API tanpa authentication, authorization, ataupun frontend.

Seluruh endpoint mengembalikan response JSON.

---

# 2. Scope

## Included

- Courier Master Table
- Migration
- Model
- CRUD API
- Request Validation
- Pagination
- Filtering
- Searching
- Sorting
- Feature Test
- Unit Test (jika diperlukan)

## Excluded

- Authentication
- Authorization
- Role Permission
- Frontend
- Swagger/OpenAPI
- Docker
- Queue
- Cache
- Soft Delete
- Audit Log

---

# 3. Naming Convention

Untuk menjaga konsistensi project digunakan standar berikut.

## Database Table

Master Data

```
m_courier
```

Contoh table lain di masa depan

```
m_product
m_customer
m_supplier
```

Setup Table

```
s_company
s_branch
```

Transaction Table

```
t_order
t_invoice
```

---

## Primary Key

Seluruh tabel menggunakan format

```
courier_id
product_id
customer_id
```

bukan

```
id
```

---

## Foreign Key

Mengikuti nama primary key

Contoh

```
courier_id
customer_id
branch_id
```

---

## Timestamp

Menggunakan bawaan Laravel

```
created_at
updated_at
```

---

# 4. Database Design

## Table

```
m_courier
```

| Column | Type | Nullable | Description |
|---------|------|----------|-------------|
| courier_id | BIGINT | No | Primary Key |
| courier_code | VARCHAR(20) | No | Unique code |
| courier_name | VARCHAR(150) | No | Courier Name |
| courier_phone | VARCHAR(30) | Yes | Phone Number |
| courier_email | VARCHAR(100) | Yes | Email |
| courier_level | TINYINT | No | Level 1-5 |
| courier_address | TEXT | Yes | Address |
| is_active | BOOLEAN | No | Active Status |
| created_at | TIMESTAMP | No | Created Time |
| updated_at | TIMESTAMP | No | Updated Time |

---

# 5. Business Rules

## Courier Level

Valid value

```
1
2
3
4
5
```

Selain angka tersebut harus ditolak.

---

## Courier Code

Harus unik.

Contoh

```
CR001
CR002
CR003
```

---

## Courier Name

Wajib diisi.

Minimal

```
3 karakter
```

---

## Email

Opsional.

Jika diisi harus valid.

---

## Phone

Opsional.

Maksimal 30 karakter.

---

# 6. API Endpoints

## GET

```
GET /api/couriers
```

Menampilkan daftar courier.

### Query Parameters

Pagination

```
?page=1
```

Search

```
?search=budi+agung
```

Harus dapat menemukan

```
Budiono Hadi Agung
```

Sorting (default)

```
courier_name ASC
```

Override sorting

```
?sort=created_at
```

atau

```
?sort=-created_at
```

(Level implementasi bebas, selama requirement terpenuhi.)

Filter Level

```
?level=2,3
```

Menghasilkan courier level

```
2
3
```

---

## GET Detail

```
GET /api/couriers/{courier_id}
```

Mengembalikan seluruh informasi courier.

---

## POST

```
POST /api/couriers
```

Membuat courier baru.

---

## PUT

```
PUT /api/couriers/{courier_id}
```

Update courier.

---

## DELETE

```
DELETE /api/couriers/{courier_id}
```

Menghapus courier.

---

# 7. Validation Rules

## Store

| Field | Rule |
|---------|------|
| courier_code | required, unique |
| courier_name | required, string, min:3, max:150 |
| courier_phone | nullable, max:30 |
| courier_email | nullable, email |
| courier_level | required, integer, between:1,5 |
| courier_address | nullable |
| is_active | boolean |

---

## Update

Sama seperti Store, namun unique mengabaikan record yang sedang diedit.

---

# 8. Pagination

Gunakan Laravel Pagination.

Default

```
15 items
```

Response mengikuti format pagination Laravel.

---

# 9. Search Behaviour

Search dilakukan pada

```
courier_name
```

Menggunakan

```
LIKE %keyword%
```

Contoh

Search

```
agung
```

Match

```
Budiono Agung
```

Search

```
budi agung
```

Harus tetap dapat menemukan

```
Budiono Hadi Agung
```

Implementasi dapat menggunakan pemisahan keyword (tokenized search) sehingga setiap kata dicari secara independen dengan kondisi `AND`, misalnya:

```
WHERE courier_name LIKE '%budi%'
AND courier_name LIKE '%agung%'
```

Pendekatan ini lebih fleksibel dibanding mencocokkan string utuh.

---

# 10. Sorting

Default

```
ORDER BY courier_name ASC
```

Jika frontend mengirim

```
sort=created_at
```

Maka sorting berubah menjadi

```
ORDER BY created_at ASC
```

Jika

```
sort=-created_at
```

Maka

```
ORDER BY created_at DESC
```

---

# 11. Filtering

Filter level

```
level=2,3
```

Diubah menjadi

```
WHERE courier_level IN (2,3)
```

---

# 12. Response Format

Success

```json
{
    "success": true,
    "message": "Courier created successfully.",
    "data": {}
}
```

Validation Error

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {}
}
```

Not Found

```json
{
    "success": false,
    "message": "Courier not found."
}
```

---

# 13. Testing

Feature Test minimal mencakup

## Store

- berhasil membuat courier
- gagal validation
- courier_code duplicate

---

## Index

- pagination berjalan
- sorting default
- sorting created_at
- search courier
- filter level

---

## Show

- data ditemukan
- data tidak ditemukan

---

## Update

- update berhasil
- validation gagal
- unique validation

---

## Destroy

- data berhasil dihapus
- memastikan data sudah tidak ada di database

---

# 14. Suggested Folder Structure

```
app
├── Http
│   ├── Controllers
│   │      CourierController.php
│   ├── Requests
│   │      StoreCourierRequest.php
│   │      UpdateCourierRequest.php
│
├── Models
│      Courier.php

database
├── migrations

tests
├── Feature
│      CourierApiTest.php
```

---

# 15. Technical Notes

- Menggunakan Eloquent ORM.
- Validasi dipisahkan menggunakan Form Request (`StoreCourierRequest` dan `UpdateCourierRequest`) agar controller tetap bersih.
- Query filtering, searching, dan sorting disusun secara dinamis menggunakan `when()` agar mudah dibaca dan dikembangkan.
- Model menggunakan custom primary key (`courier_id`) dengan `$primaryKey = 'courier_id'`.
- Menggunakan Resource Controller (`index`, `show`, `store`, `update`, `destroy`).
- Seluruh endpoint mengembalikan response JSON.
- Mengikuti PSR-12 Coding Style serta best practice Laravel.

---

# 16. Acceptance Criteria

- Migration berhasil dijalankan.
- CRUD API berfungsi.
- Pagination tersedia.
- Search berfungsi.
- Filter Level berfungsi.
- Sorting default berdasarkan nama.
- Sorting berdasarkan tanggal dapat dilakukan melalui query parameter.
- Validasi input lengkap.
- Semua Feature Test berhasil dijalankan.
- Mengikuti standar penamaan tabel (`m_*`) dan primary key (`*_id`).