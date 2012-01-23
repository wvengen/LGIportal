#!/usr/bin/env php
<?php
/**
 * Command-line script for user management
 *
 * @author wvengen
 * @package contrib
 */
/** */
require_once(dirname(__FILE__).'/inc/common.php');
require_once('inc/user.php');


// only to be called from command-line
if (@$_SERVER['REQUEST_URI']) exit(0);

// parse options
$opts = getopt("m:u:p:c:k:lLdhV", array("method:", "user:", "password:", "certificate:", "key:", "list", "listauth", "delete", "help", "version"));
if (array_key_exists('V', $opts)) show_version(true);
if (array_key_exists('h', $opts)) show_help(true);

if (array_key_exists('l', $opts)) {
	// list users
	user_list(@$opts['u']);

} elseif (array_key_exists('L', $opts)) {
	// list authentication sources
	user_list_authsources(@$opts['u'], @$opts['m'], @$opts['p']);

} elseif (array_key_exists('d', $opts)) {
	// delete
	if (!array_key_exists('u', $opts)) {
		print("Missing username.\n");
		exit(1);
	}
	user_delete($opts['u'], @$opts['m'], @$opts['p']);

} else {
	if (!array_key_exists('u', $opts)) {
		print("Missing username.\n");
		exit(1);
	}
	if ( (array_key_exists('k', $opts) && !array_key_exists('c', $opts)) ||
	     (array_key_exists('c', $opts) && !array_key_exists('k', $opts)) ) {
		print("Must specify both key and certificate, or none.\n");
		exit(1);
	}
	user_add($opts['u'], @$opts['m'], @$opts['p'], @$opts['c'], @$opts['k']);
}




/* ** */

function show_version($doexit=false) {
	print("LGIportal createuser ".config('LGI_VERSION')."\n");
	if ($doexit) exit(0);
}

function show_help($doexit=false) {
	global $argv;
	print("Add, modify, list or delete LGIportal users.\n");
	print("\n");
	print("Usage: $argv[0] [-m local]    [-c <cert> -k <key>] -u <username> [-p <password>]\n");
	print("       $argv[0]  -m <authsrc> [-c <cert> -k <key>] -u <username> [-p <authid>]\n");
	print("       $argv[0]  -l [-u <username>]\n");
	print("       $argv[0]  -L [-u <username>] [-m <authsrc> [-p <authid>]]\n");
	print("       $argv[0]  -d  -u <username>  [-m <authsrc> [-p <authid>]]\n");
	print("       $argv[0]  [-hV]\n");
	print("\n");
	print("Options:\n");
	print("       -m <authsrc>     Authentication source. This is either 'local' or an\n");
	print("                        authentication source as defined in SimpleSAMLphp's\n");
	print("                        config/authsources.php.\n");
	print("       -c <cert>        Set full path of LGI certificate for user.\n");
	print("       -k <key>         Set full path of LGI private key for user.\n");
	print("       -u <username>    Username to operate on.\n");
	print("       -p <passwd>      Password to set (for 'local') or authentication id for\n");
	print("                        other authentication sources.\n");
	print("       -l               List user(s).\n");
	print("       -L               List authentication sources.\n");
	print("       -d               Delete authentication source for user. When no authsrc\n");
	print("                        is specified, the user is deleted completely.\n");
	print("\n");
	print("Setting an empty password removes the ability to login with a local account.\n");
	if ($doexit) exit(0);
}

function user_list($username=null) {
	$qfull  = "SELECT `name`, `key`, `cert` FROM %t(users) LEFT JOIN %t(usercerts) ON %t(users).`name`=%t(usercerts).`user`";
	$filt   = array();
	$args   = array();
	// filter
	if ($username) {
		$filt[] = "`name`='%s'";
		$args[] = $username;
	}
	// select
	$qwhere = $filt ? "WHERE ".implode(' AND ', $filt) : '';
	$args = array_merge(array("$qfull $qwhere"), $args);
	$result = call_user_func_array('lgi_mysql_query', $args);
	// show results
	$fmt = "%-20s %-5s %-5s\n";
	printf($fmt, "Portal user", "Key", "Cert");
	while ($row=mysql_fetch_array($result)) {
		printf($fmt, $row[0], is_readable($row[1])?'x':'', is_readable($row[2])?'x':'');
	}
}

