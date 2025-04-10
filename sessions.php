<?php
session_start();

if (!isset($_SESSION['loggedIn'])) {
    $_SESSION['loggedIn'] = false;
}

if (!isset($_SESSION['userType'])) {
    $_SESSION['userType'] = '';
}

if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = '';
}
?>