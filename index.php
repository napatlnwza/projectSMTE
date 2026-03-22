<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit();
}

include "connect.php";

$stmt=$conn->prepare("SELECT name, email, class, score FROM student WHERE id = ?");
$stmt->bind_param("s",$_SESSION['user_id']);
$stmt->execute();
$row=$stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
    integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
    integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        .container-map {
            display: flex;
            justify-content: center;
        }
        #map {
            width: 60%;
            height: 600px;
            
        }
    </style>
</head>
<body>
    <h1>ยินดีต้อนรับ 🌿</h1>
    <p>ชื่อ: <?php echo htmlspecialchars($row['name']); ?></p>
    <p>อีเมล: <?php echo htmlspecialchars($row['email']); ?></p>
    <p>ห้อง: <?php echo htmlspecialchars($row['class']); ?></p>
    <p>คะแนน: <?php echo htmlspecialchars($row['score']); ?></p>
    <div class="container-map">
        <div id="map"></div>
    </div>

 
    <a href="logout.php">ออกจากระบบ</a>

    <script>
        // L มีอยู่แล้วจาก script dead
        // 18 คือระดับซูมยิ่งมากจะซูมใกล้ขึ้น
        let map=L.map("map").setView([18.596181649640396, 98.87654703958457] , 18);
        // ใส่ภาพพื้นหลัง
        L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);


        const lat_long=[
            {lat:18.596181649640396, long:98.87654703958457 ,status:"พร้อมใช้งาน"},
            {lat:18.597427322078225, long:98.87693161673309 ,status:"กำลังปรับปรุง"},
            {lat:18.59638399573754, long:98.87426954360035 , status:"ไม่พร้อมใช้งาน"}
        ]
        // icon
        let trash=null;

        
        for (let i=0;i<lat_long.length;i++) {
            const point=lat_long[i];
            if (point.status === "พร้อมใช้งาน") {
                trash=L.icon({
                    iconUrl:"green.png",
                    iconSize:[30,40]
                })
            }
            else if (point.status === "ไม่พร้อมใช้งาน") {
                trash=L.icon({
                    iconUrl:"red.png",
                    iconSize:[30,40]
                })
            }
            if (point.status === "กำลังปรับปรุง") {
                trash=L.icon({
                    iconUrl:"yellow.png",
                    iconSize:[30,40]
                })
            }
            L.marker([point.lat , point.long] , { icon:trash}).addTo(map).bindPopup(point.status);
        }
    </script>
    
</body>
</html>