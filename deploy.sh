#!/bin/bash

# Install dependencies
echo "****** Installing Composer dependencies..."
cd lumen; composer install; cd ..;
echo "****** Installing Composer dependencies DONE!"

# build images 
echo "****** Building images..."
docker-compose build;
echo "****** Building images DONE!"

# run db image
echo "****** Starting DB container..."
docker-compose -f docker-compose.yml up --detach db_server;
docker exec -it merchant_db_server_1 /root/waitForMySQL.sh;
echo "****** Starting DB container DONE!"

# run app image
echo "****** Starting APP container..."
docker-compose -f docker-compose.yml up --detach swoole;
echo "****** Starting APP container DONE!"