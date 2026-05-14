# Hutang Tracker — Dokumentasi Module

Dibina pada: 14 Mei 2026  
Stack: Laravel 11 + Alpine.js + Tailwind CSS + PostgreSQL  
Projek: Rahsia Dunia

---

## Struktur Fail

```
app/
├── Http/Controllers/
│   ├── HutangController.php           — CRUD hutang, log bayaran, mark ansuran bayar
│   └── TelegramWebhookController.php  — Handle semua update dari Telegram Bot
├── Models/
│   ├── Debt.php                       — Model hutang (accessors, scopes, helpers)
│   ├── DebtPayment.php                — Model rekod bayaran
│   └── DebtInstallment.php            — Model jadual ansuran
├── Services/
│   └── TelegramService.php            — API Telegram (send, download file, webhook)
└── Console/Commands/
    ├── SendDueWarnings.php            — Hantar peringatan harian (scheduler)
    ├── SetTelegramWebhook.php         — Register/semak webhook
    └── TestTelegramWarning.php        — Test warning untuk 1 hutang

database/migrations/
├── 2026_05_14_000001_add_telegram_fields_to_users_table.php
├── 2026_05_14_000002_create_debts_table.php
├── 2026_05_14_000003_create_debt_payments_table.php
└── 2026_05_14_000004_create_debt_installments_table.php

resources/views/hutang/
├── index.blade.php     — Dashboard + senarai hutang + filter
├── create.blade.php    — Form tambah hutang + preview jadual ansuran (Alpine.js)
├── show.blade.php      — Detail hutang, ansuran, sejarah bayaran
├── edit.blade.php      — Edit maklumat hutang
└── partials/
    ├── _status_badge.blade.php      — Badge status (Pending/Partial/Settled/Overdue)
    ├── _warning_settings.blade.php  — Form tetapan due date & peringatan
    ├── _installment_table.blade.php — Jadual ansuran + butang tandakan bayar
    └── _payment_form.blade.php      — Form log bayaran baru

routes/
├── web.php    — Route hutang.* + hutang.payment.store + hutang.installment.pay
├── api.php    — POST /api/telegram/webhook
└── console.php — Schedule telegram:send-due-warnings dailyAt('08:00')

config/
└── services.php — Tambah blok 'telegram' (bot_token, webhook_secret, allowed_chat_id)
```

---

## Database Schema

### Table: `debts`
| Column | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| user_id | FK → users | |
| contact_name | string | Nama orang berkenaan |
| direction | enum | `i_owe` / `they_owe` |
| total_amount | decimal(10,2) | Jumlah asal |
| paid_amount | decimal(10,2) | Jumlah yang dah dibayar |
| payment_method | string | cash/maybank/tng/duitnow/splitwise/other |
| description | text nullable | Nota/tujuan hutang |
| status | enum | `pending` / `partial` / `settled` |
| due_day_of_month | tinyint nullable | Hari dalam bulan (1–31) |
| warning_days | json nullable | cth: `[7,3,1]` |
| warn_on_due_date | boolean | Default true |
| warn_if_overdue | boolean | Default true |
| is_installment | boolean | Default false |
| installment_count | smallint nullable | Berapa kali ansuran |
| installment_frequency | enum | `monthly` / `weekly` / `custom` |
| first_installment_date | date nullable | Tarikh ansuran pertama |

### Table: `debt_payments`
| Column | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| debt_id | FK → debts | |
| amount | decimal(10,2) | |
| payment_date | date | |
| payment_method | string | |
| notes | text nullable | |
| proof_path | string nullable | Path dalam storage/public |
| proof_source | enum nullable | `web` / `telegram` |
| telegram_message_id | string nullable | ID mesej Telegram |

### Table: `debt_installments`
| Column | Type | Keterangan |
|---|---|---|
| id | bigint PK | |
| debt_id | FK → debts | |
| installment_number | smallint | Urutan ansuran (1,2,3...) |
| amount | decimal(10,2) | |
| due_date | date | |
| paid_at | timestamp nullable | |
| status | enum | `pending` / `paid` / `overdue` |
| proof_path | string nullable | |
| proof_source | enum nullable | `web` / `telegram` |
| telegram_message_id | string nullable | |
| notes | text nullable | |
| warning_sent_at | json nullable | cth: `{"7_days":"2025-06-21 08:00:00"}` |
| snooze_until | date nullable | Skip warning sehingga tarikh ini |

### Tambahan dalam `users`
| Column | Type | Keterangan |
|---|---|---|
| telegram_chat_id | string nullable | Untuk hantar mesej Telegram |
| default_warning_days | json nullable | Default warning baru |
| warning_time | time | Default 08:00:00 |

---

## Routes

### Web (auth + verified)
```
GET    /hutang                              hutang.index
GET    /hutang/create                       hutang.create
POST   /hutang                              hutang.store
GET    /hutang/{hutang}                     hutang.show
GET    /hutang/{hutang}/edit                hutang.edit
PUT    /hutang/{hutang}                     hutang.update
DELETE /hutang/{hutang}                     hutang.destroy
POST   /hutang/{hutang}/payments            hutang.payment.store
POST   /hutang/{hutang}/installments/{ins}  hutang.installment.pay
```

