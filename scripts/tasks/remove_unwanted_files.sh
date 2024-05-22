#!/bin/bash

. $(dirname $(dirname ${BASH_SOURCE[0]}))/script-parameters.sh

echo -e "${LIGHT_GREEN}Move to build directory.${NC}"
cd $1

for UNWANTED_FILE in "${UNWANTED_FILES[@]}"
do
  echo -e "${LIGHT_GREEN}Remove unwanted file: ${UNWANTED_FILE}${NC}"
  rm -rf $UNWANTED_FILE
done

echo -e "${LIGHT_GREEN}Move back to previous directory.${NC}"
cd -
