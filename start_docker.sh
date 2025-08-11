#!/bin/bash

here=$(dirname $(readlink -f $0))
cd $here



check_docker() {
    if ! command -v docker &> /dev/null; then
        echo "Error: docker is not installed"
        exit 1
    fi
}

check_docker_compose() {
    if ! docker compose version &> /dev/null; then
        echo "Error: docker compose is not installed"
        exit 1
    fi
}

check_env_file() {
  if [ ! -f ".env" ]; then
      echo ".env file is missing."
      echo "Creating .env file from .env.dist."
      echo "please adjust the ports and names to your needs."
      cp .env.dist .env

      read -p "Do you want to edit the .env file with vim? (y/N): " response
      if [[ "$response" =~ ^[Yy]$ ]]; then
          vim .env
      else
          exit 0
      fi
  fi
}


check_docker
check_docker_compose
check_env_file

source .env


echo "Starting CertWatch Docker environment..."

# Check if docker-compose is available
if ! command -v "docker" &> /dev/null; then
    echo "Error: docker is not installed"
    exit 1
fi

# Start docker containers

source .env


required_vars=("COMPOSE_PROJECT_NAME" "WEB_PORT")
missing_vars=()

for var_name in "${required_vars[@]}"; do
    if [ -z "${!var_name}" ]; then
        missing_vars+=("$var_name")
    fi
done

if [ ${#missing_vars[@]} -ne 0 ]; then
    echo "Error: The following variables are not set in .env file: ${missing_vars[*]}"
    exit 1
fi


docker compose --env-file .env --file docker-compose.yml  up -d --build --force-recreate --pull always

# Check if containers started successfully
if [ $? -eq 0 ]; then
    docker compose exec web php ./composer.phar install
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
