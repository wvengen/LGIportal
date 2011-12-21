<?php
/**
 * User logout page
 * @author Deepthi
 * @package default
 */
/** */
session_destroy();
header("Location: ../index.php");
?>

