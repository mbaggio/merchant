#!/bin/sh

# wait until MySQL is really available
printf "\n****** Waiting for Composer dependencies availability (it may take a few minutes)..."

maxcounter=600
counter=1

sleep 5
installed_dependencies=$(ls -l vendor/ | wc -l);

while [ $installed_dependencies != '38' ]
do
    sleep 5
    printf "\n****** $installed_dependencies of 38"
    counter=`expr $counter + 1`
    if [ $counter -gt $maxcounter ]; then
        >&2 echo "\nWe have been waiting for Composer too long already; failing."
        exit 1
    fi;
    
    installed_dependencies=$(ls -l vendor/ | wc -l);
done

echo "\n****** Composer dependencies ready!"
