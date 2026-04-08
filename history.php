<?php
session_start();

// เช็ค session — เฉพาะ student เท่านั้น
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit();
}

include "connect.php";

// ดึงข้อมูลนักเรียน
$stmt = $conn->prepare("SELECT name, score FROM student WHERE id = ?");
$stmt->bind_param("s", $_SESSION['user_id']);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// ดึงประวัติทำรายการของนักเรียนคนนี้ เรียงจากใหม่ไปเก่า
$stmt2 = $conn->prepare("SELECT * FROM history WHERE students_id = ? ORDER BY data_time DESC");
$stmt2->bind_param("s", $_SESSION['user_id']);
$stmt2->execute();
$history = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการทำรายการ</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&family=Prompt:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="stylehistory.css">
</head>
<body>

<header>
    <h1>🌿 ประวัติการทำรายการ</h1>
    <a href="index.php" class="back-btn">← กลับหน้าหลัก</a>
</header>

<div class="container">

    <!-- ── Summary ── -->
    <div class="summary">
        <div class="summary-card">
            <div class="label">👤 นักเรียน</div>
            <div class="name-text"><?= htmlspecialchars($student['name']) ?></div>
        </div>
        <div class="summary-card">
            <div class="label">⭐ คะแนนปัจจุบัน</div>
            <div class="value"><?= $student['score'] ?> คะแนน</div>
        </div>
    </div>

    <!-- ── History Table ── -->
    <div class="panel">
        <div class="panel-title">รายการทั้งหมด</div>

        <?php if (empty($history)): ?>
            <div class="empty-state">
                <div class="icon">📋</div>
                <div>ยังไม่มีประวัติการทำรายการ</div>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>รายละเอียด</th>
                        <th>คะแนนที่ได้รับ</th>
                        <th>คะแนนคงเหลือ</th>
                        <th>วันเวลา</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($history as $i => $h): ?>
                    <tr>
                        <td style="color:var(--muted);font-weight:600"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($h['details']) ?></td>
                        <td>
                            <?php if ($h['points'] >= 0): ?>
                                <span class="badge-pos">+<?= $h['points'] ?></span>
                            <?php else: ?>
                                <span class="badge-neg"><?= $h['points'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="total-val"><?= $h['total'] ?></td>
                        <td class="date-text">
                            <?= date('d/m/Y H:i', strtotime($h['data_time'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

</body>
</html>