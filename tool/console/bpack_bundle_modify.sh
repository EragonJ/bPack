#!/bin/bash

		if [ ! -e ./config/bundle ]; then
			response "$error" "Initlization first. Please execute $yellow bpack bundle init $white"
		fi

		vi ./config/bundle
		exit 0;

