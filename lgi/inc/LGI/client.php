<?php
/**
 * LGI client
 *
 * @author wvengen
 * @package LGI
 */
/** */
require_once(dirname(__FILE__).'/connection.php');


/** Exception in LGI client
 * @package LGI */
class LGIClientException extends LGIException { }

/** LGI client
 *
 * This is a thin layer to the LGI project server's 'application interface'
 * as described in the "Leiden Grid Infrastructure" design document. The
 * API calls return a nested array based on the XML response. Only the
 * contents of the 'response' tag is returned. Binhex-ed fields are decoded
 * first.
 *
 * Configuration is either supplied to the constructor, or read from the
 * user's default configuration in ~/.LGI otherwise.
 *
 * An example that lists the id and status of all jobs:
 * <code>
 * require_once('LGI/client.php');
 * $client = new LGIClient();
 * $response = $client->jobList();
 * foreach ($response['job']  as $job) {
 *   print "job #$job[job_id]: status $job[state]\n";
 * }
 * </code>
 *
 * @package LGI
 */
class LGIClient extends LGIConnection
{
	/** LGI project name to work with
	 * @var string */
	protected $project;
	/** LGI username
	 * @var string */
	protected $user;
	/** LGI groups, used when submitting new jobs
	 * @var string */
	protected $groups;

	/** Create new client
	 *
	 * All parameters are optional. When they are either null or not
	 * supplied, defaults are read from the standard LGI user configuration
	 * files in ~/.LGI (or %USERPROFILE%/.LGI on Windows).
	 *
	 * @param string $url LGI project server url
	 * @param string $project LGI project on server
	 * @param string $user LGI username
	 * @param string $groups LGI groups
	 * @param string $certificate user certificate filename
	 * @param string $privatekey user private key filename
	 * @param string $ca_chain LGI CA chain filename to validate project server with
	 * @throws LGIException when there is a configuration error
	 */
	function __construct($url=null, $project=null, $user=null, $groups=null, $certificate=null, $privatekey=null, $ca_chain=null)
	{
		// defaults from configuration in ~/.LGI
		$cfgdir = ((strtolower(substr(PHP_OS,0,3))=='win') ? getenv('USERPROFILE'): getenv('HOME')) .
			DIRECTORY_SEPARATOR . '.LGI' . DIRECTORY_SEPARATOR;
		if ($user===null)
			$user = trim(@file_get_contents($cfgdir.'user'));
		if ($groups===null)
			$groups = trim(@file_get_contents($cfgdir.'groups'));
		if ($url===null)
			$url = trim(@file_get_contents($cfgdir.'defaultserver'));
		if ($project===null)
			$project = trim(@file_get_contents($cfgdir.'defaultproject'));
		if ($certificate===null)
			$certificate = $cfgdir.'certificate';
		if ($privatekey===null)
			$privatekey = $cfgdir.'privatekey';
		if ($ca_chain===null)
			$ca_chain = $cfgdir.'ca_chain';

		if (strlen($user)==0)
			throw new LGIException("No LGI user specified");
		if (strlen($groups)==0)
			throw new LGIException("No LGI groups specified");
		if (strlen($project)==0)
			throw new LGIException("No LGI project specified");

		$this->project = $project;
		$this->user = $user;
		$this->groups = $groups;

		parent::__construct($url, $certificate, $privatekey, $ca_chain);
	}

	/** Return list of jobs
	 *
	 * @param string $application filter application
	 * @param string $state filter job state
	 * @param int $start start listing at index
	 * @param int $limit limit number of jobs returned
	 * @return array job information
	 * @throws LGIConnectionException when there is a connection or server problem
	 * @throws LGIServerException when the project server returns an error response
	 */
	function jobList($application=null, $state=null, $start=null, $limit=null)
	{
		$args = array(
			'project' => $this->project,
			'user' => $this->user,
			'groups' => $this->groups,
		);
		if ($application!==null) $args['application'] = $application;
		if ($state!==null) $args['state'] = $state;
		if ($start!==null) $args['start'] = $start;
		if ($limit!==null) $args['limit'] = $limit;
		$ret = $this->postToServer('/interfaces/interface_job_state.php', $args);
		$ret = $ret['response'];
		if (!array_key_exists('job', $ret)) $ret['job'] = array();
		elseif (!is_array($ret['job'][0])) $ret['job'] = array($ret['job']);
		return $ret;
	}

