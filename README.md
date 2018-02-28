# slave-to-pingdom

A script to present the status of MySQL replication from a slave to
Pingdom/StatusCake in XML form with an appropriate HTTP status code

## Installation

These instructions assume you are using Apache 2.2 in a CentOS 6-like
environment. Please adapt them for your own. Some steps also assume that you
are installing slave-to-pingdom on the slave itself.  This is not always the
case but is the simplest configuration.

Run the following commands on the MySQL slave server:

Install webserver, PHP, and PHP's MySQL libraries

     yum install httpd php php-mysql

Start webserver and enable it to start on boot

     service httpd start && chkconfig httpd on

Allow access to http through firewall, e.g. by running 'lokkit -s http'
 
Edit `/etc/php.ini` and set date.timezone appropriately, e.g. to Europe/London

Clone down this repo and run the installer (NB: you may wish to edit the
`install_location` in install.sh)

     cd ~
     git clone https://github.com/fubralimited/slave-to-pingdom.git
     sudo ./install.sh

Ensure that noone can see the slave-to-pingdom folder in a web-based directory
listing by creating an index.html file in the document root

     touch /var/www/html/index.html

Run the following SQL query on the MySQL **master** server (NB: the master
server, not the slave server)  If you are installing slave-to-pingdom on a
webserver other than the MySQL slave, you will need to replace "localhost"
with the FQDN of the webserver.

     mysql
     mysql> GRANT REPLICATION CLIENT ON *.* TO 'slave-to-pingdom'@'localhost' IDENTIFIED BY 'random_password_here';
     mysql> \q

Due to replication, the query will also be executed on the slave server and so
the user will be created there too

Back on the MySQL **slave** server

If you are installing slave-to-pingdom on a webserver other than the MySQL
slave, you will need to configure the firewall on the slave to allow the
webserver to connect in to the slave on the MySQL port (3306.)

     cd /var/www/html/slave-to-pingdom

Edit config.inc.php and update it with the correct details, taking the given
values as a template. Basically you wish to define a number of entries of the
form `$config['servers']['serverkey']...` replacing serverkey with an ID string
that identifies a slave server to query.

 Then run the script...

     php index.php

The last command should result in a short XML report that Pingdom can use.  If
you have access to the `php-cgi` binary, you can use that instead and you should
get output of the HTTP headers.  You can also pass a GET var (to select 'server') via
the command line by running something like...

     php-cgi index.php server=localhost

Now setup your monitoring in Pingdom/StatusCake using the URL
http://your-slave.example.com/slave-to-pingdom/

or if you have configured multiple servers, something like
http://your-slave.example.com/slave-to-pingdom/?server=local
