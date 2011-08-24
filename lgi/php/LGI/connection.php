<?php

/**
 * Base LGI connection
 *
 * @author wvengen
 * @package lgijob
 */

/** Base LGI exception */
class LGIException extends Exception { }
/** Exception in LGI connection */
class LGIConnectionException extends LGIException { }
/** Exception for LGI server error */
class LGIServerException extends LGIConnectionException { }

/** Base class for communicating with an LGI server.
 *
 * This takes care of authentication, posting, xml parsing and basic error handling.
 */
class LGIConnection
{
	private $curlh;

	protected $url;

	function __construct($url, $certificate, $privatekey, $ca_chain)
	{
		$this->curlh = null;
		$this->url = $url;
		$this->certificate = $certificate;
		$this->privatekey = $privatekey;
		$this->ca_chain = $ca_chain;
	}

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

	function close()
	{
		curl_close($this->curlh);
		$this->curlh = null;
	}

	protected function postToServer($apipath, $variables=array(), $files=array(), $path=null)
	{
		if (!$this->curlh) $this->connect();
		// be safe when settings variables
		$variables = array_map(create_function('$s', 'return ($s&&$s[0]=="@") ? "&#64;".substr($s,1) : $s;'), $variables);
		// file uploads as special postfields
		$files = array_map(create_function('$s', 'return is_array($s) ? "@".$s[0].";filename=".$s[1] : "@".$s;'), $files);
		// perform request
		curl_setopt($this->curlh, CURLOPT_URL, $this->url . $apipath);
		curl_setopt($this->curlh, CURLOPT_POST, true);
		curl_setopt($this->curlh, CURLOPT_POSTFIELDS, array_merge($variables, $files));
		$result = curl_exec($this->curlh);
		if (curl_errno($this->curlh) > 0) {
			throw new LGIConnectionException(sprintf('cURL error %d: %s',
				curl_errno($this->curlh), curl_error($this->curlh)), curl_errno($this->curlh));
		}
		if (($httpcode=curl_getinfo($this->curlh, CURLINFO_HTTP_CODE)) >= 400) {
			throw new LGIConnectionException(sprintf('Server error %d', $httpcode), $httpcode);
		}
		$resp = json_decode(json_encode(simplexml_load_string($result)), TRUE);
		// LGI adds spaces to each and every element *sigh*
		array_walk_recursive($resp, create_function('&$s', '$s=trim($s);'));
		// move @attributes to 'normal' members for easy access (ok for LGI)
		$resp = array_attributes_to_values($resp);
		// handle LGI error response
		if (array_key_exists('error', $resp['response'])) {
			$err = $resp['response']['error'];
			throw new LGIServerException(sprintf('LGI error %d: %s',
				$err['number'], $err['message']), $err['number']);
		}
		return $resp;
	}

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

	function setDebug($debug)
	{
		if (!$this->curlh) return;
		curl_setopt($this->curlh, CURLOPT_VERBOSE, $debug);
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
