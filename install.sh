#!/bin/bash

# Shortlink Application Installation Script
# This script automates the installation process

set -e

echo "🔗 Shortlink Application Installer"
echo "=================================="

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed. Please install PHP 8.1+ first."
    exit 1
fi

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
if ! php -r "exit(version_compare(PHP_VERSION, '8.1.0', '<') ? 1 : 0);"; then
    echo "❌ PHP 8.1+ is required. Current version: $PHP_VERSION"
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed. Please install Composer first."
    exit 1
fi

echo "✅ PHP $PHP_VERSION detected"
echo "✅ Composer detected"

# Install dependencies
echo ""
echo "📦 Installing dependencies..."
composer install --no-interaction

# Setup environment
echo ""
echo "⚙️ Setting up environment..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "✅ Environment file created"
else
    echo "⚠️ Environment file already exists"
fi

# Generate application key
echo ""
echo "🔑 Generating application key..."
php artisan key:generate --no-interaction

# Check database configuration
echo ""
echo "📊 Database Configuration"
echo "Please ensure your database is configured in .env file:"
echo "  DB_CONNECTION=mysql"
echo "  DB_HOST=127.0.0.1"
echo "  DB_PORT=3306"
echo "  DB_DATABASE=shortlink"
echo "  DB_USERNAME=your_username"
echo "  DB_PASSWORD=your_password"
echo ""

# Ask if user wants to run migrations
read -p "Run database migrations now? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🗄️ Running migrations..."
    php artisan migrate --no-interaction
    echo "✅ Migrations completed"
    
    # Ask about seeding
    read -p "Seed sample data? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "🌱 Seeding sample data..."
        php artisan db:seed --no-interaction
        echo "✅ Seeding completed"
    fi
fi

# Enable SQLite PDO if needed
echo ""
echo "🔧 Checking PHP extensions..."
if php -m | grep -q "pdo_sqlite"; then
    echo "✅ PDO SQLite extension is enabled"
else
    echo "⚠️ PDO SQLite extension is not enabled (needed for testing)"
    echo "Please enable pdo_sqlite in your php.ini file"
fi

# Run tests
echo ""
read -p "Run tests to verify installation? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🧪 Running tests..."
    php artisan test --testsuite=Unit
fi

# Final instructions
echo ""
echo "🎉 Installation completed!"
echo ""
echo "📝 Next steps:"
echo "1. Configure your .env file with proper database credentials"
echo "2. Run: php artisan migrate (if not done already)"
echo "3. Start development server: php artisan serve"
echo "4. Visit: http://localhost:8000"
echo ""
echo "📚 Documentation: See README.md for detailed usage instructions"
echo "🧪 Tests: Run 'php artisan test --testsuite=Unit' to run the test suite"
echo ""
echo "Happy shortening! 🔗"