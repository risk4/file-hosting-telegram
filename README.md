# TeleStore Laravel

Aplikasi penyimpanan file berbasis web menggunakan **Telegram** sebagai backend storage. Dibangun dengan **Laravel 12 + Livewire v3 + Tailwind CSS**, upload hingga **2GB** via GramJS (Telegram User API).

---

## Arsitektur

```
Browser
  │
  ├── Laravel (port 8000)   ← Semua request user & admin
  │     ├── Livewire components
  │     ├── SQLite / MySQL
  │     └── HTTP → Node.js Sidecar
  │
  └── Node.js Sidecar (port 3001)  ← GramJS, hanya diakses Laravel
        └── Upload/Download Telegram User API (2GB)
```

---

## Fitur Utama

### Publik
- 📂 Halaman browse file publik `/browse`
- ☁️ Halaman upload publik `/upload`
- 👤 Guest upload tanpa login (jika diaktifkan)
- 🔎 Pencarian, filter per tipe, dan sorting
- 📄 Preview gambar & teks di halaman file publik
- 🔗 Share link / copy link untuk file publik
- 🚫 Jika browse dinonaktifkan, homepage menonaktifkan section file terbaru dan menampilkan redirect/message

### Admin (`/admin`)
- 📊 Dashboard statistik
- 📁 Manajemen file dengan pagination, edit, hapus, kategori, dan toggle publik/private
- 🔗 Tombol copy link di file manager admin
- ⚙️ Pengaturan Telegram, batas upload, toggle public browse/upload, guest upload
- 👥 Manajemen user admin

---

## Prasyarat

