#!/bin/bash

if [[ $CHANNEL == "local" ]]; then
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
  docker exec -u www-data prestashop_autoupgrade curl -L $XML_URL -o modules/autoupgrade/download/prestashop.xml
  echo "{\"channel\":\"local\",\"archive_prestashop\":\"prestashop.zip\",\"archive_num\":\"${VERSION}\", \"archive_xml\":\"prestashop.xml\", \"PS_AUTOUP_CHANGE_DEFAULT_THEME\":${UPDATE_THEME}, \"skip_backup\": ${SKIP_BACKUP}}" > modules/autoupgrade/config.json
  docker exec -u www-data prestashop_autoupgrade php modules/autoupgrade/bin/console update:start --action="compareReleases" --config-file-path="modules/autoupgrade/config.json" admin-dev
  docker exec -u www-data prestashop_autoupgrade php modules/autoupgrade/bin/console update:start --config-file-path="modules/autoupgrade/config.json" admin-dev
fi

docker exec -u www-data prestashop_autoupgrade php modules/autoupgrade/bin/console update:start --action="compareReleases" admin-dev
docker exec -u www-data prestashop_autoupgrade php modules/autoupgrade/bin/console update:start admin-dev
