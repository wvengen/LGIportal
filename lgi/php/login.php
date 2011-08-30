<?php
/**
 * User login page
 * @author Deepthi
 * @package default
 */
/** */
require_once(dirname(__FILE__).'/utilities/common.php');
require_once('utilities/login.php');
require_once('utilities/sessions.php');
require_once('utilities/errors.php');


session_start();
//if user has already logged in redirect to home page.
if(checkValidSession())
{
	header("Location: jobs.php");

}
else   //authenticate user
{
	$valid=false;

	$user=strip_tags($_POST['name']); //HTML tags are stripped for preventing cross site scripting. $user is later stored in session.

	if(verifyUserPassword($user,$_POST['password']))
	{
		setValidSession($user);
		//user has logged in. Go to home
		header("Location: jobs.php");
	}
	else
	{
		//Username or password does not match a valid user. So request for relogin.
		pushErrorMessage("Invalid username or password. Try Again.");
		relogin();
	}
}
?>
