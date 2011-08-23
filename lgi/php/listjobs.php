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

$dwoo = new LGIDwoo();
$data = new Dwoo_Data();
$lgi  = new LGIPortalClient();

$result = $lgi->jobList();

$data->assign('jobs', $result['job']);
$data->assign('nonce', generateNonce()); // required for delete buttons
$dwoo->output('jobslist.tpl', $data);

