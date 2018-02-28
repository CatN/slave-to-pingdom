<?php

$config = array();

// this block defines 4 config variables for a server with ID 'local'
$config['servers']['local']['hostname'] = 'localhost';
$config['servers']['local']['username'] = 'slave-to-pingdom';
$config['servers']['local']['password'] = 'xxxxxxxxxxxxxxxx';
$config['servers']['local']['max_secs_behind_master'] = 3600;

// this block defines 4 config variables for a server with ID 'example'
/*
$config['servers']['example']['hostname'] = 'db2.example.com';
$config['servers']['example']['username'] = 'slave-to-pingdom';
$config['servers']['example']['password'] = 't0ps3cr3tp455w0rd';
$config['servers']['example']['max_secs_behind_master'] = 3600;
*/

