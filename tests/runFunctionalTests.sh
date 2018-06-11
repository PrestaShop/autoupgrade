#!/bin/bash

if [[ -z "${TRAVIS_BUILD_DIR}" ]]; then
    echo "Variable TRAVIS_BUILD_DIR must defined as the root of your autoupgrade project!"
    echo "Example: export TRAVIS_BUILD_DIR=$(realpath $(dirname "$0")/..)"
    exit 1
fi

BRANCH=mbadrani-develop
FOLDER=tests/E2E

cd $TRAVIS_BUILD_DIR
if [ ! -d "$FOLDER" ]; then
    echo "$FOLDER does not exist, cloning tests..."
    git clone --depth=10 --branch=$BRANCH https://github.com/Quetzacoalt91/PrestaShop.git $FOLDER
    cd $FOLDER
    git filter-branch --prune-empty --subdirectory-filter tests/E2E $BRANCH 
fi

docker-compose up
