# BotKonten - Telegram Mini Webapp untuk Berjualan Media

## 📋 Ringkasan

Platform e-commerce media (foto, video, audio, dokumen) terintegrasi dengan Telegram Mini Webapp. Setiap bot memiliki webhook pribadi untuk menerima media yang kemudian dikelola melalui sistem draft.

## 🏗️ Struktur Proyek

```
BotKonten/
├── backend/               # Laravel 8.x API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   ├── Api/    # REST API Controllers
│   │   │   │   └── WebhookController.php
│   │   │   └── Middleware/
│   │   └── Models/         # Eloquent Models
│   ├── config/
│   ├── database/
│   │   └── migrations/    # Database Migrations
│   ├── public/
│   │   ├── css/          # Frontend Styles
│   │   └── js/           # Frontend Scripts
│   ├── resources/
│   │   └── views/        # Blade Templates
│   └── routes/           # Route Definitions
├── frontend/             # Static Frontend (copy dari backend)
└── PLAN.md              # Rencana Proyek
```

## 🚀 Cara Menjalankan

### 1. Setup Database

```sql
CREATE DATABASE botkonten;
```

### 2. Install Dependencies

```bash
cd backend
composer install
```

### 3. Konfigurasi

Edit file `.env` sesuai konfigurasi database:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=botkonten
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Migration & Seeding

```bash
cd backend
php artisan migrate
```

### 5. Jalankan Server

```bash
php artisan serve
```

Akses: http://localhost:8000/webapp

## 🔌 API Endpoints

### Authentication
- `POST /api/auth/register` - Register user
- `POST /api/auth/login` - Login dengan telegram_id
- `GET /api/user/profile` - Get profile (auth required)
- `PUT /api/user/profile` - Update profile (auth required)

### Media
- `GET /api/media` - List published media
- `POST /api/media` - Create media (auth)
- `GET /api/media/{id}` - Get media detail
- `POST /api/media/{id}/publish` - Publish draft (auth)

### Drafts
- `GET /api/drafts` - List user drafts
- `POST /api/drafts` - Create draft
- `POST /api/drafts/{id}/publish` - Publish to media
- `DELETE /api/drafts/{id}` - Delete draft

### Albums
- `GET /api/albums` - List albums
- `POST /api/albums` - Create album

### Payment
- `POST /api/payment/checkout` - Checkout
- `POST /api/payment/{id}/simulate` - Simulate payment

### Reviews
- `POST /api/reviews` - Create review

### Webhook
- `POST /webhook/bot/{bot_id}` - Telegram webhook

## 📱 Integrasi Telegram

1. Buat bot via @BotFather
2. Set webhook: `https://your-domain.com/webhook/bot/{bot_id}`
3. User mengirim media ke bot
4. Media masuk sebagai draft
5. User buka Webapp untuk publish/manage

## 📊 Fitur yang Diimplementasikan

### MVP (Phase 1)
- ✅ Anonymous authentication
- ✅ Media upload via Telegram
- ✅ Draft system
- ✅ Album creation (max 10 media)
- ✅ Multi-bot support
- ✅ Payment simulation
- ✅ Rating & Review
- ✅ Wishlist
- ✅ Support conversation
- ✅ Analytics dashboard

### Phase 2-4
- ⏳ Search & Filter
- ⏳ Multi-language (ID/EN)
- ⏳ Dark mode
- ⏳ Badges & Gamification

## 🛠️ Teknologi

- **Backend**: Laravel 8.x + PHP 8.0-8.4
- **Database**: MySQL InnoDB
- **Frontend**: Plain HTML/CSS/JS + Telegram WebApp API
- **Payment**: Simulation + manual (extensible)
- **Logging**: Monolog (Laravel default)

## 📝 Lisensi

MIT