### API (no auth — protected by webhook secret header)
```
POST   /api/telegram/webhook
```

---

## Artisan Commands

```bash
# Hantar semua peringatan hari ini
php artisan telegram:send-due-warnings

# Test warning untuk hutang tertentu
php artisan telegram:test-warning {debt_id}

# Daftar webhook ke Telegram
php artisan telegram:set-webhook

# Semak maklumat webhook semasa
php artisan telegram:set-webhook --info
```

---

## Telegram Bot

### Commands yang disokong
| Command | Fungsi |
|---|---|
| `/start` | Salam + senarai arahan |
| `/hutang` | Senarai hutang aktif |
| `/bayar {id} {jumlah}` | Log bayaran cepat |
| `/ansuran {id}` | Jadual ansuran hutang |
| `/myid` | Dapatkan chat ID (tanpa auth) |
| `/skip` | Skip upload bukti |

### Inline Buttons (dalam mesej warning)
| Button | Aksi |
|---|---|
| ✅ Dah Bayar | Set pending state → tunggu bukti |
| ⏰ Snooze 1 hari | Set `snooze_until = esok` |
| 📋 Lihat Semua Hutang | Reply senarai hutang aktif |

### Aliran Upload Bukti via Telegram
1. User tekan **Dah Bayar** → bot simpan pending state dalam Cache (10 min)
2. User hantar gambar/PDF → bot download dari Telegram API
3. Simpan ke `storage/public/proofs/tg_xxxx.jpg`
4. Update `proof_source = 'telegram'` dalam `debt_installments` / `debt_payments`
5. Bot reply: "✅ Bukti berjaya disimpan. Baki: RMxx"

Jika tiada pending state, bot tanya "Ini bukti untuk hutang mana?" dengan inline buttons.

---

## Format Mesej Warning

```
7/14 hari sebelum (ringan):
⚠️ Peringatan Hutang
{nama} — Ansuran {n}/{total}
Jumlah: RM {amount}
Due date: {date} ({N} hari lagi)
Baki keseluruhan: RM {balance}

3/1 hari sebelum (urgent):
⚠️ Peringatan Segera!
{nama} — Ansuran {n}/{total}
Due date: {date} ({N} hari lagi!)
Hantar bukti bayaran ke sini selepas bayar.

Hari due date:
🔴 HARI INI DUE DATE!
Sila bayar sekarang untuk elak penalti.

Overdue:
🚨 OVERDUE - BELUM BAYAR
Due date: {date} (dah lepas!)
Sila bayar segera.
```

Anti-duplicate: sistem semak `warning_sent_at` sebelum hantar — key format `7_days`, `due_date`, `overdue_YYYY-MM-DD`.

---

## Cara Setup (Dev ke Prod)

### 1. Jalankan migration
```bash
php artisan migrate
```

### 2. Symbolic link storage
```bash
php artisan storage:link
```

### 3. Setup .env
```env
TELEGRAM_BOT_TOKEN=123456:ABC-DEF...
TELEGRAM_WEBHOOK_SECRET=sebarang_string_rahsia
TELEGRAM_ALLOWED_CHAT_ID=       # optional, biarkan kosong untuk semua
```

### 4. Register webhook
```bash
# Untuk local dev — guna ngrok atau expose dahulu
ngrok http 8000

# Kemudian:
php artisan telegram:set-webhook

# Verify:
php artisan telegram:set-webhook --info
```

### 5. Dapatkan Chat ID
- Buka bot di Telegram → hantar `/myid`
- Bot reply dengan chat ID anda
- Masukkan dalam Tetapan Profil (field `telegram_chat_id`)

### 6. Test end-to-end
```bash
# Buat hutang dulu, kemudian:
php artisan telegram:test-warning 1

# Test scheduler secara manual:
php artisan telegram:send-due-warnings
```

### 7. Aktifkan scheduler (production)
Tambah dalam crontab server:
```bash
* * * * * cd /path/ke/projek && php artisan schedule:run >> /dev/null 2>&1
```

---

## Ciri Alpine.js

- **Form create** — `hutangForm()` component dengan live preview jadual ansuran dalam browser (kira tanpa AJAX)
- **Warning settings partial** — `warningSettingsMixin()` untuk kira dan preview "due date seterusnya" secara real-time
- **Payment form** — collapsible accordion dengan `x-show`
- **Installment pay** — inline expand form untuk upload bukti per-ansuran

---

## Nota Tambahan

- `balance` — dikira sebagai accessor `$debt->balance = total_amount - paid_amount` (bukan stored column)
- `progress_percent` — accessor 0–100 untuk progress bar
- `next_due_date` — accessor Carbon yang auto-adjust untuk bulan pendek (Feb, dll.)
- Bukti dari Telegram ditanda dengan 📱 dalam UI
- Profile edit page perlu ditambah field `telegram_chat_id` secara manual
