<?php
/**
 * The PHP file that serves all page requests.
 *
 * The requested page is loaded from 
 *
 * @author wvengen
 * @package default
 */
/** */
require_once(dirname(__FILE__).'/inc/common.php');
require_once('inc/sessions.php');

// require a login for everything
$page = null;
session_start();
if(checkValidSession())
{
	// path info: appended to script: index.php/foo will return foo
	if (isset($_SERVER['PATH_INFO']))
		$page = $_SERVER['PATH_INFO'];
	// fallback to jobs overview
	if ($page=='')
		$page='jobs';
}
else
{
	// login page
	$page = 'login';
}

// validate page
$argv = explode('/', $page);
$page = $argv[0];
$pagepath = dirname(__FILE__)."/page/$page.php";

if ( !preg_match('/^[a-zA-Z0-9]+$/', $page) ||
     !file_exists($pagepath) ) {
        http_status(404, 'Page not found');
        throw new LGIPortalException('Page not found');
}

// arguments are in argv
define('LGI_PORTAL', 1);
include($pagepath);

?>