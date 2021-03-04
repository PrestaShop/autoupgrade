#!/bin/bash

if [[ $CHANNEL == "archive" ]]; then
  if [[ $FROM == 1.6* ]] && [[ $VERSION == 1.7* ]]; then
    UPDATE_THEME=1
  else
    UPDATE_THEME=0
  fi
  docker exec -u www-data prestashop_autoupgrade mkdir admin-dev/autoupgrade/download
  docker exec -u www-data prestashop_autoupgrade curl $ARCHIVE_URL -o admin-dev/autoupgrade/download/prestashop.zip
  echo "{\"channel\":\"archive\",\"archive_prestashop\":\"prestashop.zip\",\"archive_num\":\"${VERSION}\", \"PS_AUTOUP_CHANGE_DEFAULT_THEME\":${UPDATE_THEME}}" > config.json
  docker exec -u www-data prestashop_autoupgrade php admin-dev/autoupgrade/cli-updateconfig.php --from=modules/autoupgrade/config.json --dir=admin-dev
fi

docker exec -u www-data prestashop_autoupgrade php modules/autoupgrade/tests/testCliProcess.php admin-dev/autoupgrade/cli-upgrade.php  --dir="admin-dev"
