#!/usr/bin/env bash
LOG_PATH=/var/www/html/server/runtime/logs
echo "########################################################### APP LOG ###########################################################"
docker container exec $(docker container ls -q -l) tail -n 1000 $LOG_PATH/app.log
echo "########################################################### ERROR LOG ###########################################################"
docker container exec $(docker container ls -q -l) tail -n 1000 $LOG_PATH/error.log