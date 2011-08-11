<?php 

/**
 * This is the page a user sees after logging in.
 * @author Deepthi 
 */
 
 /**
  *
  */
require_once 'utilities/dwoo.php';
require_once 'utilities/sessions.php';
require_once 'utilities/login_utilities.php';
require_once 'utilities/data.php';

	session_start();
	//authenticate User. If user is not logged in, request for log in.
	authenticateUser();
		
	//Display home page
	$dwoo = new LGIDwoo(); 
	$data=createDwooData();	
    $dwoo->output('../dwoo/home.tpl', $data);      
	

?>
