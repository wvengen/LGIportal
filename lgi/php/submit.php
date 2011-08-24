<?php
/**
 * Submit job page
 * @author Deepthi
 * @package default
 */

require_once(dirname(__FILE__).'/utilities/common.php');
require_once('utilities/dwoo.php');
require_once('utilities/sessions.php');
require_once('utilities/login.php');
require_once('utilities/jobs.php');

session_start();
//authenticate User. If user is not logged in, request for log in.
authenticateUser();

if(!isset($_POST['submit']))
{
	//display form
	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();

	// set nonce to avoid cross-site request forgery (see generateNonce)
	$data->assign('nonce', generateNonce());
	$data->assign('applications', array(_LGI_APPLICATION_));
	$dwoo->output('submit.tpl', $data);
}
else
{
	// submit new job
	verifyNonce($_POST['nonce']);

	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();
	$lgi  = new LGIPortalClient();

	$application = $_POST['application'];
	if (defined('_LGI_APPLICATION_') && $application!=_LGI_APPLICATION_)
		throw new LGIPortalException('Application not allowed: '.htmlentities($application));
	$input = $_POST['input'];
	$read_access = $_POST['read_access'];
	$write_access = $_POST['write_access'];
	$files = array();
	foreach ($_FILES as $n=>$f) {
		if (!substr($n,0,13)=='uploaded_file') continue;
		$files[$f['name']] = $f['tmp_name'];
	}

	$result = $lgi->jobSubmit($application, $input, 'any', $write_access, $read_access, $files);

	$data->assign('job', $result['job']);
	$data->assign('job_id', $result['job']['job_id']);
	$data->assign('nonce', generateNonce()); // for delete/abort button
	$dwoo->output('jobdetails.tpl', $data);
}

?>
