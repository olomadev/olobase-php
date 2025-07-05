#!/bin/bash

WORKING_DIRECTORY=$2
JOB=$3
PHP_VERSION=$(echo "${JOB}" | jq -r '.php')

# Temporary workaround for cyclic dependencies - must be adjusted in next major branch!
echo "Branch as 3.99.x in Pre-Install"
git checkout -b 3.99.x

${WORKING_DIRECTORY}/.laminas-ci/install-apcu-extension-via-pecl.sh "${PHP_VERSION}" || exit 1
