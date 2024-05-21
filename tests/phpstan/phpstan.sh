#!/bin/bash
set -e

if [ $# -le 0 ]; then
  echo "No version provided. Use:"
  echo "tests/phpstan/phpstan.sh [PrestaShop_version]"
  exit 1
fi

PS_VERSION=$1

if [ ! -f $PWD/tests/phpstan/phpstan-$PS_VERSION.neon ]; then
  echo "Configuration file for PrestaShop $PS_VERSION does not exist."
  echo "Please try another version."
  exit 2
fi

# Docker images prestashop/prestashop are used to get source files
echo "Pull PrestaShop files (Tag ${PS_VERSION})"

docker rm -f temp-ps || true
docker volume rm -f ps-volume || true

docker run -tid --rm -v ps-volume:/var/www/html --name temp-ps prestashop/prestashop:$PS_VERSION

# Clear previous instance of the module in the PrestaShop volume
echo "Clear previous module"

docker exec -t temp-ps rm -rf /var/www/html/modules/autoupgrade

echo "Run PHPStan using phpstan-${PS_VERSION}.neon file"

docker run --rm --volumes-from temp-ps \
       -v $PWD:/var/www/html/modules/autoupgrade \
       -e _PS_ROOT_DIR_=/var/www/html \
       --workdir=/var/www/html/modules/autoupgrade \
       --entrypoint=/var/www/html/modules/autoupgrade/tests/vendor/bin/phpstan \
       prestashop/base:7.4-apache \
       analyse \
       --configuration=/var/www/html/modules/autoupgrade/tests/phpstan/phpstan-$PS_VERSION.neon \
       "${@:2}"
