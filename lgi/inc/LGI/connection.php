<?php
/**
 * Base LGI connection
 *
 * @author wvengen
 * @package LGI
 */
/** */

// @todo skip json conversion to array, the object should be fair enough

// need json module
if (!function_exists('json_decode')) {
  $prefix = (PHP_SHLIB_SUFFIX === 'dll') ? 'php_' : '';
  dl($prefix . 'json.' . PHP_SHLIB_SUFFIX);
  if (!function_exists('json_decode'))
    die('Server error: need PHP 5.2.0 or higher, or the JSON module');
}

/** Base LGI exception
 * @package LGI */
class LGIException extends Exception { }
/** Exception in LGI connection
 * @package LGI */
class LGIConnectionException extends LGIException { }
/** Exception for LGI server error
 * @package LGI */
class LGIServerException extends LGIConnectionException { }

/** Base class for communicating with an LGI server.
 *
 * This takes care of authentication, posting, xml parsing and basic error handling.
 *
 * @package LGI
 */
class LGIConnection
{
	/** Curl handle, or null if no connection made
	 * @var object */
	private $curlh;
	/** LGI project server url to work with
	 * @var string */
	protected $url;
	/** LGI CA chain for validating the project server's certificate
	 * @var string */
	protected $ca_chain;
	/** User certificate filename
	 * @var string */
	protected $certificate;
	/** User private key filename
	 * @var string */
	protected $privatekey;

	/** Create new connection object
	 *
	 * @param string $url LGI project server url
	 * @param string $certificate location of user certificate file
	 * @param string $privatekey location of user key file
	 * @param string $ca_chain location of CA certificates file
	 */
	function __construct($url, $certificate, $privatekey, $ca_chain)
	{
		$this->curlh = null;
		$this->url = $url;
		$this->certificate = $certificate;
		$this->privatekey = $privatekey;
		$this->ca_chain = $ca_chain;
	}

