<?php
/**
 * This file stores the Database access details. This file should be have
 * restricted access using .htaccess or should be stored outside webroot. The
 * absolute path to this file should be specified in lgi.config.php. 
 *
 * @package default
 */	

/** address of mysql database where user accounts are stored
 * @global string $mysql_server  */
$mysql_server='localhost';

/** username to access mysql table, should have read access to 'users' table
 * @global string $mysql_user */
$mysql_user="root";

/** password of {@link $mysql_user $mysql_user}
 * @global string $mysql_password */
$mysql_password="";

/** name of the database used
 * @global string $mysql_dbname */
$mysql_dbname="lgi";	

?> 
