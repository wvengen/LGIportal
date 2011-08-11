<?php
/**
 * This is the page for viewing a job details.
 * @author Deepthi
 */

/**
 *
 */
require_once 'utilities/dwoo.php';
require_once 'utilities/sessions.php';
require_once 'utilities/login_utilities.php';
require_once 'utilities/jobs.php';
require_once 'utilities/data.php';

session_start();
//authenticate User. If user is not logged in, request for log in.
authenticateUser();

$dwoo = new LGIDwoo();
$data=createDwooData();
$output=listJobs(); //$output is an array containing details of all jobs

$data->assign('jobs',$output);
$dwoo->output('../dwoo/jobslist.tpl', $data);

