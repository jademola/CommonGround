<?php 
include "sessions.php";

echo "Logout Successful";
session_unset();
session_destroy();
header("Location: index.php");
?>