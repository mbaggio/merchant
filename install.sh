#!/bin/bash

# Install dependencies
cd lumen; composer install; cd ..;

# build images 
docker-compose build;

# run images
docker-compose -f docker-compose.yml up --detach db_server;
sleep 5; docker exec -it merchant_db_server_1 /root/waitForMySQL.sh;

# run app
docker-compose -f docker-compose.yml up --detach swoole;