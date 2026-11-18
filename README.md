# SIM-RS Medika — Server Utama (A) Frontend & RBAC (`sim_pasien`)

Aplikasi Web Frontend (**Service Consumer**) untuk **Sistem Informasi Manajemen Pasien & Poliklinik Rumah Sakit** berbasis **Service-Oriented Architecture (SOA — Arsitektur Dua Server)**.

- **Server A (repo ini)** — Frontend PHP + RBAC, tidak mengakses database secara langsung.
- **Server B** — Web Service REST API + MySQL `db_pasien` → repo [`ws_pasien`](https://github.com/Pratama12340/ws_pasien).

## Fitur Utama
- **Landing Page Publik (`index.php`)**: Menampilkan statistik rumah sakit, daftar unit poliklinik, dokter spesialis, dan mitra asuransi secara *real-time* dari Web Service Server B.
- **Autentikasi JWT & RBAC 6 Role (`login.php` & `auth.php`)**:
  1. `super_admin` (`superadmin`) — Akses penuh seluruh sistem & manajemen pengguna/RBAC
  2. `admin` (`admin.klinik`) — Pengelolaan operasional klinik
  3. `pendaftaran` (`loket.dina`) — Registrasi pasien & antrean kunjungan
  4. `dokter` (`dr.hendra`) — Pemeriksaan pasien & rekam medis
  5. `kasir` (`kasir.fajar`) — Penjaminan asuransi & pembayaran
  6. `pasien` (`pasien`) — Portal mandiri pasien & kartu berobat digital
- **Modul UI CRUD ke Web Service**:
  - `poliklinik.php` (`/api/poli`)
  - `pasien.php` (`/api/pasien`)
  - `asuransi.php` (`/api/asuransi`)
  - `dokter.php` (`/api/dokter`)
  - `pengguna.php` (`/api/pengguna` & `/api/roles`)

## Instalasi

1. Clone repo ke folder web server (mis. Laragon):
   ```bash
   cd C:\laragon\www
   git clone https://github.com/Pratama12340/sim_pasien.git
   ```
2. Atur alamat API Server B di `api_url.txt` (default: `http://172.168.1.178:8099/api`).
3. **Buat file konfigurasi rahasia** (lihat bagian *Keamanan* di bawah):
   ```bash
   copy config.example.php config.php
   ```
   lalu isi `JWT_SECRET` di `config.php` dengan nilai **yang sama** dengan `JWT_SECRET` pada file `.env` Server B.
4. Buka `http://localhost/sim_pasien/`.

## Keamanan — Pengelolaan `JWT_SECRET`

### Mengapa secret tidak ada di repo?
Landing Page (`index.php`) dapat dibuka **sebelum login**, padahal endpoint `/api/poli`, `/api/dokter`, `/api/asuransi`, dan `/api/dashboard` di Server B mewajibkan *Bearer JWT*. Karena itu `api_client.php` membuat **token layanan (service token)** sendiri menggunakan `JWT_SECRET` milik Server B (fungsi `make_service_jwt()`).

Siapa pun yang mengetahui `JWT_SECRET` dapat **memalsukan token login apa pun** (termasuk `super_admin`) ke Server B. Maka secret **tidak boleh** ditulis di kode yang di-push ke GitHub.

### Cara kerjanya sekarang
| File | Di-commit? | Isi |
|---|---|---|
| `config.example.php` | ✅ Ya | Template kosong (`ISI_DENGAN_JWT_SECRET_SERVER_B`) |
| `config.php` | ❌ Tidak (`.gitignore`) | Secret asli, hanya ada di komputer/server lokal |
| `api_client.php` | ✅ Ya | Membaca secret dari `config.php`, atau dari *environment variable* `JWT_SECRET` bila `config.php` tidak ada |

Jika `config.php` belum dibuat, aplikasi tetap berjalan untuk pengguna yang login (token didapat dari `/api/auth/login`), tetapi data Landing Page sebelum login tidak akan tampil.

### Rotasi secret (wajib dilakukan)
Pada versi awal repo ini, `JWT_SECRET` sempat tertulis langsung di `api_client.php` dan **masih tersimpan di riwayat commit Git**. Menghapusnya dari kode saja tidak cukup, sehingga secret lama harus dianggap bocor dan **diganti**:

1. **Di Server B** — ubah `JWT_SECRET` pada `.env` dengan string acak baru, misalnya:
   ```bash
   php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
   ```
   lalu restart service API. Semua token lama otomatis tidak berlaku (pengguna perlu login ulang).
2. **Di Server A** — perbarui nilai `JWT_SECRET` di `config.php` dengan secret baru yang sama.
3. Bagikan secret baru **hanya lewat jalur privat** (chat pribadi), jangan lewat repo, README, atau laporan.

> Catatan: untuk produksi, pendekatan yang lebih baik adalah Server B menyediakan endpoint baca-publik khusus (tanpa JWT) atau akun layanan *read-only*, sehingga Server A tidak perlu menyimpan secret Server B sama sekali.
