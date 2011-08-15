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
$output=listJobs(); //$output is an array containing details of all jobs

$data->assign('jobs',$output);
$dwoo->output('jobslist.tpl', $data);

