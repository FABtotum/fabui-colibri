#!/bin/bash

# Note: this script is unused

usage()
{
cat << EOF
usage: $0 interface

This script returns json representation of WiFi scan results.
EOF
}

if [ -z "$1" ]; then
    IFACE=wlan0
else
    IFACE=$1
fi

CELL=
PREV=
echo "{"
IFS=$'\n'
for line in $(iwlist $IFACE scan | sed -e '/^.*IE.*/d;/^.*Scan completed.*/d')
do
    line=$(echo $line | sed -e 's/^[[:space:]]*//')
    key=$(echo $line | awk 'BEGIN{FS=":"}{print $1;}')
    value=$(echo $line | awk 'BEGIN{FS=":"}{print $2;}')
    if [[ -z $value ]]; then
        key=$(echo $line | awk 'BEGIN{FS="="}{print $1;}')
        value=$line
    fi
    
    case $key in
        Cell*)
            if [[ -n "$CELL" ]]; then
                echo -e "\n  },"
            fi
            CELL=$(echo $key | awk '{print $2;}')
            PREV=
            echo "  \"$CELL\" : {"
            ;;
        ESSID)
            [ -n "$PREV" ] && echo ","
            echo -n "    \"essid\" : $value"
            PREV=y
            ;;
        Channel)
            [ -n "$PREV" ] && echo ","
            echo -n "    \"channel\" : $value"
            PREV=y
            ;;
        "Encryption key")
            [ -n "$PREV" ] && echo ","
            echo -n "    \"locked\" : $value"
            PREV=y
            ;;
        Quality)
            [ -n "$PREV" ] && echo ","
            echo -n "    \"quality\" : $value"
            PREV=y
            ;;
    esac
done

if [ -n "$CELL" ]; then
    echo "\n  }"
    echo "}"
else
    echo "}"
fi
