<?php
/**
 * This is the page for viewing all resources.
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
$output=listResources();
/*$data->assign('jobId',$output['jobId']);
$data->assign('jobStatus',$output['jobStatus']);
$data->assign('application',$output['application']);
$data->assign('target',$output['target']);
$data->assign('jobOwner',$output['jobOwner']);
$data->assign('readAccess',$output['readAccess']);*/
$data->assign('resources',$output);
$dwoo->output('../dwoo/resourcedetails.tpl', $data);

?>
