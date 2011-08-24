<?php 
/**
 * Page the user sees after logging in (home)
 * @author Deepthi 
 * @package default
 */

require_once(dirname(__FILE__).'/utilities/common.php');
require_once('utilities/dwoo.php');
require_once('utilities/sessions.php');
require_once('utilities/login.php');

session_start();
//authenticate User. If user is not logged in, request for log in.
authenticateUser();

//Display home page
$dwoo = new LGIDwoo(); 
$dwoo->output('home.tpl');
	
?>
