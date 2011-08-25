<?php
/**
 * Entry page, redirecting to either login or home
 * @author Deepthi 
 * @package pages
 */

require_once(dirname(__FILE__).'/php/utilities/common.php');
require_once('utilities/dwoo.php');
require_once('utilities/sessions.php');

session_start();
if(checkValidSession()) //if already logged in redirect it to home
{	
	header("Location: php/jobs.php");
}
else
{
	$dwoo = new LGIDwoo();
	$dwoo->output('login.tpl');
}
	
?>
