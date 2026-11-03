# Sistem Pesanan Laundry

Aplikasi pengelolaan pesanan laundry sederhana yang dibuat untuk UTS Praktikum Pemrograman Web (Paket C). Aplikasi berjalan di Laravel dengan akses basis data memakai PDO prepared statement, dan tampilan memakai HTML serta Blade tanpa CSS dan JavaScript.

## Identitas

- Nama: Salsadilla Frisca Anjani
- NIM: 2401020164
- Program Studi: Teknik Informatika
- Mata Kuliah: Praktikum Pemrograman Web (INF11197)

## Fitur

- Menampilkan daftar pesanan laundry
- Menambah pesanan baru dengan validasi server-side
- Mengubah data pesanan
- Menghapus data pesanan
- Mencari pesanan berdasarkan kode pesanan, nama pelanggan, atau nomor telepon
- Memfilter pesanan berdasarkan jenis layanan
- Menampilkan status berat melalui fungsi `statusBerat()` (Besar jika berat 10 kg atau lebih, Reguler jika di bawah 10 kg)
- Menolak kode pesanan atau nomor telepon yang sudah dipakai

## Teknologi

- PHP 8.5
- Laravel 13
- MariaDB 10.4 (XAMPP)
- PDO prepared statement
- Blade tanpa CSS dan JavaScript

## Struktur Basis Data

Nama basis data: `laundry_2401020164`

Tabel `jenis_layanan`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| id | BIGINT UNSIGNED | Primary key, auto increment |
| nama_layanan | VARCHAR(100) | Unik, wajib diisi |

Tabel `pesanan_laundry`

| Kolom | Tipe | Keterangan |
| --- | --- | --- |
| id | BIGINT UNSIGNED | Primary key, auto increment |
| kode_pesanan | CHAR(10) | Unik, wajib diisi |
| nama_pelanggan | VARCHAR(100) | Wajib diisi |
| nomor_telepon | VARCHAR(20) | Unik, wajib diisi |
| berat_kg | SMALLINT UNSIGNED | Wajib diisi, 1 sampai 20 |
| jenis_layanan_id | BIGINT UNSIGNED | Foreign key ke jenis_layanan.id |
| created_at | TIMESTAMP | Waktu dibuat |
| updated_at | TIMESTAMP | Waktu diperbarui |

Relasi satu ke banyak antara `jenis_layanan` dan `pesanan_laundry` memakai `ON UPDATE CASCADE` dan `ON DELETE RESTRICT`.

## Daftar Route

| Method | URI | Nama Route | Keterangan |
| --- | --- | --- | --- |
| GET | / | beranda | Pengalihan ke halaman pesanan |
| GET | /pesanan-laundry | pesanan.index | Daftar pesanan, pencarian, dan filter |
| POST | /pesanan-laundry | pesanan.store | Menyimpan pesanan baru |
| GET | /pesanan-laundry/{id}/edit | pesanan.edit | Form ubah pesanan |
| PUT | /pesanan-laundry/{id} | pesanan.update | Menyimpan perubahan pesanan |
| DELETE | /pesanan-laundry/{id} | pesanan.destroy | Menghapus pesanan |

## Cara Menjalankan

1. Pastikan PHP, Composer, dan MySQL dari XAMPP sudah aktif.
2. Salin proyek ke komputer lokal, lalu masuk ke folder proyek.
3. Jalankan `composer install`.
4. Salin `.env.example` menjadi `.env`.
5. Sesuaikan bagian berikut pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laundry_2401020164
DB_USERNAME=root
DB_PASSWORD=
```

6. Buat basis data dengan mengimpor `database/laundry_2401020164.sql`, misalnya melalui phpMyAdmin atau perintah:

```sh
mysql -u root < database/laundry_2401020164.sql
```

7. Jalankan `php artisan key:generate`.
8. Jalankan `php artisan serve`, lalu buka `http://127.0.0.1:8000/pesanan-laundry`.

## Berkas Penting

- `routes/web.php` berisi seluruh route aplikasi
- `resources/views/pesanan-laundry.blade.php` berisi form tambah, pencarian, filter, dan tabel pesanan
- `resources/views/pesanan-laundry-edit.blade.php` berisi form ubah pesanan
- `database/laundry_2401020164.sql` berisi struktur tabel dan data awal

Berkas `.env` tidak disimpan di repository karena memuat konfigurasi lokal.
