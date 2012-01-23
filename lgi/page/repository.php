<?php
/**
 * Repository browse and download page
 * @author wvengen
 * @package default
 */
/** */
if (!defined('LGI_PORTAL')) throw new Exception('Page requested outside of portal');

require_once('inc/dwoo.php');
require_once('inc/jobs.php');


$lgi = new LGIPortalClient();

$url = urldecode($_REQUEST['url']);
if (isset($_REQUEST['file'])) {
	// download file
	$filename = urldecode($_REQUEST['file']);
	$fullurl = $url.'/'.$filename;
	// give proper filename for saveas function of browser
	header('Content-Disposition: inline;filename='.$filename);
	$lgi->filePassthru($fullurl);
} else {
	// list files
	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();

	$result = $lgi->fileList($url);
	$result['file'] = array_filter($result['file'], create_function('$s','return substr($s["name"],0,5)!=".LGI_";'));
	
	$data->assign('url', $url);
	$data->assign('repo_id', substr(strrchr($url,'/'),1));
	$data->assign('files', $result['file']);
	$dwoo->output('repolist.tpl', $data);
}

?>
