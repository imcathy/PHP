<?php
/************ DB INFO *****/
$ini_array = parse_ini_file('/usr/local/properties/datasource.ini');

define('DB_DRIVER', $ini_array['driver'].':server='.$ini_array['host'].';Database='.$ini_array['dbname']); 
    define('DB_USER', $ini_array['userid']);
    define('DB_PASSWD', $ini_array['passwd']);
/**********************************/
?>
