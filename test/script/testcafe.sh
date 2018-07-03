#!/bin/bash

# this script assumes that the mysql server is running

set -o errexit # Exit on error
HOST="127.0.0.1:8080"

# kill all processes started by this script when it ends
trap "trap - SIGTERM && kill -- -$$" SIGINT SIGTERM EXIT

npm run dist-create
pushd dist >/dev/null
php -S $HOST >/dev/null &
popd >/dev/null

npx testcafe safari test/testcafe/testcafe.js --disable-page-reloads --app-init-delay=10000
