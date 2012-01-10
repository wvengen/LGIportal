<?php
/**
 * Command-line script for creating a user
 *
 * @author wvengen
 * @package contrib
 */
/** */
require_once(dirname(__FILE__).'/inc/common.php');
require_once('inc/user.php');


// only to be called from command-line
if (@$_SERVER['REQUEST_URI']) exit(0);

// handle help or missing options
if ($argc!=5 || in_array('-h', $argv) || in_array('--help', $argv)) {
	print("Usage: $argv[0] [-h] <username> <password> <certificate_path> <privatekey_path>\n");
	exit(1);
}

LGIUser::create($argv[1], $argv[2], $argv[3], $argv[4]);
 
?>
