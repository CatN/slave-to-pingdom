<?php
/*
 a script written by Ben Kennish from 10-Jul-2013 that 
 returns the status of the local MySQL slave for Pingdom
*/

define('LOG_FILE', '/tmp/slave-to-pingdom.log');


// ---------------------------
// write an error message to our log file
function logError($msg)
{
    //Set an error header for monitoring tools
    header($_SERVER["SERVER_PROTOCOL"].' 503 '.$msg, true, '503');
    if (!file_exists(LOG_FILE))
    {
        $oldUmask = umask(0077);
        if (!touch(LOG_FILE)) 
            trigger_error("Couldn't touch ".LOG_FILE, E_USER_ERROR);
        umask($oldUmask);
    }

    $date = date("Y-m-d H:i:s");
    error_log("$date - $msg\n", 3, LOG_FILE);
}

require_once('config.inc.php');

header("Content-Type: text/xml; charset=UTF-8");

$status = "OK";
$responseTime = 0;


if (!function_exists('mysqli_connect'))
{
    trigger_error("MySQLi PHP extension not installed. Try: yum install php-mysql", E_USER_ERROR);
}

$con = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWD);
if (!$con)
{
    logError('MySQL connection error: '.mysqli_connect_error());
    trigger_error(mysqli_connect_error(), E_USER_ERROR);
}


$qry = mysqli_query($con, 'SHOW SLAVE STATUS');
if (!$qry)
{
    logError('MySQL query error: '.mysqli_error($con));
    trigger_error(mysqli_error($con), E_USER_ERROR);
}


$rows = mysqli_num_rows($qry);

if ($rows != 1)
{
    logError('SHOW SLAVE STATUS returned '.$rows.' rows');
    trigger_error("Slave status query returned $rows rows", E_USER_ERROR);
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
elseif ($row['Seconds_Behind_Master'] === null)
{
    $status = "WARNING: Slave is behind master by unknown amount";
}
elseif ($row['Seconds_Behind_Master'] > MAX_SECS_BEHIND_MASTER)
{
    $status = "WARNING: Slave is $row[Seconds_Behind_Master] seconds behind master";
}

$responseTime = $row['Seconds_Behind_Master']; 

// display an "unknown" seconds behind master as 666
if ($responseTime === null) $responseTime = '666.000';


// write to an error log if things aren't OK
if ($status != "OK")
{
    logError($status);
}

?><pingdom_http_custom_check>
    <status><?php echo htmlspecialchars($status, ENT_NOQUOTES, 'UTF-8'); ?></status>
    <response_time><?php echo $responseTime; ?></response_time>
</pingdom_http_custom_check>
