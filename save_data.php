<?php
// 1. ตั้งค่าการเชื่อมต่อฐานข้อมูล (Host, Username, Password, Database Name)
$conn = new mysqli("localhost", "root", "", "trash");

// 2. ตรวจสอบว่าเชื่อมต่อกับ MySQL ได้หรือไม่
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. รับค่า 'val' ที่ Arduino ส่งมาผ่านวิธี POST
$value = $_POST['val'];

// 4. สร้างคำสั่ง SQL สำหรับเพิ่มข้อมูล (เปลี่ยน sensor_logs เป็นชื่อ Table ของคุณ)
$sql = "INSERT INTO sensor_logs (value, timestamp) VALUES ('$value', NOW())";

// 5. สั่งให้ฐานข้อมูลทำงานตามคำสั่ง SQL
if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// 6. ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>