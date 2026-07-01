<?php
// api_bottle.php
// รับ POST จาก Python → บวกคะแนน + บันทึก history
// วางไฟล์นี้ไว้โฟลเดอร์เดียวกับ index.php

include "connect.php";

// รับค่าจาก Python
$student_id  = $_POST['student_id']  ?? '';
$point_name  = $_POST['point_name']  ?? 'ไม่ระบุจุด';
$bottles     = (int)($_POST['bottles'] ?? 3);
$score_add   = (int)($_POST['score_add'] ?? 1);  // +1 คะแนน ต่อ 3 ขวด

if (empty($student_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ไม่มี student_id']);
    exit();
}

// ดึงคะแนนปัจจุบัน
$stmt = $conn->prepare("SELECT score FROM student WHERE id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'ไม่พบนักเรียน']);
    exit();
}

$old_score = (int)$row['score'];
$new_score = $old_score + $score_add;

// อัปเดตคะแนน
$stmt2 = $conn->prepare("UPDATE student SET score = ? WHERE id = ?");
$stmt2->bind_param("is", $new_score, $student_id);
$stmt2->execute();

// บันทึก history — details แสดงจุดทิ้ง + จำนวนขวด
$details = "ทิ้งขวดน้ำ {$bottles} ขวด ที่ {$point_name}";
$stmt3 = $conn->prepare(
    "INSERT INTO history (students_id, details, points, total, data_time) VALUES (?, ?, ?, ?, NOW())"
);
$stmt3->bind_param("ssii", $student_id, $details, $score_add, $new_score);
$stmt3->execute();

echo json_encode([
    'success'   => true,
    'message'   => 'บันทึกสำเร็จ',
    'old_score' => $old_score,
    'new_score' => $new_score,
    'details'   => $details
]);
?>