<?php
/**
 * Common LGI portal functions
 *
 * This should probably require_once'd in most other php files.
 * 
 * Apart from defining common functions and classes, this project's
 * root is added to the include path, which makes it possible to
 * use relative includes without resorting to dirname(__FILE__).
 * Also the lgi.config.php is included here.
 *
 * @author wvengen
 * @package utilities
 */
/** */
set_include_path(dirname(__FILE__).'/..'.PATH_SEPARATOR.get_include_path());
require_once('lgi.config.php');
require_once('inc/errors.php');


/** Exception in LGI portal code
 * @package utilities */
class LGIPortalException extends Exception {
	public $returnto = null;

	/** Create new LGIPortalException
	 *
	 * @param string $msg error message
	 * @param string $returnto page to return to if uncaught
	 */
	function __construct($msg, $returnto=null)
	{
		$this->returnto = $returnto;
		parent::__construct($msg);
	}
}

/** Default exception handler.
 *
 * This catches uncaught exceptions and stores the error message. Then the
 * most sensible page is shown with the error message.
 *
 * If the constant LGI_OUTPUT_TYPE is set to 'text/plain', a plain-text
 * error message will be shown. This is useful for command-line scripts.
 *
 * @todo show either calling page or same page, with error message
 */
function lgi_portal_exception_handler($exception)
{
	pushErrorMessage($exception);
	if (defined('LGI_OUTPUT_TYPE') && constant('LGI_OUTPUT_TYPE')=='text/plain') {
		print("Error: ".getErrorMessage()."\n");
		clearErrorMessage();
	} else {
		require_once('inc/dwoo.php');
		LGIDwoo::show('error.tpl');
	}
	exit(0);
}
set_exception_handler('lgi_portal_exception_handler');

/** Get configuration value.
 *
 * This is used instead of defines or globals so that settings could be
 * retrieved from a database, for example. Or to have a hierarchical
 * idea of configuration where multiple portals share a single 
 * codebase. All of this is not implemented right now, but it should
 * be possible by changing this function.
 *
 * @param string $key configuration value to get
 * @param mixed $default value to return when key was not present
 * @return mixed
 */
function config($key, $default=null)
{
	if (isset($GLOBALS[$key]))
		return $GLOBALS[$key];
	else
		return $default;
}

/** Get configuration, return as array.
 *
 * If value is no array, it wraps it into one. This is for configuration
 * items that have either a value or an array of values, so that the
 * calling code can always iterate over it.
 *
 * @param string $key configuration value to get
 * @param mixed $default value to return when key was not present
 * @return mixed
 */
function config_array($key, $default=array())
{
	$r = config($key, null);
	if (is_null($r))
		return $default;
	return is_array($r) ? $r : array($r);
}

/** Send http status code.
 *
 * Besides an HTTP it also sends a Status header for FastCGI.
 *
 * @param int $code status code
 * @param string $msg message
 */
function http_status($code, $msg)
{
	header("HTTP/1.1 $code $msg", false);
	header("Status: $code $msg", false);
}

/**
 * HTTP redirect
 * 
 * @param string $url url to redirect to
 */
function http_redirect($url)
{
        header('Location: '.$url);
}

?>
