<?php
/**
 * Command-line script for upgrading an installation
 *
 * @author wvengen
 * @package contrib
 */
/** */
define('LGI_OUTPUT_TYPE', 'text/plain');
require_once(dirname(__FILE__).'/inc/common.php');
require_once('inc/db.php');
require_once('inc/user.php');


// current database schema version (update with each change, please)
$newdbver = 1;


// only to be called from command-line
if (@$_SERVER['REQUEST_URI']) exit(0);

// handle help or missing options
if (in_array('-h', $argv) || in_array('--help', $argv)) {
	print("Usage: $argv[0] [-h] [-y]\n");
	print("Run this tool after updating LGIportal to upgrade the database.\n");
	exit(1);
}

$olddbver = lgi_get_db_version();
if ($olddbver == $newdbver) {
	print("database version already at $newdbver: nothing to do\n");
	exit(0);
}
if ($olddbver > $newdbver) {
	print("database schema version $olddbver is newer than current $newdbver\n");
	print("you should probably upgrade your LGIportal software\n");
	exit(1);
}
print("database upgrade from schema version $olddbver to $newdbver\n");

if (!in_array('-y', $argv) && !in_array('--yes', $argv)) {
	print("Before upgrading it is strongly recommended to BACKUP YOUR DATABASE!\n");
	print("In case of any errors, you can then restore the old version. Upgrading may not be reversible.\n");
	print("Then to do the upgrade, specify the -y option.\n");
	exit(0);
}


// LGIportal 0.4 saw a complete restructuring of the database
if ($olddbver < 1) {
	// migrate users to new table structure (including password hash change)
	lgi_mysql_query("RENAME TABLE %t(users) TO %t(users_old)");
	try {
		lgi_mysql_query("CREATE TABLE %t(users) ("
		               ."  `name`         VARCHAR(20) PRIMARY KEY,"
		               ."  `dfl_project`  VARCHAR(127),"
		               ."  `passwd_hash`  VARCHAR(150)"
		               .") SELECT "
		               ."    `userId` AS `name`,"
		               ."    CONCAT('\$LGIportal_old_1\$',`salt`,'\$',`passwordHash`) AS `passwd_hash`"
		               ."FROM %t(users_old)");
		// new certificate table structure
		print("creating new tables: usercerts");
		lgi_mysql_query("CREATE TABLE %t(usercerts) ("
		               ."  `id`           INTEGER AUTO_INCREMENT PRIMARY KEY,"
		               ."  `user`         VARCHAR(20) REFERENCES %t(users)(`name`),"
		               ."  `cert`         TEXT,"
		               ."  `key`          TEXT,"
		               ."  `username`     VARCHAR(20),"
		               ."  `fixedgroups`  BOOLEAN"
		               .")");
		print(" usergroups");
		lgi_mysql_query("CREATE TABLE %t(usergroups) ("
		               ."  `usercertid`   INTEGER REFERENCES %t(usercerts)(`id`),"
		               ."  `name`         VARCHAR(20),"
		               ."  `dfl`          BOOLEAN DEFAULT FALSE,"
		               ."  PRIMARY KEY(`usercertid`, `name`)"
		               .")");
		print(" userprojects");
		lgi_mysql_query("CREATE TABLE `userprojects` ("
		               ."  `usercertid`   INTEGER REFERENCES %t(usercerts)(`id`),"
		               ."  `name`         VARCHAR(20),"
		               ."  PRIMARY KEY(`usercertid`, `name`)"
		               .")");
		print("\n");
		// import all users
		print("transferring users: ");
		$r = lgi_mysql_query("SELECT `userId`, `certificate`, `userkey` FROM %t(usercertificates)");
		while ($f = mysql_fetch_array($r)) {
			print($f[0]." ");
			$user = new LGIUser($f[0]);
			$user->set_certkey($f[1], $f[2]);
		}
		print("\n");
		print("creating new tables: auth_simplesamlphp");
		// create new simplesamlphp auth table
		lgi_mysql_query("CREATE TABLE `auth_simplesamlphp` ("
		               ."  `user`         VARCHAR(20) NOT NULL REFERENCES %t(users)(`name`),"
		               ."  `authsource`   VARCHAR(255) NOT NULL,"
		               ."  `authid`       VARCHAR(255) NOT NULL,"
		               ."  `enabled`      BOOLEAN NOT NULL DEFAULT FALSE,"
		               ."   PRIMARY KEY(`authsource`, `authid`)"
		               .")");
		// create _meta table
		print(" _meta");
		lgi_mysql_query("CREATE TABLE `_meta` ("
		               ."  `db_version`     INTEGER PRIMARY KEY,"
		               ."  `portal_version` VARCHAR(20),"
		               ."  `applied`        DATETIME NOT NULL,"
		               ."  `note`           TEXT"
		               .")");
		print("\n");
		// migration successful, delete old tables
		print("cleaning up\n");
		lgi_mysql_query("DROP TABLE %t(usercertificates)");
		lgi_mysql_query("DROP TABLE %t(users_old)");
	} catch (Exception $e) {
		// migration failed, restore old situation
		print("\n");
		print("error, reverting old database\n");
		@lgi_mysql_query("DROP TABLE %t(users)"); 
		@lgi_mysql_query("DROP TABLE %t(usercerts)"); 
		@lgi_mysql_query("DROP TABLE %t(userprojects)"); 
		@lgi_mysql_query("DROP TABLE %t(usergroups)"); 
		@lgi_mysql_query("DROP TABLE %t(auth_simplesamlphp)"); 
		@lgi_mysql_query("DROP TABLE %t(_meta)");
		lgi_mysql_query("RENAME TABLE %t(users_old) TO %t(users)");
		throw $e;
	}
}


// register this upgrade in the _meta table
print("updating _meta table with new version information\n");
if (config('LGI_VERSION', null)!==null)
	lgi_mysql_query("INSERT INTO %t(_meta) SET `db_version`=%s, `portal_version`='%s', `applied`=NOW(), `note`='by upgrade script (md5=%s)'", config('LGI_VERSION'), (int)$newdbver, md5_file(__FILE__));
else
	lgi_mysql_query("INSERT INTO %t(_meta) SET `db_version`=%s, `applied`=NOW(), `note`='by upgrade script (md5=%s)'", (int)$newdbver, md5_file(__FILE__));

print("upgrade succesfull!\n");

// done!
exit(0);


// return database schema version of current database
function lgi_get_db_version() {
	$r = lgi_mysql_query("SHOW TABLES");
	while ($f=mysql_fetch_array($r)) {
		if ($f[0]==config('MYSQL_TBLPREFIX', '').'_meta') {
			$r = lgi_mysql_query("SELECT `db_version` FROM %t(_meta) ORDER BY `db_version` DESC LIMIT 1");
			$f = mysql_fetch_array($r);
			return (int)$f[0];
		}
	}
	// _meta table not found: version 0
	return 0;
}

?>
