<?php
/*
 a script written by Ben Kennish from 10-Jul-2013 that 
 returns a status of the local MySQL slave for Pingdom

 the MySQL user needs the 'REPLICATION CLIENT' priviledge
 try something like this (as root):
     
 mysql> GRANT REPLICATION CLIENT ON *.* TO 'slavemon'@'localhost' IDENTIFIED BY 'random_password_here'; 
 
 then insert the details into config.inc.php (use config.template.inc.php as template)
 
*/

require_once('config.inc.php');

// ---------------------------

header("Content-Type: text/xml; charset=UTF-8");

$status = "OK";
$responseTime = 0;

$con = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWD)
    or trigger_error(mysqli_connect_error(), E_USER_ERROR);

$qry = mysqli_query($con, 'SHOW SLAVE STATUS')
    or trigger_error(mysqli_error($con), E_USER_ERROR);

$rows = mysqli_num_rows($qry);

if ($rows != 1)
{
    trigger_error("Slave status query returned $rows row(s)", E_USER_ERROR);
} 

$row = mysqli_fetch_assoc($qry);


if ($row["Slave_SQL_Running"] != 'Yes')
{
    $status = "ERROR: Slave SQL not running";
}
elseif ($row["Slave_IO_Running"] != 'Yes')
{
    $status = "ERROR: Slave IO not running";
}
elseif ($row['Seconds_Behind_Master'] == null)
{
    $status = "WARNING: Slave is behind master by unknown amount";
}
elseif ($row['Seconds_Behind_Master'] > MAX_SECS_BEHIND_MASTER)
{
    $status = "WARNING: Slave is $row[Seconds_Behind_Master] seconds behind master";
}

$responseTime = $row['Seconds_Behind_Master']; 
if ($responseTime === null) $responseTime = '666.000';

?><pingdom_http_custom_check>
    <status><?php echo htmlspecialchars($status, ENT_NOQUOTES, 'UTF-8'); ?></status>
    <response_time><?php echo $responseTime; ?></response_time>
</pingdom_http_custom_check>
