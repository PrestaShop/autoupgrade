#!/bin/bash

# Navigate to the script's directory
cd "$(dirname "$0")" || exit

# Check if a command argument was provided
if [ $# -ne 1 ]; then
    echo "Usage: $0 {lint|check|reformat}"
    exit 1
fi

COMMAND=$1

# Build the Docker image
docker build -t djlint-image -f Dockerfile ../../

# Run the appropriate djLint command based on the argument
case $COMMAND in
    lint)
        docker run --rm -v "$(pwd)/../../":/app djlint-image --lint --configuration /app/tests/djLint/.djlintrc -
        ;;
    format)
        docker run --rm -v "$(pwd)/../../":/app djlint-image --check --configuration /app/tests/djLint/.djlintrc -
        ;;
    format:fix)
        docker run --rm -v "$(pwd)/../../":/app djlint-image --reformat --configuration /app/tests/djLint/.djlintrc -
        ;;
    *)
        echo "Invalid command: $COMMAND"
        echo "Usage: $0 {lint|check|reformat}"
        exit 1
        ;;
esac
