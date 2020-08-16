#!/bin/bash

# build images 
echo "****** Building images..."
cp .env ./lumen/
docker-compose build;
echo "****** Building images DONE!"

# run db image
echo "****** Starting DB container..."
docker-compose -f docker-compose.yml up --detach db_server;
docker exec -it container_db_server /root/waitForMySQL.sh;
echo "****** Starting DB container DONE!"

# run cache image
echo "****** Starting APP container..."
docker-compose -f docker-compose.yml up --detach cache_server;
echo "****** Starting APP container DONE!"

# run app image
echo "****** Starting APP container..."
docker-compose -f docker-compose.yml up --detach swoole_server;
docker exec -it container_swoole_server /root/waitForComposer.sh;
docker exec -it container_swoole_server /root/waitForURL.sh;
echo "****** Starting APP container DONE!"