<?php
/**
 * Job details page
 * @author Deepthi
 * @package default
 */
/** */
require_once(dirname(__FILE__).'/utilities/common.php');
require_once('utilities/dwoo.php');
require_once('utilities/sessions.php');
require_once('utilities/login.php');
require_once('utilities/jobs.php');


session_start();
//authenticate User. If user is not logged in, request for log in.
authenticateUser();

if(!isset($_REQUEST['job_id']))
{
	// no job id supplied, show form
	$dwoo = new LGIDwoo();
	$dwoo->output('viewjob.tpl');
}
else
{
	// job id supplied, show details
	$job_id = verifyJobid($_REQUEST['job_id'], 'viewjob.php');

	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();
	$lgi  = new LGIPortalClient();

	$result = $lgi->jobState($job_id);

	$data->assign('job_id', $job_id);
	$data->assign('job', $result['job']);
	$data->assign('nonce', generateNonce()); // for abort/delete button
	$dwoo->output('jobdetails.tpl', $data);
}

?>
