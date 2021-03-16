#!/bin/bash

ARCHIVE_URL=$1
FILENAME=$2

docker exec -u www-data prestashop_autoupgrade mkdir -p admin-dev/autoupgrade/download
docker exec -u www-data prestashop_autoupgrade curl $ARCHIVE_URL -o admin-dev/autoupgrade/download/$FILENAME
docker exec -u root prestashop_autoupgrade chmod 777 -R /var/www/html