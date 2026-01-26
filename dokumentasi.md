# Dokumentasi SIVMI Buzzer Marketing

## Ringkasan
Aplikasi dashboard untuk manajemen performa tim buzzer marketing dengan input aktivitas harian, verifikasi berjenjang, perhitungan poin, leaderboard, dan notifikasi.

## Role dan Akses
- Superadmin: kelola tim, user, settings poin, approve final.
- Admin Ads: akses penuh modul Ads/Iklan.
- Campaign Planner: membuat dan mengelola rencana kampanye ads.
- Ads Specialist: input monitoring dan optimasi performa ads.
- Analyst: analisa & evaluasi performa ads.
- Management: melihat laporan ads.
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

## Modul Ads/Iklan (Manual Input)
Ringkas fitur:
- Perencanaan kampanye (brief, objective, target audiens, budget, jadwal, KPI per platform).
- Monitoring kampanye (tayangan, jangkauan, klik, leads, closing, engagement).
- Pelaporan & evaluasi (ringkasan per platform, grafik tren, target vs realisasi).

## Modul Akademik & Keuangan
### Alur Proses
1. Siswa
   - Mengisi biodata siswa (pendaftaran publik).
   - Biodata siswa masuk ke Bagian Administrasi (Data Siswa).
   - Siswa menerima jadwal KBM.
   - Siswa menerima laporan kemajuan siswa.
2. Bagian Administrasi
   - Menerima biodata siswa.
   - Memvalidasi biodata siswa.
   - Mencatat biodata siswa -> menghasilkan daftar siswa.
   - Mengirim invoice sesuai program bimbel via WhatsApp ke nomor orang tua/wali.
   - Membuat daftar kelas.
   - Daftar kelas dikirim ke Bagian Akademik.
   - Daftar kelas juga mendukung pembuatan jadwal KBM.
3. Bagian Akademik
   - Mengolah daftar kelas, data pelajaran, dan data pengajar.
   - Mencatat data pelajaran dan data pengajar -> menghasilkan daftar pelajaran & daftar pengajar.
   - Membuat jadwal KBM.
   - Membuat daftar absensi dan nilai siswa.
   - Menghasilkan laporan kemajuan siswa.
4. Pengajar
   - Menyediakan data pengajar.
   - Menerima jadwal KBM.
   - Mencatat absensi & nilai siswa.
   - Mengirim kembali daftar absensi dan nilai siswa ke Bagian Akademik.

### Output Utama
- Jadwal KBM (untuk siswa & pengajar).
- Laporan kemajuan siswa (untuk siswa).

### Matriks Proses (Ringkas)
| Pelaku | Kegiatan / Data yang Diolah | Hasil / Output | Diteruskan ke |
| --- | --- | --- | --- |
| Siswa | Mengisi biodata siswa | Biodata siswa | Bagian Administrasi |
| Siswa | Menerima jadwal KBM | Jadwal KBM | - |
| Siswa | Menerima laporan kemajuan siswa | Laporan Kemajuan Siswa | - |
| Bagian Administrasi | Menerima dan memvalidasi biodata siswa | Status validasi | - |
| Bagian Administrasi | Mencatat biodata siswa | Daftar Siswa | - |
| Bagian Administrasi | Mengirim invoice sesuai program bimbel | Invoice via WA | Orang Tua/Wali |
| Bagian Administrasi | Membuat daftar kelas | Daftar Kelas | Bagian Akademik |
| Bagian Akademik | Mengolah daftar kelas, data pelajaran, data pengajar | Daftar Pelajaran & Daftar Pengajar | - |
| Bagian Akademik | Membuat jadwal KBM | Jadwal KBM | Siswa & Pengajar |
| Bagian Akademik | Membuat daftar absensi & nilai siswa | Data Absensi dan Nilai | Pengajar & Administrasi |
| Bagian Akademik | Membuat laporan kemajuan siswa | Laporan Kemajuan Siswa | Siswa |
| Pengajar | Menyediakan data pengajar | Data Pengajar | Bagian Akademik |
| Pengajar | Menerima jadwal KBM | Jadwal KBM | - |
| Pengajar | Mencatat absensi & nilai siswa | Daftar Absensi dan Nilai Siswa | Bagian Akademik |

