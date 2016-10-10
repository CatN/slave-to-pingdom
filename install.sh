#!/bin/bash
#
# install/deploy script for slave-to-pingdom

set -o errexit

install_location=/var/www/html/slave-to-pingdom
# TODO: this could be worked out automatically...
this_script=install.sh


if [ "$(whoami)" != "root" ]; then
   echo "You must be root to run this script - you are '$(whoami)'" >&2
   exit 1
fi

echo "Creating $install_location..."
mkdir -p "$install_location"

echo "Copying files to install location..."
# --filter ':- .gitignore' means that rsync ignores the same files that .gitignore does
rsync -va --filter ':- .gitignore' --exclude .gitignore --exclude .git --exclude "$this_script" . "$install_location/"

echo "Setting root ownership of copied files..."
chown -R root:root "$install_location" 

# if necessary, use template to create config file
if [ ! -f "$install_location/config.inc.php" ]; then
    echo "Copying config template to config file..."
    cp "$install_location/config.template.inc.php" "$install_location/config.inc.php"
fi

# ensure correct ownership and permissions for config file
echo "Ensure correct ownership and permissions for config file..."
chmod 640 "$install_location/config.inc.php"
chown root:apache "$install_location/config.inc.php"

echo "slave-to-pingdom is now installed in $install_location"
