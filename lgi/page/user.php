<?php
/**
 * User details and settings page
 * @author wvengen
 * @package default
 *
 * @todo in config download, use user-selected application instead of first one
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/dwoo.php');
require_once('inc/sessions.php');
require_once('inc/user.php');
require_once('inc/jobs.php');


$dwoo = new LGIDwoo();
$data = new Dwoo_Data();
$user = new LGIUser();

// download config when submitted via dlcred
if (isset($_POST['dlcred']) && $_POST['dlcred']) {
	$applications = config_array('LGI_APPLICATION');
	$data->assign('certificate', trim(file_get_contents($user->get_cert())));
	$data->assign('privatekey', trim(file_get_contents($user->get_key())));
	$data->assign('ca_chain', trim(file_get_contents(config('LGI_CA_FILE'))));
	$data->assign('groups', $user->get_cur_group());
	$data->assign('application', $applications[0]);
	// TODO allow to select application and/or groups
	$cfgtxt = $dwoo->get('LGI.cfg.tpl', $data);

	header('Content-Type: text/xml');
	header('Content-Disposition: attachment; filename=LGI.cfg');
	print($cfgtxt);

	exit(0);
}

// password form submitted
if (isset($_POST['submit_pwd'])) {
	// change password
	if ($_POST['pwd_old'] || $_POST['pwd1'] || $_POST['pwd2']) {
		if ($_POST['pwd1'] != $_POST['pwd2']) {
			pushErrorMessage('New passwords do not match.');
		} elseif (strlen($_POST['pwd1']) < 3) {
			pushErrorMessage('Password must be longer than 3 characters');
		} elseif (!verifyUserPassword($_SESSION['user'], $_POST['pwd_old'])) {
			pushErrorMessage('Old password is incorrect.');
		} else {
			$user->password_update($_POST['pwd1']);
			$data->assign('infomessage', 'Password changed.');
		}
	}
}

// defaults form submitted
if (isset($_POST['submit_dfl'])) {
	// update defaults
	if (isset($_POST['project'])) $user->set_cur_project(trim($_POST['project']));
	if (isset($_POST['groups'])) $user->set_groups(array_map('trim', explode(',', $_POST['groups'])));
	if (isset($_POST['group'])) $user->set_cur_group(trim($_POST['group']));
	// TODO move json-detection to another place
	if (!isset($_POST['json'])) $data->assign('infomessage', 'Defaults saved.');
}

// no form, just show form
$data->assign('nonce', generateNonce());
$data->assign('servers', config_array('LGI_SERVER'));
$dwoo->output('user.tpl', $data);

?>
