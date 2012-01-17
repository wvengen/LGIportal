<?php
/**
 * User login page
 * @author wvengen
 * @package default
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/dwoo.php');
require_once('inc/user.php');
require_once('inc/sessions.php');
require_once('inc/errors.php');


// if argument was given, the first one is the method
$method = @$argv[1];

$username = strip_tags(@$_REQUEST['name']); // to avoid XSS on display; stored in session later
$password = @$_POST['password'];


if (!is_null($method) && $method!='local')
{
	// try to load SimpleSAMLphp first
	$sspinclude = config('SIMPLESAMLPHP_ROOT').'/lib/_autoload.php';
	if (!is_readable($sspinclude))
		throw new LGIPortalException('External authentication requested but no SimpleSAMLphp configured.');
	require_once($sspinclude);
	// and authenticate or redirect
	$as = new SimpleSAML_Auth_Simple($method); // TODO check what characters are allowed in $method!
	if ($as->isAuthenticated()) {
		// authenticate and redirect to frontpage
		$attrs = $as->getAttributes();
		$suser = @$attrs[config('SIMPLESAMLPHP_ATTR_USER', 'eppn')];
		if (is_null($suser))
			throw new LGIPortalException('External authentication incorrectly configured (empty user).');
		$res = lgi_mysql_query("SELECT `name` FROM %t(users) WHERE `simplesamlphp_user`='%s'", $suser);
		$num = mysql_num_rows($res);
		if (!$res || $num<1)
			throw new LGIPortalException('User '.$suser.' has no access to this portal, sorry.');
		if ($num>1)
			throw new LGIPortalException('Multiple portal users found for this id, contact your portal administrator.');
		$f = mysql_fetch_array($res);
		setValidSession($f[0]);
		$_SESSION['simplesamlphp_auth'] = true; // for logout
		http_redirect(config('LGI_APPROOT').'/'.config('LGI_DEFAULTPAGE'));
	} else {
		// redirect to SimpleSAMLphp for authentication
		$as->requireAuth();
	}
}
elseif (is_null($password) || is_null($username))
{
        LGIDwoo::show('login.tpl', array('name'=>$username, 'method'=>$method));
}
elseif(LGIUser::password_check_user($username, $password))
{
	setValidSession($username);
	$_SESSION['simplesamlphp_auth'] = false;
	// user has logged in, go to default page
	http_redirect(config('LGI_APPROOT').'/'.config('LGI_DEFAULTPAGE'));
}
else {
	// Username or password does not match a valid user. Try again.
	// TODO prevent brute force attacks
	pushErrorMessage("Invalid username or password. Try Again.");
	LGIDwoo::show('login.tpl', array('name'=>$username, 'method'=>$method));
}

?>
