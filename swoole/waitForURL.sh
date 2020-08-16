#!/bin/sh

printf "\n****** Waiting for Lumen availability (it may take a few minutes)..."

until $(curl --output /dev/null --silent --head --fail http://localhost); do
    printf '.'
    sleep 5
done

echo "\n****** Lumen is ready!"