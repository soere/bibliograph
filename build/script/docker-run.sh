#!/usr/bin/env bash
HOST=127.0.0.1
PORT=7070
echo " >>> Starting docker container..."
docker run -d -p $HOST:$PORT:80 cboulanger/bibliograph:$(git describe --tags) > /dev/null
if [[ "$OSTYPE" == "darwin"* ]]; then
  echo " >>> Waiting until server has started..."
  sleep 30s
  echo " >>> Opening browser window..."
  open -a "Google Chrome" http://$HOST:$PORT
  # send Alt+Command+I to open Web inspector
  osascript -e 'tell application "System Events" to keystroke "i" using {option down, command down}'
fi