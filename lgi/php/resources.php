<?php
/**
 * Resource list page
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

$resources = $lgi->resourceList();
$servers   = $lgi->serverList();

$data->assign('resources', $resources['resource']);
$data->assign('project_master_server', $servers['project_master_server']);
$data->assign('this_project_server', $servers['this_project_server']);
$data->assign('servers', $servers['project_server']);
$dwoo->output('resourcelist.tpl', $data);

?>
