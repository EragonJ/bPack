#!/bin/bash

if [ "$2" == "modify" ]; then
	vi "$bPack_Directory/console/show_fields.php"
else
	php "$bPack_Directory/console/show_fields.php" "$2" 
fi
