<?php
/**
 * User details and settings page
 * @author wvengen
 * @package default
 *
 * @todo in config download, use user-selected application instead of first one
 */

require_once(dirname(__FILE__).'/utilities/common.php');
require_once('utilities/dwoo.php');
require_once('utilities/sessions.php');
require_once('utilities/login.php');
require_once('utilities/jobs.php');

session_start();
//authenticate User. If user is not logged in, request for log in.
authenticateUser();

$dwoo = new LGIDwoo();
$data = new Dwoo_Data();

// download config when submitted via dlcred
if (isset($_POST['dlcred']) && $_POST['dlcred']) {
	$applications = preg_split('/;\s*/', _LGI_APPLICATION_);
	$data->assign('certificate', trim(file_get_contents(getCertificateFile())));
	$data->assign('privatekey', trim(file_get_contents(getKeyFile())));
	$data->assign('ca_chain', trim(file_get_contents($CA_FILE)));
	$data->assign('groups', $_SESSION['user']);
	$data->assign('application', $applications[0]);
	$cfgtxt = $dwoo->get('LGI.cfg.tpl', $data);

	header('Content-Type: text/xml');
	header('Content-Disposition: attachment; filename=LGI.cfg');
	print($cfgtxt);

	exit(0);
}

// form submitted
if (isset($_POST['submit'])) {
	// change password
	if ($_POST['pwdold'] || $_POST['pwd1'] || $_POST['pwd2']) {
		if ($_POST['pwd1'] != $_POST['pwd2']) {
			pushErrorMessage('New passwords do not match.');
		} elseif (strlen($_POST['pwd1']) < 3) {
			pushErrorMessage('Password must be longer than 3 characters');
		} elseif (!verifyUserPassword($_SESSION['user'], $_POST['pwd_old'])) {
			pushErrorMessage('Old password is incorrect.');
		} else {
			// TODO change password
			setUserPassword($_SESSION['user'], $_POST['pwd1']);
			$data->assign('infomessage', 'Password changed.');
		}
	}
}

// no form, just show form
$data->assign('nonce', generateNonce());
$data->assign('servers', preg_split('/;\s*/', _LGI_SERVER_));
$data->assign('projects', preg_split('/;\s*/', _LGI_PROJECT_));
$dwoo->output('user.tpl', $data);

