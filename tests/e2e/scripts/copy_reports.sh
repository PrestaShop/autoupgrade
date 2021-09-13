#!/bin/bash

REPORTS_PATH=$1
PS_START_VERSION=$2
PS_TARGET_VERSION=$3
MODULE_BRANCH=$4

mkdir -p $REPORTS_PATH
cp ./tests/e2e/mochawesome-report/mochawesome.json $REPORTS_PATH/$MODULE_BRANCH-upgrade-from-$PS_START_VERSION-to-$PS_TARGET_VERSION.json
