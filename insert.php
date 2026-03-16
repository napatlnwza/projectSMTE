<?php


include "connect.php";

$id=$_POST['id'];
$password=$_POST['birthday'];

$parts=explode("/",$password);

$day=$parts[0];
$mount=$parts[1];
$year=$parts[2]-543;
$mysql_date=$year."-".$mount."-".$day;

$sql = "SELECT * FROM student 
        WHERE id='$id' 
        AND birthday='$mysql_date'";

$result=mysqli_query($conn,$sql);
if (mysqli_num_rows($result) == 1) {
    header("Location: index.php");
}
else {
    header("Location: login.php?error=1");
}

?>