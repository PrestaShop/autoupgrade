#!/bin/bash

docker exec -u www-data prestashop_autoupgrade php modules/autoupgrade/bin/console backup:create admin-dev
docker exec -u www-data prestashop_autoupgrade php modules/autoupgrade/bin/console update:start --action="CompareReleases" admin-dev
docker exec -u www-data prestashop_autoupgrade php modules/autoupgrade/bin/console update:start admin-dev