### Tabel Database (Akademik & Keuangan)
#### TSiswa (data siswa)
- Primary key: No_Regs
- Foreign key: Kd_Bimbel, Periode, Kd_Kelas

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | No_Regs | Varchar | 11 |
| 2 | Tgl_Daftar | Datetime | 8 |
| 3 | No_Urut | Varchar | 8 |
| 4 | Nama | Varchar | 25 |
| 5 | Jenis kelamin | Varchar | 5 |
| 6 | Tmpt_Lahir | Varchar | 20 |
| 7 | Tgl_Lahir | Datetime | 8 |
| 8 | No_WA | Varchar | 25 |
| 9 | Asal_Sekolah | Varchar | 25 |
| 10 | Nama_Ortu | Varchar | 25 |
| 11 | Alamat_Ortu | Varchar | 35 |
| 12 | No_WA_Ortu | Varchar | 25 |
| 13 | Pekerjaan_Ortu | Varchar | 25 |
| 14 | Informasi_VM | Varchar | 10 |
| 15 | Program_Bimbel | Varchar | 3 |
| 16 | Tahun_Ajaran | Varchar | 5 |
| 17 | Photo | Image | 16 |
| 18 | Status | Varchar | 10 |
| 19 | Status_Byr | Varchar | 5 |
| 20 | Tgl_Regs | Datetime | 8 |
| 21 | Tgl_Batal | Datetime | 8 |

#### TBimbel (program bimbel)
- Primary key: Kd_Bimbel

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | Kd_Bimbel | Varchar | 3 |
| 2 | Program_Bimbel | Varchar | 35 |
| 3 | Biaya_Bimbel | Money | 8 |
| 4 | Jml_Pertemuan | Int | 4 |

#### TKelas (kelas)
- Primary key: Kd_Kelas

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | Kd_Kelas | Varchar | 3 |
| 2 | Kelas | Varchar | 20 |

#### TPelajaran (pelajaran)
- Primary key: Kd_Pel

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | Kd_Pel | Varchar | 6 |
| 2 | Mata_Pelajaran | Varchar | 35 |

#### TPengajar (pengajar)
- Primary key: Kd_Pengajar

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | Kd_Pengajar | Varchar | 3 |
| 2 | Nama_Pengajar | Varchar | 30 |
| 3 | Alamat_Pengajar | Varchar | 35 |
| 4 | Telp | Varchar | 25 |
| 5 | Pendidikan | Varchar | 30 |

#### TThn_Ajaran (tahun ajaran)
- Primary key: Kd_Periode

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | Id_tahun | - | - |
| 2 | Periode | Varchar | 5 |
| 3 | Thn_Ajaran | Varchar | 9 |

#### TTo (try out)
- Primary key: Try_Out

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | Try_Out | Varchar | 5 |
| 2 | Tanggal | Datetime | 8 |

#### TRuang (ruang)
- Primary key: Ruang

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | Ruang | Varchar | 1 |

#### TPetugas (petugas)
- Primary key: Id_Petugas

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | Id_Petugas | Varchar | 5 |
| 2 | Nama | Varchar | 35 |
| 3 | Password | Varchar | 3 |
| 4 | Divisi | Varchar | 5 |

#### TAbsen (akumulasi absensi per bulan)
- Primary key: No_Regs, Bulan, Kd_Bimbel

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | No_Regs | Varchar | 11 |
| 2 | Bulan | Smallint | 2 |
| 3 | Program_Bimbel | Varchar | 3 |
| 4 | Total_Hadir | Int | 4 |

#### TAbsen_Siswa (absensi per pertemuan)
- Primary key: No_Regs, Bulan, Pertemuan

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | No_Regs | Varchar | 11 |
| 2 | Bulan | Smallint | 2 |
| 3 | Pertemuan | Int | 4 |
| 4 | Absensi | Int | 4 |

