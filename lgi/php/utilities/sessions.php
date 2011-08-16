<?php
/**
 * Utility functions for session management
 *
 * @author Deepthi
 * @package utilities
 */

/**
 * Check whether current session corresponds to an authenticated user. Returns True if session is valid, otherwise returns false.
 * @return boolean 
 */	
function checkValidSession()
{
	if(isset($_SESSION['user']))
		return true;
	else
		return false;
}

/**
 * Clear previous session and sets a new session. Usefull when a user login. The username of user logged in should be passed.
 * @param string $user username of the user logged in	
 */
function setValidSession($user)
{
	//$user :- expecting a clean username, where it is processed for preventing cross site scripting.
	session_unset();
	session_destroy();
	INI_Set('session.cookie_secure',true);		
	//TODO: check whether https is enabled. Otherwise generate a warning/error
	session_start();
	session_regenerate_id(true);
	$_SESSION['user']=$user;
	return true;
}

/**
 * Generates a nonce for a future call and stores in into the session.
 *
 * Assumes that a session was started before.
 *
 * This is required for modifying operations to avoid cross-site request forgery (CRSF).
 * These requests should be POSTed and require a valid
 *
 * @return string nonce
 * @see verifyNonce
 */
function generateNonce()
{
	$nonce=uniqid(rand(), true); 
	$_SESSION['nonce']=$nonce;
	return $nonce;
}

/**
 * Verifies that the supplied nonce matches the session
 *
 * Assumes that a session was started before.
 *
 * Current implementation also deletes the nonce from the session, so that
 * a single nonce can only be used once.
 *
 * @param string $supplied nonce from user request
 * @return boolean whether the nonce was valid or not
 * @see generateNonce
 */
function verifyNonce($supplied)
{
	if (!isset($_SESSION['nonce']))
		return false;
	if (strcmp($supplied, $_SESSION['nonce'])!=0)
		return false;
	# ok, we can delete it as well now
	unset($_SESSION['nonce']);
	return true;
}

?>
