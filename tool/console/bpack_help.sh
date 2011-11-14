#!/bin/bash

actions=$(ls $bPack_Directory"/console/bpack_"*.sh)

help_help="danceflow"

for action in $actions
do
	action=${action/$bPack_Directory\/console\//}
	action=${action/.sh/}
	action=${action/bpack_/}

	module=${action%%_*}
	action=${action#*_}

	if [ "$module" == "$action" ]; then
		action=""
	fi

	if [ "$module" != "func" ]; then

		if [ "$last_module" != $module ] && [ "$last_module" != "" ]; then
			section_break
		fi
		
		status_text "$cyan" "$module" "$action $green $(php "$bPack_Directory/console/help.php" $module $action)$white"

		last_module="$module"

	fi
done
