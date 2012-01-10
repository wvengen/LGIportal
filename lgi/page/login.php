<?php
/**
 * User login page
 * @author wvengen
 * @package default
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/dwoo.php');
require_once('inc/login.php');
require_once('inc/sessions.php');
require_once('inc/errors.php');


$username = strip_tags(@$_REQUEST['name']); //HTML tags are stripped for preventing cross site scripting. $user is later stored in session.
$password = @$_POST['password'];

if (is_null($password) || is_null($username))
{
        LGIDwoo::show('login.tpl', array('name'=>$username));
}
elseif(LGIUser::password_check_user($username, $password))
{
	setValidSession($username);
	// user has logged in, go to default page
	http_redirect(config('LGI_APPROOT').'/'.config('LGI_DEFAULTPAGE'));
}
else
{
	// Username or password does not match a valid user. Try again.
	// TODO prevent brute force attacks
	pushErrorMessage("Invalid username or password. Try Again.");
	LGIDwoo::show('login.tpl', array('name'=>$username));
}

?>