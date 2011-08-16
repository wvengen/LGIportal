<?php
/**
 * Job related functions such as submission and deletion.
 *
 * @author Deepthi
 * @package utilities
 */

require_once dirname(__FILE__).'/../../lgi.config.php';
require_once dirname(__FILE__).'/../lgijob/jobmanage.php';
require_once dirname(__FILE__).'/../lgijob/errors.php';
require_once 'errors.php';
require_once 'login_utilities.php';


/**
 * Verify that job id is a valid expression, or return to page.
 *
 * @param string $jobid job id supplied by user
 * @param string $returnto page to return to in case of error
 * @return int jobid on success
 */
function verifyJobid($jobid, $returnto)
{
	if (preg_match('/^[0-9]+$/', $jobid))
		return (int)$jobid;
	else
		handleError('Invalid job id, must be an integer', $returnto);
}

/**
 * Function for submitting job. It takes parameters from POST variables and use the class Job for submitting job.
 * @return string output
 */
function submitJob()
{

	//Set authentication parameters
	global $CA_FILE;
	//check whether the user is authenticated
	authenticateUser();
	$user=$_SESSION['user'];
	$cert= getCertificateFile($user);
	$key= getKeyFile($user);
	if(empty($cert) || empty($key))
	{
		handleError("You do not have a valid certificate or key. Please contact System administrator","submit.php");
	}
	$group=$user;		//TODO : verify what to set for groups
	$CA=$CA_FILE;

	// Get inputs from user or defaults
	// TODO possibly warn if predefined things and they differ from input
	$application = defined('_LGI_APPLICATION_') ? _LGI_APPLICATION_ : $_POST['application'];
	$server = defined('_LGI_SERVER_') ? _LGI_SERVER_ : $_POST['server'];
	$project = defined('_LGI_PROJECT_') ? _LGI_PROJECT_ : $_POST['project'];
	$readaccess = $_POST['readaccess'];
	$writeaccess = $_POST['writeaccess'];
	$target = $_POST['target'];
	$jobspecifics = $_POST['jobspecifics'];

	//Create instance of Job
	$newjob=new Job($key,$cert,$CA,$user,$group);
	$newjob->setApplication($application);
	$newjob->setServer($server);
	$newjob->setProject($project);
	$newjob->setReadAccessList($readaccess);
	$newjob->setWriteAccessList($writeaccess);
	$newjob->setTargetResource($target);
	$newjob->setJobSpecifics($jobspecifics);

	//submit job
	$errorset=$newjob->submitJob();

	if($errorset)
	{
		handleError($newjob->getError(),"submit.php");	
	}
	else
	{
		$result=$newjob->getResults(); // Return an object of serverREsponse
		$jobs=$result->getJobs();
		$job=$jobs[0];
		$output=array();
		$output['jobId']=$job->getJobId();
		$output['jobStatus']=$job->getState();
		$output['application']=$job->getapplication();
		$output['target']=$job->getTargetResources();
		$output['jobOwner']= $job->getOwners();
		$output['readAccess']= $job->getReadAccess();
		return $output;
	}
}

/**
 * Function for deleting job. It takes parameters from POST variables and use the class Job for submitting job.
 * @return string output
 */
function deleteJob()
{
	//session_start();
	global $CA_FILE;
	authenticateUser();
	$user=$_SESSION['user'];
	$cert= getCertificateFile($user);
	$key= getKeyFile($user);
	if(empty($cert) || empty($key))
	{
		handleError("You do not have a valid certificate or key. Please contact your system administrator","delete.php");
	}
	$group=$user;
	$CA=$CA_FILE;


	$server = defined('_LGI_SERVER_') ? _LGI_SERVER_ : $_POST['server'];
	$project = defined('_LGI_PROJECT_') ? _LGI_PROJECT_ : $_POST['project'];
	$jobid = $_POST['jobid'];


	//create instance of Job
	$newjob=new Job($key,$cert,$CA,$user,$group);

	$newjob->setServer($server);
	$newjob->setProject($project);

	//delete job
	$errorset=$newjob->deleteJob($jobid);

	if($errorset)
	{
		handleError($newjob->getError(),"delete.php");
	}
	else
	{
		$output=$newjob->getResults();
		$jobs=$output->getJobs();
		$job=$jobs[0];
		return "Job ".$job->getJobId()." is ".$job->getState()." from server ".$output->getServer();
	}
}

/**
 * Function for viewing status of job. It takes parameters from POST variables and use the class Job for submitting job.
 * @return array output
 */
