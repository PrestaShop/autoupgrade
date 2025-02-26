# Autoupgrade UI Tests

## About sanity tests
This campaign includes a non-exhaustive set of tests and will ensure that the most important functions of Prestashop work
after upgrade and rollback.

## Requirement

Before begin working on tests, make sure you have installed

* [nodejs](https://nodejs.org/) v14.x or newer
* [npm](https://www.npmjs.com/) v7.x or newer
* [docker](https://docs.docker.com/engine/install/)

## Test matrix

The PS versions compatible with our tests are :
- `1.7.0`
  - `1.7.0.6 to 1.7.8.11 on php 7.1`
- `1.7.1`
  - `1.7.1.0 ~ 1.7.1.2 to 1.7.8.11 on php 7.1`
- `1.7.2`
    - `1.7.2.0 ~ 1.7.2.5 to 1.7.8.11 on php 7.1`
- `1.7.3`
    - `1.7.3.0 ~ 1.7.3.4 to 1.7.8.11 on php 7.1`
- `1.7.4`
    - `1.7.4.2 ~ 1.7.4.4 to 1.7.8.11 on php 7.1`
- `1.7.5`
    - `1.7.5.0 ~ 1.7.5.2 to 1.7.8.11 on php 7.1`
    - `1.7.5.0 ~ 1.7.5.2 to 8.2.0 on php 7.2`
- `1.7.6`
    - `1.7.6.0 ~ 1.7.6.9 to 1.7.8.11 on php 7.1`
    - `1.7.6.0 ~ 1.7.6.9 to 8.2.0 on php 7.2`
- `1.7.7`
    - `1.7.7.0 ~ 1.7.7.7 to 1.7.8.11 on php 7.1`
    - `1.7.7.0 ~ 1.7.7.7 to 8.2.0 on php 7.2`
    - `1.7.7.0 ~ 1.7.7.7 to 8.2.0 on php 7.3`
- `1.7.8`
    - `1.7.8.0 ~ 1.7.8.10 to 1.7.8.11 on php 7.1`
    - `1.7.8.0 ~ 1.7.8.11 to 8.2.0 on php 7.2`
    - `1.7.8.0 ~ 1.7.8.11 to 8.2.0 on php 7.3`
    - `1.7.8.0 ~ 1.7.8.11 to 8.2.0 on php 7.4`
- `8.0`
    - `8.0.0 ~ 8.0.5 to 8.2.0 on php 7.2`
    - `8.0.0 ~ 8.0.5 to 8.2.0 on php 7.3`
    - `8.0.0 ~ 8.0.5 to 8.2.0 on php 7.4`
    - `8.0.0 ~ 8.0.5 to 8.2.0 on php 8.0`
    - `8.0.0 ~ 8.0.5 to 8.2.0 on php 8.1`
    - `8.0.0 ~ 8.0.5 to 9.0.0 on php 8.1`
- `8.1`
    - `8.1.0 ~ 8.1.7 to 8.2.0 on php 7.2`
    - `8.1.0 ~ 8.1.7 to 8.2.0 on php 7.3`
    - `8.1.0 ~ 8.1.7 to 8.2.0 on php 7.4`
    - `8.1.0 ~ 8.1.7 to 8.2.0 on php 8.0`
    - `8.1.0 ~ 8.1.7 to 8.2.0 on php 8.1`
    - `8.1.0 ~ 8.1.7 to 9.0.0 on php 8.1`

## Upgrade channel
- local : Download a zip and xml file of the new version of PS to upgrade
- online : Using the link of the zip and xml of PS to upgrade

## First step : Clone project and install dependencies

```bash
# Clone Autoupgrade project
git clone git@github.com:PrestaShop/autoupgrade.git
# Install dependencies in UI folder
cd tests/UI/
npm ci
npx playwright install
```

## Second step : Install docker image of PS then upgrade by CLI
```bash
# 1 - Install a docker image of the version you need to test (for example 8.1.5 on php 7.2)
PS_VERSION=8.1.5-7.2 PS_DOMAIN=localhost:9999 docker-compose -f "docker-compose.yml" up --build
# 2 - Create backup
docker exec -t prestashop php modules/autoupgrade/bin/console backup:create admin-dev

# 3 - If you want to upgrade using channel **local**
# Download local ZIP and XML of PS 8.2.0
docker exec -t prestashop curl --fail -L https://github.com/PrestaShop/zip-archives/raw/main/prestashop_8.2.0.zip
 -o admin-dev/autoupgrade/download/prestashop_8.2.0.zip
docker exec -t prestashop curl --fail -L https://api.prestashop.com/xml/md5/8.2.0.xml -o admin-dev/autoupgrade/download/prestashop_8.2.0.xml
# Create config file of new version 8.2.0
docker exec -t prestashop sh -c "echo '{\"local\":\"online\",\"archive_zip\":\"prestashop_8.2.0.zip\",
\"archive_xml\":\"prestashop_8.2.0.xml\",\"PS_AUTOUP_CUSTOM_MOD_DESACT\":\"true\",\"PS_AUTOUP_CHANGE_DEFAULT_THEME\"
:\"false\",\"PS_AUTOUP_KEEP_IMAGES\":\"true\",\"PS_DISABLE_OVERRIDES\":\"true\"}' > modules/autoupgrade/config.json"

# 3 - If you want to upgrade using channel **online**
# Create config file of new version 8.2.0
docker exec -t prestashop sh -c "echo '{\"channel\":\"online\",\"archive_zip\":\"prestashop_8.2.0.zip\",
\"archive_xml\":\"prestashop_8.2.0.xml\",\"PS_AUTOUP_CUSTOM_MOD_DESACT\":\"true\",\"PS_AUTOUP_CHANGE_DEFAULT_THEME\"
:\"false\",\"PS_AUTOUP_KEEP_IMAGES\":\"true\",\"PS_DISABLE_OVERRIDES\":\"true\"}' > modules/autoupgrade/config.json"

# 4 - Update to 8.2.0
docker exec -t prestashop php modules/autoupgrade/bin/console update:start --config-file-path=modules/autoupgrade/config.json
 --channel=online admin-dev
# 5 - Permission
docker exec -t prestashop chmod 777 -R /var/www/html/var

# 6 - If your PS start version is < 1.7.1 you should disable welcome module
docker exec -t prestashop php bin/console prestashop:module disable welcome
docker exec -t prestashop chmod 777 -R /var/www/html/var
```

## Third step : Launch tests

If you want to run all sanity tests, you can run scripts in **campaigns/sanity/*** with custom value **PS__VERSION**
```bash
PS_VERSION=8.2.0 npm run test:sanity
```

## Fourth step : Rollback
```bash
backupName=$(docker exec -t prestashop bash -c "ls -td -- /var/www/html/admin-dev/autoupgrade/backup/*/ | head -n 1 |
 cut -d'/' -f8 | tr -d '\n'")
docker exec -t prestashop php modules/autoupgrade/bin/console backup:restore --backup=$backupName admin-dev
docker exec -t prestashop chmod 777 -R /var/www/html/app
```

Enjoy 😉
