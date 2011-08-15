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

session_start();
//authenticate User. If user is not logged in, request for log in.
authenticateUser();

if(!isset($_POST['submitrequest']))
{
	//display form
	$dwoo = new LGIDwoo();
	$dwoo->output('viewjob.tpl');
}
else //request for submit job.
{
	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();
	$output=viewJob();
	
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
