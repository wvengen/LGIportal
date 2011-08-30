<?php
/**
 * Base LGI connection
 *
 * @author wvengen
 * @package LGI
 */
/** */

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
		if (!$this->curlh) $this->connect();
		// relative to base url
		if (strtolower(substr($url,0,6))!='https:') $url = $this->url.$url;
		// be safe when settings variables
		$variables = array_map(create_function('$s', 'return ($s&&$s[0]=="@") ? "&#64;".substr($s,1) : $s;'), $variables);
		// file uploads as special postfields
		$files = $this->postPrepareUpload($files);
		// perform request
		curl_setopt($this->curlh, CURLOPT_POST, true);
		curl_setopt($this->curlh, CURLOPT_POSTFIELDS, array_merge($variables, $files));
		$resp = $this->curl_exec($url);
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
	 * @param array(string) $files
	 * @return array suitable to pass to {@link curl_setopt curl_setopt}'s {@link CURLOPT_POSTFIELDS CURLOPT_POSTFIELDS}
	 */
	protected function postPrepareUpload($files)
	{
		if (count($files)===0) return $files;
		// The bug was fixed at some point
		if (version_compare(PHP_VERSION, '5.3.1') >= 0)
			return array_map(create_function('$s', 'return is_array($s) ? "@".$s[0].";filename=".$s[1] : "@".$s;'), $files);
		// but for earlier versions we need to rename the files
		throw new LGIConnectionException('Currently need PHP 5.3.1 or higher to do file uploads with submit.');
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
		if (!$this->curlh) $this->connect();
		curl_setopt($this->curlh, CURLOPT_POST, false);

		return $this->curl_exec($url);
	}

	/** Download file from repository, print to stdout
	 *
	 * @param string $url url of the file to download
	 * @throws LGIConnectionException when there is a connection or server problem
	 */
	function filePassthru($url)
	{
		if (!$this->curlh) $this->connect();
		curl_setopt($this->curlh, CURLOPT_POST, false);
		curl_setopt($this->curlh, CURLOPT_HEADERFUNCTION, create_function('$c,$s','header($s);return strlen($s);'));
		curl_setopt($this->curlh, CURLOPT_WRITEFUNCTION, create_function('$c,$s','print($s);return strlen($s);'));
		$this->curl_exec($url, false);
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
		if (!$this->curlh) $this->connect();

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
	 * @param string $url url to work with
	 * @param bool $checkreturn whether throw an exception if the HTTP response code >= 400
	 * @return string contents of the url
	 * @throws LGIConnectionException when there is a connection or server problem
	 */
	private function curl_exec($url, $checkreturn=true)
	{
		curl_setopt($this->curlh, CURLOPT_URL, $url);
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

?>