function viewJob($jobid)
{
	//session_start();
	global $CA_FILE;
	authenticateUser();
	$user=$_SESSION['user'];
	$cert= getCertificateFile($user);
	$key= getKeyFile($user);
	if(empty($cert) || empty($key))
	{
		handleError("You do not have a valid certificate or key. Please contact your system administrator","viewjob.php");
	}
	$group=$user;
	$CA=$CA_FILE;


	$server = defined('_LGI_SERVER_') ? _LGI_SERVER_ : $_POST['server'];
	$project = defined('_LGI_PROJECT_') ? _LGI_PROJECT_ : $_POST['project'];


	$newjob=new Job($key,$cert,$CA,$user,$group);

	$newjob->setServer($server);
	$newjob->setProject($project);

	//view status

	$errorset=$newjob->statusJob($jobid);


	if($errorset)
	{
		handleError($newjob->getError(),"viewjob.php");
	}
	else
	{
		$result=$newjob->getResults(); // Return an object of serverREsponse
		$jobs=$result->getJobs();
		$job=$jobs[0];
		$output=array();

		//Add here to give more details of jobs.
		$output['jobId']=$job->getJobId();
		$output['jobStatus']=$job->getState();
		$output['application']=$job->getapplication();
		$output['target']=$job->getTargetResources();
		$output['jobOwner']= $job->getOwners();
		$output['readAccess']= $job->getReadAccess();
		return $output;

	}
}

function listJobs()
{
	global $CA_FILE;
	authenticateUser();
	$user=$_SESSION['user'];
	$cert= getCertificateFile($user);
	$key= getKeyFile($user);

	if(empty($cert) || empty($key))
	{
		handleError("You do not have a valid certificate or key. Please contact your system administrator","listjobs.php");
	}
	$group=$user;
	$CA=$CA_FILE;


	$server = defined('_LGI_SERVER_') ? _LGI_SERVER_ : $_POST['server'];
	//$project = defined('_LGI_PROJECT_') ? _LGI_PROJECT_ : $_POST['project'];
	
	$newjob=new Job($key,$cert,$CA,$user,$group);

	$newjob->setServer($server);
	//$newjob->setProject($project);

	//get details of jobs
	$errorset=$newjob->listJobs();

	if($errorset)
	{
		handleError($newjob->getError(),"listjobs.php");
	}
	else
	{
		$result=$newjob->getResults(); // Return an object of ServerResponse
		$jobs=$result->getJobs();
		$output=array();
		$j=0;
		foreach($jobs as $i=>$value)
		{
			$output[$j]['jobId']=$jobs[$i]->getJobId();
			$output[$j]['jobStatus']=$jobs[$i]->getState();
			$output[$j]['application']=$jobs[$i]->getapplication();
			$output[$j]['target']=$jobs[$i]->getTargetResources();
			$output[$j]['jobOwner']= $jobs[$i]->getOwners();
			$output[$j]['readAccess']= $jobs[$i]->getReadAccess();
			$j=$j+1;
		}
		return $output;

	}

}

function listResources()
{
	global $CA_FILE;
	authenticateUser();
	$user=$_SESSION['user'];
	$cert= getCertificateFile($user);
	$key= getKeyFile($user);

	if(empty($cert) || empty($key))
	{
		handleError("You do not have a valid certificate or key. Please contact your system administrator","listjobs.php");
	}
	$group=$user;
	$CA=$CA_FILE;


	$server = defined('_LGI_SERVER_') ? _LGI_SERVER_ : $_POST['server'];
	//$project = defined('_LGI_PROJECT_') ? _LGI_PROJECT_ : $_POST['project'];
	//$jobid=$_POST['jobid'];

	$newjob=new Job($key,$cert,$CA,$user,$group);

	$newjob->setServer($server);
	//$newjob->setProject($project);

	//get details of all resources
	$errorset=$newjob->listResources();

	if($errorset)
	{
		handleError($newjob->getError(),"listresources.php");
	}
	else
	{
		$result=$newjob->getResults(); // Return an object of serverREsponse
		$resources=$result->getResources();
		$output=array();
		$j=0;
		foreach($resources as $i=>$value)
		{
			$output[$j]['name']=$resources[$i]->getResourceName();
			$output[$j]['capabilities']=$resources[$i]->getCapabilities();
			$output[$j]['lastcalltime']=$resources[$i]->getLastCallTime();
			$j=$j+1;
		}
		return $output;
	}


}

/**
 * Function to be called when an error encountered while requesting to the project server
 * @param LGIError|string $error the error
 * @param string $redirect url to which the page should be redirected after handling error
 */
function handleError($error,$redirect)
{
	if ($error instanceof LGIError)
		$error=$error->getErrorString();
	pushErrorMessage($error);
	header("Location: ".$redirect);
	die();
}
?>
