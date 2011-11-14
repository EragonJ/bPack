#!/bin/bash

bpack_check_project_root

models=$(ls "$ProjectDirectory/model/Model/" | sed 's/.php//g')

for model in $models
do  
	status_text "$cyan" "Model" "$model"
done
