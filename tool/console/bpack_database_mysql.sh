#!/bin/bash

# 2 = host, 3 = user, 4 = password, 5 = database name

		response "$trying" "create database"

		rm -f temp.sql database_rs
		touch database_rs temp.sql

		echo "create database \`$5\` default character set utf8;" > temp.sql

		mysql -h"$2" -u"$3" -p"$4" < temp.sql 2> database_rs

		mysql_result="$(cat database_rs | wc -c)"

		rm -f temp.sql database_rs

		if [ ! "$mysql_result" == 0 ]; then
			response "$existed" "Database [$5]"
			response "$error" "Please remove the existing database \`$cyan$5$white\` or try another name"
		else
			response "$create" "Dataabse [$5]"
		fi

		# create link file(database.yaml)
		if [ -e ./config/dev/database.yaml ]; then
			mv ./config/dev/database.yaml ./config/dev/database.yaml.old
		fi

		echo "# created by bpack script" 1>> ./config/dev/database.yaml
		echo "" 1>> ./config/dev/database.yaml
		echo "adaptor: bPack_DB_PDO_MySQL" 1>> ./config/dev/database.yaml
		echo "post_do: set_names_utf8" 1>> ./config/dev/database.yaml
		echo "" 1>> ./config/dev/database.yaml
		echo "host: $2" 1>> ./config/dev/database.yaml
		echo "username: $3" 1>> ./config/dev/database.yaml
		echo "password: $4" 1>> ./config/dev/database.yaml
		echo "name: $5" 1>> ./config/dev/database.yaml
		
		response "$success" "Database setuped."

