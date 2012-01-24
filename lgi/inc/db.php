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
		$lgi_mysql_connection = mysql_connect(config('MYSQL_SERVER'), config('MYSQL_USER'), config('MYSQL_PASSWORD')) or lgi_mysql_throw();
	mysql_select_db(config('MYSQL_DBNAME'), $lgi_mysql_connection) or lgi_mysql_throw();
}

/** Perform a query on the LGI database with proper escaping; sprintf-style function.
 *
 * Each '%t(<tblname>)' is replaced by the table name with table prefix (backtick quoted).
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
	$fmt = preg_replace('/%t\(([^)]+)\)/', '`'.preg_quote(config('MYSQL_TBLPREFIX','')).'$1`', $fmt);
	$fmt = preg_replace('/%%/', '%s', $fmt);
	array_walk($args, create_function('&$v', '$v=mysql_real_escape_string($v);'));
	//error_log("$fmt | ".var_export($args,true));
	$result = mysql_query(vsprintf($fmt, $args));
	if ($result === false) lgi_mysql_throw();
	return $result;
}

/** Perform a query on the LGI database and update session.
 * 
 * Same as {@link mysql_query() mysql_query()} but results are used to
 * update the {@link $_SESSION $_SESSION} variable. For example,
 * <code>
 * session_start();
 * lgi_mysql_fetch_session("SELECT '%%' AS `answer`", "hi there");
 * print $_SESSION['answer'];
 * </code>
 * will return "hi there".
 * 
 * Only the first row will be processed. If you want to have a comma-separated
 * list of values for all rows, use
 * {@link http://dev.mysql.com/doc/refman/5.0/en/group-by-functions.html#function%5Fgroup-concat GROUP_CONCAT}.
 * 
 * This function should only be used with SELECT statements, naturally.
 * 
 * @see lgi_mysql_query()
 */
function lgi_mysql_fetch_session() {
	// call_user_func_array('foo', func_get_args()) does not work in some php versions
	$args = func_get_args();
	$result = call_user_func_array('lgi_mysql_query', $args);
	is_resource($result) or lgi_mysql_throw("No SQL result to fetch.");
	$result = mysql_fetch_assoc($result);
	foreach($result as $k=>$v)
		$_SESSION[$k] = $v;
	return $result;
}

/**
 * Throw and log MySQL database error.
 * 
 * @param string $msg error message to log, or null to get from mysql driver  
 */
function lgi_mysql_throw($msg=null) {
	if (is_null($msg)) $msg = mysql_error();
	error_log('MySQL error: '.$msg);
	throw new LGIPortalException("Server Error. Please contact web administrator.");
}

?>
