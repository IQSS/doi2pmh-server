#!/bin/sh

# SYMFONY_ROOT variable must come from Dockerfile
cd $SYMFONY_ROOT

npm install && yarn && yarn install
