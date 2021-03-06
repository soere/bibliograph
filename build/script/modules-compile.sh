#!/usr/bin/env bash

COMPILE_TARGET=${1:source}
MODULE_CLIENT_DIRS=src/server/modules/*/client

qx contrib update > /dev/null
for dir in $MODULE_CLIENT_DIRS
do
  if [ -f "$dir/compile.json" ]; then
    echo " >>> Compiling plugin client in $dir"
    pushd $dir > /dev/null
    qx contrib list > /dev/null
    [[ -d contrib ]] || qx contrib install
    qx compile --target=$COMPILE_TARGET
    popd > /dev/null
  fi
done