#!/bin/bash

if [ -L ./lib/bPack ]; then
	response "$error" "This directory had installed bPack"
else
	perl ~/Playground/bPack/tool/init.pl
	response "$success" "This diectory had bPacked."
fi
