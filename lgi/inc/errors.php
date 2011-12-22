<?php
/**
 * Error handling
 *
 * @author Deepthi
 * @package utilities
 */
/** */
require_once(dirname(__FILE__).'/common.php');


/**
 * Sets an error message. This function can be called before it is redirected to error page. getErrorMessage() can be called to see the error message.
 * @param string $msg Error message
 */
function setErrorMessage($msg)
{
	user_error($msg);
	$_SESSION["ErrorMessage"]=$msg;
}

/**
 * Appends an error message. This function can be called before it is redirected to error page. getErrorMessage() can be called to see the error message.
 * @param string $msg Error message
 */
function pushErrorMessage($msg)
{
        if ($msg instanceof Exception) {
            $efile = $msg->getFile();
            $eline = $msg->getLine();
            $emsg = $msg->getMessage();
            $etrace = explode("\n", $msg->getTraceAsString());
        } else {
            $_trace = debug_backtrace();
            $efile = $_trace[0]['file'];
            $eline = $_trace[0]['line'];
            $emsg = $msg;
            $etrace = NULL; // TODO backtrace array to string
        }
        // log
        error_log("LGI error: $emsg in $efile on $eline");
        if ($etrace) {
            error_log('LGI Stack trace:');
            foreach ($etrace as $line)
                error_log('LGI   '.$line);
        }
        // and add to list for output to user
	if(!isset($_SESSION["ErrorMessage"]))
	    $_SESSION["ErrorMessage"]="Error: ".$emsg;
	else
	    $_SESSION["ErrorMessage"]=$_SESSION["ErrorMessage"]."<br/>".$msg;
}

/**
 * Returns the error message set by setErrorMessage().
 * @return string
 */
function getErrorMessage()
{
	if(isset($_SESSION["ErrorMessage"]))
		return $_SESSION["ErrorMessage"];
	else
		return ""; 
}
/**
 * Clears the error message set by setErrorMessage().This function should be called explicitly to clear error message after reading it. 
 */
function clearErrorMessage()
{
	unset($_SESSION["ErrorMessage"]);
}

?>