function user_list_authsources($username=null, $authsource=null, $authid=null) {
	$qlocal = "SELECT `name` AS `user`,'local' AS `authsource`, `name` AS `authid`, `passwd_hash` IS NOT NULL AS `enabled` FROM %t(users)";
	$qextrn = "SELECT `user`, `authsource`, `authid`, `enabled` FROM %t(auth_simplesamlphp)";
	$qorder = "ORDER BY `user`, `authsource`, `authid`";
	$filt   = array();
	$args   = array();
	// filter
	if ($username) {
		$filt[] = "`user`='%s'";
		$args[] = $username;
	}
	if ($authsource) {
		$filt[] = "`authsource`='%s'";
		$args[] = $authsource;
	}
	if ($authid) {
		$filt[] = "`authid`='%s'";
		$args[] = $authid;
	}
	// select
	$qwhere = $filt ? "WHERE ".implode(' AND ', $filt) : '';
	$qfull  = "SELECT * FROM (($qlocal) UNION ($qextrn)) AS `user_list` $qwhere $qorder";
	$args = array_merge(array($qfull), $args, $args);
	$result = call_user_func_array('lgi_mysql_query', $args);
	// show results
	$fmt = "%-20s %-20s %-5s %-20s\n";
	printf($fmt, "Portal user", "Authsource", "Enbl", "Auth id");
	while ($row=mysql_fetch_array($result)) {
		printf($fmt, $row[0], $row[1], $row[3]?'x':'', $row[2]);
	}
}

function user_delete($username, $authsource=null, $authid=null) {
	if ($authsource=='local') {
		// just unset password
		lgi_mysql_query("UPDATE %t(users) SET `passwd_hash`=NULL WHERE `name`='%s'", $username);
		return;
	}
	if (!$authsource) {
		// delete account first
		lgi_mysql_query("DELETE FROM %t(users) WHERE `name`='%s'", $username);
	}
	// delete matching external authentication sources
	$qfull  = "DELETE FROM %t(auth_simplesamlphp) WHERE `user`='%s'";
	$filt   = array();
	$args   = array($username);
	if ($authsource) {
		$filt[] = "`authsource`='%s'";
		$args[] = $authsource;
	}
	if ($authid) {
		$filt[] = "`authid`='%s'";
		$args[] = $authid;
	}
	// select
	$qwhere = $filt ? "WHERE ".implode(' AND ', $filt) : '';
	$args = array_merge(array("$qfull $qwhere"), $args);
	$result = call_user_func_array('lgi_mysql_query', $args);
	
}

function user_add($username, $authsource, $authid, $key, $cert) {
	// check if user exists
	$result = lgi_mysql_query("SELECT `name` FROM %t(users) WHERE `name`='%s'", $username);
	if (mysql_num_rows($result)>0) {
		// existing user
		$user = new LGIUser($username);
	} else {
		// new user, create it
		$user = LGIUser::create($username);
	}
	// update authsources
	if (!$authsource || $authsource=='local') {
		// local user password
		if (is_null($authid)) throw new LGIPortalException('Please specify password.');
		$user->password_update($authid);
	} else {
		// external authentication source
		if (is_null($authid)) throw new LGIPortalException('Please specify authid.');
		lgi_mysql_query("INSERT INTO %t(auth_simplesamlphp) SET `user`='%s', `authsource`='%s', `authid`='%s', `enabled`=TRUE", $username, $authsource, $authid);
	}

	// finally update key and certificate, when specified
	if ($key && $cert) $user->set_certkey($cert, $key);
}

?>
