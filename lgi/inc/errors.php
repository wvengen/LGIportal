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
	session_start();
	$_SESSION["ErrorMessage"]=$msg;
}

/**
 * Appends an error message. This function can be called before it is redirected to error page. getErrorMessage() can be called to see the error message.
 * @param string $msg Error message
 */
function pushErrorMessage($msg)
{
	user_error($msg);
	session_start();
	if(!isset($_SESSION["ErrorMessage"]))
	    $_SESSION["ErrorMessage"]="Error: ".$msg;
	else
	    $_SESSION["ErrorMessage"]=$_SESSION["ErrorMessage"]."<br/>".$msg;
}

/**
 * Returns the error message set by setErrorMessage().
 * @return string
 */
function getErrorMessage()
{
	session_start();
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
	session_start();
	unset($_SESSION["ErrorMessage"]);
}

function showErrorPage()
{
     header("Location: ".config('LGI_ROOT')."/php/error.php");
}
?>
