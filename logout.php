<?php 
$_SESSION['loggedIn'] = false;
$_SESSION['username'] = "";
header("Location: login.php");
exit();
?>