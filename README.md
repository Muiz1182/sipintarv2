# SIPINTAR (Speland Digital Library)

SIPINTAR adalah platform perpustakaan digital interaktif modern yang menghadirkan katalog koleksi literasi dalam tampilan lemari buku kayu realistik (*realistic bookcase catalog*) dan pembaca buku digital berbasis lembaran interaktif (*interactive flipbook*).

Aplikasi ini dibangun menggunakan arsitektur modern Laravel 11, Tailwind CSS, dan Alpine.js, dengan fokus pada estetika antarmuka, optimasi kompresi dokumen digital, dan kenyamanan membaca lintas perangkat (desktop, tablet, dan ponsel pintar).

---

## 🌟 Fitur Unggulan

### 1. Katalog Lemari Buku Realistis (Bookcase UI)
- Tampilan lemari rak kayu bertekstur dengan bayangan kedalaman realistik (*depth shadows*).
- Setiap kompartemen rak dilengkapi balok kayu penyangga (*wooden shelf beam*) dan plakat kuningan penomoran rak.
- Lencana status interaktif seperti **Baru** (*New Release*) dan **Viral** (*Trending Readers*).
- Indikator jumlah pembaca (*views counter*) dan plakat judul presisi sejajar.
- Filter pencarian instan dan klasifikasi berdasarkan rak/kategori literasi.

### 2. Pembaca Flipbook Interaktif (Interactive Reader)
- Pengalaman membalik halaman buku digital secara realistis layaknya membaca buku fisik menggunakan integrasi PDF.js dan Turn.js.
- **Mobile Single-Page Mode**: Pada layar ponsel pintar (`< 768px`), sistem otomatis beralih ke mode halaman tunggal agar teks dan ilustrasi tetap terbaca jelas tanpa perlu memperbesar layar secara berlebih.
- Kontrol navigasi ramah sentuhan, tombol layar penuh (*fullscreen*), serta indikator kemajuan membaca (*reading progress bar*).
- Pelacakan frekuensi baca otomatis untuk setiap sesi literasi.

### 3. Kompresi Cerdas & Generator Sampul Otomatis
- **Intelligent PDF Compression**: Fitur kompresi otomatis di sisi peramban (*client-side*) dan perintah CLI Artisan di sisi peladen (*server-side*) yang mengoptimalkan berkas PDF besar ke target &le; 1 MB tanpa menghilangkan keterbacaan teks.
- **Auto-Generate Cover**: Lembar pertama dokumen PDF otomatis dirender menjadi sampul visual buku berkualitas tinggi saat pengunggahan tanpa memerlukan unggahan gambar manual.

### 4. Pusat Kendali & Manajemen Koleksi
- **Manajemen Inventaris Buku**: Pencarian cepat, filter rak, penyalinan judul massal, dan fitur unduh buku terpilih ke arsip `.zip`.
- **Dual-Layout Responsif**:
  - *Desktop*: Tabel data lengkap dengan informasi ukuran berkas dan status rak.
  - *Mobile*: Kartu literasi ringkas bebas geser samping (*zero horizontal scroll leak*) dengan tombol sentuh berukuran minimum 44 &times; 44 piksel.
- **Papan Peringkat (Leaderboard)**: Podium penghargaan digital untuk 3 buku terpopuler dan daftar peringkat pembaca tertinggi.
- **Manajemen Peran**:
  - *Super Admin*: Kontrol penuh rak koleksi, manajemen akun staf petugas, audit integritas berkas di disk, dan utilitas pengosongan/kompresi massal.
  - *Petugas*: Pengunggahan koleksi baru dan pengelolaan inventaris literasi harian.

---

## 🛠️ Tumpukan Teknologi (Tech Stack)

- **Backend Framework**: PHP 8.2+, Laravel 11
- **Database**: MySQL / MariaDB (Mendukung SQLite untuk lingkungan lokal)
- **Frontend & Styling**: Tailwind CSS, Blade Templates, Alpine.js
- **Mesin Dokumen & Flipbook**:
  - [PDF.js](https://mozilla.github.io/pdf.js/) (Rendering dokumen PDF ke elemen canvas)
  - [pdf-lib](https://pdf-lib.js.org/) (Manipulasi dan optimasi struktur dokumen PDF)
  - [Turn.js](http://www.turnjs.com/) (Efek animasi pembalik halaman realistis)
- **Paket Pendukung**:
  - `vinkla/hashids` (Enkripsi URL identifikasi buku)
  - `chillerlan/php-qrcode` (Pembuatan kode QR buku)

---

## 📋 Prasyarat Sistem

Sebelum memasang aplikasi, pastikan sistem Anda telah terpasang:
- **PHP** versi 8.2 atau lebih tinggi
- **Ekstensi PHP**: `BCMath`, `Ctype`, `Fileinfo`, `JSON`, `Mbstring`, `OpenSSL`, `PDO`, `Tokenizer`, `XML`, `GD` / `Imagick`
- **Composer** (Manajer paket ketergantungan PHP)
- **Node.js & NPM** (Untuk kompilasi aset frontend)
- **Basis Data**: MySQL 8.0+, MariaDB 10.4+, atau SQLite 3

---

## 🚀 Panduan Instalasi Lokal

Ikuti langkah-langkah di bawah ini untuk menjalankan aplikasi pada lingkungan pengembangan:

### 1. Kloning Repositori
```bash
git clone https://github.com/Muiz1182/sipintarv2.git
cd sipintarv2
```

### 2. Pasang Ketergantungan Backend & Frontend
```bash
composer install
npm install
```

### 3. Konfigurasi Lingkungan (.env)
Salin berkas contoh konfigurasi lingkungan:
```bash
cp .env.example .env
```
Buka berkas `.env` dan sesuaikan parameter basis data sesuai konfigurasi lokal Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipintar
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Buat Kunci Aplikasi & Tautan Penyimpanan
```bash
php artisan key:generate
php artisan storage:link
```

### 5. Jalankan Migrasi Basis Data
```bash
php artisan migrate
```
*(Opsional) Jalankan seeder bawaan jika diperlukan:*
```bash
php artisan db:seed
```

### 6. Kompilasi Aset & Jalankan Server
Buka terminal dan jalankan server pengembangan:
```bash
php artisan serve
```
Aplikasi kini dapat diakses melalui peramban web pada alamat:
```
http://localhost:8000
```

---

## 💻 Perintah Artisan Khusus

Platform menyediakan perintah Artisan untuk optimasi berkas secara berkala:

- **Kompresi Seluruh Berkas PDF yang Tersimpan**:
  ```bash
  php artisan books:compress-pdfs
  ```
- **Membersihkan dan Membangun Ulang Cache Tampilan**:
  ```bash
  php artisan view:clear
  php artisan route:clear
  php artisan config:clear
  ```

---

## 📱 Standar Aksesibilitas & Responsivitas Mobile

Sistem dirancang mengikuti pedoman desain ramah sentuhan (*touch-first*):
- Target sentuh (*touch target*) interaktif minimum berukuran 44 &times; 44 piksel dengan jarak aman antarelemen.
- Bebas dari kebocoran gulir samping (*zero horizontal scroll leak*) pada semua resolusi ponsel pintar (mulai lebar 360 piksel ke atas).
- Reflow tata letak dinamis: 5 kolom pada desktop, 3 kolom pada tablet, dan 2 kolom seimbang pada ponsel.

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).
