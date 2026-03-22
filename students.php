<?php
// ── ตรวจสอบ session ──
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// ── เชื่อมต่อ database ──
include "connect.php";

// ── รับค่าค้นหาและ filter ──
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all'; // all | pass | fail
$threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 0;

// ── ดึงข้อมูลนักเรียนทั้งหมด ──
$result   = $conn->query("SELECT id, name, score, class, email FROM student ORDER BY class, score DESC");
$students = $result->fetch_all(MYSQLI_ASSOC);
$total    = count($students);

// ── จัดการ POST (เพิ่ม/ลบ/แก้ไข) ──
include "actions.php";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายชื่อนักเรียนทั้งหมด</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&family=Prompt:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="students.css">
</head>
<body>

<!-- ── Header ── -->
<header>
    <h1>🌿 ADMIN DASHBOARD — trash svk</h1>
    <div style="display:flex;gap:12px;align-items:center;">
        <span class="admin-badge">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
        <a href="logout.php" class="logout-btn">ออกจากระบบ</a>
    </div>
</header>

<div class="container">

    <!-- ── Page Header ── -->
    <div class="page-header">
        <div class="page-title">📋 รายชื่อนักเรียนทั้งหมด</div>
        <a href="indexadmin.php?threshold=<?= $threshold ?>" class="back-btn">← กลับ Dashboard</a>
    </div>

    <!-- ── ฟอร์มเพิ่มนักเรียน ── -->
    <div class="panel" id="add" style="margin-bottom:20px">
        <div class="panel-title">เพิ่มนักเรียน</div>
        <form method="post" class="add-form">
            <input type="hidden" name="action" value="add">
            <input type="text"   name="new_id"       placeholder="รหัส (เช่น 29700)" required>
            <input type="text"   name="new_name"     placeholder="ชื่อ-นามสกุล" required>
            <input type="text"   name="new_class"    placeholder="ห้อง (เช่น 501)" required>
            <input type="text"   name="new_password" placeholder="รหัสผ่าน (วว/ดด/ปปปป)" required>
            <input type="number" name="new_score"    placeholder="คะแนน" required>
            <button type="submit" class="btn">+ เพิ่มนักเรียน</button>
        </form>
    </div>

    <!-- ── Toolbar: ค้นหา + filter ── -->
    <div class="toolbar">
        <input type="text" id="search" placeholder="🔍 ค้นหาชื่อ, รหัส หรือห้อง...">

        <div class="filter-group">
            <a href="?filter=all&threshold=<?= $threshold ?>"
               class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>">
                ทั้งหมด (<?= $total ?>)
            </a>
            <a href="?filter=pass&threshold=<?= $threshold ?>"
               class="filter-btn <?= $filter === 'pass' ? 'active' : '' ?>">
                ✅ ผ่าน
            </a>
            <a href="?filter=fail&threshold=<?= $threshold ?>"
               class="filter-btn <?= $filter === 'fail' ? 'active-fail' : '' ?>">
                ❌ ไม่ผ่าน
            </a>
        </div>

        <!-- ปรับเกณฑ์ผ่านแบบ inline -->
        <form method="get" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="filter" value="<?= $filter ?>">
            <input type="number" name="threshold" value="<?= $threshold ?>"
                   style="width:90px;background:#f1f8f1;border:1.5px solid var(--border);border-radius:10px;
                          color:var(--text);padding:9px 12px;font-family:'Sarabun',sans-serif;">
            <button type="submit" class="btn" style="padding:9px 16px;font-size:.85rem;">
                🌱 เกณฑ์
            </button>
        </form>

        <span class="count-label">แสดง <span id="showing"><?= $total ?></span> คน</span>
    </div>

    <!-- ── ตารางนักเรียน ── -->
    <div class="panel">
        <div class="table-wrap">
            <table id="studentTable" class="students-table">
                <thead>
                    <tr>
                        <th class="sort-th" onclick="sortTable('id')">รหัส <span class="sort-arrow">↕</span></th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>ห้อง</th>
                        <th class="sort-th" style="text-align:right" onclick="sortTable('score')">คะแนน <span class="sort-arrow">↕</span></th>
                        <th>อีเมล</th>
                        <th>สถานะ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($students as $s):
                    // filter pass/fail
                    if ($filter === 'pass' && $s['score'] < $threshold) continue;
                    if ($filter === 'fail' && $s['score'] >= $threshold) continue;
                ?>
                    <tr data-score="<?= $s['score'] ?>" data-id="<?= htmlspecialchars($s['id']) ?>">
                        <td style="color:var(--muted);font-weight:600"><?= htmlspecialchars($s['id']) ?></td>
                        <td><?= htmlspecialchars($s['name']) ?></td>
                        <td><?= htmlspecialchars($s['class']) ?></td>
                        <td class="<?= $s['score'] > 0 ? 'score-pos' : ($s['score'] < 0 ? 'score-neg' : 'score-zero') ?>">
                            <?= $s['score'] ?>
                        </td>
                        <td style="color:var(--muted);font-size:.85rem"><?= htmlspecialchars($s['email']) ?></td>
                        <td>
                            <?php if ($s['score'] >= $threshold): ?>
                                <span class="badge badge-pass">✅ ผ่าน</span>
                            <?php else: ?>
                                <span class="badge badge-fail">❌ ไม่ผ่าน</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <button type="button" class="edit-btn"
                                    onclick="openEdit(
                                        '<?= htmlspecialchars($s['id'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($s['score'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($s['class'], ENT_QUOTES) ?>'
                                    )">แก้ไข</button>
                                <form method="post" onsubmit="return confirm('ลบ <?= htmlspecialchars($s['name'], ENT_QUOTES) ?> ?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="del_id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="del-btn">ลบ</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ── Edit Modal ── -->
<div id="editModal" class="modal-overlay" onclick="closeEdit(event)">
    <div class="modal-box">
        <div class="modal-title">✏️ แก้ไขข้อมูลนักเรียน</div>
        <form method="post" class="modal-form">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="edit_id" id="edit_id">
            <label>รหัสนักเรียน</label>
            <input type="text" id="edit_id_show" disabled>
            <label>ชื่อ-นามสกุล</label>
            <input type="text" name="edit_name" id="edit_name" required>
            <label>ห้อง</label>
            <input type="text" name="edit_class" id="edit_class" required>
            <label>คะแนน</label>
            <input type="number" name="edit_score" id="edit_score" required>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">ยกเลิก</button>
                <button type="submit" class="btn">💾 บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script src="dashboard.js"></script>
<script src="sort.js"></script>

</body>
</html>