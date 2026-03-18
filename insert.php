<?php

session_start();

include "connect.php";

$id=$_POST['id'];
$password=$_POST['password'];

$parts=explode("/",$password);

if (count($parts) == 3) {
    $day = $parts[0];
    $month = $parts[1];
    $year = (int)$parts[2] - 543;
    $formatpassword = $day."/".$month."/".$year;

}
else {
    $formatpassword = $password;
}


$stmt = $conn->prepare("SELECT * FROM student WHERE email = ? AND password = ?");
$stmt->bind_param("ss", $id, $formatpassword);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 1) {
    header("Location: index.php");
    exit();
}
else {
    $stmt_admin = $conn->prepare("SELECT * FROM admin WHERE email = ? AND password = ?");
    $stmt_admin->bind_param("ss", $id, $password);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_admin->num_rows == 1) {
        header("Location: indexadmin.php");
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
}

?>