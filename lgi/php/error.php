<?php
/**
 * Show error message
 * @author Deepthi
 * @package default
 */
/** */
require_once(dirname(__FILE__).'/utilities/common.php');
require_once('utilities/errors.php');


echo getErrorMessage();
clearErrorMessage();

?>
