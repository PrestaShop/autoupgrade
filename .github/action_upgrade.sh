#!/bin/bash

if [[ $CHANNEL == "archive" ]]; then
  if [[ "${FROM:0:1}" == 1 ]] && [[ "${VERSION:0:1}" == 1 ]]; then
    if [[ "${FROM:2:1}" == "${VERSION:2:1}" ]]; then
      UPDATE_THEME=0
    else
      UPDATE_THEME=1
    fi
  else
    if [[ "${FROM:0:1}" == "${VERSION:0:1}" ]]; then
      UPDATE_THEME=0
    else
      UPDATE_THEME=1
    fi
  fi

  SKIP_BACKUP=$(docker exec -u www-data prestashop_autoupgrade ls admin-dev/autoupgrade/backup/ | wc -l)
  if [[ "$SKIP_BACKUP" > 1 ]]; then
    SKIP_BACKUP=1
  else
    SKIP_BACKUP=0
  fi

  docker exec -u www-data prestashop_autoupgrade mkdir admin-dev/autoupgrade/download
  docker exec -u www-data prestashop_autoupgrade curl -L $ARCHIVE_URL -o admin-dev/autoupgrade/download/prestashop.zip
  echo "{\"channel\":\"archive\",\"archive_prestashop\":\"prestashop.zip\",\"archive_num\":\"${VERSION}\", \"PS_AUTOUP_CHANGE_DEFAULT_THEME\":${UPDATE_THEME}, \"skip_backup\": ${SKIP_BACKUP}}" > config.json
  docker exec -u www-data prestashop_autoupgrade php admin-dev/autoupgrade/cli-updateconfig.php --from=modules/autoupgrade/config.json --dir=admin-dev
fi

docker exec -u www-data prestashop_autoupgrade php modules/autoupgrade/tests/testCliProcess.php admin-dev/autoupgrade/cli-upgrade.php  --dir="admin-dev"
