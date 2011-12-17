#!/bin/bash

bpack_check_project_root

if [ "$2" == "" ]; then
	response "$error" "No module name"
fi

if [ "$3" == "" ]; then
	response "$error" "No controller name"
fi

if [ "$4" == "" ] || [ ! -e "./model/Model/$4.php" ]; then
	response "$error" "No corresponding model name"
fi

if [ "$5" == "" ]; then
	response "$error" "No corresponding short name"
fi

if [ "$6" == "" ]; then
	response "$error" "No corresponding short name in purual form"
fi

# start working


if [ ! -e ./config/dev/database.yaml ]; then
	response "$error" "No database connection data"
fi

if [ ! -e ./model/ScaffoldController.php ]; then
	cp "$bPack_Directory/ScaffoldController.php" ./model/ScaffoldController.php

	response "$copied" "Scaffold Base Controller"
fi

if [ ! -e ./tpl/scaffold ]; then
	cp -r "$bPack_Directory/scaffold" ./tpl/scaffold

	response "$copied" "Scaffold Layout"
fi

if [ "$(cat ./config/bundle | grep Flash)" == "" ]; then

	echo "Flash" 1>> ./config/bundle
	bpack bundle update

	response "$installed" "Flash plugin"
fi

if [ "$(cat ./config/bundle | grep TwigURL)" == "" ]; then
	echo "TwigURL" 1>> ./config/bundle
	bpack bundle update
	response "$installed" "TwigURL plugin"
fi

if [ ! -e "./do/$2" ]; then

	mkdir "./do/$2"
	response "$created" "Module directory"

fi

if [ -e "./do/$2/$3.php" ];then

	response "$existed" "Controller_$2_$3. Remove old? (y/n)"
	
	read confirm
	
	if [ "$confirm" == "y" ] || [ "$confirm" == "Y" ]; then

		rm -f "./do/$2/$3.php"
		response "$deleted" "Controller_$2_$3"

	else
		response "$error" "Please confirm the existing controller located in do/$2/$3.php"
	fi

fi

lowercase_model="$(echo $4 | tr '[A-Z]' '[a-z]')"

cat "$bPack_Directory/scaffold_controller.php" | sed "s/%module%/$2/g" | sed "s/%controller%/$3/g" | sed "s/%lowercase_model%/$lowercase_model/g" | sed "s/%short%/$5/g" | sed "s/%model%/$4/g" | sed "s/%shorts%/$6/g"> "./do/$2/$3.php"

response "$created" "Scaffold controller [Controller_$2_$3]"

section_break

# prepare template and done

if [ ! -e "./tpl/$2" ]; then
	mkdir "./tpl/$2"
	response "$created" "Template directory for module"
fi

if [ ! -e "./tpl/$2/$3" ]; then
	mkdir "./tpl/$2/$3"
	response "$created" "Template directory for controller"
fi

if [ -e "./tpl/$2/$3/list.html" ];then

	response "$existed" "Template for $2.$3 Remove old? (y/n)"
	
	read confirm
	
	if [ "$confirm" == "y" ] || [ "$confirm" == "Y" ]; then
		rm -f "./tpl/$2/$3/*"
		response "$deleted" "Old Template for $2.$3"
	else
		response "$error" "Please confirm the existing template located in tpl/$2/$3/"
	fi

fi

php "$bPack_Directory/generate_scaffold.php" "$2" "$3" "$4" "$5" "$6"

response "$created" "Scaffold controller templates"
response "$success" "Scaffold of $cyan$2.$3$white Ready."
