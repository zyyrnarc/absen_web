# Mobile API

Base URL:

```text
/api/mobile
```

## Tujuan

Dokumen ini dipakai sebagai kontrak integrasi antara backend Laravel dan aplikasi mobile.

Anda cukup mengerjakan backend di project ini. Aplikasi mobile nanti hanya perlu:

- login ke API
- mengirim request ke endpoint yang sesuai
- menampilkan response JSON dari backend

## Database

Fitur mobile memakai database yang sama dengan panel admin. Tidak perlu database terpisah.

Tabel utama yang dipakai:

- `users`
- `intern_profiles`
- `mobile_auth_tokens`
- `attendances`
- `intern_activities`
- `permits`

Artinya:

- admin membuat dan mengubah akun intern dari panel admin
- mobile membaca dan memperbarui data intern dari tabel yang sama
- absensi, aktivitas, permit, dan profil tetap sinkron dengan panel admin

## Persiapan

```text
php artisan migrate
php artisan db:seed
php artisan storage:link
```

Atau jika hanya ingin akun contoh mobile tanpa master data tambahan:

```text
php artisan db:seed --class=AdminAndInternSeeder
php artisan storage:link
```

Akun contoh:

```text
Admin  : admin@absen-web.test / password
Intern : intern@absen-web.test / password
```

Seeder default juga menambahkan data contoh:

- absensi beberapa hari kerja
- aktivitas mingguan contoh
- permit `pending` dan `approved`

## Header

Semua endpoint selain `POST /login` memakai header:

```text
Authorization: Bearer {token}
Accept: application/json
```

Untuk endpoint upload file, tetap kirim bearer token dan gunakan `multipart/form-data`.

## Ringkasan Endpoint

### Auth

- `POST /login`
- `GET /me`
- `POST /logout`

### Dashboard

- `GET /dashboard`

### Profile

- `GET /profile`
- `PUT /profile`
- `PUT /profile/password`

### Attendance

- `GET /attendances`
- `GET /attendances/monthly`
- `GET /attendances/monthly/export`
- `POST /attendances/check-in`
- `POST /attendances/check-out`

### Activities

- `GET /activities`
- `GET /activities/weekly`
- `GET /activities/weekly/export`
- `POST /activities`
- `PUT /activities/{id}`
- `DELETE /activities/{id}`

### Permits

- `GET /permits/meta`
- `GET /permits`
- `POST /permits`

## Auth

### `POST /login`

Request:

```json
{
  "email": "intern@absen-web.test",
  "password": "password"
}
```

Response sukses:

```json
{
  "message": "Login berhasil.",
  "token": "TOKEN_BEARER",
  "user": {
    "id": 3,
    "name": "Mahasiswa Magang",
    "initial": "M",
    "email": "intern@absen-web.test",
    "username": null,
    "phone": "081200000002",
    "role": "intern",
    "is_active": true,
    "avatar_url": null,
    "profile": {
      "student_id": "MAGANG-001",
      "institution_name": "Universitas Contoh",
      "major": "Teknik Informatika",
      "study_program": "D4 RPL",
      "division": "IT Support",
      "supervisor_name": "Pembimbing Lapangan",
      "gender": "Female",
      "internship_start": "2026-04-01",
      "internship_end": "2026-07-31",
      "status": "active"
    }
  }
}
```

### `GET /me`

Dipakai untuk mengambil data user login saat aplikasi mobile dibuka ulang.

### `POST /logout`

Dipakai untuk menghapus token aktif dari perangkat mobile.

## Dashboard

### `GET /dashboard`

Dipakai untuk layar Home.

Contoh response:

```json
{
  "current_date": {
    "iso": "2026-04-29",
    "label": "Wednesday, April 29, 2026"
  },
  "date": "2026-04-29",
  "user": {
    "id": 3,
    "name": "Vivi Lestari",
    "initial": "V",
    "email": "intern@absen-web.test",
    "username": null,
    "phone": "081200000002",
    "role": "intern",
    "is_active": true,
    "profile": {
      "student_id": "MAGANG-001",
      "institution_name": "Politeknik Negeri Indramayu",
      "major": "Teknik Informatika",
      "study_program": "D4 RPL",
      "division": "IT Support",
      "supervisor_name": "Pembimbing Lapangan",
      "status": "active"
    }
  },
  "attendance_today": null,
  "today_activity_count": 0,
  "monthly_attendance_count": 0,
  "monthly_statistics": {
    "present": 0,
    "permit": 0,
    "absent": 21,
    "workdays": 21
  },
  "quick_actions": {
    "can_check_in": true,
    "can_check_out": false,
    "can_submit_permit": true,
    "can_submit_activity": true
  },
  "recent_activities": []
}
```

