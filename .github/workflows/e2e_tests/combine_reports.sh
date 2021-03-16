#!/bin/bash

REPORTS_PATH=$1
COMBINE_REPORT_NAME=$2

# Run python script
./tests/e2e/scripts/combine-reports.py $REPORTS_PATH $REPORTS_PATH/$COMBINE_REPORT_NAME