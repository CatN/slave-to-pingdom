# slave-to-pingdom

A script to present the status of MySQL replication from a slave to Pingdom in XML form

## Installation

These instructions assume a CentOS 6-like environment. Please adapt them for your own.
Run the following commands on the MySQL slave server

     yum install httpd php php-mysql
     service httpd start && chkconfig httpd on
     ( enable http through firewall, e.g. lokkit -s http )

     cd ~
     git clone https://github.com/CatN/slave-to-pingdom.git
     sudo install.sh

This next bit ensures that noone can see slave-to-pingdom folder in a directory listing

     cd /var/www/html
     touch index.html

Run this on the MySQL **master** server

     mysql
     mysql> GRANT REPLICATION CLIENT ON *.* TO 'slave-to-pingdom'@'localhost' IDENTIFIED BY 'random_password_here';
     mysql> \q

Back on the MySQL slave server

     ( edit config.inc.php and update it with the correct details )
     php index.php

The last command should result in a short XML report that Pingdom can use.

Now setup your monitoring in Pingdom using Check type "HTTP Custom" and enter the URL to XML file as 
http://your-slave.example.com/slave-to-pingdom/
