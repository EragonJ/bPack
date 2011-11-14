#!/bin/bash

function require()
{
	local filename=${1//./_}

	local filepath="$bPack_Directory/console/$filename.sh"

	if [ -e $filepath ]; then
		source $filepath 
		return 1
	else
		return 0
	fi

}
