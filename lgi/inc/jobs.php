<?php
/**
 * LGI job management for the portal
 *
 * @author wvengen
 * @package utilities
 */
/** */
require_once(dirname(__FILE__).'/common.php');
require_once('inc/login.php');
require_once('inc/LGI/client.php');


/**
 * Verify that job id is a valid expression, or return to page.
 *
 * @param string $jobid job id supplied by user
 * @param string $returnto page to return to in case of error
 * @throws LGIPortalException when job id is not a valid integer
 * @return int jobid on success
 */
function verifyJobid($jobid, $returnto)
{
	if (preg_match('/^[0-9]+$/', $jobid))
		return (int)$jobid;
	else
		throw new LGIPortalException('Invalid job id, must be an integer', $returnto);
}

/**
 * LGIClient that integrates with the portal
 *
 * By default the LGI project server details are used from the LGIportal
 * configuration, and the key and certificate are retrieved from the
 * database.
 *
 * Also repository access is checked against LGI_REPOSITORY configuration.
 *
 * @todo add job name to job info (joblist/jobinfo/submit)
 * @package utilities
 */
class LGIPortalClient extends LGIClient
{
	/**
	 * Create a new LGIPortalClient.
	 *
	 * @param string $user username to connect as, or null to get from portal login
	 * @param string $groups groups to submit jobs with, or null to use same as user
	 * @param string $server LGI project server url to connect with, or null to use {@link config config}('LGI_SERVER') when defined
	 * @param string $project LGI project to connect to, or null to use {@link config config}('LGI_PROJECT') when defined
	 */
	function __construct($user=null, $groups=null, $server=null, $project=null)
	{
		if ($user===null) {
			authenticateUser();
			$user = $_SESSION['user'];
		}
		if ($groups===null) $groups = $user;
		if ($server===null) $server = config('LGI_SERVER');
		if ($project===null) $project = config('LGI_PROJECT');
		$ca = config('LGI_CA_FILE');
		parent::__construct($server, $project, $user, $groups, getCertificateFile($user), getKeyFile($user), $ca);
	}

	/** {@inheritdoc} */
	function filePassthru($url)
	{
		$this->check_repository($url);
		return parent::filePassthru($url);
	}

	/** {@inheritdoc} */
	function fileDownload($url)
	{
		$this->check_repository($url);
		return parent::fileDownload($url);
	}

	/** {@inheritdoc} */
	function fileList($url)
	{
		$this->check_repository($url);
		return parent::fileList($url);
	}

	/** Checks if a repository url is allowed.
	 *
 	 * If the repository url given is not allowed by the portal configuration
	 * an LGIPortalException is thrown.
	 *
	 * @param string $url repository or file location to check
	 * @throws LGIPortalException when location is not allowed
	 */
	protected function check_repository($url)
	{
		$allowed_repos = config_array('LGI_REPOSITORY');
		foreach ($allowed_repos as $repo)
		{
			if (substr($url, 0, strlen($repo))==$repo)
				return true;
		}
		throw new LGIPortalException('Repository not allowed');
	}

}

?>