### Langkah-Langkah Deploy Aplikasi BotKonten (Laravel) ke Shared Hosting

Berdasarkan struktur proyek BotKonten (Laravel 8.x backend dengan frontend static), berikut adalah langkah-langkah lengkap untuk deploy ke shared hosting (seperti Hostinger, Namecheap, atau cPanel-based hosting). Asumsikan hosting mendukung PHP 7.3+ dan MySQL.

#### 0. Verifikasi Hosting Compatibility
- Cek versi PHP (minimal 7.3) dan extensions wajib (pdo_mysql, mbstring, fileinfo, bcmath, ctype, json, tokenizer, xml) via phpinfo() atau control panel.
- Pastikan hosting support MySQL InnoDB dan SSH jika memungkinkan.

#### 1. Persiapan Lokal
- Pastikan aplikasi berjalan di lokal dengan benar (jalankan `php artisan serve` dan test API/webhook, termasuk auth, media, dan webhook).
- Instal dependencies: `composer install --no-dev --optimize-autoloader`.
- Build assets frontend jika menggunakan webpack: `npm install && npm run prod` (jika ada webpack.mix.js).
- Export database schema: Jalankan `php artisan migrate --pretend` untuk generate SQL, atau gunakan `php artisan migrate:status` untuk memverifikasi migrations.
- Buat backup database lokal jika perlu: `mysqldump -u username -p database_name > backup.sql`.
- Copy `.env.example` ke `.env`, set `APP_KEY` dengan `php artisan key:generate`, dan konfigurasikan environment variables (database, Telegram token, MAIL_*, dll.).
- Jika ada queue jobs, test dengan `php artisan queue:work` lokal.

#### 2. Setup Shared Hosting
- Login ke control panel hosting (cPanel/Plesk).
- Buat subdomain atau folder untuk aplikasi (misal: `public_html/botkonten` atau subdomain `botkonten.yourdomain.com`).
- Buat database MySQL baru:
  - Akses "MySQL Databases".
  - Buat database baru (misal: `botkonten_db`).
  - Buat user database dan assign ke database tersebut.
  - Catat credentials: DB_HOST (biasanya localhost), DB_USERNAME, DB_PASSWORD, DB_DATABASE.
- Jika hosting mendukung, install Composer via SSH (jika tersedia) atau upload vendor folder dari lokal.

#### 3. Upload Files ke Hosting
- Gunakan FTP/SFTP client (seperti FileZilla) atau file manager hosting.
- Upload seluruh folder `backend/` ke root folder hosting (public_html atau subdomain folder).
- Pastikan file `public/index.php` berada di root web-accessible (jika hosting menggunakan public_html, upload backend ke public_html).
- Jika ada frontend static, upload folder `frontend/` ke subfolder atau domain terpisah jika diperlukan.
- Upload folder `vendor/` dari lokal jika Composer tidak tersedia di hosting (untuk menghindari install online).
- Pastikan file `.htaccess` di folder `public/` di-upload (penting untuk URL rewriting Laravel).

#### 4. Konfigurasi Database
- Akses phpMyAdmin via control panel hosting.
- Import schema database:
  - Buat database sesuai langkah 2.
  - Import file SQL dari lokal (dari `php artisan migrate --pretend`) atau jalankan migrations via SSH jika hosting mendukung PHP CLI.
- Jika SSH tersedia:
  - Connect via SSH.
  - Navigasi ke folder aplikasi: `cd public_html` (sesuaikan path).
  - Jalankan `php artisan migrate` (pastikan .env sudah dikonfigurasi).
- Jika tidak ada SSH, import manual via phpMyAdmin atau gunakan script PHP custom untuk migrate.

#### 5. Konfigurasi Environment (.env)
- Edit file `.env` di hosting via file manager atau SSH.
- Set variables penting:
  ```
  APP_NAME=BotKonten
  APP_ENV=production
  APP_KEY=base64_generated_key_from_local
  APP_DEBUG=false
  APP_URL=https://yourdomain.com

  DB_CONNECTION=mysql
  DB_HOST=localhost
  DB_PORT=3306
  DB_DATABASE=botkonten_db
  DB_USERNAME=db_user
  DB_PASSWORD=db_password

  TELEGRAM_BOT_TOKEN=your_bot_token
  TELEGRAM_WEBHOOK_URL=https://yourdomain.com/webhook/bot/{bot_id}

  MAIL_MAILER=smtp
  MAIL_HOST=your_smtp_host
  MAIL_PORT=587
  MAIL_USERNAME=your_email
  MAIL_PASSWORD=your_password
  MAIL_ENCRYPTION=tls
  MAIL_FROM_ADDRESS=your_email

  LOG_CHANNEL=single
  CACHE_DRIVER=file
  QUEUE_CONNECTION=sync
  SESSION_DOMAIN=.yourdomain.com
  SANCTUM_STATEFUL_DOMAINS=yourdomain.com
  ```

