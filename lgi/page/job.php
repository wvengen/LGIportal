<?php
/**
 * Job details page
 * @author wvengen
 * @package default
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/sessions.php');
require_once('inc/dwoo.php');
require_once('inc/jobs.php');


if (count($argv)!=2)
    throw new LGIPortalException("No job id specified.");

// job id supplied, show details
$job_id = verifyJobid($argv[1], 'jobs');

$dwoo = new LGIDwoo();
$data = new Dwoo_Data();
$lgi  = new LGIPortalClient();

$result = $lgi->jobState($job_id);

$data->assign('job_id', $job_id);
$data->assign('job', $result['job']);
$data->assign('nonce', generateNonce()); // for abort/delete button
$dwoo->output('jobdetails.tpl', $data);

?>