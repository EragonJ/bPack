#!/bin/bash

bpack_check_project_root

controllers=$(ls -R "./do/" | sed 's/.php//g')

after_count=0

for controller in $controllers
do
	is_dir=`expr index "$controller" :`

	if [ $is_dir != "0" ]; then

		current_directory=${controller/\.\/do\//}
		current_directory=${current_directory/:/}

		if [ "$current_directory" != "" ]; then

			if [ $after_count -gt 0 ]; then
				section_break
			fi

			after_count=`expr $after_count + 1`
		fi

	else
		if [ "$current_directory" != "" ]; then
			status_text "$cyan" "$current_directory" "$controller"
		fi
	fi
done
