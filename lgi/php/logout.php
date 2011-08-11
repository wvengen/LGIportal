
 <?php

require_once 'utilities/dwoo.php';

// Create the controller, it is reusable and can render multiple templates
$dwoo = new LGIDwoo(); 

   session_start();
   session_destroy();
   header("Location: ../index.php");
  ;
?>

