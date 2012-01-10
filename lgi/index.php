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
        $page = trim(@$_SERVER['PATH_INFO']);
}
else
{
	// login page
	$page = '/login';
}

// validate page
$argv = explode('/', $page);
if ($argv[0]!='') throw new LGIPortalException("Internal error: path_info does not start with /.");
array_shift($argv);
$page = $argv[0];

// arguments are in argv
define('LGI_PORTAL', 1);
portal_page($page);

/**
* Open the named page in the portal.
* 
* If an empty or no page name is specified, the default is opened as specified
* in the configuration entry LGI_DEFAULTPAGE.
* 
* @param str $page Page name to open, php file in page/<$page>.php must exist.
*/
function portal_page($page=NULL) {
  if ($page==NULL) $page=config('LGI_DEFAULTPAGE');
  $pagepath = dirname(__FILE__)."/page/$page.php";
  if ( !preg_match('/^[a-zA-Z0-9]+$/', $page) || !file_exists($pagepath) ) {
      http_status(404, 'Page not found');
      throw new LGIPortalException('Page not found: '.$page);
  }
  // include page, but first bring important globals in scope
  global $argv;
  require($pagepath);
}

?>