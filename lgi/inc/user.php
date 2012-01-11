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
 * While multiple certificates per user are supported in the database, this class
 * assumes mostly that there is a single certificate for each user.
 * 
 * @author wvengen
 */
class LGIUser {

	/** Userid of this user. */
	protected $userid;
	
	/** Creates new LGI user object.
	 *
	 * The user should be present in the database, though this is not
	 * checked currently (subsequent methods may fail though).
	 * 
	 * @param $user username, or null to get from session
	 */
	function LGIUser($user=null) {
		if (is_null($user)) $user = $_SESSION['user'];
		$this->userid = $user;
	}
	
	/** Return whether supplied password is correct or no for this user. */
	function password_check($password) {
		return password_check_user($this->userid, $password);
	}
	
	/** Return whether specified username/password combination is correct or no. */
	static function password_check_user($userid, $password) {
		$result = lgi_mysql_query("SELECT `passwd_hash` FROM %t(users) WHERE `name`='%%'", $userid);
		if (!($row=mysql_fetch_row($result))) return false;
		$hash = $row[0];
		return hash_password($password, $hash) === $hash;
	}
	
	/** Update password in the database */
	function password_update($password) {
		$hash = hash_password($password);
		lgi_mysql_query("UPDATE %t(users) SET `passwd_hash`='%%' WHERE `name`='%%'",$hash, $this->userid);
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
		lgi_mysql_query("INSERT INTO %t(users) SET `name`='%%', `passwd_hash`='%%'", $userid, $hash);
		$o = new LGIUser($userid);
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
	 * TODO it may be good to wrap this (or more) into a transaction
	 * 
	 * @param string $cert path to certificate file
	 * @param string $key path to private key file
	 */
	function set_certkey($cert, $key) {
		// get user and groups from certificate
		$cn = explode(';', LGIUser::get_cert_cn($cert));
		$certuser = $certproject = $certgroups = null;
		if (count($cn)==2) {
			$certuser = $cn[0];
			$certgroups = null;
			$certprojects = explode(',', $cn[1]);
		} elseif (count($cn)==3) {
			$certuser = $cn[0];
			$certgroups = explode(',', $cn[1]);
			$certprojects = explode(',', $cn[2]);
		} else {
			throw new LGIPortalException("Unsupported certificate: need 2 or 3 semicolon-separated fields in CN");
		}
		// update or insert key/certificate
		$query = "%t(usercerts) SET `cert`='%%', `key`='%%', `username`='%%', `fixedgroups`='%%', `user`='%%'";
		$result = lgi_mysql_query("SELECT `id` FROM %t(usercerts) WHERE cert='%%' AND `key`='%%' AND `user`='%%'", $cert, $key, $this->userid);
		if ($result && mysql_num_rows($result)>0) {
			// already exists: update
			$usercertid = mysql_fetch_row($result);
			$usercertid = $usercertid[0];
			lgi_mysql_query("UPDATE $query WHERE `id`='%%'",  $cert, $key, $certuser, !is_null($certgroups), $this->userid, $usercertid);
		// new row: add it instead
		} else {
			lgi_mysql_query("INSERT INTO $query",  $cert, $key, $certuser, !is_null($certgroups), $this->userid);
			$usercertid = mysql_insert_id();
		}
		// create groups
		if ($certgroups!==null) {
			lgi_mysql_query("DELETE FROM %t(usergroups) WHERE `usercertid`='%%'", $usercertid);
			foreach ($certgroups as $g)
				lgi_mysql_query("INSERT INTO %t(usergroups) SET `usercertid`='%%', `name`='%%'", $usercertid, $g);
		}
		// and projects
		lgi_mysql_query("DELETE FROM %t(userprojects) WHERE `usercertid`='%%'", $usercertid);
		foreach($certprojects as $p)
			lgi_mysql_query("INSERT INTO %t(userprojects) SET `usercertid`='%%', `name`='%%'", $usercertid, $p);
	}
	
	/** Returns the username */
	function get_name() {
		return $this->userid;
	}
	
	/** Returns the certificate file location.
	 *
	 * This is cached in the session.
	 */
	function get_cert() {
		if (!isset($_SESSION['user_cert']))
			lgi_mysql_fetch_session("SELECT `cert` AS `user_cert`, `key` AS `user_key` FROM %t(usercerts) WHERE `user`='%%'", $this->userid);
		return $_SESSION['user_cert'];
	}
	
	/** Returns the private key file location.
	 * 
	 * This is cached in the session.
	 */
	function get_key() {
		// get_cert() also fetches key
		if (!isset($_SESSION['user_key']))
			$this->get_cert();
		return $_SESSION['user_key'];
	}
	
	/** Returns an array with the user's projects.
	 *
	 * This is cached in the session.
	 */
	function get_projects() {
		if (!isset($_SESSION['projects']))
			lgi_mysql_fetch_session("SELECT GROUP_CONCAT(`name`) AS `projects` FROM %t(userprojects) AS p, %t(usercerts) AS c WHERE p.`usercertid`=c.`id` AND c.`user`='%%'", $this->userid);
		return explode(',', $_SESSION['projects']);	
	}

	/** Returns an array with the user's groups.
	 *
	 * This is cached in the session.
	 */
	function get_groups() {
		if (!isset($_SESSION['groups']))
			lgi_mysql_fetch_session("SELECT GROUP_CONCAT(`name`) AS `groups` FROM %t(usergroups) AS p, %t(usercerts) AS c WHERE p.`usercertid`=c.`id` AND c.`user`='%%'", $this->userid);
		return explode(',', $_SESSION['groups']);
	}
	
	/** Returns the user's current group */
	function get_cur_group() {
		if (!isset($_SESSION['dfl_group'])) {
			// return comma-separated list of default groups from database
			lgi_mysql_fetch_session("SELECT GROUP_CONCAT(`name`) AS `dfl_group` FROM %t(usergroups) AS p, %t(usercerts) AS c WHERE p.`usercertid`=c.`id` AND c.`user`='%%' AND p.`dfl`=TRUE", $this->userid);
			// maybe there were no groups defined and it is not specified in the certificate; then use username
			//   TODO this will work only if usercerts.fixedgroups if TRUE, but I'm too lame to check that right now
			if (!isset($_SESSION['dfl_group']))
				$_SESSION['dfl_group'] = $this->userid;
		}
		return $_SESSION['dfl_group'];
	}
	
	/** Returns the user's current project */
	function get_cur_project() {
		if (!isset($_SESSION['dfl_project'])) {
			lgi_mysql_fetch_session("SELECT `dfl_project` AS `dfl_project` FROM %t(users) WHERE `name`='%%'", $this->userid);
			// if not set, return first project found
			if (!isset($_SESSION['dfl_project']))
				lgi_mysql_fetch_session("SELECT `name` AS `dfl_project` FROM %t(userprojects) AS p, %t(usercerts) AS c WHERE p.`usercertid`=c.`id` AND c.`user`='%%' LIMIT 1", $this->userid);
			if (!isset($_SESSION['dfl_project']))
				throw new LGIPortalException("No LGI projects for user: check certificate.");
		}
		return $_SESSION['dfl_project'];
	}
	
	/** Returns certificate CN.
	 * 
	 * @param string $cert certificate file path, or null to get from database.
	 * @return string distinguished name from certificate
	 */
	function get_cert_cn($cert=null) {
		if (is_null($cert)) $cert = $this->get_cert();
		// use OpenSSL extension when found
		if (extension_loaded('openssl')) {
			$x = openssl_x509_parse(file_get_contents($cert));
			if (!array_key_exists('subject', $x))
				throw new LGIPortalException("Cannot parse certificate (no subject; using extension)");
			if (!array_key_exists('CN', $x['subject']))
				throw new LGIPortalException("Cannot parse certificate (no CN in subject; using extension)");
			return $x['subject']['CN'];
		// Use OpenSSL binary when found
		} elseif (strncasecmp(exec('openssl version'), "OpenSSL", 7)==0) {
			$x = exec('openssl x509 -noout -subject -in '.escapeshellarg($cert));
			if (trim($x)=='')
				throw new LGIPortalException("Cannot parse certificate (no subject; using exec)");
			if (!preg_match('/^.*\/CN=(.*)(\/\.*?|)$/', $x, $matches))
				throw new LGIPortalException("Cannot parse certificate (no CN in subject; using exec)");
			return $matches[1];
		// No mechanism, fail
		} else {
			throw new LGIPortalException("Cannot parse certificate (openssl absent)");
		}
	}
}

?>