- **PHP** >= 8.2
- **Composer**
- **Node.js** >= 18
- **npm**
- Akun **Telegram** + API credentials dari [my.telegram.org](https://my.telegram.org)

---

## Instalasi

### 1. Clone / Extract Project

```bash
# Letakkan di folder Laragon
C:\laragon\www\telestore-laravel\
```

### 2. Install dependensi Laravel

```bash
cd C:\laragon\www\telestore-laravel
composer install
```

### 3. Setup environment

```bash
copy .env.example .env
php artisan key:generate
```

Edit `.env`, set minimal:

```env
APP_URL=http://telestore-laravel.test
DB_CONNECTION=sqlite

SIDECAR_URL=http://localhost:3001
SIDECAR_SECRET=isi_random_secret_kuat_disini
```

### 4. Setup database

```bash
# Windows: buat file kosong
break > database\database.sqlite

php artisan migrate
```

### 5. Install dependensi frontend

```bash
npm install
npm run build
```

### 6. Install & jalankan Node.js Sidecar

```bash
cd sidecar
npm install
```

Buat file `sidecar/.env` atau biarkan sidecar membaca dari root `.env`:

```env
SIDECAR_PORT=3001
SIDECAR_SECRET=isi_random_secret_kuat_disini
TELEGRAM_API_ID=
TELEGRAM_API_HASH=
```

Jalankan sidecar di terminal terpisah:

```bash
node server.js
```

### 7. Jalankan Laravel

```bash
cd C:\laragon\www\telestore-laravel
php artisan serve
```

Atau gunakan virtual host Laragon: `http://telestore-laravel.test`

---

## Menjalankan Setelah Install

Jalankan dua proses:

**Terminal 1 — Sidecar**
```bash
cd sidecar
node server.js
```

**Terminal 2 — Laravel**
```bash
php artisan serve
```

Untuk development dengan live reload:

```bash
npm run dev
```

---

## Konfigurasi Telegram Pertama Kali

1. Buka `http://localhost:8000/admin/login`
2. Login dengan `admin@telestore.local` / `admin123`
3. Masuk ke **Pengaturan** → **Koneksi Telegram**
4. Isi:
   - **API ID** & **API Hash** dari [my.telegram.org](https://my.telegram.org)
   - **Nomor HP** format `+628xxx`
5. Klik **Kirim Kode OTP** lalu masukkan kode Telegram
6. Jika ada 2FA, masukkan password Telegram
7. Atur **Target Chat** (`me` disarankan untuk Saved Messages)

---

## Struktur Project

```
telestore-laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── DownloadController.php
│   │   └── Middleware/
│   │       └── EnsureUserIsActive.php
│   ├── Livewire/
│   │   ├── Public/
│   │   │   ├── FileBrowser.php
│   │   │   └── FileUpload.php
│   │   └── Admin/
│   │       ├── Dashboard.php
│   │       ├── FileManager.php
│   │       ├── Settings.php
│   │       └── UserManager.php
│   ├── Models/
│   │   ├── TeleFile.php
│   │   ├── Setting.php
│   │   └── User.php
│   └── Services/
│       └── TelegramService.php
├── database/migrations/
├── resources/views/
│   ├── layouts/
│   ├── public/
│   ├── admin/
│   └── livewire/
├── routes/web.php
├── sidecar/
│   ├── server.js
│   ├── package.json
│   └── .session
├── config/telestore.php
├── .env.example
└── README.md
```

---

## Default Admin

| Field    | Value                    |
|----------|--------------------------|
| Email    | `admin@telestore.local`  |
| Password | `admin123`               |

> ⚠️ Ganti password segera setelah login pertama kali.

---

## Tips

- **Target Chat `me`** = Saved Messages Telegram kamu
- **Gunakan `@username` atau ID numerik** jika target chat adalah channel/group
- **Toggle public browse/upload** di admin settings
- **Guest upload** dapat dinonaktifkan untuk menutup akses tanpa login
- File yang dihapus dari admin hanya dihapus dari database kecuali pilih opsi hapus juga dari Telegram

---

## Deploy ke VPS (aaPanel)

### Langkah-langkah

1 — Upload project ke VPS

Di lokal, build dulu aset:

```bash
npm run build
```

Lalu upload via Git (rekomendasi) atau rsync/SFTP:

```bash
# Opsi A: Git
git init && git add . && git commit -m "init"
# push ke GitHub, lalu di VPS:
git clone https://github.com/username/telestore-laravel.git /www/wwwroot/telestore
```

```bash
# Opsi B: rsync dari Git Bash lokal
rsync -avz --exclude node_modules --exclude .git \
  /c/laragon/www/telestore-laravel/ \
  user@vps-ip:/www/wwwroot/telestore/
```

2 — Setup Laravel di VPS

```bash
cd /www/wwwroot/telestore

composer install --optimize-autoloader --no-dev

cp .env.example .env
php artisan key:generate
```

Edit `.env` di VPS:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://telestore.domainmu.com

DB_CONNECTION=sqlite
# atau MySQL jika mau

SIDECAR_URL=http://localhost:3001
SIDECAR_SECRET=secret_yang_sama_dengan_lokal
```

```bash
php artisan migrate --force
php artisan optimize
chmod -R 775 storage bootstrap/cache
chown -R www:www .
```

3 — Setup Node.js Sidecar sebagai Service Permanen

Di VPS, sidecar harus tetap berjalan meski terminal ditutup. Gunakan PM2:

```bash
# Install PM2 global
npm install -g pm2

cd /www/wwwroot/telestore/sidecar
npm install --production

# Jalankan via PM2
pm2 start server.js --name telestore-sidecar

# Auto-start saat VPS reboot
pm2 startup
pm2 save
```

Perintah PM2 yang berguna:

```bash
pm2 status                    # cek status
pm2 logs telestore-sidecar    # lihat log realtime
pm2 restart telestore-sidecar # restart
pm2 stop telestore-sidecar    # stop
```

4 — Setup Website di aaPanel

Di aaPanel → Website → Add site:

- Domain: `telestore.domainmu.com`
- Root: `/www/wwwroot/telestore/public`
- PHP: `8.2`

Setelah site dibuat, masuk ke site config → Rewrite → pilih preset Laravel atau paste manual:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Lalu pasang SSL via Let's Encrypt di aaPanel (1 klik).

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Sidecar tidak bisa dijangkau | Pastikan `node server.js` di folder `sidecar` sudah berjalan |
| Upload gagal | Cek log sidecar dan pastikan Telegram sudah login |
| Public browse tidak muncul | Pastikan toggle `public browse` di admin settings sudah aktif |
| SIDECAR_SECRET tidak cocok | Pastikan nilai sama di `.env` Laravel dan `sidecar/.env` |
| File tidak muncul di browse | Cek kolom `is_public` di database, atau cek pengaturan admin |
| Gagal login Telegram | Pastikan API ID/Hash benar, coba dari [my.telegram.org](https://my.telegram.org) |
