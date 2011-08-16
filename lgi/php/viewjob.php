<?php
/**
 * Job details page
 * @author Deepthi
 * @package default
 */

require_once 'utilities/dwoo.php';
require_once 'utilities/sessions.php';
require_once 'utilities/login_utilities.php';
require_once 'utilities/jobs.php';
require_once 'utilities/errors.php';

session_start();
//authenticate User. If user is not logged in, request for log in.
authenticateUser();

if(!isset($_REQUEST['jobid']))
{
	// no job id supplied, show form
	$dwoo = new LGIDwoo();
	$dwoo->output('viewjob.tpl');
}
else
{
	// job id supplied, show details
	$jobid = verifyJobid($_REQUEST['jobid'], 'viewjob.php');

	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();
	$output = viewJob($jobid);
	
	//Add more details to $output in viewJob() to get more details. Add them to $data and update jobdetails.tpl	
	$data->assign('jobId',$output['jobId']);
	$data->assign('jobStatus',$output['jobStatus']);
	$data->assign('application',$output['application']);
	$data->assign('target',$output['target']);
	$data->assign('jobOwner',$output['jobOwner']);
	$data->assign('readAccess',$output['readAccess']);
	
	$dwoo->output('jobdetails.tpl', $data);
}

?>
