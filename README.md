# 🔗 Shortlink Application

> A powerful URL shortening service built with Laravel

[![Tests](https://img.shields.io/badge/tests-91%20passing-brightgreen.svg)](./CLEAN_UNIT_TEST_STATUS.md)
[![PHP](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11.x-red.svg)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [API Documentation](#-api-documentation)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Contributing](#-contributing)
- [License](#-license)

## ✨ Features

- **URL Shortening** - Create short, memorable links for long URLs
- **Custom Domains** - Support for multiple custom domains
- **Password Protection** - Secure links with password authentication
- **Expiration Dates** - Set automatic link expiration
- **Click Analytics** - Track clicks, geography, and user agents
- **Bulk Operations** - Create and manage multiple links
- **RESTful API** - Full API support for integrations
- **Admin Dashboard** - Web interface for link management
- **Database Support** - MySQL, PostgreSQL, SQLite compatible

## 🔧 Requirements

- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **Database**: MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+
- **Extensions**: PDO, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON

## 🚀 Installation

### 1. Clone Repository
```bash
git clone https://github.com/your-username/shortlink.git
cd shortlink
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Configuration
Edit `.env` file with your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shortlink
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations
```bash
# Create database tables
php artisan migrate

# (Optional) Seed sample data
php artisan db:seed
```

### 6. Start Development Server
```bash
php artisan serve
```

Visit: `http://localhost:8000`

## ⚙️ Configuration

### Essential Settings

```env
# Application
APP_NAME="Shortlink"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Default Domain
DEFAULT_DOMAIN=yourdomain.com

# Link Settings
DEFAULT_SHORT_CODE_LENGTH=6
MAX_URL_LENGTH=2048

# Analytics
ENABLE_ANALYTICS=true
TRACK_USER_AGENTS=true
TRACK_GEOGRAPHY=true
```

### Cache Configuration (Optional)
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## 📖 Usage

### Web Interface

1. **Create Short Link**
   - Navigate to `/shortlinks/create`
   - Enter original URL
   - Set optional parameters (expiration, password, custom code)
   - Click "Create"

2. **View Analytics**
   - Go to `/shortlinks/{id}/analytics`
   - View click statistics, geography, and trends

3. **Manage Links**
   - List all links at `/shortlinks`
   - Edit, activate/deactivate, or delete links

### Command Line

```bash
# Create a short link
php artisan shortlink:create https://example.com

# Generate analytics report
php artisan shortlink:analytics {id}

# Clean expired links
php artisan shortlink:cleanup
```

## 🔌 API Documentation

### Authentication
```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

### Create Short Link
```http
POST /api/shortlinks
Authorization: Bearer {token}
Content-Type: application/json

{
  "original_url": "https://example.com/very/long/url",
  "domain_id": 1,
  "short_code": "custom",
  "expires_at": "2024-12-31",
  "password": "secret",
  "description": "My Link"
}
```

### Get Link Analytics
```http
GET /api/shortlinks/{id}/analytics
Authorization: Bearer {token}
```

### Response Format
```json
{
  "success": true,
  "data": {
    "id": 1,
    "short_code": "abc123",
    "original_url": "https://example.com",
    "short_url": "https://yourdomain.com/abc123",
    "clicks_count": 42,
    "created_at": "2024-01-15T10:30:00Z"
  }
}
```

## 🧪 Testing

### Run All Tests
```bash
# Run complete test suite
php artisan test

# Run only unit tests
php artisan test --testsuite=Unit

# Run with coverage (if configured)
php artisan test --coverage
```

### Test Structure
```
tests/
├── Unit/
│   ├── Models/ShortlinkTest.php     ✅ 25 tests
│   ├── Database/FactoryTest.php     ✅ 34 tests
│   └── Database/MigrationTest.php   ✅ 31 tests
└── Feature/
    └── ShortlinkApiTest.php
```

**Current Status**: 91 tests passing (255 assertions)

## 🌐 Deployment

### Production Environment

1. **Server Setup**
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

2. **Environment Configuration**
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use production database
DB_CONNECTION=mysql
DB_HOST=production-host
DB_DATABASE=production_db
```

3. **Web Server (Nginx)**
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/shortlink/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Docker Deployment

```dockerfile
# Dockerfile
FROM php:8.1-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application
WORKDIR /var/www
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www
```

### Environment Variables for Production

```env
# Security
APP_KEY=base64:your-32-character-secret-key
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true

# Performance
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Monitoring
LOG_CHANNEL=stack
LOG_LEVEL=error

# Backup
BACKUP_DISK=s3
```

## 🔒 Security

- **Input Validation** - All inputs are validated and sanitized
- **CSRF Protection** - Built-in CSRF token validation
- **Rate Limiting** - API rate limiting to prevent abuse
- **Password Hashing** - Secure password hashing for protected links
- **SQL Injection** - Protected by Eloquent ORM
- **XSS Prevention** - Output escaping and content security policy

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Ensure all tests pass

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

---

**Built with ❤️ using Laravel**
