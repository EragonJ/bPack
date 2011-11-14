#!/bin/bash

if [ ! -e ./config/bundle ]; then

	touch ./config/bundle

	response "$success" "Bundle initalization"
else
	response "$error" "This directory had initlizited. Please execute $yellow bpack bundle update $white"
fi


