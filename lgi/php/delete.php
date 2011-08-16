<?php
/**
 * Delete job page
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

//if request does not have details about job, display the form . post variable 'submitrequest' is set in the form.
if(!isset($_POST['jobid']))
{
	//display form
	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();
	
	// set nonce to avoid cross-site request forgery (see generateNonce)
	$data->assign('nonce', generateNonce());
	$dwoo->output('delete.tpl', $data);
}
else
{
	// delete job
	$jobid = verifyJobid($_POST['jobid'], 'viewjob.php');

	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();

	//Verify the nonce from POST fields before deleting the job (Test the following code)
	if (verifyNonce($_POST['nonce']))
	{
		$output = deleteJob();
		$data->assign('message',$output);
		$dwoo->output('deletesuccess.tpl', $data);
	}
	else
	{
		handleError('Suspected cross-site request forgery attack', 'delete.php');
	}
}

?>
