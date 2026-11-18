# SIM-RS Medika — Server Utama (A) Frontend & RBAC (`sim_pasien` / `rumah_sakit`)

Aplikasi Web Frontend (**Service Consumer**) untuk **Sistem Informasi Manajemen Pasien & Poliklinik Rumah Sakit** berbasis **Service-Oriented Architecture (SOA — Arsitektur Dua Server)**.

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
