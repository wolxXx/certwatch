#!/bin/bash

here=$(dirname $(readlink -f $0))
cd $here

echo "Starting CertWatch Docker environment..."

# Check if docker-compose is available
if ! command -v "docker" &> /dev/null; then
    echo "Error: docker is not installed"
    exit 1
fi

# Start docker containers
docker compose up -d --build --force-recreate --pull always
source .env

# Check if containers started successfully
if [ $? -eq 0 ]; then
    echo "CertWatch Docker environment is running under http://127.0.0.1:$WEB_PORT"
else

  while true; do
      PORTOPEN=$(sudo netstat -tapn | grep $WEB_PORT)
      if [ -z "$PORTOPEN" ]; then
        break ;
      fi
      (( WEB_PORT++ ))
      continue;
  done;

  echo "Error: Failed to start Docker environment"
  echo "possible free port: $WEB_PORT"
  exit 1
fi
