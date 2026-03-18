<?php
include "connect.php";

$sql="SELECT * FROM student";
$result=mysqli_query($conn,$sql);
while ($row=mysqli_fetch_assoc($result)) {
    echo $row['name'].'<br>';

    }
?>