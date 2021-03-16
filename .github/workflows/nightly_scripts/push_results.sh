#!/bin/bash

# Nightly api URL
NIGHTLY_API_URL=$1

# Token for GCP
QANB_TOKEN=$2

# Filename to upload
FILENAME=$3

# Name of tests campaign of nightly database
CAMPAIGN=$4

# Tests platform: chromium or cli
PLATFORM=$5

gsutil cp $FILENAME gs://prestashop-core-nightly/reports
curl -X GET "${{ env.nightly_api_url }}?filename=$FILENAME&platform=${{ env.plateform }}&campaign=${{ env.campaign }}&token=${{ secrets.QANB_TOKEN }}"

