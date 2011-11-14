#!/bin/bash

bpack_check_project_root

php "$bPack_Directory/bundle_update"

response "$success" "Bundle updated"

