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


$dwoo = new LGIDwoo();
$data = new Dwoo_Data();
$lgi  = new LGIPortalClient();

$result = $lgi->jobList();
// sort by job id
uasort($result['job'], create_function('$a,$b', 'return(((int)$b["job_id"]) - ((int)$a["job_id"]));'));

$data->assign('jobs', $result['job']);
$data->assign('nonce', generateNonce()); // required for delete buttons
$dwoo->output('joblist.tpl', $data);

?>
