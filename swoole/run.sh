#!/bin/sh

# clear config cache
php artisan config:clear

# run migrations
php artisan migrate

php artisan swoole:http start