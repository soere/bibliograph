#!/usr/bin/env bash
echo $(pwd)
travis lint ./.travis.yml -x || exit 1