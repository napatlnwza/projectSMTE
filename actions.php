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
        // ดึงคะแนนเดิมก่อน เพื่อคำนวณผลต่าง
        $stmt_old = $conn->prepare("SELECT score FROM student WHERE id = ?");
        $stmt_old->bind_param("s", $_POST['edit_id']);
        $stmt_old->execute();
        $old = $stmt_old->get_result()->fetch_assoc();
        $old_score = (int)$old['score'];
        $new_score = (int)$_POST['edit_score'];
        $diff = $new_score - $old_score;

        // อัปเดตข้อมูลนักเรียน
        $auto_email = $_POST['edit_id'] . '@svk.ac.th';
        $stmt = $conn->prepare("UPDATE student SET name=?, score=?, class=?, email=? WHERE id=?");
        $stmt->bind_param("sisss",
            $_POST['edit_name'],
            $new_score,
            $_POST['edit_class'],
            $auto_email,
            $_POST['edit_id']
        );
        $stmt->execute();

        // บันทึกประวัติลง history (เฉพาะเมื่อคะแนนเปลี่ยน)  ของครู
        if ($diff !== 0) {
            $admin_name = $_SESSION['user_name'];
            $details = $diff > 0
                ? "$admin_name ปรับคะแนนเพิ่ม (+$diff)"
                : "$admin_name ปรับคะแนนลด ($diff)";
            $stmt_h = $conn->prepare("INSERT INTO history (students_id, details, points, total, data_time) VALUES (?, ?, ?, ?, NOW())");
            $stmt_h->bind_param("ssii",
                $_POST['edit_id'],
                $details,
                $diff,
                $new_score
            );
            $stmt_h->execute();
        }
        // เหลือของถังขยะ
        
    }

    // reload หน้าเดิม พร้อม threshold เดิม
    header("Location: indexadmin.php?threshold=$threshold");
    exit();
}
?>