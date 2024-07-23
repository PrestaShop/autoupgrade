#!/bin/bash

if [ -z "$1" ]; then
  echo "Usage: $0 <url> [expected_http_code]"
  exit 1
fi

URL=$1
HTTP_CODE=${2:-200}
start_time=$(date +%s)

while [[ "$(curl -L -s -o /dev/null -w %{http_code} "$URL")" != "$HTTP_CODE" ]]; do
    if [[ $(($(date +%s) - start_time)) -ge 300 ]]; then
        echo "Timeout reached"
        exit 1
    fi
    sleep 5
done
