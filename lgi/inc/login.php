<?php
/**
 * Utility functions for user authentication
 *
 * @author Deepthi
 * @package utilities
 */
/** */
require_once(dirname(__FILE__).'/common.php');
require_once('inc/errors.php');


//$DB_CONFIG_FILE is set by the administrator. Hence check whether the file exists or not. If file does not exists we cannot access database. Hence report error!
$dbcfg = config('DB_CONFIG_FILE');
if(!file_exists($dbcfg))
{
	//given path to the DB_configuration file is invalid. Generate an error.
	error_log("Error: in lgi.config.php: File not found ".$dbcfg);
	throw new LGIPortalException("Server Configuration Error. Please report to web-administrator.");

}
// include the file having database access details
//   Before inclusion make sure in this namespace, which could have been included from a
//   function, the relevant variables are declared global.
global $mysql_server, $mysql_user, $mysql_password, $mysql_dbname;
require($dbcfg);

/**
 * Checks whether username and passwords corresponds to a valid user. Returns True if credentials are valid otherwise returns false.
 * @param string $user	plaintext username to be checked
 * @param string $password	plaintext password
 * @return boolean
 */
function verifyUserPassword($user,$password)	//input plain text username and password
{
	global $mysql_server,$mysql_user,$mysql_password,$mysql_dbname;
	$connection = mysql_connect($mysql_server, $mysql_user, $mysql_password) or showDBError();
	mysql_select_db($mysql_dbname, $connection) or showDBError();

	//validate username and password for preventing SQL injection
	$username=mysql_real_escape_string($user);
	$pswd=mysql_real_escape_string($password);

	$query="SELECT passwordHash,salt FROM users WHERE userId='".$username."'";
	$result=mysql_query($query) or showDBError();

	$row= mysql_fetch_row($result);
	mysql_close($connection);
	if($row)	//if there is a record in the database for the user
	{
		$password_hash=$row[0];
		$salt=$row[1];
		return(strcmp(hashPassword($password,$salt),$password_hash)==0);	//check whether passwords match

	}
	else	//if there is no record in the database for the user
	{
		return false;
	}
}

/**
 * Set user password in database
 *
 * @param string $user username to set password of
 * @param string $password new password to set
 */
function setUserPassword($user, $password)
{
	global $mysql_server,$mysql_user,$mysql_password,$mysql_dbname;
	$connection = mysql_connect($mysql_server, $mysql_user, $mysql_password) or showDBError();
	mysql_select_db($mysql_dbname, $connection) or showDBError();

	$salt=substr(md5(uniqid(rand(), true)),0,19);
	$hash=mysql_real_escape_string(hashPassword($password,$salt));
	$user=mysql_real_escape_string($user);

	$query="UPDATE users SET passwordHash='$hash', salt='$salt' WHERE userId='$user'";
	$result=mysql_query($query) or showDBError();

	mysql_close($connection);
}

/**
 * Inserts a new user into the database
 *
 * @param string $user username
 * @param string $password initial password
 * @param string $certificate full path to his certificate
 * @param string $privatekey full path to his private key
 */
function createUser($user, $password, $certificate, $privatekey)
{
	global $mysql_server,$mysql_user,$mysql_password,$mysql_dbname;
	$connection = mysql_connect($mysql_server, $mysql_user, $mysql_password) or showDBError();
	mysql_select_db($mysql_dbname, $connection) or showDBError();

	$salt=substr(md5(uniqid(rand(), true)),0,19);
	$hash=mysql_real_escape_string(hashPassword($password,$salt));
	$user=mysql_real_escape_string($user);

	$query="INSERT INTO users (passwordHash, salt, userId) VALUES ('$hash', '$salt', '$user')";
	$result=mysql_query($query) or showDBERror();

	$certificate=mysql_real_escape_string($certificate);
	$password=mysql_real_escape_string($password);

	$query="INSERT INTO usercertificates (userId, certificate, userkey) VALUES ('$user', '$certificate', '$privatekey')";
	$result=mysql_query($query) or showDBError();

	mysql_close($connection);
}

/**
 * Find the hash of concatenated string of two parameters passed. Returns the resulting hash. Used for password hashing with salt.
 * @param string $password
 * @param string $salt
 * @return string
 */
function hashPassword($password,$salt)
{
	//return md5($password.$salt);
	return hash("sha512",$password.$salt);
}

/**
 * Request user for relogin
 */
function relogin()
{
	header("Location: ".config('LGI_ROOT')."/index.php");
}

/**
 * Check whether current session belongs to an authenticated user. If not request for log in. To be called before doing any function where user authentication is required.
 */
function authenticateUser()
{
	if(checkValidSession())
	{
		return true;
	}
	else
	{
		relogin();
	}
}

/**
 * Retrieve the reference to certificate of the user
 * @param string $user
 * @return string
 */
function getCertificateFile($user=null)
{
	if ($user===null) $user=$_SESSION['user'];

	if(isset($_SESSION['certificate']))          //if reference to certificate is set in session, no need to query database
	{
		return $_SESSION['certificate'];
	}
		
	global $mysql_server,$mysql_user,$mysql_password,$mysql_dbname;
	$connection = mysql_connect($mysql_server, $mysql_user, $mysql_password) or showDBError();
	mysql_select_db($mysql_dbname, $connection) or showDBError();
	$username=mysql_real_escape_string($user); //$user will already be escaped. But this is for extra safety
	$query="SELECT certificate FROM usercertificates WHERE userId='".$username."'";
	$result=mysql_query($query) or showDBError();
	
	$row= mysql_fetch_row($result);
	mysql_close($connection);
	//if there is a record in the database for the user
	if($row)
	{
		$_SESSION['certificate']=$row[0];       //save the reference certificate in session. so next time you dont have to query database again.
		return $row[0];
	}
	return NULL;
}

/**
 * Retreive the reference to key of the user stored in database
 * @param string $user
 * @return string
 */
function getKeyFile($user=null)
{
	if ($user===null) $user=$_SESSION['user'];

	if(isset($_SESSION['key']))          //if reference to key is set in session, no need to query database
	{
		return $_SESSION['key'];
	}
	global $mysql_server,$mysql_user,$mysql_password,$mysql_dbname;
	$connection = mysql_connect($mysql_server, $mysql_user, $mysql_password) or showDBError();
	mysql_select_db($mysql_dbname, $connection) or showDBError();
	$username=mysql_real_escape_string($user); //$user will already be escaped. But this is for extra safety
	$query="SELECT userkey FROM usercertificates WHERE userId='".$username."'";
	$result=mysql_query($query) or showDBError();
		
	$row= mysql_fetch_row($result);
	mysql_close($connection);
	//if there is a record in the database for the user
	if($row)
	{
		$_SESSION['key']=$row[0];       //save the reference key in session. so next time you dont have to query database again.
		return $row[0];
	}

	return NULL;
}

/**
 * Throw and log MySQL database error.
 */
function showDBError() { 
        error_log("MySQL error: ".mysql_error());
        throw new LGIPortalException("Server Error. Please contact web administrator.");
}

?>