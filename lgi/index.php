<?php
/**
 * Entry page, redirecting to either login or home
 * @author Deepthi 
 * @package pages
 */

require_once(dirname(__FILE__).'/php/utilities/common.php');
require_once('utilities/dwoo.php');
require_once('utilities/sessions.php');

session_start();
if(checkValidSession()) //if already logged in redirect it to home
{	
	header("Location: php/home.php");
}
else
{
         //check which authentication mechanism
          if(strcmp(_AUTH_MECHANISM_,"DATABASE")==0)
          {
               $dwoo = new LGIDwoo();
               $dwoo->output('login.tpl');
          }
          else if(strcmp(_AUTH_MECHANISM_,"DIGEST")==0)
          {
               //authenticateDigest()
               header("Location: php/login.php");
          }
}
	
?>