	/** Return job state
	 *
	 * @param int $job_id job number to query
	 * @return array job information
	 * @throws LGIConnectionException when there is a connection or server problem
	 * @throws LGIServerException when the project server returns an error response
	 */
	function jobState($job_id)
	{
		$args = array(
			'project' => $this->project,
			'user' => $this->user,
			'groups' => $this->groups,
		);
		if ($job_id!==null) $args['job_id'] = $job_id;
		$ret = $this->postToServer('/interfaces/interface_job_state.php', $args);
		$ret = $ret['response'];
		if (!array_key_exists('job', $ret)) $ret['job'] = array();
		# hex decode input and output
		if (array_key_exists('input', $ret['job'])) {
		  // would higher php versions produce array instead of string when single value?
		  // at some point $ret['job']['(in|out)put'] started to be an array instead of a string 
		  if (is_array($ret['job']['input'])) $ret['job']['input'] = implode(' ',$ret['job']['input']);
		  $ret['job']['input'] = pack('H*', $ret['job']['input']);
		}
		if (array_key_exists('output', $ret['job'])) {
		  if (is_array($ret['job']['output'])) $ret['job']['output'] = implode(' ',$ret['job']['output']);
		  $ret['job']['output'] = pack('H*', $ret['job']['output']);
		}
		return $ret;
	}

	/** Abort or delete job
	 *
	 * @param int $job_id job id to delete
	 * @return array job information
	 * @throws LGIConnectionException when there is a connection or server problem
	 * @throws LGIServerException when the project server returns an error response
	 */
	function jobDelete($job_id)
	{
		$args = array(
			'project' => $this->project,
			'user' => $this->user,
			'groups' => $this->groups,
		);
		if ($job_id!==null) $args['job_id'] = $job_id;
		$ret = $this->postToServer('/interfaces/interface_delete_job.php', $args);
		return $ret['response'];
	}

	/** Submit new job
	 *
	 * @param string $application application name to submit to
	 * @param string $target_resources valid resources, or 'any'
	 * @param string $write_access comma-separated list of users/groups
	 *          to give additional write access to
	 * @param string $read_access comma-separated list of users/groups
	 *          to give additional read access to
	 * @param string $files files to upload, with keys as destination filenames
	 *          and values as absolute paths on local filesystem.
	 * @return array job information
	 * @throws LGIConnectionException when there is a connection or server problem
	 * @throws LGIServerException when the project server returns an error response
	 */
	function jobSubmit($application, $input=null, $target_resources='any', $write_access=null, $read_access=null, $files=array())
	{
		$args = array(
			'project' => $this->project,
			'user' => $this->user,
			'groups' => $this->groups,
			'number_of_uploaded_files' => count($files),
		);
		if ($application!==null) $args['application'] = $application;
		if ($target_resources!==null) $args['target_resources'] = $target_resources;
		if ($write_access!==null) $args['write_access'] = $write_access;
		if ($read_access!==null) $args['read_access'] = $read_access;
		if ($input!==null) $args['input'] = implode(unpack('H*', $input));
		$fileargs = array();
		foreach ($files as $n=>$f) {
			if (is_string($n)) $f = array($f, $n);
			$fileargs['uploaded_file_'.(count($fileargs)+1)] = $f;
		}
		$ret = $this->postToServer('/interfaces/interface_submit_job.php', $args, $fileargs);
		$ret = $ret['response'];
		# hex decode input and output
		if (array_key_exists('input', $ret['job'])) $ret['job']['input'] = pack('H*', $ret['job']['input']);
		if (array_key_exists('output', $ret['job'])) $ret['job']['output'] = pack('H*', $ret['job']['output']);
		return $ret;
	}

	/** Return list of known resources
	 *
	 * @return array known resources
	 * @throws LGIConnectionException when there is a connection or server problem
	 * @throws LGIServerException when the project server returns an error response
	 */
	function resourceList()
	{
		$args = array(
			'project' => $this->project,
			'user' => $this->user,
			'groups' => $this->groups,
		);
		$ret = $this->postToServer('/interfaces/interface_project_resource_list.php', $args);
		$ret = $ret['response'];
		if (!array_key_exists('resource', $ret)) $ret['resource'] = array();
		elseif (!is_array($ret['resource'][0])) $ret['resource'] = array(0=>$ret['resource']);
		return $ret;
	}

	/** Return list of known project servers
	 *
	 * @return array known project servers
	 * @throws LGIConnectionException when there is a connection or server problem
	 * @throws LGIServerException when the project server returns an error response
	 */
	function serverList()
	{
		$args = array(
			'project' => $this->project,
			'user' => $this->user,
			'groups' => $this->groups,
		);
		$ret = $this->postToServer('/interfaces/interface_project_server_list.php', $args);
		$ret = $ret['response'];
		if (!array_key_exists('project_server', $ret)) $ret['project_server'] = array();
		elseif (!is_array($ret['project_server'][0])) $ret['project_server'] = array(0=>$ret['project_server']);
		return $ret;
	}
}

?>
