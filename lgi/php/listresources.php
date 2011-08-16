<?php
/**
 * Resource list page
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
$output=listResources();

$data->assign('resources', $output);
$dwoo->output('resourcedetails.tpl', $data);

?>
