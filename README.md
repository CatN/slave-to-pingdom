# slave-to-pingdom

A script to present the status of MySQL replication from a slave to Pingdom in XML form

## Installation

     git clone https://github.com/CatN/slave-to-pingdom.git
     cp config.template.inc.php config.inc.php
     mysql
     mysql> GRANT REPLICATION CLIENT ON *.* TO 'slavemon'@'localhost' IDENTIFIED BY 'random_password_here';     
     mysql> \q
     ( edit config.inc.php and update it with the correct details)

