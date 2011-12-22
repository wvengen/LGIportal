<?php
/**
 * User management and authentication
 * 
 * @author wvengen
 * @package utilities
 */
/** */
require_once(dirname(__FILE__).'/common.php');

require_once('inc/db.php');

/**
* Create a password hash.
*
* Currently uses SHA-512, but this may change.
*
* @see http://stackoverflow.com/questions/1581610/how-can-i-store-my-users-passwords-safely
* @see http://stackoverflow.com/questions/401656/secure-hash-and-salt-for-php-passwords
* @see http://net.tutsplus.com/tutorials/php/understanding-hash-functions-and-keeping-passwords-safe/
* @see http://packages.python.org/passlib/modular_crypt_format.html
*
* @param string $password password to hash
* @param string $salt hex salt to use, or omit to generate random 64-bit number
* @return string password hash in modular crypt format
*/
function hash_password($password, $salt=NULL) {
	if ($salt==NULL) {
		// no salt given, generate random
		$salt = substr(sha1(mt_rand()),0,16);
	} elseif ($salt[0]=='$') {
		// salt is password hash, extract salt
		$salt = explode('$',$salt);
		$salt = $salt[2];
	} // otherwise use salt as-is
	return '$6$'.$salt.'$'.hash('sha512', $salt.':'.$password);
}


/** A user of the LGI portal.
 * 
 * 
 * 
 * @author wvengen
 */
class LGIUser {

	/** Userid of this user. */
	protected $userid;
	
	/** Creates new LGI user from username in database */
	function LGIUser($userid) {
		$this->userid = $userid;
	}
	
	/** Return whether supplied password is correct or no. */
	function password_check($password) {
		$result = lgi_mysql_query("SELECT password_hash FROM %t(users) WHERE userid='%%'", $this->userid);
		if (!($row=mysql_fetch_row($result))) return false;
		$hash = $row[0];
		return hash_password($password, $hash) === $hash;
	}
	
	/** Update password in the database */
	function password_update($password) {
		$hash = hash_password($password);
		lgi_mysql_query("UPDATE %t(users) SET password_hash='%%' WHERE userid='%%'",$hash, $this->userid);
	}
	
	/** Create a new user in the database.
	 * 
	 * If $cert and $key are both omitted or NULL, no LGI credentials are setup for the user.
	 * 
	 * @param string $userid Userid of the user to create
	 * @param string $password password of the new user
	 * @param string $cert LGI credential certificate file location
	 * @param string $key LGI credential private key file location
	 * @return unknown
	 */
	static function create($userid, $password, $cert=NULL, $key=NULL) {
		$hash = hash_password($password);
		lgi_mysql_query("INSERT INTO %t(users) SET name='%%', passwd_hash='%%'", $userid, $hash);
		$o = LGIUser($userid);
		if ($cert && $key) $o->set_certkey($cert, $key);
		return $o;
	}
	
	/** Set private key and certificate for user.
	 * 
	 * While the database model supports multiple certificates/keys for a single user,
	 * this web application supports only one.
	 * 
	 * The certificate is parsed first, so that user and group information can be put
	 * into the database as well.
	 * 
	 * @param string $cert path to certificate file
	 * @param string $key path to private key file
	 */
	function set_certkey($cert, $key) {
		// get user and groups from certificate
		
		// update or insert row
		$query = "%t(usercerts) SET cert='%%', key='%%', username='%%', fixedgroups='%%' WHERE userid='%%'";
		lgi_mysql_query("UPDATE $query",  $cert, $key, $certuser, $certgroups, $this->userid);
		if (mysql_num_rows()==0)
			lgi_mysql_query("INSERT INTO $query",  $cert, $key, $certuser, $certgroups, $this->userid);
	}
	
	/** Returns the certificate file location.
	 *
	 * This is cached in the session.
	 */
	function get_cert() {
		if (!isset($_SESSION['user_cert'])) {
			// not in session, get from db
			$result = lgi_mysql_query("SELECT cert,key FROM %t(usercerts) WHERE userid='%%'", $this->userid);
			$row = mysql_fetch_row($result);		
			$_SESSION['user_cert'] = $row[0];
			$_SESSION['user_key'] = $row[1];
		} 
		return $_SESSION['user_cert'];
	}
	
	/** Returns the private key file location.
	 * 
	 * This is cached in the session.
	 */
	function get_key() {
		// get_cert() also fetches key
		if (!isset($_SESSION['user_key'])) get_cert();
		return $_SESSION['user_key'];
	}
}

?>