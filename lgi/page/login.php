<?php
/**
 * User login page
 * @author Deepthi
 * @package default
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/dwoo.php');
require_once('inc/login.php');
require_once('inc/sessions.php');
require_once('inc/errors.php');

// this is the only page that does not need portal_require_session() :)

$user=strip_tags(@$_POST['name']); //HTML tags are stripped for preventing cross site scripting. $user is later stored in session.
$password=@$_POST['password'];

if (is_null($password))
{
        LGIDwoo::show('login.tpl', array('user'=>$user));
}
elseif(verifyUserPassword($user, $password))
{
	setValidSession($user);
	//user has logged in. Go to home
	portal_page('jobs');
}
else
{
	//Username or password does not match a valid user. So request for relogin.
	pushErrorMessage("Invalid username or password. Try Again.");
	LGIDwoo::show('login.tpl', array('user'=>$user));
}

?>