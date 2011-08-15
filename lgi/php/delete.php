<?php
/**
 * Delete job page
 * @author Deepthi
 * @package default
 */

require_once 'utilities/dwoo.php';
require_once 'utilities/sessions.php';
require_once 'utilities/login_utilities.php';
require_once 'utilities/jobs.php';
require_once 'utilities/errors.php';

session_start();
//authenticate User. If user is not logged in, request for log in.
authenticateUser();

//if request does not have details about job, display the form . post variable 'submitrequest' is set in the form.
if(!isset($_POST['submitrequest']))
{
	//display form
	$dwoo = new LGIDwoo();
	$data = new Dwoo_Data();
	
	//To prevent cross site request forgery attack - set a nonce in the form and session. Verify the nonce before deleting the job.
	$nonce=uniqid(rand(), true); 
	$data->assign("nonce",$nonce);
	$_SESSION["deletenonce"]=$nonce;
	$dwoo->output('delete.tpl', $data);
}
else //request for delete job.
{
	$dwoo = new Dwoo();
	$data = new Dwoo_Data();
	//Verify the nonce from POST fields before deleting the job (Test the following code)
	$nonce=$_POST["nonce"];
	if(strcmp($_SESSION["deletenonce"],$nonce)==0)
	{
		unset($_SESSION["deletenonce"]);
		$output=deleteJob();	
		$data->assign('message',$output);
		$dwoo->output('deletesuccess.tpl', $data);
	}
	else
	{
		header("Location: delete.php");
	}

}

?>