## Profile

Layar `Profile`, `Edit Profile`, dan `Reset Password` memakai database admin yang sama.

### `GET /profile`

Dipakai untuk layar Profile.

Contoh response:

```json
{
  "user": {
    "id": 3,
    "name": "Vivi Lestari",
    "initial": "V",
    "email": "vlestari924@gmail.com",
    "username": null,
    "phone": "08123456789",
    "role": "intern",
    "is_active": true,
    "avatar_url": null,
    "profile": {
      "student_id": "2305055",
      "study_program": "D4 RPL",
      "major": "Teknik Informatika",
      "institution_name": "Politeknik Negeri Indramayu",
      "gender": "Female",
      "division": "IT Support",
      "supervisor_name": "Pembimbing Lapangan",
      "status": "active"
    },
    "badges": {
      "student_id": "2305055",
      "study_program": "D4 RPL"
    },
    "personal_information": {
      "email": "vlestari924@gmail.com",
      "study_program": "D4 RPL",
      "major": "Teknik Informatika",
      "campus": "Politeknik Negeri Indramayu",
      "gender": "Female"
    }
  },
  "options": {
    "gender": [
      { "value": "Male", "label": "Male" },
      { "value": "Female", "label": "Female" }
    ]
  },
  "actions": {
    "can_edit_profile": true,
    "can_reset_password": true,
    "logout_endpoint": "http://localhost/api/mobile/logout"
  }
}
```

### `PUT /profile`

Dipakai untuk layar Edit Profile.

Gunakan `multipart/form-data` jika ada avatar.

Field:

```text
name
student_id
study_program
major
institution_name
gender
phone (optional)
division (optional)
supervisor_name (optional)
avatar (optional file image)
```

Contoh field:

```text
name=Vivi Lestari
student_id=2305055
study_program=D4 RPL
major=Teknik Informatika
institution_name=Politeknik Negeri Indramayu
gender=Female
phone=08123456789
```

### `PUT /profile/password`

Dipakai untuk layar Reset Password.

Request:

```json
{
  "current_password": "passwordlama",
  "password": "passwordbaru",
  "password_confirmation": "passwordbaru"
}
```

## Attendance

### `GET /attendances`

Dipakai untuk daftar absensi biasa.

Query opsional:

```text
month=4
year=2026
```

### `GET /attendances/monthly?month=4&year=2026`

Dipakai untuk layar `Monthly Absence`.

Contoh response:

```json
{
  "month": {
    "number": 4,
    "year": 2026,
    "label": "April 2026",
    "start_date": "2026-04-01",
    "end_date": "2026-04-30",
    "previous": {
      "month": 3,
      "year": 2026
    },
    "next": {
      "month": 5,
      "year": 2026
    }
  },
  "summary": {
    "present": 1,
    "permit": 0,
    "absent": 1,
    "workdays": 22
  },
  "calendar": {
    "weekdays": ["S", "M", "T", "W", "T", "F", "S"],
    "start_weekday_index": 3,
    "days": [
      {
        "date": "2026-04-27",
        "day": 27,
        "weekday": "Mon",
        "status": "present",
        "is_today": false,
        "is_future": false,
        "is_weekend": false,
        "check_in_time": "08:00",
        "check_out_time": "16:00",
        "permit_type": null,
        "permit_status": null
      }
    ],
    "legend": [
      { "key": "present", "label": "Present", "color": "green" },
      { "key": "permit", "label": "Permit", "color": "orange" },
      { "key": "absent", "label": "Absent", "color": "red" }
    ]
  },
  "today_attendance": null,
  "actions": {
    "can_check_in": true,
    "can_check_out": false,
    "can_submit_permit": true,
    "export_endpoint": "http://localhost/api/mobile/attendances/monthly/export?month=4&year=2026"
  }
}
```

### `GET /attendances/monthly/export?month=4&year=2026`

Dipakai untuk tombol `Export PDF`.

Endpoint ini mengembalikan payload siap generate PDF dari mobile.

### `POST /attendances/check-in`

Request:

```json
{
  "latitude": -6.2000000,
  "longitude": 106.8166660,
  "notes": "Datang ke kantor"
}
```

### `POST /attendances/check-out`

Request:

```json
{
  "latitude": -6.2000000,
  "longitude": 106.8166660,
  "notes": "Pulang"
}
```

## Activities

### `GET /activities`

Dipakai untuk list aktivitas biasa.

Query opsional:

```text
month=4
year=2026
```

### `GET /activities/weekly?date=2026-04-29`

Dipakai untuk layar `Weekly Activity`.

Contoh response:

```json
{
  "week": {
    "reference_date": "2026-04-29",
    "start_date": "2026-04-27",
    "end_date": "2026-05-01",
    "label": "April 27, 2026 - May 1, 2026",
    "previous_start_date": "2026-04-20",
    "next_start_date": "2026-05-04"
  },
  "summary": {
    "count": 2
  },
  "actions": {
    "can_create": true,
    "export_endpoint": "http://localhost/api/mobile/activities/weekly/export?date=2026-04-29"
  },
  "items": [
    {
      "id": 1,
      "attendance_id": 1,
      "activity_date": "2026-04-27",
      "activity_day": "Monday",
      "title": "Membuat API absensi",
      "description": "Mengerjakan endpoint login, absensi, dan aktivitas.",
      "status": "submitted",
      "times": "08:00 - 16:00",
      "can_edit": true,
      "can_delete": true,
      "created_at": "2026-04-27 08:00:00",
      "updated_at": "2026-04-27 08:00:00"
    }
  ]
}
```

### `GET /activities/weekly/export?date=2026-04-29`

Dipakai untuk tombol `Export to PDF`.

Endpoint ini mengembalikan payload siap generate PDF dari mobile.

### `POST /activities`

Request:

```json
{
  "attendance_id": 1,
  "activity_date": "2026-04-28",
  "title": "Membuat API absensi",
  "description": "Mengerjakan endpoint login, absensi, dan aktivitas.",
  "status": "submitted"
}
```

### `PUT /activities/{id}`

Body sama seperti `POST /activities`.

### `DELETE /activities/{id}`

Dipakai untuk tombol hapus aktivitas.

## Permits

### `GET /permits/meta`

Dipakai untuk isi dropdown `Permit Type` dan info lampiran.

Contoh response:

```json
{
  "permit_types": [
    { "value": "Sakit", "label": "Sakit" },
    { "value": "Izin", "label": "Izin" },
    { "value": "Keperluan Kampus", "label": "Keperluan Kampus" },
    { "value": "Acara Keluarga", "label": "Acara Keluarga" },
    { "value": "Lainnya", "label": "Lainnya" }
  ],
  "attachment": {
    "allowed_extensions": ["pdf", "jpg", "jpeg", "png"],
    "max_size_mb": 4,
    "is_optional": true
  }
}
```

### `GET /permits`

Query opsional:

```text
month=4
year=2026
```

### `POST /permits`

Gunakan `multipart/form-data` jika ada lampiran.

Field:

```text
type
permit_date
reason
attachment (optional)
```

Contoh:

```text
type=Izin
permit_date=2026-04-29
reason=Keperluan pribadi
attachment=(optional file)
```

## Error Umum

### Token tidak valid

Status:

```text
401 Unauthorized
```

Body:

```json
{
  "message": "Token tidak valid atau sudah kedaluwarsa."
}
```

### Validasi gagal

Status:

```text
422 Unprocessable Entity
```

Contoh body:

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": [
      "Pesan error"
    ]
  }
}
```

### Password salah saat reset password

Status:

```text
422 Unprocessable Entity
```

Body:

```json
{
  "message": "Password saat ini tidak sesuai.",
  "errors": {
    "current_password": [
      "Password saat ini tidak sesuai."
    ]
  }
}
```

## Integrasi Mobile

Urutan integrasi yang disarankan:

1. `POST /login`
2. Simpan `token`
3. `GET /dashboard`
4. `GET /profile`
5. Hubungkan layar:

- Home ke `GET /dashboard`
- Monthly Absence ke `GET /attendances/monthly`
- Weekly Activity ke `GET /activities/weekly`
- Permit Application ke `GET /permits/meta` dan `POST /permits`
- Profile ke `GET /profile`
- Edit Profile ke `PUT /profile`
- Reset Password ke `PUT /profile/password`

## Catatan

- Semua data mobile sudah menyatu dengan data admin.
- Backend di project ini sudah cukup; Anda tidak perlu masuk ke project mobile untuk membuat logic backend lagi.
- Tim mobile tinggal menghubungkan UI ke endpoint yang ada.