#### TBayar (akumulasi pembayaran)
- Primary key: No_Regs, Bulan, Kd_Bimbel

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | No_Regs | Varchar | 11 |
| 2 | Kd_Bimbel | Varchar | 2 |
| 3 | Biaya_Bimbel | Money | 8 |
| 4 | Total_Terbayar | Money | 8 |
| 5 | Discount | Float | 8 |
| 6 | Ket_Disc | Varchar | 20 |

#### TItemBayar (pembayaran angsuran)
- Primary key: No_Regs, Kd_Bimbel, Angsuran_Ke

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | No_Regs | Varchar | 11 |
| 2 | Kd_Bimbel | Varchar | 3 |
| 3 | Angsuran_Ke | Smallint | 2 |
| 4 | No_Kwit | Int | 4 |
| 5 | Besar_Bayar | Money | 8 |
| 6 | Jenis_Trans | Varchar | 10 |
| 7 | Tgl_Trans | Datetime | 8 |
| 8 | Id_Petugas | Varchar | 3 |

#### TJadwal (jadwal KBM)
- Primary key: Kd_Pel, Tanggal, Hari, Ruang, Jam
- Foreign key: Kd_Kelas, Kd_Pengajar

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | Kd_Pel | Varchar | 6 |
| 2 | Tanggal | Datetime | 8 |
| 3 | Hari | Varchar | 8 |
| 4 | Ruang | Varchar | 8 |
| 5 | Jam | Varchar | 8 |
| 6 | Kd_Kelas | Varchar | 8 |
| 7 | Kd_Pengajar | Varchar | 20 |

#### TLokasi_Belajar (lokasi belajar)
- Primary key: Id_Lokasi

| No | Field | Type | Length |
| --- | --- | --- | --- |
| 1 | Id_Lokasi | Varchar | 6 |
| 2 | Lokasi_Belajar | Varchar | 18 |

## Halaman Utama
- Dashboard: `/`
- Aktivitas: `/activities`
- Konversi: `/conversions`
- Leaderboard: `/leaderboard`
- Tim (superadmin): `/teams`
- User (superadmin): `/users`
- Akademik (superadmin): `/akademik`
- Keuangan (superadmin): `/keuangan`
- Ads/Iklan (role ads & superadmin): `/ads`
- Profil: `/profile`
- Notifikasi: `/notifications`

## Panduan Penggunaan
### Login & Navigasi
1. Buka `/login` lalu masuk dengan akun yang sesuai peran.
2. Setelah login, gunakan menu utama (`/menu`) atau sidebar untuk berpindah modul.
3. Topbar menampilkan email, tombol profil, notifikasi, dan logout.

### Superadmin
- Kelola Tim & User di menu **Tim** dan **User**.
- Verifikasi akhir Aktivitas/Konversi (status `Reviewed` → `Verified`).
- Atur bobot poin di **Settings Poin**.
- Lihat **Data Siswa** dari form publik `/pendaftaran`.
- Kelola modul **Akademik** dan **Keuangan**.

### Leader
- Review Aktivitas & Konversi anggota tim (status `Pending` → `Reviewed`).
- Lihat target tim dan pencapaiannya di **Target Tim**.
- Bisa chat dengan anggota di **Chat**.

### Staff
- Input Aktivitas di **Aktivitas** (link, tanggal, engagement, bukti).
- Input Konversi di **Konversi** (lead/closing + bukti).
- Pantau poin dan target pribadi di dashboard.

### Guest
- Hanya melihat data yang diizinkan (read-only).

### Modul Buzzer Marketing (Aktivitas & Konversi)
1. Staff input data → status `Pending`.
2. Leader review → status `Reviewed`.
3. Superadmin verifikasi akhir → status `Verified` dan poin dihitung.
4. Notifikasi akan muncul di topbar dan halaman **Notifikasi**.

### Modul Ads/Iklan
#### Perencanaan Kampanye
- Isi **Nama**, **Platform**, **Objective**, **Brief**, **Target audiens**, **Budget**, **KPI**, dan **PIC**.
- Role yang bisa membuat: **Superadmin, Admin Ads, Campaign Planner**.

