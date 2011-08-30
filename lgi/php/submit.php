<?php
/**
 * Submit job page
 * @author wvengen
 * @package default
 */
/** */
require_once(dirname(__FILE__).'/utilities/common.php');
require_once('utilities/dwoo.php');
require_once('utilities/sessions.php');
require_once('utilities/login.php');
require_once('utilities/jobs.php');


session_start();
authenticateUser();

if(!isset($_POST['submit']))
{
	//display form
	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();

	// set nonce to avoid cross-site request forgery (see generateNonce)
	$data->assign('nonce', generateNonce());
	$data->assign('applications', config_array('LGI_APPLICATION', null));
	$dwoo->output('submit.tpl', $data);
}
else
{
	// submit new job
	verifyNonce($_POST['nonce']);

	$lgi  = new LGIPortalClient();

	$application = $_POST['application'];
	$allowed_apps = config_array('LGI_APPLICATION', null);
	if (!is_null($allowed_apps) && !in_array($application, $allowed_apps))
		throw new LGIPortalException('Application not allowed: '.htmlentities($application));
	$input = $_POST['input'];
	$read_access = $_POST['read_access'];
	$write_access = $_POST['write_access'];
	$files = array();
	foreach ($_FILES as $n=>$f) {
		if (!substr($n,0,13)=='uploaded_file') continue;
		if (strlen($f['name'])==0) continue;
		$files[$f['name']] = $f['tmp_name'];
	}

	$result = $lgi->jobSubmit($application, $input, 'any', $write_access, $read_access, $files);

	// success! redirect to allow user to reload
	header('Location: viewjob.php?job_id='.urlencode($result['job']['job_id']));
}

?>
