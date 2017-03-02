#!/bin/bash

if [ -f /etc/resolv.conf.head ]; then
	cat /etc/resolv.conf.head | grep nameserver | awk '{ print "H=" $2 ";" }'
fi

cat /etc/resolv.conf | grep nameserver | awk '{ print "C=" $2 ";" }'

if [ -f /etc/resolv.conf.tail ]; then
	cat /etc/resolv.conf.tail | grep nameserver | awk '{ print "T=" $2 ";"}'
fi
