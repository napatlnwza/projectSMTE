<?php
$servername="localhost";
$user="root";
$pass="";
$db="trash";

$conn = mysqli_connect($servername,$user,$pass,$db);

if (!$conn) {
    die("Connect Failed : ".mysqli_connect_error());
}

?>