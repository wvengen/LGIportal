<?php
/**
 * LGI job management for the portal
 *
 * @author wvengen
 * @package utilities
 */

require_once(dirname(__FILE__).'/common.php');
require_once('utilities/login.php');
require_once('LGI/client.php');


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
 * @todo add job name to job info (joblist/jobinfo/submit)
 */
class LGIPortalClient extends LGIClient
{
	/**
	 * Create a new LGIPortalClient.
	 *
	 * @param string $user username to connect as, or null to get from portal login
	 * @param string $groups groups to submit jobs with, or null to use same as user
	 * @param string $server LGI project server url to connect with, or null to use {@link _LGI_SERVER_ _LGI_SERVER_} when defined
	 * @param string $project LGI project to connect to, or null to use {@link _LGI_PROJECT_ _LGI_PROJECT_} when defined
	 */
	function __construct($user=null, $groups=null, $server=null, $project=null)
	{
		global $CA_FILE;
		if ($user===null) {
			authenticateUser();
			$user = $_SESSION['user'];
		}
		if ($groups===null) $groups = $user;
		if ($server===null && defined('_LGI_SERVER_')) $server = _LGI_SERVER_;
		if ($project===null && defined('_LGI_PROJECT_')) $project = _LGI_PROJECT_;
		parent::__construct($server, $project, $user, $groups, getCertificateFile($user), getKeyFile($user), $CA_FILE);
	}
}

?>
