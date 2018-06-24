#!/usr/bin/env bash
REPO=cboulanger/bibliograph
TAG=`if [ "$TRAVIS_BRANCH" == "master" ]; then echo "latest"; else echo $TRAVIS_BRANCH ; fi`
docker build -f ./build/env/Dockerfile -t $REPO:$COMMIT .
docker tag $REPO:$COMMIT $REPO:$TAG
echo "$DOCKER_PASSWORD" | docker login -u "$DOCKER_USERNAME" --password-stdin
docker push $REPO