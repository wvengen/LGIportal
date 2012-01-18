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
$authsource = @$argv[1];

$username = strip_tags(@$_REQUEST['name']); // to avoid XSS on display; stored in session later
$password = @$_POST['password'];

// find out whether SimpleSAMLphp is installed or not
$sspinclude = config('SIMPLESAMLPHP_DIR','').'/lib/_autoload.php';
$ssp_exists = is_readable($sspinclude);


if (!is_null($authsource) && $authsource!='local')
{
	// try to load SimpleSAMLphp first
	if (!$ssp_exists)
		throw new LGIPortalException('External authentication requested but no SimpleSAMLphp configured.');
	require_once($sspinclude);
	// and authenticate or redirect
	$as = new SimpleSAML_Auth_Simple($authsource); // TODO check what characters are allowed in $authsource!
	if ($as->isAuthenticated()) {
		// get user id from one of the attributes
		$attrs = $as->getAttributes();
		$suser = null;
		foreach (config('SIMPLESAMLPHP_ATTR_USER',array()) as $a) {
			if (array_key_exists($a, $attrs) && !is_null($attrs[$a])) {
				$suser = $attrs[$a];
				if (!is_array($suser)) break;
				if (count($suser)>1)
					user_error('Multiple users returned for authsource '.$authsource.'; using first one.');
				$suser = $suser[0];
			}
		}
		if ($suser===null)
			throw new LGIPortalException('External authentication incorrectly configured (no user attribute).');
		$res = lgi_mysql_query("SELECT `user`,`enabled` FROM %t(auth_simplesamlphp) WHERE `authid`='%s'", $suser);
		$num = mysql_num_rows($res);
		if (!$res || $num<1)
			throw new LGIPortalException('User '.$suser.' is not known to this portal, sorry.');
		if ($num>1)
			throw new LGIPortalException('Multiple portal users found for this id, contact your portal administrator.');
		$f = mysql_fetch_array($res);
		if (!$f[1])
			throw new LGIPortalException('User '.$suser.' has no access to this portal, sorry.');
		setValidSession($f[0], $authsource);
		http_redirect(config('LGI_APPROOT').'/'.config('LGI_DEFAULTPAGE'));
		exit(0);
	} else {
		// redirect to SimpleSAMLphp for authentication
		$as->requireAuth();
		exit(0);
	}
}
elseif (is_null($password) || is_null($username))
{
	/* show page */
}
elseif(LGIUser::password_check_user($username, $password))
{
	setValidSession($username, 'local');
	// user has logged in, go to default page
	http_redirect(config('LGI_APPROOT').'/'.config('LGI_DEFAULTPAGE'));
	exit(0);
}
else {
	// Username or password does not match a valid user. Try again.
	// TODO prevent brute force attacks
	pushErrorMessage("Invalid username or password. Try Again.");
}

LGIDwoo::show('login.tpl', array(
	'name'=>$username,
	'authsource'=>$authsource,
	'simplesamlphp_exists'=>$ssp_exists,
));
?>
