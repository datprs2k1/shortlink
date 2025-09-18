@echo off
setlocal enabledelayedexpansion

echo 🔗 Shortlink Application Installer (Windows)
echo =============================================

REM Check if PHP is installed
php -v >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP is not installed. Please install PHP 8.1+ first.
    pause
    exit /b 1
)

REM Check if Composer is installed
composer --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ Composer is not installed. Please install Composer first.
    pause
    exit /b 1
)

echo ✅ PHP detected
echo ✅ Composer detected

REM Install dependencies
echo.
echo 📦 Installing dependencies...
composer install --no-interaction
if %errorlevel% neq 0 (
    echo ❌ Failed to install dependencies
    pause
    exit /b 1
)

REM Setup environment
echo.
echo ⚙️ Setting up environment...
if not exist .env (
    copy .env.example .env >nul
    echo ✅ Environment file created
) else (
    echo ⚠️ Environment file already exists
)

REM Generate application key
echo.
echo 🔑 Generating application key...
php artisan key:generate --no-interaction

REM Database configuration info
echo.
echo 📊 Database Configuration
echo Please ensure your database is configured in .env file:
echo   DB_CONNECTION=mysql
echo   DB_HOST=127.0.0.1
echo   DB_PORT=3306
echo   DB_DATABASE=shortlink
echo   DB_USERNAME=your_username
echo   DB_PASSWORD=your_password
echo.

REM Ask about migrations
set /p migrate="Run database migrations now? (y/n): "
if /i "!migrate!"=="y" (
    echo 🗄️ Running migrations...
    php artisan migrate --no-interaction
    if %errorlevel% equ 0 (
        echo ✅ Migrations completed
        
        REM Ask about seeding
        set /p seed="Seed sample data? (y/n): "
        if /i "!seed!"=="y" (
            echo 🌱 Seeding sample data...
            php artisan db:seed --no-interaction
            if %errorlevel% equ 0 (
                echo ✅ Seeding completed
            )
        )
    )
)

REM Check PHP extensions
echo.
echo 🔧 Checking PHP extensions...
php -m | findstr pdo_sqlite >nul
if %errorlevel% equ 0 (
    echo ✅ PDO SQLite extension is enabled
) else (
    echo ⚠️ PDO SQLite extension is not enabled (needed for testing)
    echo Please enable pdo_sqlite in your php.ini file
)

REM Ask about running tests
set /p tests="Run tests to verify installation? (y/n): "
if /i "!tests!"=="y" (
    echo 🧪 Running tests...
    php artisan test --testsuite=Unit
)

REM Final instructions
echo.
echo 🎉 Installation completed!
echo.
echo 📝 Next steps:
echo 1. Configure your .env file with proper database credentials
echo 2. Run: php artisan migrate (if not done already)
echo 3. Start development server: php artisan serve
echo 4. Visit: http://localhost:8000
echo.
echo 📚 Documentation: See README.md for detailed usage instructions
echo 🧪 Tests: Run 'php artisan test --testsuite=Unit' to run the test suite
echo.
echo Happy shortening! 🔗
pause