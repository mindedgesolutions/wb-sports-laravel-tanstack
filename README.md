All Wb Services pages (Website and Admin panel) are prefixed with : Wb
All Wb Services components (Website and Admin panel) are prefixed with : Wbc

All Wb Sports pages (Website and Admin panel) are prefixed with: Spp
All Wb Sports components (Website and Admin panel) are prefixed with: Spc

## How to run the project

Backend:
Run composer install
Run php artisan key:generate
Run php artisan migrate
Run php artisan migrate:fresh (only when no tables)
Run php artisan db:seed to run seeders, if any

For this project:

1. php artisan db:seed --class=RoleSeeder
2. php artisan db:seed --class=UserSeeder

For passport run: php artisan passport:keys

Run: php artisan optimize:clear

php artisan passport:client --personal

Update values of the following in .env:

PASSPORT_PERSONAL_ACCESS_CLIENT_ID="client-id-value"
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET="unhashed-client-secret-value"

Run: php artisan optimize:clear

Run php artisan serve

Frontend:
Run npm install
Run npm run dev

<!-- Changes in production Laravel 12 application: -->

## config/cors.php

Old : 'supports_credentials' => false,
New : 'supports_credentials' => true,

Old : 'allowed_origins' => ['*'],
New : 'allowed_origins' => ['http://localhost:5173', 'http://172.25.150.159'],

## Changes in .env

Old : APP_ENV=local
New : APP_ENV=production

Old : APP_DEBUG=true
New: APP_DEBUG=false

Old : APP_URL=http://127.0.0.1:8000
New : APP_URL=http://172.25.150.159:8000

Old : LOG_LEVEL=debug
New : LOG_LEVEL=warning
