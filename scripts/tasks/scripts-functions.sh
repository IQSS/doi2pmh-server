#!/bin/sh

#Method to set the settings files according to the environment
targeted_settings() {
    if [ -z "$2" ]
    then
        echo "Aborting, invalid arguments: usage targeted_settings [env] [dir]"
        exit 1
    fi
    FILES=$(find $2 -name "*__$1__*")
    for file in $FILES
    do
        basename=`echo $file | sed -e "s#__$1__##g"`
        cp -f $file $basename
    done
    find $2 -name "*__*__*" | xargs -l rm -f
}
