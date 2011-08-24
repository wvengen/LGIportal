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

/** Exception in LGI portal code */
class LGIPortalException extends Exception { }

?>
