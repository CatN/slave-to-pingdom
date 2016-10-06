#!/bin/bash
#
# install/deploy script for slave-to-pingdom

set -o errexit

install_location=/var/www/html/slave-to-pingdom
this_script=install.sh


if [ "$(whoami)" != "root" ]; then
   echo "You must be root to run this script - you are '$(whoami)'" >&2
   exit 1
fi

mkdir -p "$install_location"
# --filter ':- .gitignore' means that rsync ignores the same files that .gitignore does
rsync -va --filter ':- .gitignore' --exclude .gitignore --exclude .git --exclude "$this_script" . "$install_location/"

chown -R root:root "$install_location" 


if [ ! -f "$install_location/config.inc.php" ]; then
    cp "$install_location/config.template.inc.php" "$install_location/config.inc.php"
fi

chmod 640 "$install_location/config.inc.php"
chown root:apache "$install_location/config.inc.php"

