#!/bin/sh

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