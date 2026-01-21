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
Engagement (ER rate):
```
ER = (Like + Comment + Save + Share) / Reach * 100
```
Bobot poin berdasarkan ER:
- < 1%: 0 poin
- >= 1%: 10 poin
- >= 3%: 30 poin
- >= 6%: 50 poin

Contoh:
- Like 120 + Comment 30 + Save 50 + Share 20 = 220 interaksi
- Reach 4.000
- ER = (220 / 4.000) * 100 = 5.5% -> 30 poin

Contoh 0 poin:
- Like 5 + Comment 2 + Save 1 + Share 2 = 10 interaksi
- Reach 2.000
- ER = (10 / 2.000) * 100 = 0.5% -> 0 poin

Contoh viral:
- Like 400 + Comment 80 + Save 120 + Share 50 = 650 interaksi
- Reach 8.000
- ER = (650 / 8.000) * 100 = 8.125% -> 50 poin

Konversi:
- Closing: amount * bobot closing
- Lead: amount * bobot lead

Bobot poin dapat diubah di menu Settings Poin (tabel `point_settings`), termasuk ambang ER.

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
