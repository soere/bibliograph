#!/usr/bin/env bash
REPO=$DOCKER_USERNAME/bibliograph
TAG=`if [ "$TRAVIS_BRANCH" == "master" ]; then echo "latest"; else echo $TRAVIS_BRANCH ; fi`
echo " >>> Building image $REPO:$TAG' ..."
docker build -f ./build/env/Dockerfile -t $REPO:$TAG .
echo " >>> Pushing to Docker hub...."
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
docker push $REPO