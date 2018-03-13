#!/usr/bin/env bash
logfile=src/server/runtime/logs/${1:-app}.log
[[ -f $logfile ]] && rm $logfile
touch $logfile
tail -F $logfile