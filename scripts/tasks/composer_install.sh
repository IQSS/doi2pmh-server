#!/bin/bash

. $(dirname $(dirname ${BASH_SOURCE[0]}))/script-parameters.sh

if [ -n "$1" ]
then
  WORKING_DIR=$1
  # Load variables from .env file for target environment.
  if [ -f $WORKING_DIR/../.env ]
  then
    set -o allexport
    source $WORKING_DIR/../.env
    set +o allexport
  fi
else
  WORKING_DIR=$PROJECT_DIRECTORY
fi

echo -e "${COLOR_LIGHT_GREEN}Composer install.${COLOR_NC}"
if [ "${APP_ENV}" = "dev" ];
then
    composer install --working-dir="${WORKING_DIR}"
else
    composer install --working-dir="${WORKING_DIR}" --no-dev --ignore-platform-reqs
fi
