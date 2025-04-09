<?php 
session_start();

echo "Logout Successful";
session_unset();
session_destroy();
header("Location: index.php");
?>