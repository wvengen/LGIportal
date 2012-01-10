<?php
/**
 * Database access
 *
 * @author wvengen
 * @package utilities
 */
/** */
require_once(dirname(__FILE__).'/common.php');


/** Make sure that there is a MySQL connection to the LGI database.
 *
 * Can be called more than one time, will only open a connection when none exists.
 */
function lgi_mysql_connect() {
	global $lgi_mysql_connection;
	// setup
	if ($lgi_mysql_connection==NULL)
		$lgi_mysql_connection = mysql_connect(config('MYSQL_SERVER'), config('MYSQL_USER'), config('MYSQL_PASSWORD')) or showDBError();
	mysql_select_db(config('MYSQL_DBNAME'), $lgi_mysql_connection) or showDBError();
}

/** Perform a query on the LGI database with proper escaping; sprintf-style function.
 *
 * Each '%t(<tblname>)' is replaced by the table name with table prefix.
 * Each '%%' in the query is replaced by the next argument, properly escaped.
 * If no MySQL connection is present, a connection is made first.
 * 
 * @param string $query query
 * @see mysql_query()
 */
function lgi_mysql_query() {
	lgi_mysql_connect();
	$args = func_get_args();
	$fmt = array_shift($args);
	$fmt = preg_replace('/%t\((.*)\)/', config('MYSQL_TBLPREFIX','').'$1', $fmt);
	$fmt = preg_replace('/%%/', '%s', $fmt);
	array_walk($args, create_function('&$v', '$v=mysql_real_escape_string($v);'));
	$result = mysql_query(vsprintf($fmt, $args));
	if ($result === false) showDBError();
	return $result;
}

/**
 * Throw and log MySQL database error.
 */
function showDBError() {
  error_log("MySQL error: ".mysql_error());
  throw new LGIPortalException("Server Error. Please contact web administrator.");
}

?> 