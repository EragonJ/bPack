#!/bin/bash


#
# colors
#
red_on_white='\E[37;41m'
white_on_cyan='\E[30;42m'

red='\E[31;40m'
green='\E[32;40m'
yellow='\E[33;40m'
blue='\E[34;40m'
magenta='\E[35;40m'
cyan='\E[36;40m'
white='\E[37;40m'

function section_break()
{
	echo
}

function status_text()
{
	message_color=${4:-$white}

	echo -e "        $1$2$message_color $3"
	tput sgr0

	return
}

function response()
{
	if [ "$1" == "$created" ] || [ "$1" == "$copied" ] || [ "$1" == "$installed" ]; then
		status_text "$green" "$1" "$2"
		return
	fi

	if [ "$1" == "$trying" ]; then
		status_text "$yellow" "$1" "$2"
		return
	fi

	if [ "$1" == "$existed" ]; then
		status_text "$magenta" "$1" "$2"
		return
	fi


	if [ "$1" == "$deleted" ]; then
		status_text "$red" "$1" "$2"
		return
	fi

	# -------------------------------------------- result or expcptino that will end the script

	if [ "$1" == "$success" ]; then
		echo
		status_text "$white_on_cyan" "$1" "$2"

		echo
		exit 0
	fi

	if [ "$1" == "$error" ] || [ "$1" == "$failed" ]; then

		status_text "$red_on_white" "$1" "$2"

		echo
		exit 0
	fi

	#--------------------------------------------- end of endscript type message

	status_text "$white" "$1" "$2"

	return
}


