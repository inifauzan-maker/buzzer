# Dokumentasi SIVMI Buzzer Marketing

## Ringkasan
Aplikasi dashboard untuk manajemen performa tim buzzer marketing dengan input aktivitas harian, verifikasi berjenjang, perhitungan poin, leaderboard, dan notifikasi.

## Role dan Akses
- Superadmin: kelola tim, user, settings poin, approve final.
- Leader: review aktivitas/konversi anggota timnya.
- Staff: input aktivitas/konversi, lihat poin pribadi.
- Guest: hanya lihat data (read-only).

## Instalasi Singkat
1. Copy `.env.example` ke `.env`, sesuaikan koneksi database.
2. Jalankan `php artisan migrate --seed`.
3. Jalankan `php artisan storage:link`.
4. Jalankan `php artisan serve` (atau gunakan Laragon).

## Akun Seeder (contoh)
- admin@sivmi.test / password (superadmin)
- leader.alpha@sivmi.test / password (leader)
- staff.alpha1@sivmi.test / password (staff)

Data seeder tim dan anggota bisa diubah di:
`database/seeders/data/team-members.php`

## Alur Verifikasi
1. Staff input aktivitas/konversi -> status `Pending`.
2. Leader review -> status `Reviewed`.
3. Superadmin approve -> status `Verified`, poin dihitung.

## Perhitungan Poin
Engagement:
```
(Share * 5) + (Save * 3) + (Comment * 2) + (Like * 1) + (Reach * 0.001)
```
Hasil engagement dikalikan grade:
- A = 1.2
- B = 1.0
- C = 0.8

Konversi:
- Closing: amount * bobot closing
- Lead: amount * bobot lead

Bobot poin dapat diubah di menu Settings Poin (tabel `point_settings`).

## Anti-Duplikat Post
Sistem menolak post duplikat per tim+platform berdasarkan:
- `platform_post_id` dari URL (IG/TT/YT/FB).
- Jika tidak ada ID, gunakan `normalized_post_url`.

## Timezone
Default timezone di `.env`:
```
APP_TIMEZONE=Asia/Jakarta
```

## Notifikasi
Notifikasi tersimpan di tabel `notifications`:
- Dibuat saat approve/reject aktivitas atau konversi.
- Badge notifikasi tampil di topbar.
- Halaman notifikasi: `/notifications`.

## Dashboard
Menampilkan:
- Heatmap kontribusi 12 bulan terakhir.
- Poin total (donut), aktivitas vs konversi.
- Leads harian.
- Jumlah closing dan jumlah leads terverifikasi.
- Top Closing (User) dan Top Aktivitas (User).

## Halaman Utama
- Dashboard: `/`
- Aktivitas: `/activities`
- Konversi: `/conversions`
- Leaderboard: `/leaderboard`
- Tim (superadmin): `/teams`
- User (superadmin): `/users`
- Profil: `/profile`
- Notifikasi: `/notifications`

## Screenshot (Placeholder)
Simpan screenshot di folder berikut:
- `docs/screenshots/dashboard/` (Dashboard utama)
- `docs/screenshots/activities/` (Halaman Aktivitas)
- `docs/screenshots/conversions/` (Halaman Konversi)
- `docs/screenshots/teams/` (Manajemen Tim)
- `docs/screenshots/users/` (Manajemen User)
- `docs/screenshots/profile/` (Profil user)
- `docs/screenshots/notifications/` (Halaman Notifikasi)

Contoh nama file: `desktop.png`, `mobile.png`, `detail.png`.

## Detail API (Route Web)
Semua endpoint berbasis web route (Laravel). Ringkasan:
- Auth:
  - `GET /login` (Form login)
  - `POST /login` (Proses login)
  - `POST /logout` (Logout)
- Profil:
  - `GET /profile` (Profil sendiri)
  - `GET /profiles/{user}` (Lihat profil user lain)
  - `PATCH /profile` (Update profil sendiri)
- Aktivitas:
  - `GET /activities` (List)
  - `GET /activities/create` (Form input)
  - `POST /activities` (Simpan)
  - `POST /activities/{activity}/verify` (Review/Approve)
  - `POST /activities/{activity}/reject` (Reject)
- Konversi:
  - `GET /conversions` (List)
  - `GET /conversions/create` (Form input)
  - `POST /conversions` (Simpan)
  - `POST /conversions/{conversion}/verify` (Review/Approve)
  - `POST /conversions/{conversion}/reject` (Reject)
- Tim (superadmin):
  - `GET /teams` (List)
  - `POST /teams` (Tambah)
  - `PATCH /teams/{team}` (Edit)
  - `DELETE /teams/{team}` (Hapus, jika tidak ada data terkait)
  - `POST /teams/members` (Tambah anggota)
- User (superadmin):
  - `GET /users` (List)
  - `POST /users` (Tambah)
  - `PATCH /users/{user}` (Edit)
  - `DELETE /users/{user}` (Hapus)
- Notifikasi:
  - `GET /notifications` (List)
  - `POST /notifications/read-all` (Tandai dibaca)

## Scheduler (Reminder)
Aktifkan scheduler agar reminder berjalan:
```
php artisan schedule:work
```

## Catatan Penting
- Hapus tim tidak diizinkan jika masih ada data terkait.
- Guest tidak bisa input atau approve.
- Leader hanya bisa review data dari timnya sendiri.
