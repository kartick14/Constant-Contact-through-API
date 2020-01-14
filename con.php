<?php
// if (!defined('ABSPATH')) {
//     exit;
// }
global $con;

define('DB_NHOST', 'localhost');
define('DB_NUSER', 'root');
define('DB_NPASS', '@Root!123');
define('DB_NNAME', 'ectnews_db');

//require('../wp-load.php');

$con = mysqli_connect(DB_NHOST,DB_NUSER,DB_NPASS,DB_NNAME);

// Check connection
if(! $con ){
    die('Could not connect: ' . mysql_error());
   // echo 'Error';
}
echo 'Connected successfully';	
?>