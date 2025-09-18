# Suggested Commands for Shortlink Project

## Development Commands
```bash
# Start development server with queue and Vite
composer dev

# Start Laravel development server only
php artisan serve

# Run queue worker
php artisan queue:listen --tries=1

# Run Vite for assets
npm run dev
```

## Testing Commands
```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test
php artisan test --filter=TestName
```

## Code Quality Commands
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run Pint with specific configuration
./vendor/bin/pint --config=pint.json
```

## Database Commands
```bash
# Run migrations
php artisan migrate

# Run migrations with grace (no errors if already run)
php artisan migrate --graceful

# Rollback migrations
php artisan migrate:rollback

# Refresh database
php artisan migrate:refresh

# Run seeders
php artisan db:seed
```

## Artisan Commands
```bash
# Clear various caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate application key
php artisan key:generate

# Tinker (REPL)
php artisan tinker
```

## Windows Specific
```powershell
# PowerShell commands for file operations
Get-ChildItem     # equivalent to ls
Set-Location      # equivalent to cd
Select-String     # equivalent to grep
Get-Content       # equivalent to cat
```