#!/bin/sh

# Install dependencies
echo "****** Installing Composer dependencies..."
composer install --ignore-platform-reqs
echo "****** Installing Composer dependencies DONE!"

# clear config cache
php artisan config:clear

# run migrations
php artisan migrate

# generate Swagger docs
php artisan swagger-lume:publish-config
php artisan swagger-lume:publish-views
php artisan swagger-lume:publish
php artisan swagger-lume:generate

# serve the application with swoole
php artisan swoole:http start