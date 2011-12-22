<?php
/**
 * Submit job page
 * @author wvengen
 * @package default
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/sessions.php');
require_once('inc/dwoo.php');
require_once('inc/jobs.php');


if(!isset($_POST['submit']))
{
	//display form
	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();

	// set nonce to avoid cross-site request forgery (see generateNonce)
	$data->assign('nonce', generateNonce());
	$data->assign('applications', config_array('LGI_APPLICATION', null));
	$data->assign(array(
	    'input' => @$_REQUEST['input'],
	    'application' => @$_REQUEST['application'],
	    'read_access' => @$_REQUEST['read_access'],
	    'write_access' => @$_REQUEST['write_access'],
	));
	$dwoo->output('submit.tpl', $data);
}
else
{
	// submit new job
	verifyNonce($_POST['nonce']);

	$lgi = new LGIPortalClient();

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
	http_redirect(config('LGI_APPROOT').'/job/'.urlencode($result['job']['job_id']));
}

?>