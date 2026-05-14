# rahsiadunia - Secure Vault

![rahsiadunia logo](public/images/logo.jpg)

**rahsiadunia** is a personal, highly-encrypted secure vault designed to keep your notes, credentials, and important data safe. Built with a focus on privacy and a minimalist "Nude UI" aesthetic, it provides a serene yet powerful environment for managing your digital secrets.

## ✨ Features

- **🔐 Secure Notes**: Store private thoughts, ideas, or sensitive information with confidence.
- **📁 Account Manager**: Keep track of your various accounts and credentials in one central, secure location.
- **📊 Google Sheets Integration**: Sync and manage specific data directly with Google Sheets for extended productivity.
- **🎨 Nude UI Theme**: A clean, calming interface designed to reduce digital clutter and focus on what matters.
- **🛡️ AES-256-GCM Encryption**: Your data is protected using industry-standard encryption protocols.

## 🚀 Getting Started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL or PostgreSQL

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/afiez97/rahsiadunia-laravel-app.git
   cd rahsiadunia
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database Migration**
   Configure your database in `.env`, then run:
   ```bash
   php artisan migrate
   ```

5. **Start the application**
   ```bash
   php artisan serve
   ```

## 🛠️ Technology Stack

- **Framework**: [Laravel 11](https://laravel.com)
- **Frontend**: [Tailwind CSS](https://tailwindcss.com) with custom Nude Theme
- **Authentication**: Laravel Breeze
- **Database**: Eloquent ORM

## 📄 License

The rahsiadunia vault is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
