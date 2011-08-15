<?php
/**
 * User logout page
 * @author Deepthi
 * @package default
 */

session_start();
session_destroy();
header("Location: ../index.php");
?>