#### Monitoring
- Input metrik harian: tayangan, jangkauan, klik, leads, closing, engagement.
- Tambahkan demografi (gender & usia) dan **Top 5 lokasi** (persentase).
- Role yang bisa input: **Superadmin, Admin Ads, Ads Specialist**.

#### Pelaporan
- Gunakan filter platform/kampanye/PIC/periode.
- Lihat ringkasan per platform + grafik tren.
- Export **CSV** (Excel) atau **PDF** (print-friendly).
- Role yang bisa melihat laporan: **Superadmin, Admin Ads, Analyst, Management**.

### Pendaftaran Publik
- Form publik di `/pendaftaran`.
- Data yang masuk akan tampil di **Data Siswa** (khusus superadmin).
- Bagian Administrasi melakukan validasi data siswa.
- Setelah valid, Bagian Administrasi mengirim invoice sesuai program bimbel via WhatsApp ke nomor orang tua/wali.

## ERD & Kardinalitas (Ringkas)
Diagram relasi utama (1 = satu, N = banyak):
```
teams (1) ───< users (N)
users (1) ───< activities_log (N)
teams (1) ───< activities_log (N)
users (1) ───< conversions (N)
teams (1) ───< conversions (N)
users (1) ───< notifications (N)
users (1) ───< social_accounts (N)

users (1) ───< chat_threads (N) >─── (1) users
chat_threads (1) ───< chat_messages (N) >─── (1) users

teams (1) ───< team_targets (N)
teams (1) ───< team_member_targets (N) >─── (1) users

produk_items (1) ───< public_registrations (N)
schools (1) ───< public_registrations (N)

ads_campaigns (1) ───< ads_metrics (N)
users (1) ───< ads_campaigns (N)
users (1) ───< ads_metrics (N)

TSiswa (1) ───< TAbsen (N)
TSiswa (1) ───< TAbsen_Siswa (N)
TSiswa (1) ───< TBayar (N)
TSiswa (1) ───< TItemBayar (N)
TBimbel (1) ───< TSiswa (N)
TBimbel (1) ───< TBayar (N)
TBimbel (1) ───< TItemBayar (N)
TKelas (1) ───< TSiswa (N)
TKelas (1) ───< TJadwal (N)
TPengajar (1) ───< TJadwal (N)
TPelajaran (1) ───< TJadwal (N)
TRuang (1) ───< TJadwal (N)
TThn_Ajaran (1) ───< TSiswa (N)
TLokasi_Belajar (1) ───< TSiswa (N)
```

Kardinalitas & catatan:
- `teams (1) -> users (N)`: `users.team_id` nullable untuk superadmin/role ads.
- `users (1) -> activities_log (N)` dan `users (1) -> conversions (N)` via `user_id`.
- `teams (1) -> activities_log (N)` dan `teams (1) -> conversions (N)` via `team_id`.
- `chat_threads` menyimpan relasi dua user (`user_one_id`, `user_two_id`) dan unik per pasangan.
- `public_registrations.program_id` opsional -> `produk_items`.
- `public_registrations.school_id` opsional -> `schools`.
- `ads_campaigns.pic_id` & `created_by` mengarah ke `users` (opsional).
- `ads_metrics.pic_id` opsional mengarah ke `users`.

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
- Ads/Iklan:
  - `GET /ads` (Dashboard ads)
  - `POST /ads/campaigns` (Simpan kampanye)
  - `POST /ads/metrics` (Simpan monitoring)
  - `GET /ads/export/csv` (Export CSV)
  - `GET /ads/export/pdf` (Export PDF)

## Scheduler (Reminder)
Aktifkan scheduler agar reminder berjalan:
```
php artisan schedule:work
```

## Catatan Penting
- Hapus tim tidak diizinkan jika masih ada data terkait.
- Guest tidak bisa input atau approve.
- Leader hanya bisa review data dari timnya sendiri.
