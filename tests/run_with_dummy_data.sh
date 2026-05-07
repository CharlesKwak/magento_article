#!/usr/bin/env bash
set -euo pipefail

if [[ $# -ne 2 ]]; then
  echo "Usage: $0 <phpunit-test-file> <dummy-data-json-file>"
  exit 1
fi

TEST_FILE="$1"
DUMMY_FILE="$2"

if [[ ! -f "$TEST_FILE" ]]; then
  echo "Test file not found: $TEST_FILE"
  exit 1
fi

if [[ ! -f "$DUMMY_FILE" ]]; then
  echo "Dummy data file not found: $DUMMY_FILE"
  exit 1
fi

DUMMY_DATA_FILE="$DUMMY_FILE" ./vendor/bin/phpunit --configuration tests/phpunit.xml "$TEST_FILE"
