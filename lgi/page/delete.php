<?php
/**
 * Delete job page
 * @author Deepthi
 * @package default
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/sessions.php');
require_once('inc/dwoo.php');
require_once('inc/jobs.php');


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
    $job_ids = array();
    foreach (explode(',', $_POST['job_id']) as $job_id)
        $job_ids[] = verifyJobid($job_id, 'jobs');

	$lgi = new LGIPortalClient();
    foreach($job_ids as $job_id)
        $result = $lgi->jobDelete($job_id);

    if (count($job_ids)==1 && !in_array($result['job']['state'], array('deleted', 'aborting', 'aborted')))
		throw new LGIPortalException('Failed to delete jobs '.$job_ids);

	// success! redirect to allow user to reload
	http_redirect(config('LGI_APPROOT').'/jobs');
}

?>
