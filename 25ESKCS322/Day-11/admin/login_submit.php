<?php session_start();
 include '../common/db_connect.php';

$email = $_POST['email'];
$password = $_POST['password'];

$query = "select * from admin where email = '$email' and password = '$password'";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

if (count($row) !== 0) {
    $_SESSION['admin'] = $row;
    $_SESSION['is_loggedin'] = true;
    header('location:dashboard.php');
    exit;
}else{    
    header('location:index.php');
    exit;
}
