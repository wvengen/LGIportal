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
if(!isset($_POST['job_id']))
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
	verifyNonce($_POST['nonce']);
	$job_id = verifyJobid($_POST['job_id']);

	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();

	$lgi = new LGIPortalClient();
	$result = $lgi->jobDelete($job_id);
	$data->assign('message', 'Job '.$job_id.' deleted');
	$dwoo->output('deletesuccess.tpl', $data);
}

?>
