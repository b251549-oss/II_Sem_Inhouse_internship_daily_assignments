<?php session_start();
// session_destroy();
unset($_SESSION['admin']);
unset($_SESSION['is_loggedin']);
header('location:index.php');
exit;
