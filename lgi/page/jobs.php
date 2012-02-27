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
$jobs = &$result['job'];
// sort by job id
usort($jobs, create_function('$a,$b', 'return(((int)$b["job_id"]) - ((int)$a["job_id"]));'));

// add dummy job lines as parent job placeholder for nested jobs
$parentsdone = array();
for ($i=0; $i<count($jobs); $i++) {
    $job = &$jobs[$i];
    // for first job with parent, create parent job placeholder
    $parent = @$job['job_specifics']['parent'];
    if (!$parent) continue;
    if (!array_key_exists($parent, $parentsdone)) {
        $newjob = $job;
        $newjob['isparent'] = true;
        unset($newjob['target_resources']); // not too useful for placeholder
        array_splice($jobs, $i, 0, array($newjob));
        $parentsdone[$parent] = array($i, array()); // index, list of child ids
        $i++;
    }
    $parentjob = &$jobs[$parentsdone[$parent][0]];
    $parentsdone[$parent][1][] = $job['job_id'];
    // number of children is available at real parent job, get it from there
    if (array_key_exists('nchildren', $job['job_specifics'])) {
        // plus one because the parent job counts as a child as well in the interface
        $parentjob['job_specifics']['nchildren'] = intval($job['job_specifics']['nchildren'])+1;
    }
    // last child gets special flag
    if (array_key_exists('nchildren', $parentjob['job_specifics']) &&
            intval($parentjob['job_specifics']['nchildren']) == count($parentsdone[$parent][1])) {
        $job['child_bottom'] = 1;
        // and now we have the last child, set children of parent job
        $parentjob['job_id'] = implode(',', $parentsdone[$parent][1]);
    }
    // title is that part of the title that is shared by all children
    if (array_key_exists('title', $job['job_specifics'])) {
        $parentjob['job_specifics']['title'] = str_prefix(
            $parentjob['job_specifics']['title'],
            $job['job_specifics']['title']);
    }
    // status is lowest of all children
    // TODO
}

$data->assign('jobs', $jobs);
$data->assign('nonce', generateNonce()); // required for delete buttons
$dwoo->output('joblist.tpl', $data);

?>
