#!/bin/bash

# 2 = module, 3 = controller

if [ -n "$3" ]; then
	modify_filename="bpack_$2_$3"
else
	modify_filename="bpack_$2"
fi

vi "$bPack_Directory/console/$modify_filename.sh"
