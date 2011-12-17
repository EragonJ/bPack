#!/bin/bash

if [ -L ./lib/bPack ]; then
	response "$error" "This directory had installed bPack"
else
	perl ~/Libraries/bPack/tool/init.pl
	response "$success" "This diectory had bPacked."
fi