#### 6. Set Permissions dan Optimasi
- Set permissions folders via file manager atau SSH:
  - `chmod 755 storage bootstrap/cache` (recursive).
  - `chmod 644 storage/logs/*` jika ada.
  - Pastikan `storage/app/public` writable untuk penyimpanan media dari webhook.
- Jalankan `php artisan cache:clear`, `php artisan config:clear`, `php artisan view:clear` untuk clean cache lama.
- Jika hosting mendukung, jalankan `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` untuk optimasi production.
- Jika ada storage link: `php artisan storage:link` (untuk akses file dari webhook).
- Disable debug mode di production: Pastikan `APP_DEBUG=false`.
- Setup custom error pages: `php artisan vendor:publish --tag=laravel-errors` dan edit di `resources/views/errors/`.

#### 7. SSL Certificate Setup
- Setup SSL certificate via hosting control panel (misal Let's Encrypt di cPanel) untuk domain/subdomain.
- Pastikan HTTPS aktif sebelum setup webhook Telegram.

#### 8. Setup Webhook Telegram
- Set webhook URL di BotFather: `https://yourdomain.com/webhook/bot/{bot_id}` (sesuaikan {bot_id} dari database).
- Pastikan route `/webhook/bot/{bot_id}` dapat diakses public (tidak ada htaccess blocking).
- Jika hosting firewall ketat, whitelist Telegram IPs (lihat daftar resmi Telegram).

#### 9. Setup Cron Jobs dan Queue Worker (Jika Diperlukan)
- Untuk scheduled tasks: Setup cron job via control panel: `* * * * * /usr/bin/php /path/to/artisan schedule:run > /dev/null 2>&1`.
- Jika ada queue jobs: Jalankan `php artisan queue:work --daemon` via SSH atau setup sebagai background service.

#### 10. Test Deployment
- Akses aplikasi via browser: `https://yourdomain.com/webapp`.
- Test API endpoints menggunakan Postman atau curl (misal: GET `/api/media`, POST `/api/auth/login`).
- Test webhook: Kirim media ke bot Telegram dan cek apakah masuk sebagai draft.
- Monitor logs: Cek `storage/logs/laravel.log` via file manager jika ada error.
- Jika ada error 500, cek PHP version, extensions (PDO MySQL, mbstring, dll.), permissions, dan memory limits.
- Jalankan `php artisan tinker` via SSH untuk debug database connection jika perlu.

#### Catatan Tambahan
- Jika hosting tidak mendukung SSH, gunakan file manager untuk semua operasi file dan gunakan PHP script custom untuk menjalankan artisan commands (misal: buat file `migrate.php` dengan `require_once 'vendor/autoload.php'; Artisan::call('migrate');`).
- Backup rutin: Setup cron job untuk backup database dan files harian. Buat backup full sebelum deploy untuk rollback jika gagal.
- Security: Gunakan HTTPS, disable directory listing via .htaccess, update Laravel/framework, dan integrasikan error reporting (misal Sentry). Pastikan tidak ada sensitive files exposed.
- Jika ada frontend terpisah, deploy ke CDN atau subfolder sesuai struktur.
- Jika aplikasi handle banyak media, integrasikan CDN (misal Cloudflare) atau gunakan `FILESYSTEM_DISK=s3` untuk storage eksternal.
- Estimasi waktu deploy pertama: 1-2 jam dengan SSH; lebih lama tanpa.
- Upload file atau media hanya dapat dilakukan melalui bot Telegram via webhook; webapp tidak menyediakan interface untuk upload langsung dari pengguna.

Jika deploy gagal di langkah tertentu, periksa logs aplikasi atau hosting error logs untuk troubleshooting spesifik.