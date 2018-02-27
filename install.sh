#!/bin/bash
#
# install/deploy script for slave-to-pingdom

set -o errexit -o pipefail -o nounset

install_location=/var/www/html/slave-to-pingdom
this_script="$(basename "$0")"
source_dir="$(dirname "$0")"

if [ "$(whoami)" != "root" ]; then
   echo "You must be root to run this script - you are '$(whoami)'" >&2
   exit 1
fi

echo "Creating $install_location..."
mkdir -p "$install_location"

echo "Copying files to install location..."
# --exclude-from .gitignore  means that rsync uses the .gitignore file as a list of patterns to exclude
# this is a bit of a hack but should work provided the .gitignore doesn't get complicated
rsync -va --exclude-from .gitignore --exclude /.gitignore --exclude /.git --exclude "/$this_script" "$source_dir/" "$install_location/"

echo "Setting root ownership of copied files..."
chown -R root:root "$install_location" 

# if necessary, use template to create config file
if [ ! -e "$install_location/config.inc.php" ]; then
    echo "Copying config template to config file..."
    cp "$install_location/config.template.inc.php" "$install_location/config.inc.php"
fi

# ensure correct ownership and permissions for config file
echo "Ensure correct ownership and permissions for config file..."
chmod 640 "$install_location/config.inc.php"
chown root:apache "$install_location/config.inc.php"

echo "slave-to-pingdom is now installed in $install_location"
