# Panduan Pengguna & Pembangun — Hutang Tracker
### Sistem Rahsia Dunia · Versi 1.1 · Dikemaskini: 14 Mei 2026

---

## Isi Kandungan

1. [User Flow — Cara Guna Module](#1-user-flow)
2. [Sistem Jemputan Contact](#2-sistem-jemputan-contact)
3. [Developer Setup Checklist](#3-developer-setup-checklist)
4. [Flow Diagram](#4-flow-diagram)

---

# 1. User Flow

## 1.1 Cara Tambah Hutang Baru

Pergi ke **Hutang → Tambah Hutang** atau klik butang `+ Tambah Hutang` di halaman utama.

### Bayar Penuh (Tanpa Ansuran)

```
1. Isi nama orang / syarikat (cth: "Azri", "Shopee PayLater")
2. Pilih Arah Hutang:
   → Saya berhutang   (wang keluar — saya kena bayar)
   ← Dia berhutang    (wang masuk — dia kena bayar saya)
3. Masukkan jumlah (RM)
4. Pilih kaedah bayaran: Tunai / Maybank / TnG / DuitNow / Splitwise / Lain
5. Nota (pilihan) — tujuan hutang, nombor rujukan, dll.
6. [BIARKAN] toggle "Bayar secara ansuran?" — jangan tick
7. Klik Simpan Hutang
```

### Bayar Ansuran

```
1. Isi maklumat asas (sama seperti atas)
2. Tick toggle "Bayar secara ansuran?"
3. Pilih bilangan ansuran: 1x, 2x, 3x, 6x, 12x, dll.
4. Pilih kekerapan: Bulanan / Mingguan
5. Pilih tarikh ansuran pertama
6. → Preview jadual ansuran akan muncul secara automatik (Alpine.js)
   Contoh: RM 650, 3x bulanan, mula 1 Jun 2025
   ┌────┬──────────┬─────────────┐
   │ #  │ Jumlah   │ Due Date    │
   ├────┼──────────┼─────────────┤
   │ 1  │ RM216.67 │ 1 Jun 2025  │
   │ 2  │ RM216.67 │ 1 Jul 2025  │
   │ 3  │ RM216.66 │ 1 Ogo 2025  │
   └────┴──────────┴─────────────┘
   (baki sen masuk ke ansuran terakhir)
7. Klik Simpan Hutang
   → Sistem auto-jana rekod dalam debt_installments
```

---

## 1.2 Cara Set Due Date dan Pilih Hari Warning

Bahagian ini muncul dalam form **Tambah / Edit Hutang**.

### Set Due Date Bulanan

```
Contoh: Shopee PayLater due setiap 28hb
→ Masukkan "28" dalam field "Due date setiap bulan"
→ Preview muncul: "Due date seterusnya: 28 Jun 2025 (dalam 14 hari)"

Nota: Jika bulan tiada hari tersebut (cth: Feb tiada 30hb),
      sistem akan guna hari terakhir bulan tersebut.
```

### Pilih Hari Warning

Tandakan hari yang anda mahu terima peringatan Telegram:

```
[ ] 14 hari sebelum  — peringatan awal
[x] 7 hari sebelum   — peringatan standard
[x] 3 hari sebelum   — peringatan sederhana urgent
[x] 1 hari sebelum   — peringatan urgent
[x] Pada hari due date
[x] Sehari selepas jika belum bayar
```

> Cadangan: Tick sekurang-kurangnya **7 hari** dan **1 hari** untuk coverage yang baik.

---

## 1.3 Cara Log Pembayaran Melalui Web

1. Pergi ke **Hutang → [Nama Hutang]**
2. Skrol ke bahagian **"Log Bayaran Baru"** — klik untuk expand
3. Isi maklumat:
   - Jumlah bayaran (RM)
   - Tarikh bayaran
   - Kaedah bayaran
   - Nota (pilihan)
4. Upload bukti (pilihan): JPG / PNG / PDF — maks 5MB
5. Klik **Rekod Bayaran**

```
Selepas simpan:
→ paid_amount dikemaskini secara automatik
→ Status hutang akan berubah:
  0%        → pending   (Belum Bayar)
  1%–99%    → partial   (Bayar Sebahagian)
  100%      → settled   (Selesai)
→ Baki dikira semula
```

---

## 1.4 Cara Upload Bukti Melalui Telegram Bot

Dua senario:

### Senario A — Selepas Tekan Butang "Dah Bayar" dalam Mesej Warning

```
1. Terima mesej warning Telegram (cth: "⚠️ Peringatan Segera!")
2. Tekan butang [✅ Dah Bayar]
3. Bot reply: "Hantar gambar bukti bayaran atau taip /skip"
4. Forward / hantar gambar resit atau screenshot ke bot
5. Bot reply: "✅ Bukti ansuran X berjaya disimpan. Baki: RMxx"

→ Sistem automatik:
   - Download gambar dari Telegram API
   - Simpan ke storage/public/proofs/
   - Update proof_source = 'telegram'
   - Mark ansuran sebagai 'paid'
   - Kemaskini baki hutang
```

### Senario B — Hantar Gambar Terus Tanpa Warning

```
1. Buka bot Telegram
2. Hantar gambar/PDF resit bayaran
3. Bot tanya: "Ini bukti untuk hutang mana?"
   → Senarai inline button muncul:
   [Azri (RM 200.00)] [Shopee (RM 150.00)] [...]
4. Tekan hutang yang berkaitan
5. Bot reply: "Hantar gambar/PDF bukti bayaran..."
6. Gambar yang dihantar tadi akan dilinkkan ke hutang tersebut
7. Bot confirm: "✅ Bukti bayaran disimpan."
   → Nota: Jumlah perlu dikemaskini manual di web kerana
     bot tidak tahu berapa yang dibayar
```

### Skip Upload Bukti

```
Jika tiada bukti, taip: /skip
→ Bot reply: "OK, skip bukti. Anda boleh hantar kemudian."
→ Pending state dibersihkan
```

---

## 1.5 Cara Guna Inline Button Telegram

Setiap mesej warning datang dengan 3 butang:

```
┌─────────────────────────────────────┐
│ ⚠️ Peringatan Segera!               │
│ Shopee PayLater — Ansuran 2/3       │
│ Jumlah: RM216.67                    │
│ Due date: 1 Jul 2025 (1 hari lagi!) │
│ Hantar bukti bayaran ke sini...     │
├─────────────────────────────────────┤
│ [✅ Dah Bayar] [⏰ Snooze 1 hari]   │
│ [📋 Lihat Semua Hutang]             │
└─────────────────────────────────────┘
```

| Button | Apa Berlaku |
|---|---|
| **✅ Dah Bayar** | Bot tunggu gambar bukti. Hantar gambar atau `/skip` |
| **⏰ Snooze 1 hari** | Warning tidak akan dihantar esok. Lusa akan remind semula |
| **📋 Lihat Semua Hutang** | Bot reply senarai semua hutang aktif dengan baki |

---

## 1.6 Cara Semak Baki dan Jadual Ansuran

### Melalui Web

```
Hutang → [Nama Hutang]

Panel atas menunjukkan:
  Jumlah Asal → Dibayar → Baki

Progress bar visual (0%–100%)

Jika hutang ansuran:
  Jadual Ansuran:
  ✅ #1 — RM216.67 | 1 Jun 2025   (paid 3 Jun 2025)
  ✅ #2 — RM216.67 | 1 Jul 2025   (paid 2 Jul 2025)
  🔲 #3 — RM216.66 | 1 Ogo 2025   → butang "Tandakan Bayar"

Badge: "2/3 ansuran"
```

### Melalui Telegram Bot

```
/hutang          — senarai semua hutang aktif + baki
/ansuran {id}    — jadual ansuran untuk hutang tertentu

Contoh output /ansuran 5:
  📅 Ansuran Shopee PayLater (2/3 bayar)
  ✅ #1 — RM216.67 | Due: 1 Jun 2025
  ✅ #2 — RM216.67 | Due: 1 Jul 2025
  🔲 #3 — RM216.66 | Due: 1 Ogo 2025
```

---

## 1.7 Cara Mark Hutang Sebagai Selesai

**Cara automatik** — sistem akan auto tukar status ke `settled` apabila:
```
paid_amount >= total_amount
```

**Cara manual** — log bayaran akhir dengan jumlah yang cukup:
```
1. Hutang → [Nama Hutang]
2. Log Bayaran Baru → masukkan baki yang tinggal
3. Klik Rekod Bayaran
4. Status bertukar: 🟢 Selesai
```

Hutang yang settled tidak akan muncul dalam senarai hutang aktif dan tidak akan terima warning lagi.

---

---

# 2. Sistem Jemputan Contact

## 2.1 Konsep

Setiap rekod hutang mempunyai **link jemputan unik** (Telegram deep link) yang boleh dihantar kepada pihak yang terlibat — sama ada pemiutang atau penghutang. Selepas mereka klik link dan join bot, mereka akan menerima peringatan bayaran secara automatik.

```
Owner (anda)                Contact (pemiutang/penghutang)
     │                               │
     │── Tambah hutang ──────────────│
     │   (masukkan phone contact)    │
     │                               │
     │   Sistem auto-jana            │
     │   invite_token unik           │
     │                               │
     │── Hantar link ───────────────>│
     │   via WhatsApp / Telegram     │
     │                               │
     │                   Contact klik link
     │                   Bot: "✅ Berjaya dipautkan"
     │                               │
     │<── "Contact telah join bot" ──│
     │                               │
     │   [Mulai sekarang]            │
     │   Warning → Owner + Contact   │
```

## 2.2 Cara Hantar Jemputan (User Flow)

### Langkah 1 — Tambah nombor telefon contact

Semasa tambah hutang baru, isi field **Nombor Telefon**:
```
cth: 0123456789  atau  +60123456789
```
Nombor ini digunakan untuk generate link WhatsApp automatik.

### Langkah 2 — Hantar link dari halaman detail hutang

Pergi ke halaman **detail hutang** → panel **"Jemputan Contact"**.

Status akan tunjuk salah satu daripada:
- `⏳ Belum Join Bot` — contact belum klik link
- `✅ Telegram Aktif` — contact sudah join bot

**Jika belum join**, tiga cara untuk hantar link:

```
┌─────────────────────────────────────────┐
│ [📱 Hantar via WhatsApp]                │
│   → Buka WhatsApp dengan mesej siap     │
│     diisi ke nombor contact             │
│                                         │
│ [✈️ Hantar via Telegram]                │
│   → Buka Telegram share dialog          │
│                                         │
│ [Salin Link]                            │
│   → Copy ke clipboard, hantar mana-mana │
└─────────────────────────────────────────┘
```

Mesej WhatsApp yang dihantar secara automatik:
```
Salam! Saya menggunakan app Hutang Tracker untuk rekod hutang kita.

Sila klik link ini untuk join bot Telegram supaya anda boleh terima peringatan bayaran:
https://t.me/hutangku_bot?start=invite_abc123xyz

Selepas klik, tekan Start dalam Telegram.
```

### Langkah 3 — Contact klik link & join bot

Contact akan nampak mesej ini selepas klik link:
```
✅ Berjaya! Anda telah dipautkan ke rekod hutang.

Shopee PayLater (dicatatkan oleh Ahmad)

💸 Anda berhutang kepada Ahmad sebanyak RM650.00

Baki semasa: RM433.33

Anda akan menerima peringatan bayaran secara automatik
melalui bot ini.
```

Owner juga akan menerima notifikasi:
```
✅ Shopee PayLater telah menerima jemputan bot.
Mereka kini akan menerima peringatan bayaran.
```

### Langkah 4 — Warning kepada kedua-dua pihak

Selepas contact join, setiap peringatan akan dihantar kepada:

| Arah Hutang | Owner Terima | Contact Terima |
|---|---|---|
| **Saya berhutang** | "Anda perlu bayar Shopee RM216.67" | "Ahmad akan bayar anda RM216.67" |
| **Dia berhutang** | "Azri perlu bayar anda RM216.67" | "Anda perlu bayar Ahmad RM216.67" |

## 2.3 Pengurusan Link Jemputan

### Jana Link Baru
Guna jika link lama terlanjur dikongsi dengan orang yang salah:
```
Halaman detail hutang → Jemputan Contact → [Jana Link Baru]
→ Link lama terus tidak sah
→ Contact yang dah join TIDAK terjejas (chat_id kekal)
→ Hanya untuk menjana link undangan baru
```

### Nyahpaut Contact
Jika perlu putuskan sambungan Telegram contact:
```
Halaman detail hutang → Jemputan Contact → [Nyahpaut Telegram]
→ contact_telegram_chat_id dikosongkan
→ Contact tidak akan terima warning lagi
→ Invite token kekal — contact boleh join semula dengan link yang sama
```

## 2.4 Env Variable Tambahan

```env
TELEGRAM_BOT_NAME=hutangku_bot   # username bot tanpa @
```

Ini diperlukan untuk generate invite link yang betul.

---

---

# 3. Developer Setup Checklist

## 2.1 Langkah Penuh dari Install hingga Bot Aktif

### Prasyarat
- [ ] PHP 8.2+
- [ ] Composer
- [ ] Node.js + npm
- [ ] PostgreSQL (atau SQLite untuk dev)
- [ ] Domain dengan HTTPS (untuk webhook Telegram — **wajib HTTPS**)

---

### Langkah 1 — Clone & Install

```bash
git clone <repo-url>
cd rahsiadunia

composer install
npm install && npm run build

cp .env.example .env
php artisan key:generate
```

---

### Langkah 2 — Setup Database

Edit `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=rahsiadunia
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

Jalankan migration:
```bash
php artisan migrate
```

---

### Langkah 3 — Storage Link

```bash
php artisan storage:link
# Bukti bayaran disimpan di storage/app/public/proofs/
```

---

### Langkah 4 — Daftar Bot dengan BotFather

```
1. Buka Telegram → cari @BotFather
2. Hantar: /newbot
3. Masukkan nama bot (cth: "Hutang Tracker Bot")
4. Masukkan username (mesti berakhir dengan 'bot', cth: hutangku_bot)
5. BotFather reply dengan TOKEN → salin token ini
```

Edit `.env`:
```env
TELEGRAM_BOT_TOKEN=7123456789:AAFxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
TELEGRAM_WEBHOOK_SECRET=pilih_string_rawak_yang_panjang
```

---

### Langkah 5 — Daftar Webhook

#### Untuk Production (domain HTTPS sedia ada):
```bash
php artisan telegram:set-webhook
# Output: ✅ Webhook berjaya didaftarkan.
# URL yang didaftar: https://yourdomain.com/api/telegram/webhook
```

#### Untuk Local Development (guna ngrok):
```bash
# Terminal 1 — jalankan server Laravel
php artisan serve

# Terminal 2 — expose port 8000
ngrok http 8000
# Ngrok bagi URL cth: https://abc123.ngrok.io

# Kemaskini APP_URL dalam .env:
APP_URL=https://abc123.ngrok.io

# Terminal 3 — daftar webhook
php artisan telegram:set-webhook

# Verify webhook aktif:
php artisan telegram:set-webhook --info
```

#### Alternatif — Expose (Laravel Herd / Valet):
```bash
# Jika guna Laravel Herd:
expose share rahsiadunia.test
# Dapat URL → kemaskini APP_URL → jalankan set-webhook
```

---

### Langkah 6 — Dapatkan telegram_chat_id

```
1. Buka Telegram → cari bot yang baru dibuat
2. Tekan Start / hantar /start
3. Hantar arahan: /myid
4. Bot reply: "Chat ID anda: 987654321"
5. Salin nombor tersebut
```

Masukkan dalam profil pengguna (sementara belum ada UI — boleh guna tinker):
```bash
php artisan tinker
>>> App\Models\User::find(1)->update(['telegram_chat_id' => '987654321'])
```

> Nota: Tambah field `telegram_chat_id` dalam form edit profil untuk UX yang lebih baik.

---

### Langkah 7 — Test Warning

```bash
# Cipta hutang dengan ansuran dahulu (melalui web)
# Kemudian test:
php artisan telegram:test-warning 1

# Output:
# ✅ Mesej ujian dihantar ke Ahmad (987654321).
```

---

### Langkah 8 — Aktifkan Scheduler

#### Cara semak scheduler didaftarkan:
```bash
php artisan schedule:list
# Patut ada:
# telegram:send-due-warnings  Daily at 08:00
```

#### Cara test scheduler secara manual:
```bash
php artisan schedule:run
# atau terus:
php artisan telegram:send-due-warnings
```

#### Pasang dalam crontab (production):
```bash
crontab -e
```
Tambah baris ini:
```
* * * * * cd /path/ke/rahsiadunia && php artisan schedule:run >> /dev/null 2>&1
```

Verify crontab aktif:
```bash
crontab -l
```

---

## 2.2 Common Errors & Cara Selesai

### ❌ "Unauthorized" dari webhook

**Punca:** `TELEGRAM_WEBHOOK_SECRET` dalam `.env` tidak sama dengan yang didaftarkan semasa `set-webhook`.

**Selesai:**
```bash
# Pastikan .env ada nilai, kemudian re-register:
php artisan telegram:set-webhook
```

---

### ❌ Bot tidak balas — tiada response

**Punca 1:** Webhook tidak berjaya didaftarkan atau URL salah.
```bash
php artisan telegram:set-webhook --info
# Semak: "url" dan "last_error_message"
```

**Punca 2:** APP_URL dalam `.env` menggunakan HTTP bukannya HTTPS.
```
Telegram webhook WAJIB HTTPS. Pastikan APP_URL bermula dengan https://
```

**Punca 3:** Queue belum jalan (jika ada job).
```bash
php artisan queue:work
```

---

### ❌ "Hutang tidak dijumpai" bila guna /bayar atau /ansuran

**Punca:** ID hutang yang dihantar bukan milik user tersebut, atau hutang telah dipadam.

**Selesai:** Guna `/hutang` dahulu untuk dapat senarai ID hutang yang betul.

---

### ❌ Gambar/bukti tidak disimpan

**Punca 1:** `storage:link` belum dijalankan.
```bash
php artisan storage:link
```

**Punca 2:** Folder `storage/app/public/proofs/` tiada permission tulis.
```bash
chmod -R 775 storage/
chown -R www-data:www-data storage/
```

**Punca 3:** `TELEGRAM_BOT_TOKEN` kosong — gagal download fail dari Telegram API.
```bash
# Semak .env, pastikan token ada dan betul
php artisan config:clear
```

---

### ❌ Warning tidak dihantar walaupun scheduler jalan

**Semak senarai:**
- [ ] `telegram_chat_id` user ada dalam database
- [ ] Hutang ada jadual ansuran (`is_installment = true`)
- [ ] Status ansuran masih `pending` (bukan `paid` atau `overdue`)
- [ ] `warning_days` debt tidak kosong
- [ ] `warning_sent_at` belum ada key untuk hari ini (anti-duplicate aktif)

**Debug:**
```bash
php artisan telegram:test-warning {id}
# Jika ini berjaya, bererti command ok tapi schedule tidak dipanggil
# Semak crontab: crontab -l
```

---

### ❌ diffInDays silap kira (hari negatif)

**Punca:** Parameter kedua `diffInDays` adalah `absolute`. Pastikan guna:
```php
$today->diffInDays($installment->due_date, false)
// false = boleh negatif (overdue)
```
Ini sudah betul dalam kod. Jika masalah berterusan, semak timezone di `config/app.php`:
```php
'timezone' => 'Asia/Kuala_Lumpur',
```

---

---

# 4. Flow Diagram

## 3.1 Flow Upload Bukti via Telegram Bot

```
USER                          TELEGRAM BOT                    SISTEM (Laravel)
 │                                 │                                │
 │── Hantar gambar resit ─────────>│                                │
 │                                 │── Ada pending state? ─────────>│
 │                                 │<──── Ya: {debt_id: 5} ────────│
 │                                 │                                │
 │                                 │── Download file dari ──────────│
 │                                 │   Telegram API                 │
 │                                 │── Simpan ke storage/ ─────────>│
 │                                 │   proofs/tg_xxx.jpg            │
 │                                 │── Update installment: ─────────│
 │                                 │   proof_source='telegram'      │
 │                                 │   status='paid'                │
 │                                 │── Kira semula baki ───────────>│
 │<── "✅ Bukti disimpan. ─────────│                                │
 │    Baki: RM216.66"              │                                │
 │                                 │── Clear pending state ────────>│
 │                                 │   Cache::forget(...)           │
 │                                 │                                │
 │                        ─────────────────────────────────────────
 │                        SENARIO: Tiada pending state
 │                        ─────────────────────────────────────────
 │── Hantar gambar resit ─────────>│                                │
 │                                 │── Tiada pending state ────────>│
 │                                 │── Ambil senarai hutang aktif ─>│
 │<── "Ini bukti untuk ────────────│                                │
 │    hutang mana?"                │                                │
 │    [Azri RM200] [Shopee RM150]  │                                │
 │                                 │                                │
 │── Tekan [Shopee RM150] ────────>│                                │
 │                                 │── Simpan pending state: ──────>│
 │                                 │   Cache {debt_id: 3} (5 min)   │
 │<── "Hantar gambar bukti..." ────│                                │
 │                                 │                                │
 │── Hantar gambar ───────────────>│                                │
 │                                 │── [proses seperti atas] ──────>│
 │<── "✅ Bukti disimpan." ────────│                                │
```

---

## 3.2 Flow Warning System

```
SCHEDULER (Setiap hari 08:00)
         │
         │  php artisan telegram:send-due-warnings
         │
         ▼
┌─────────────────────────────────────────────────────┐
│  Loop semua DebtInstallment status='pending'        │
│                                                     │
│  Untuk setiap ansuran:                              │
│  1. Ada telegram_chat_id?  ──Tidak──> SKIP          │
│  2. Dalam snooze?          ──Ya────> SKIP          │
│  3. Kira daysLeft = due_date - hari_ini             │
└──────────────┬──────────────────────────────────────┘
               │
       ┌───────┴────────┐
       ▼                ▼
  daysLeft < 0      daysLeft = 0
  (OVERDUE)         (DUE HARI INI)
       │                │
       ▼                ▼
  warn_if_overdue?  warn_on_due_date?
  ──Tidak──> SKIP   ──Tidak──> SKIP
       │                │
       ▼                ▼
  Dah hantar          Dah hantar
  hari ini?           hari ini?
  ──Ya────> SKIP      ──Ya────> SKIP
       │                │
       └───────┬────────┘
               │
       ┌───────┴────────────────────┐
       ▼                            ▼
  daysLeft dalam               Tiada match
  warning_days?                ──────────> SKIP
  (cth: [7,3,1])
       │
       ▼
  Dah hantar untuk
  key ini?
  ──Ya────> SKIP (anti-duplicate)
       │
       ▼
┌──────────────────────────────────┐
│  Bina teks mesej mengikut        │
│  bilangan hari                   │
│  (ringan/urgent/due/overdue)     │
└──────────────┬───────────────────┘
               │
               ▼
   Hantar ke Telegram API
   sendInstallmentWarning()
   dengan inline buttons:
   [✅ Dah Bayar] [⏰ Snooze] [📋 Lihat]
               │
               ▼
   Rekod dalam warning_sent_at:
   {"7_days": "2025-06-24 08:00:00"}
               │
               ▼
╔══════════════════════╗
║  USER TERIMA MESEJ   ║
╚══════════╦═══════════╝
           │
    ┌──────┴──────────────────┐
    ▼                         ▼                    ▼
[✅ Dah Bayar]          [⏰ Snooze]          [📋 Lihat Semua]
    │                         │                    │
    ▼                         ▼                    ▼
Bot reply:             snooze_until            Bot hantar
"Hantar bukti          = esok                  senarai hutang
atau /skip"                │                  aktif
    │                  Warning esok
    ▼                  tidak dihantar
User hantar
gambar/PDF
    │
    ▼
[Flow Upload Bukti]
(lihat diagram 3.1)
```

---

## 3.3 Flow Ringkas Keseluruhan Module

```
                        ┌──────────────────┐
                        │  Tambah Hutang   │
                        │  (Web Form)      │
                        └────────┬─────────┘
                                 │
                    ┌────────────┴────────────┐
                    ▼                         ▼
           Bayar Penuh                  Bayar Ansuran
           (1 rekod debt)          (debt + N installments)
                    │                         │
                    └────────────┬────────────┘
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │  Set Due Date &         │
                    │  Warning Days           │
                    └────────────┬────────────┘
                                 │
                    ┌────────────┴────────────┐
                    ▼                         ▼
           Scheduler 08:00           User Buat Bayaran
           hantar warning             (Web / Telegram)
                    │                         │
                    ▼                         ▼
           User action via           Update paid_amount
           inline button             Recalculate status
                    │                         │
                    └────────────┬────────────┘
                                 │
                                 ▼
                    ┌─────────────────────────┐
                    │  status = 'settled'?    │
                    │  paid >= total          │
                    └────────────┬────────────┘
                                 │
                    ┌────────────┴────────────┐
                    ▼                         ▼
               Ya — Selesai!           Tidak — Tunggu
               Tiada warning           bayaran seterusnya
               lagi
```

---

> **Versi:** 1.0 · Dihasilkan: 14 Mei 2026  
> **Projek:** Rahsia Dunia — Hutang Tracker Module  
> **Stack:** Laravel 11 + Alpine.js + Tailwind CSS + Telegram Bot API
