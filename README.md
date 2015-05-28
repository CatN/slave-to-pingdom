# slave-to-pingdom

A script to present the status of MySQL replication from a slave to Pingdom in XML form

## Installation

Run the following commands on the MySQL slave server

     cd /var/www/html
     touch index.html
     git clone https://github.com/CatN/slave-to-pingdom.git
     cp config.template.inc.php config.inc.php
     mysql
     mysql> GRANT REPLICATION CLIENT ON *.* TO 'slave-to-pingdom'@'localhost' IDENTIFIED BY 'random_password_here';
     mysql> \q
     ( edit config.inc.php and update it with the correct details)

Then setup your monitoring in Pingdom using Check type "HTTP Custom" and enter the URL to XML file as 
http://your-slave.example.com/slave-to-pingdom/
