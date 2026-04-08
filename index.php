<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit();
}

include "connect.php";

$stmt = $conn->prepare("SELECT name, email, class, score FROM student WHERE id = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก — trash svk</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&family=Prompt:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <link rel="stylesheet" href="styleindex.css">
</head>
<body>

<!-- ── Header ── -->
<header>
    <div class="header-left">
        <span class="header-logo">🌿</span>
        <div>
            <div class="header-title">trash svk</div>
            <div class="header-sub">ระบบจัดการขยะโรงเรียน</div>
        </div>
    </div>
    <div class="header-right">
        <span class="student-badge">👤 <?= htmlspecialchars($row['name']) ?></span>
        <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
    </div>
</header>

<div class="container">

    <!-- ── Info Cards ── -->
    <div class="info-grid">
        <div class="info-card">
            <div class="label">👤 ชื่อ</div>
            <div class="value"><?= htmlspecialchars($row['name']) ?></div>
        </div>
        <div class="info-card">
            <div class="label">📧 อีเมล</div>
            <div class="value" style="font-size:1rem"><?= htmlspecialchars($row['email']) ?></div>
        </div>
        <div class="info-card">
            <div class="label">🏫 ห้อง</div>
            <div class="value"><?= htmlspecialchars($row['class']) ?></div>
        </div>
        <div class="info-card score">
            <div class="label">⭐ คะแนน</div>
            <div class="value"><?= htmlspecialchars($row['score']) ?></div>
        </div>
    </div>

    <!-- ── Quick Actions ── -->
    <div class="quick-actions">
        <a href="history.php" class="action-btn">📋 ประวัติการทำรายการ</a>
    </div>

    <!-- ── Map ── -->
    <div class="map-panel">
        <div class="map-header">
            <div class="map-title">แผนที่จุดทิ้งขยะ</div>
            <div class="map-legend">
                <span><span class="legend-dot" style="background:#4caf50"></span>พร้อมใช้งาน</span>
                <span><span class="legend-dot" style="background:#ffc107"></span>กำลังปรับปรุง</span>
                <span><span class="legend-dot" style="background:#f44336"></span>ไม่พร้อมใช้งาน</span>
            </div>
        </div>
        <div id="map"></div>
    </div>

</div>

<script>
    const map = L.map("map").setView([18.596181649640396, 98.87654703958457], 17);
    L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png").addTo(map);

    const lat_long = [
        { lat: 18.596181649640396, long: 98.87654703958457, status: "พร้อมใช้งาน",    name: "point A" },
        { lat: 18.597427322078225, long: 98.87693161673309, status: "กำลังปรับปรุง",  name: "point B" },
        { lat: 18.59638399573754,  long: 98.87426954360035, status: "ไม่พร้อมใช้งาน", name: "point C" }
    ];

    const iconMap = {
        "พร้อมใช้งาน":    "green.png",
        "กำลังปรับปรุง":  "yellow.png",
        "ไม่พร้อมใช้งาน": "red.png"
    };
    let trash=null;
    for (let i=0;i<lat_long.length;i++) {
        const point=lat_long[i];
        if (point.status === "พร้อมใช้งาน") {
            trash=L.icon({
                iconUrl:"green.png",
                iconSize:[30,40]
            });
        }
        else if (point.status === "กำลังปรับปรุง") {
            trash=L.icon({
                iconUrl:"yellow.png",
                iconSize:[30,40]
            });
        }
        else if (point.status === "ไม่พร้อมใช้งาน") {
            trash=L.icon({
                iconUrl:"red.png",
                iconSize:[30,40]
            });
        }
        L.marker([point.lat , point.long] ,{icon:trash}).addTo(map).bindPopup(`<b>${point.name} : </b><br>${point.status}`)
    }

    // lat_long.forEach(point => {
    //     const icon = L.icon({ iconUrl: iconMap[point.status], iconSize: [30, 40] });
    //     L.marker([point.lat, point.long], { icon }).addTo(map).bindPopup(`<b>${point.name} : </b><br>${point.status}`);
    // });
</script>

</body>
</html>