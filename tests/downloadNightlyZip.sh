#!/bin/bash

if [[ -z "${PS_TARGET_VERSION}" ]]; then
    echo "Variable PS_TARGET_VERSION must be defined"
    exit 1
fi

if [[ -z "${PS_TARGET_BRANCH}" ]]; then
    echo "Variable PS_TARGET_BRANCH must be defined"
    exit 1
fi


YESTERDAY="$(date -d yesterday '+%Y-%m-%d')"
DOWNLOAD_URL_BASE="https://storage.googleapis.com/prestashop-core-nightly/"
DOWNLOAD_URL="$DOWNLOAD_URL_BASE$YESTERDAY-$PS_TARGET_BRANCH-prestashop_$PS_TARGET_VERSION.zip"
echo "Archive will be download from $DOWNLOAD_URL..."

wget -O prestashop.zip $DOWNLOAD_URL
