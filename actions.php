<?php
// ── actions.php ──
// จัดการ POST requests ทั้งหมด: เพิ่ม / แก้ไข / ลบนักเรียน
// ถูก include จาก indexadmin.php (ต้องมี $conn และ $threshold อยู่แล้ว)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    if ($_POST['action'] === 'add') {
        // เพิ่มนักเรียนใหม่ — สร้าง email อัตโนมัติจาก id
        $auto_email = $_POST['new_id'] . '@svk.ac.th';
        $stmt = $conn->prepare("INSERT INTO student (id, name, password, score, class, email) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdss",
            $_POST['new_id'],
            $_POST['new_name'],
            $_POST['new_password'],
            $_POST['new_score'],
            $_POST['new_class'],
            $auto_email
        );
        $stmt->execute();

    } elseif ($_POST['action'] === 'delete') {
        // ลบนักเรียนตาม id
        $stmt = $conn->prepare("DELETE FROM student WHERE id = ?");
        $stmt->bind_param("s", $_POST['del_id']);
        $stmt->execute();

    } elseif ($_POST['action'] === 'edit') {
        // อัปเดตข้อมูลนักเรียน — สร้าง email อัตโนมัติจาก id
        $auto_email = $_POST['edit_id'] . '@svk.ac.th';
        $stmt = $conn->prepare("UPDATE student SET name=?, score=?, class=?, email=? WHERE id=?");
        $stmt->bind_param("sisss",
            $_POST['edit_name'],
            $_POST['edit_score'],
            $_POST['edit_class'],
            $auto_email,
            $_POST['edit_id']
        );
        $stmt->execute();
    }

    // reload หน้าเดิม พร้อม threshold เดิม
    header("Location: indexadmin.php?threshold=$threshold");
    exit();
}
?>