#!/bin/bash

bpack_check_project_root

if [ -e "$ProjectDirectory/model/Model/$2.php" ]; then
	vi "$ProjectDirectory/model/Model/$2.php"
else
	response "$error" "Model_$2 not exists"
fi
