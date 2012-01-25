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
// if idp parameter was given, use that for saml. 
//    since this can be a full url, it's not nice as PATH_INFO param
$authident = @$_REQUEST['idp'];

$username = strip_tags(@$_REQUEST['name']); // to avoid XSS on display; stored in session later
$password = @$_POST['password'];

// find out whether SimpleSAMLphp is installed or not
$sspinclude = config('SIMPLESAMLPHP_DIR','').'/lib/_autoload.php';
$ssp_exists = is_readable($sspinclude);


if ($authsource && $authsource!='local')
{
	// need SimpleSAMLphp now
	if (!$ssp_exists)
		throw new LGIPortalException('External authentication requested but no SimpleSAMLphp configured.');
	require_once($sspinclude);
	// User comes here first, is redirected to SimpleSAMLphp, logs in, and is redirected back here.
	$as = new SimpleSAML_Auth_Simple($authsource); // TODO check what characters are allowed in $authsource!
	if (!$as->isAuthenticated()) {
		// First step is to authenticate using SimpleSAMLphp
		$as->requireAuth(array('saml:idp'=>$authident));
		exit(0);
	} else try {
		// Then we come back here and set the LGIportal session
		// First figure out what attributes we should look at for this authsource
		$attrselect = config('SIMPLESAMLPHP_ATTR_USER',array());
		if (array_key_exists($authsource, $attrselect)) $attrselect = $attrselect[$authsource];
		elseif (array_key_exists('*', $attrselect)) $attrselect = $attrselect['*'];
		else $attrselect = array();
		// Find attribute to use as user identifier
		$attrs = $as->getAttributes();
		$suser = null;
		foreach ($attrselect as $a) {
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
		$res = lgi_mysql_query("SELECT `user`,`enabled` FROM %t(auth_simplesamlphp) WHERE `authid`='%s' AND `authsource`='%s'", $suser, $authsource);
		$num = mysql_num_rows($res);
		if (!$res || $num<1)
			throw new LGIPortalException("$authsource user $suser is not known to this portal, sorry.");
		if ($num>1)
			throw new LGIPortalException('Multiple portal users match your login, contact your portal administrator.');
		$f = mysql_fetch_array($res);
		if (!$f[1])
			throw new LGIPortalException('User '.$suser.' has no access to this portal, sorry.');
		setValidSession($f[0], $authsource);
		http_redirect(config('LGI_APPROOT').'/'.config('LGI_DEFAULTPAGE'));
		exit(0);
	} catch(Exception $e) {
		// go back to login page upon failure
		pushErrorMessage($e->getMessage());
		http_redirect(config('LGI_APPROOT').'/login');
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
	'authsource'=>$authsource ? $authsource : '',
	'simplesamlphp_exists'=>$ssp_exists,
));
?>
