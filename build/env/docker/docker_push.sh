#!/usr/bin/env bash
REPO=cboulanger/bibliograph
TAG=`if [ "$TRAVIS_BRANCH" == "master" ]; then echo "latest"; else echo $TRAVIS_BRANCH ; fi`
echo " >>> Building image $REPO:$TAG' ..."
docker build -f ./build/env/docker/Dockerfile -t $REPO:$TAG . || echo "Build failed." && exit 1
echo " >>> Pushing to Docker hub...."
echo "$DOCKER_PASSWORD" | docker login -u cboulanger --password-stdin
docker push $REPO:$TAG