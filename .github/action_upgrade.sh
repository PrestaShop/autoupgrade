#!/bin/bash

if [[ $CHANNEL == "archive" ]]; then
  docker exec -u www-data prestashop_autoupgrade mkdir admin-dev/autoupgrade/download
  docker exec -u www-data prestashop_autoupgrade curl $ARCHIVE_URL -o admin-dev/autoupgrade/download/prestashop.zip
  echo "{\"channel\":\"archive\",\"archive_prestashop\":\"prestashop.zip\",\"archive_num\":\"${VERSION}\"}" > config.json
  docker exec -u www-data prestashop_autoupgrade php admin-dev/autoupgrade/cli-updateconfig.php --from=modules/autoupgrade/config.json --dir=admin-dev
fi

docker exec -u www-data prestashop_autoupgrade php modules/autoupgrade/tests/testCliProcess.php admin-dev/autoupgrade/cli-upgrade.php  --dir="admin-dev" --channel="$CHANNEL"
