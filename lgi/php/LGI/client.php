<?php

/**
 * LGI client
 *
 * @author wvengen
 * @package lgijob
 */
require_once(dirname(__FILE__).'/connection.php');

class LGIClientException extends LGIException { }

/** LGI client
 */
class LGIClient extends LGIConnection
{
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
		if (array_key_exists('input', $ret['job'])) $ret['job']['input'] = pack('H*', $ret['job']['input']);
		if (array_key_exists('output', $ret['job'])) $ret['job']['output'] = pack('H*', $ret['job']['output']);
		return $ret;
	}

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
		if ($input!==null) $args['input'] = unpack('H*', $input);
		$fileargs = array();
		foreach ($files as $f) $fileargs['uploaded_file_'.(count($fileargs)+1)] = $f;
		$ret = $this->postToServer('/interfaces/interface_submit_job.php', $args, $fileargs);
		return $ret['response'];
	}

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
