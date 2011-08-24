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

require_once(dirname(__FILE__).'/../../lgi.config.php');
set_include_path(dirname(__FILE__).'/..'.PATH_SEPARATOR.get_include_path());
require_once('utilities/errors.php');

/** Exception in LGI portal code */
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
 * @todo show either calling page or same page, with error message
 */
function lgi_portal_exception_handler($exception)
{
	pushErrorMessage($exception->getMessage());
	LGIDwoo::show('error.tpl');
	exit(0);
}
set_exception_handler('lgi_portal_exception_handler');

?>
