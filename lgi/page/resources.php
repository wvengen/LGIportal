<?php
/**
 * Resource list page
 * @author Deepthi
 * @package default
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/dwoo.php');
require_once('inc/sessions.php');
require_once('inc/login.php');
require_once('inc/jobs.php');


$dwoo = new LGIDwoo();
$data = new Dwoo_Data();
$lgi  = new LGIPortalClient();

$resources = $lgi->resourceList();
$servers   = $lgi->serverList();

// sort resources by last seen
uasort($resources['resource'], create_function('$a,$b', 'return(((int)$b["last_call_time"]) - ((int)$a["last_call_time"]));'));

$data->assign('resources', $resources['resource']);
$data->assign('project_master_server', $servers['project_master_server']);
$data->assign('this_project_server', $servers['this_project_server']);
$data->assign('servers', $servers['project_server']);
$dwoo->output('resourcelist.tpl', $data);

?>