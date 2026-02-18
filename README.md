# GMAO-TECH (Laravel 12)

Industrial maintenance management platform (GMAO) with:
- Maintenance requests and workflows
- Preventive/predictive plans
- Projects and assets management
- Team roles and notifications
- Arabic, French, and English UI
- Audit trail for create/update/delete operations
- Rate limiting and active-user enforcement

## Requirements
- PHP 8.2+ (8.3 recommended)
- Composer
- MySQL 8+
- Apache 2.4+
- Node.js 18+ (for frontend build)

## Installation
1. Copy `.env.example` to `.env`.
2. Configure MySQL credentials in `.env`.
3. Run:
```powershell
composer install
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

## Run (Local)
- Laravel dev server:
```powershell
php artisan serve --host=127.0.0.1 --port=8000
```
- Apache setup script (optional):
```powershell
powershell -ExecutionPolicy Bypass -File scripts/configure-apache.ps1
```

## Default Accounts (seed)
- `admin@gmao.local` / `Admin@12345`
- `manager@gmao.local` / `Manager@12345`
- `tech@gmao.local` / `Tech@12345`
- `employee@gmao.local` / `Employee@12345`

## Useful Commands
```powershell
php artisan optimize:clear
php artisan test
php artisan route:list
```

## Production Notes
- Enable HTTPS.
- Set `APP_ENV=production`, `APP_DEBUG=false`.
- Configure queue worker and scheduler:
```powershell
php artisan schedule:work
```
- Ensure log rotation for `storage/logs/laravel.log`.
