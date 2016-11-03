#!/bin/bash

usage()
{
cat << EOF
usage: $0 interface

This script disconnect a wifi interface.
EOF
}

if [ -z "$1" ]; then
    usage
    exit 1
fi

ifdown $1
ip addr flush dev $1

