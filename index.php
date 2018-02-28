<?php
/*
 a script written by Ben Kennish from 10-Jul-2013 that 
 returns the status of the local MySQL slave for Pingdom

 extended by Ben Kennish around 27-Feb-2018 to allow definition of multiple slave
 hosts in the config - the one to test is chosen by the GET param "server"
*/

define('LOG_FILE', '/tmp/slave-to-pingdom.log');


// ---------------------------
// write an error message to our log file
function logError($msg)
{

    if (empty($_SERVER['SERVER_PROTOCOL']))
        $protocol = 'HTTP/1.1';
    else
        $protocol = $_SERVER['SERVER_PROTOCOL'];

    //Set an error header for monitoring tools
    header($protocol.' 503 Service Unavailable', true, 503);

    // create log file with 0600 (rwx------) perms if it doesnt exist already
    if (!file_exists(LOG_FILE))
    {
        $oldUmask = umask(0077);
        if (!touch(LOG_FILE)) 
            trigger_error("Couldn't touch ".LOG_FILE, E_USER_ERROR);
        umask($oldUmask);
    }

    // write message to log file, prefixed by current date
    $date = date("Y-m-d H:i:s");
    error_log("$date - $msg\n", 3, LOG_FILE);
}

require_once('config.inc.php');


$status = "OK";
$responseTime = 0;


if (!function_exists('mysqli_connect'))
{
    echo "MySQLi PHP extension not installed. Try: yum install php-mysql";
    trigger_error("MySQLi PHP extension not installed. Try: yum install php-mysql", E_USER_ERROR);
}

if (!isset($config))
{
    echo '$config not defined.  You should define it in config.inc.php';
    trigger_error('$config not defined.  You should define it in config.inc.php', E_USER_ERROR);
}

if (count($config['servers']) < 1)
{
    echo '$config[servers] needs at least one element';
    trigger_error('$config[servers] needs at least one element', E_USER_ERROR);
}

$server = '';

if (!isset($_GET['server']))
{
    if (count($config['servers']) > 1)
    {
        echo 'No server ID selected and multiple defined in config. Pass one as a GET var';
        trigger_error('No server ID selected and multiple defined in config. Pass one as a GET var', E_USER_ERROR);
    }
    else
    {
        // choose the only server configured
        reset($config['servers']);
        $server = key($config['servers']);
    }
}
else
{
    if (!isset($config['servers'][$_GET['server']]))
    {
        echo "'{$_GET['server']}' is not a valid server ID";
        trigger_error("'{$_GET['server']}' is not a valid server ID", E_USER_ERROR);
    }
    $server = $_GET['server'];
}

$server_config = $config['servers'][$server];

//echo "Server '$server' selected".PHP_EOL;
//print_r($server_config);

define('DB_HOST', $server_config['hostname']);
define('DB_USER', $server_config['username']);
define('DB_PASSWD', $server_config['password']);
define('MAX_SECS_BEHIND_MASTER', $server_config['max_secs_behind_master']);

$con = @mysqli_connect(DB_HOST, DB_USER, DB_PASSWD);
if (!$con)
{
    echo "MySQL connection error";
    logError('MySQL connection error: '.mysqli_connect_error());
    trigger_error(mysqli_connect_error(), E_USER_ERROR);
}


$qry = mysqli_query($con, 'SHOW SLAVE STATUS');
if (!$qry)
{
    echo "MySQL query error";
    logError('MySQL query error: '.mysqli_error($con));
    trigger_error(mysqli_error($con), E_USER_ERROR);
}


$rows = mysqli_num_rows($qry);

if ($rows != 1)
{
    echo 'SHOW SLAVE STATUS returned '.$rows.' rows';
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

header("Content-Type: text/xml; charset=UTF-8");

?><pingdom_http_custom_check>
    <status><?php echo htmlspecialchars($status, ENT_NOQUOTES, 'UTF-8'); ?></status>
    <response_time><?php echo $responseTime; ?></response_time>
</pingdom_http_custom_check>
