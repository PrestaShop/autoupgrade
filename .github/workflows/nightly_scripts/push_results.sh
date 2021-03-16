#!/bin/bash

# Nightly api URL
NIGHTLY_API_URL=$1

# Filename to upload
FILENAME=$2

# Name of tests campaign of nightly database
CAMPAIGN=$3

# Tests platform: chromium or cli
PLATFORM=$4

# Token for GCP
QANB_TOKEN=$5

gsutil cp $FILENAME gs://prestashop-core-nightly/reports
curl -X GET "$NIGHTLY_API_URL?filename=$FILENAME&platform=$PLATFORM&campaign=$CAMPAIGN&token=$QANB_TOKEN"