	/** Check configuration and open connection to project server
	 *
	 * @throws LGIException when there is a configuration error
	 */
	function connect()
	{
		// some checks first
		$this->check_file($this->certificate, 'LGI certificate');
		$this->check_file($this->privatekey, 'LGI private key');
		$this->check_file($this->ca_chain, 'LGI CA chain');
		if (!$this->url)
			throw new LGIException('No server url specified');
		if (strtolower(substr($this->url,0,6))!='https:')
			throw new LGIException('Server must be an https url');
		// then initialise
		$this->curlh = curl_init();
		$this->setDebug(false);
		curl_setopt($this->curlh, CURLOPT_SSLCERT, $this->certificate);
		curl_setopt($this->curlh, CURLOPT_SSLKEY, $this->privatekey);
		curl_setopt($this->curlh, CURLOPT_CAINFO, $this->ca_chain);
		curl_setopt($this->curlh, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($this->curlh, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($this->curlh, CURLOPT_NOSIGNAL, true);
		curl_setopt($this->curlh, CURLOPT_NOPROGRESS, true);
		curl_setopt($this->curlh, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($this->curlh, CURLOPT_RETURNTRANSFER, true);
		// we handle errors ourselves because curl doesn't show the result on error
		curl_setopt($this->curlh, CURLOPT_FAILONERROR, false);
	}

	/** Close any open connections */
	function close()
	{
		curl_close($this->curlh);
		$this->curlh = null;
	}

	/** Do request at LGI project server
	 *
	 * Files can be uploaded with the request. The filename can be a string
	 * or array. In the latter case, the first value of the array is the
	 * actual filename, and the second value the destination filename on
	 * the server.
	 *
	 * @param string $url path to call, relative to this object's url or absolute
	 *        when starting with 'https:'
	 * @param array(string) $variables key=>value arguments
	 * @param array(string) $files key=>filename to upload with request
	 * @throws LGIConnectionException when there is a connection or server problem
	 * @throws LGIServerException when the project server returns an error response
	 * @return array with parsed response
	 */
	protected function postToServer($url, $variables=array(), $files=array())
	{
		// relative to base url
		if (strtolower(substr($url,0,6))!='https:') $url = $this->url.$url;
		// be safe when settings variables
		$variables = array_map(create_function('$s', 'return ($s&&$s[0]=="@") ? "&#64;".substr($s,1) : $s;'), $variables);
		// file uploads as special postfields
		$files = $this->postPrepareUpload($files);
		// perform request
		$resp = $this->curl_exec($url, array(
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => array_merge($variables, $files)
			));
		$resp = json_decode(json_encode(simplexml_load_string($resp)), TRUE);
		// LGI adds spaces to each and every element *sigh*
		array_walk_recursive($resp, create_function('&$s', '$s=trim($s);'));
		// move @attributes to 'normal' members for easy access (ok for LGI)
		$resp = array_attributes_to_values($resp);
		// handle LGI error response
		if (array_key_exists('response', $resp) && array_key_exists('error', $resp['response'])) {
			$err = $resp['response']['error'];
			throw new LGIServerException(sprintf('LGI error %d: %s',
				$err['number'], $err['message']), $err['number']);
		}
		return $resp;
	}

	/** Prepares files for curl file upload
	 *
	 * Implements a workaround for PHP bug #48962, where the uploaded file
	 * receives the same filename as the local file has here.
	 *
	 * @param array(mixed) $files list of files to upload, or each element
	 *        can be an array(local_file_path, destination_file_name)
	 * @return array suitable to pass to {@link curl_setopt curl_setopt}'s {@link CURLOPT_POSTFIELDS CURLOPT_POSTFIELDS}
	 * @link http://bugs.php.net/48962
	 */
	protected function postPrepareUpload($files)
	{
		if (count($files)===0) return $files;
		// The bug was fixed at some point
		if (version_compare(PHP_VERSION, '5.3.1') >= 0)
			return array_map(create_function('$s', 'return is_array($s) ? "@".$s[0].";filename=".$s[1] : "@".$s;'), $files);
		// but for earlier versions we need to rename the files
		$newfiles = array();
		$tmpdir = lgiconnection_mktempdir(null, 'LGIportal');
		register_shutdown_function('lgiconnection_delete_tempdir', $tmpdir);
		foreach ($files as $key=>$file){
			if (!is_array($file)) {
				// no different filename, just upload as is
				$newfiles[$key]='@'.$file;
			} else {
				// move or copy file to tempdir with new name
				$newfilename = $tmpdir.'/'.$file[1];
				if (is_uploaded_file($file[0]))
					move_uploaded_file($file[0], $newfilename);
				else
					copy($file[0], $newfilename);
				$newfiles[$key]='@'.$newfilename;
			}
		}
		return $newfiles;
	}

	/** Checks if file is readable
	 *
	 * @param string $file filename to check
	 * @param string $desc description of filename, for error message
	 * @throws LGIException when file could not be read
	 */
	protected function check_file($file, $desc)
	{
		if (!file_exists($file)) {
			user_error($desc.' not found: '.$file);
			throw new LGIException($desc.' not found');
		}
		if (!is_readable($file)) {
			user_error($desc.' found but not readable: '.$file);
			throw new LGIException($desc.' not readable');
		}
	}

	/** Set curl debug flag
	 *
	 * @param bool $debug debug flag
	 */
	function setDebug($debug)
	{
		if (!$this->curlh) return;
		curl_setopt($this->curlh, CURLOPT_VERBOSE, $debug);
	}

	/** Download file from repository
	 *
	 * @param string $url url of the file to download
	 * @return string contents of the file
	 * @throws LGIConnectionException when there is a connection or server problem
	 */
	function fileDownload($url)
	{
		return $this->curl_exec($url);
	}

	/** Download file from repository, print to stdout
	 *
	 * @param string $url url of the file to download
	 * @throws LGIConnectionException when there is a connection or server problem
	 */
	function filePassthru($url)
	{
		$this->curl_exec($url, array(
				CURLOPT_HEADERFUNCTION => create_function('$c,$s','header($s);return strlen($s);'),
				CURLOPT_WRITEFUNCTION => create_function('$c,$s','print($s);return strlen($s);')
			), false);
	}

	/** Delete file from repository
	 *
	 * @param string $url url of the file to delete
	 * @throws LGIConnectionException when there is a connection or server problem
	 */
	function fileDelete($url)
	{
		return $this->curl_exec($url, array(CURLOPT_CUSTOMREQUEST => "DELETE"));
	}

	/** Upload a file to the repository
	 *
	 * @param string $url url of the file to upload
	 * @param string $data file contents
	 * @throws LGIConnectionException when there is a connection or server problem
	 */
	function fileUpload($url, $data)
	{
		return $this->curl_exec($url, array(
				CURLOPT_POST => true,
				CURLOPT_CUSTOMREQUEST => "PUT",
				CURLOPT_HTTPHEADER => array('Content-Length: '.strlen($data)),
				CURLOPT_POSTFIELDS => $data
			));
	}

	/** List files in repository
	 *
	 * @param string $url repository url
	 * @return array file information from server response
	 * @throws LGIConnectionException when there is a connection or server problem
	 * @throws LGIServerException when the project server returns an error response
	 */
	function fileList($url)
	{
		$slashpos = strrpos($url,'/');
		$repourl = substr($url,0,$slashpos+1).'..';
		$repoid = substr($url,$slashpos+1);
		
		$result = $this->postToServer($repourl.'/repository_content.php', array('repository'=>$repoid));
		if (!array_key_exists('file', $result)) $result['file'] = array();
		elseif (!is_array($result['file'][0])) $result['file'] = array(0=>$result['file']);
		return $result;
	}

	/** Curl call
	 *
	 * Resets curl options to sensible values so that one can re-use
	 * connections without having to worry what happened before. This
	 * is not too easy because of php bug #54022; if you start to use
	 * this function and set other curl options as well, make sure to
	 * add code to reset it here when not specified.
	 *
	 * @param string $url url to work with
	 * @param array(mixed) $curlopts curl options to use
	 * @param bool $checkreturn whether throw an exception if the HTTP response code >= 400
	 * @return string contents of the url
	 * @throws LGIConnectionException when there is a connection or server problem
	 * @see http://bugs.php.net/bug.php?id=54022
	 */
	private function curl_exec($url, $curlopts=array(), $checkreturn=true)
	{
		if (!$this->curlh) $this->connect();
		// some defaults that may need to be reset
		//   lacking curl options reset (php bug #54022)
		//   all settings that may be changes need to be reset here :(
		if (!array_key_exists(CURLOPT_POST, $curlopts) &&
		    !array_key_exists(CURLOPT_NOBODY, $curlopts))
			$curlopts[CURLOPT_HTTPGET] = true;
		if (!array_key_exists(CURLOPT_CUSTOMREQUEST, $curlopts))
			$curlopts[CURLOPT_CUSTOMREQUEST] = 
				 (@$curlopts[CURLOPT_POST])   ? 'POST' :
				((@$curlopts[CURLOPT_NOBODY]) ? 'HEAD' : 'GET' );
		if (!array_key_exists(CURLOPT_HTTPHEADER, $curlopts))
			$curlopts[CURLOPT_HTTPHEADER] = array();
		$curlopts[CURLOPT_URL] = $url;

		/*
		// the order may be important for request-related options
		// when you have problems, try to enable this code fragment
		$rfields = array(CURLOPT_POST, CURLOPT_HTTPGET, CURLOPT_POSTFIELDS, CURLOPT_CUSTOMREQUEST);
		foreach (array_reverse($rfields) as $f) {
			if (array_key_exists($f, $curlopts)) {
				$v = $curlopts[$f];
				unset($curlopts[$f]);
				$curlopts = array($f=>$v) + $curlopts;
			}
		}
		*/

		// set options; PHP=<5.3.1 needs to do it one by one
		if (function_exists('curl_setopt_array')) {
			curl_setopt_array($this->curlh, $curlopts);
		} else {
			foreach ($curlopts as $o => $v)
				curl_setopt($this->curlh, $o, $v);
		}
		$result = curl_exec($this->curlh);

		if (curl_errno($this->curlh) > 0) {
			throw new LGIConnectionException(sprintf('cURL error %d: %s',
				curl_errno($this->curlh), curl_error($this->curlh)), curl_errno($this->curlh));
		}
		if ($checkreturn && ($httpcode=curl_getinfo($this->curlh, CURLINFO_HTTP_CODE)) >= 400) {
			throw new LGIConnectionException(sprintf('Server error %d', $httpcode), $httpcode);
		}
		return $result;
	}
}

/**
 * Recursively move all @attributes children to its parent. This
 * is useful for an array from xml to move attributes to element
 * positions.
 *
 * @param array $arr array from xml structure to modify
 * @return the modified array
 * @access private
 */
function array_attributes_to_values(&$arr)
{
	foreach (array_keys($arr) as $k)
	{
		if (!is_array($arr[$k])) continue;
		if (array_key_exists('@attributes', $arr[$k])) {
			$arr[$k] = array_merge($arr[$k], $arr[$k]['@attributes']);
			unset($arr[$k]['@attributes']);
		}
		$arr[$k] = array_attributes_to_values($arr[$k]);
	}
	return($arr);
}

/** Create temporary directory
 *
 * @param string $dir directory to create temporary directory in, or null
 *        for default temporary directory
 * @param string $prefix prefix for directory name
 * @param int $mode permissions to create directory with
 * @return string name of temporary directory
 * @access private
 */
function lgiconnection_mktempdir($dir=null, $prefix='', $mode=0700)
{
	if (is_null($dir)) {
		if ( !function_exists('sys_get_temp_dir')) {
			function sys_get_temp_dir() {
				if( $temp=getenv('TMP') ) return $temp;
				if( $temp=getenv('TEMP') ) return $temp;
				if( $temp=getenv('TMPDIR') ) return $temp;
				$temp=tempnam(__FILE__,'');
				if (file_exists($temp)) {
					unlink($temp);
					return dirname($temp);
				}
				return null;
			}
		}
		if (is_null($dir)) $dir = sys_get_temp_dir();
	}

	if (substr($dir, -1)!='/') $dir .= '/';

	do {
		$path = $dir.$prefix.mt_rand(0, 9999999);
	} while (!mkdir($path, $mode));

	return $path;
} 

/** Delete directory and files in it (not recursively)
 *
 * @param string $dir directory to delete
 * @access private
 */
function lgiconnection_delete_tempdir($dir)
{
	if (!($dh=opendir($dir))) return;
	while (($f=readdir($dh))!==false) {
		if ($f=='.' | $f=='..') continue;
		unlink($dir.'/'.$f);
	}
	closedir($dh);
	rmdir($dir);
}

?>
