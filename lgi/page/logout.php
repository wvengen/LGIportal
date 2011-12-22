<?php
/**
 * User logout page
 * @author wvengen
 * @package default
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/sessions.php');


session_destroy();
http_redirect(config('LGI_APPROOT').'/login');

?>