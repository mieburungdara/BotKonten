# Telegram Mini Webapp untuk Berjualan Media – Rencana Sederhana

## 📋 Ringkasan Proyek
- **Backend**: Laravel 8.x + PHP 8.5, database MySQL InnoDB
- **Frontend**: Plain HTML/CSS/JS yang terintegrasi dengan Telegram WebApp API
- **Pembayaran**: Simulasi pembayaran + metode manual (untuk integrasi ShopeePay di masa depan)
- **Media**: Foto, video, audio, dokumentasi digital
- **Konten ID Unik**: Setiap konten (media atau album) yang dijual memiliki ID unik untuk tracking dan penjualan
- **Multi‑Bot Telegram**: Setiap bot memiliki webhook pribadi dan hanya perintah `/start` yang menampilkan daftar bot yang dapat dipilih untuk upload
- **Channel Backup**: Semua media yang dikirim bot disimpan di satu channel backup (message_id & channel_id) sehingga dapat diakses semua bot
- **Bot Watcher**: Bot khusus yang memantau channel backup dan menggabungkan foto/video menjadi album (maks 10 media per album)
- **Draft System**: Media yang dikirim muncul sebagai draft di Webapp; pengguna dapat preview, publish, atau hapus
- **User**: Setiap pengguna memiliki `anonymous_id` yang unik dan anonim
- **Logging**: Monolog untuk pencatatan semua aktivitas

## 🔧 Fitur Tambahan
- **Sistem Rating dan Review**: Pengguna dapat memberikan rating dan ulasan pada media yang dibeli; tampilkan rata-rata rating di detail media.
- **Search dan Filter Media**: Fitur pencarian teks dan filter berdasarkan kategori, tanggal, atau bot sumber di dashboard media.
- **Analytics Dashboard untuk Penjual**: Dashboard statistik penjualan, pendapatan, dan media terpopuler menggunakan grafik sederhana.
- **Notifikasi Real-Time**: Notifikasi Telegram atau in-app untuk update draft, album, dan pembayaran.
- **Multi-Language Support**: Dukung bahasa Indonesia dan Inggris di webapp dan bot.
- **Integrasi Pembayaran Eksternal**: Ekstensikan ke ShopeePay, GoPay, atau Stripe untuk pembayaran riil.
- **Dark Mode untuk Webapp**: Toggle mode gelap/terang berdasarkan preferensi pengguna.
- **Customer Support Chat**: Live chat atau ticketing untuk dukungan pelanggan via Telegram.
- **Wishlist dan Favorites**: Pengguna dapat menambahkan media/album ke wishlist untuk pembelian nanti.
- **Social Sharing**: Bagikan link media/album ke platform sosial langsung dari webapp.
- **Gamification dan Badges**: Berikan badge berdasarkan jumlah penjualan atau rating untuk mendorong kompetisi.  

## 🏗️ Struktur Proyek
```
BotKonten/
├── backend/   # Laravel backend
│   ├── app/
│   ├── config/
│   ├── database/
│   └── …
├── frontend/  # Telegram Mini Webapp
│   ├── index.html
│   ├── css/
│   └── js/
└── README.md
```

## 🔧 Tahapan Implementasi

### 1. Setup Backend Laravel
- Install Laravel 8.x + PHP 8.5  
- Konfigurasi MySQL InnoDB  
- Buat migrasi tabel utama:
  - `users` (kolom `anonymous_id`)  
  - `media` (`bot_id`, `user_id`)  
  - `drafts` (`bot_id`, `user_id`)  
  - `albums` (`bot_id`, `user_id`) – tiap album ≤ 10 media  
  - `bots` (konfigurasi bot, token, webhook URL)  
  - `media_backup` (mapping ke channel backup)  
- Buat model & controller untuk tabel di atas  
- Buat API endpoint inti:
  - **Auth**: `POST /api/register`, `POST /api/login`, `GET /api/user/profile`  
  - **Media**: `GET /api/media`, `GET /api/media/{id}`, `POST /api/media` (upload dari draft)  
  - **Draft**: `GET /api/drafts`, `POST /api/drafts/publish`, `DELETE /api/drafts/{id}`  
  - **Album**: `POST /api/albums` (buat album), `GET /api/albums/{id}`  
  - **Bot Management**: `GET /api/bots`, `POST /api/user/select-bot`  
  - **Webhook**: `POST /webhook/bot/{bot_id}` (handle `/start` & media upload)  

### 2. Frontend Telegram Mini Webapp
- `index.html` dengan inisialisasi Telegram WebApp API  
- Dashboard Draft: tampilkan daftar draft, tombol **Publish** & **Delete**  
- Daftar Media: tampilkan produk yang sudah dipublish, tombol **Add to Cart**  
- Album View: tampilkan album (grid untuk foto, player untuk video)  
- Checkout: pilih metode pembayaran (simulasi atau manual)  
- Deep‑linking: webapp dapat menerima `?bot_id=XYZ&draft_id=ABC` dari inline button bot untuk langsung membuka draft  

### 3. Bot & Bot Watcher
- Setiap bot memiliki webhook pribadi (`/webhook/bot/{bot_id}`)  
- Pada `/start` bot mengirim daftar bot yang tersedia  
- Media yang diterima bot:
  1. Forward ke channel backup  
  2. Simpan mapping (`media_backup`) dengan `bot_id` & `user_id`  
  3. Kirim inline button “Lihat di Webapp” dengan link ke draft  
- Bot Watcher memantau channel backup, mengelompokkan media menjadi album (maks 10 item) dan menyimpan ke tabel `albums`  

### 4. Testing & Deployment
- Unit test untuk auth, media CRUD, draft flow, album creation, webhook handling  
- End‑to‑end test: kirim media → draft → publish → beli → pembayaran  
- Deploy di Apache/Nginx + PHP 8.5, aktifkan HTTPS, set webhook masing‑masing bot  

## 🚀 Langkah Selanjutnya
Setelah Anda mengonfirmasi rencana ini, saya akan menyiapkan struktur proyek (folder, composer, npm) dan menulis migrasi serta konfigurasi awal.

---
*Rencana ini sudah mencakup semua fitur utama dengan format yang lebih singkat dan mudah dipahami, siap untuk implementasi.*