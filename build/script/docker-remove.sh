#!/usr/bin/env bash
echo " >>> Stopping container..."
docker container stop $(docker container ls -q -l) > /dev/null
echo " >>> Removing container ..."
docker container rm $(docker container ls -q -l) > /dev/null
echo "Stopped and removed the latest created container."