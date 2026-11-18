<?php
/**
 * config.example.php - CONTOH konfigurasi rahasia Server A (Frontend).
 *
 * Cara pakai:
 *   1. Salin file ini menjadi `config.php` (di folder yang sama).
 *   2. Isi nilai JWT_SECRET sesuai `JWT_SECRET` pada file `.env` Server B (Web Service API).
 *
 * PENTING: `config.php` sudah dimasukkan ke `.gitignore` sehingga TIDAK ikut ter-push ke GitHub.
 * Jangan pernah menulis secret asli di file ini.
 */

return [
    // Harus sama persis dengan JWT_SECRET di .env Server B (dipakai untuk token baca-publik Landing Page)
    'JWT_SECRET' => 'ISI_DENGAN_JWT_SECRET_SERVER_B',
];
