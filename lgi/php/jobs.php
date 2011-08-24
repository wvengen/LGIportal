<?php
/**
 * Job details page
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

$dwoo = new LGIDwoo();
$data = new Dwoo_Data();
$lgi  = new LGIPortalClient();

$result = $lgi->jobList();
// sort by job id
uasort($result['job'], create_function('$a,$b', 'return(((int)$b["job_id"]) - ((int)$a["job_id"]));'));

$data->assign('jobs', $result['job']);
$data->assign('nonce', generateNonce()); // required for delete buttons
$dwoo->output('joblist.tpl', $data);

