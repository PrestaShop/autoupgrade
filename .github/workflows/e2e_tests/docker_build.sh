#!/bin/bash

docker-compose -f docker-compose.yml up -d
bash -c 'while [[ "$(curl -L -s -o /dev/null -w %{http_code} http://localhost:8001/index.php)" != "200" ]]; do sleep 5; done'