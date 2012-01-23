<?php
/**
 * User logout page
 * @author wvengen
 * @package default
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/sessions.php');


// logout of SimpleSAMLphp first
if (isset($_SESSION['authsource']) && $_SESSION['authsource']!='local') {
	$sspinclude = config('SIMPLESAMLPHP_DIR').'/lib/_autoload.php';
	require_once($sspinclude);
	$as = new SimpleSAML_Auth_Simple($_SESSION['authsource']);
	unset($_SESSION['authsource']);
	$as->logout();
}

session_destroy();
http_redirect(config('LGI_APPROOT').'/login');

?>
