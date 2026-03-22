<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include "connect.php";

$threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 0;

$result   = $conn->query("SELECT id, name, score, class, email FROM student ORDER BY class, score DESC");
$students = $result->fetch_all(MYSQLI_ASSOC);

$pass  = array_filter($students, fn($s) => $s['score'] >= $threshold);
$fail  = array_filter($students, fn($s) => $s['score'] < $threshold);
$total = count($students);

include "actions.php";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600;700&family=Prompt:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* ── Layout หลัก ── */
        .dashboard-layout {
            display: grid;
            grid-template-columns: 500px 1fr;
            gap: 32px;
            align-items: start;
        }

        /* ── Card Grid ฝั่งขวา 2x2 ── */
        .card-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        /* ── Menu Card ── */
        .menu-card {
            background: var(--card);
            border: 1.5px solid var(--border);
            border-radius: 20px;
            padding: 36px;
            text-decoration: none;
            display: block;
            box-shadow: 0 4px 18px var(--shadow);
            transition: transform .2s, box-shadow .2s, border-color .2s;
            cursor: pointer;
        }
        .menu-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 28px var(--shadow);
            border-color: var(--accent2);
        }
        .menu-card .card-title {
            font-family: 'Prompt', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 12px;
        }
        .menu-card .card-desc {
            font-size: 1rem;
            color: var(--muted);
            line-height: 1.6;
        }
        .menu-card .card-stat {
            margin-top: 20px;
            font-family: 'Prompt', sans-serif;
            font-size: 2.8rem;
            font-weight: 700;
        }
        .menu-card.total .card-stat { color: var(--accent); }
        .menu-card.pass  .card-stat { color: var(--pass); }
        .menu-card.fail  .card-stat { color: var(--fail); }
        .menu-card.settings .card-stat { color: var(--accent2); }
    </style>
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
    <div class="dashboard-layout">

        <!-- ── ซ้าย: กราฟอย่างเดียว ── -->
        <div class="left-panel">
            <div class="panel">
                <div class="panel-title">สถิติคะแนน</div>
                <div class="chart-wrap">
                    <canvas id="donut" width="260" height="260"></canvas>
                    <div class="chart-center">
                        <div class="big"><?= $total ?></div>
                        <div class="small">คนทั้งหมด</div>
                    </div>
                </div>
                <div class="legend">
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#388e3c"></div>
                        <span>ผ่านเกณฑ์ — <?= count($pass) ?> คน (<?= $total ? round(count($pass)/$total*100,1) : 0 ?>%)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-dot" style="background:#e57373"></div>
                        <span>ไม่ผ่านเกณฑ์ — <?= count($fail) ?> คน (<?= $total ? round(count($fail)/$total*100,1) : 0 ?>%)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── ขวา: Card Grid 2x2 ── -->
        <div class="card-grid">

            <!-- นักเรียนทั้งหมด -->
            <a href="students.php?threshold=<?= $threshold ?>&filter=all" class="menu-card total">
                <div class="card-title">🌍 ข้อมูลนักเรียน</div>
                <div class="card-desc">ดู แก้ไข เพิ่ม หรือลบข้อมูลนักเรียน</div>
                <div class="card-stat"><?= $total ?> คน</div>
            </a>

            <!-- ผ่านเกณฑ์ -->
            <a href="students.php?threshold=<?= $threshold ?>&filter=pass" class="menu-card pass">
                <div class="card-title">✅ นักเรียนผ่านเกณฑ์</div>
                <div class="card-desc">ตรวจสอบนักเรียนที่คะแนน ≥ <?= $threshold ?> คะแนน</div>
                <div class="card-stat"><?= count($pass) ?> คน</div>
            </a>

            <!-- ไม่ผ่านเกณฑ์ -->
            <a href="students.php?threshold=<?= $threshold ?>&filter=fail" class="menu-card fail">
                <div class="card-title">❌ นักเรียนไม่ผ่านเกณฑ์</div>
                <div class="card-desc">ตรวจสอบนักเรียนที่คะแนนต่ำกว่าเกณฑ์</div>
                <div class="card-stat"><?= count($fail) ?> คน</div>
            </a>

            <!-- ปรับเกณฑ์ผ่าน -->
            <div class="menu-card settings">
                <div class="card-title">🌱 ปรับเกณฑ์ผ่าน</div>
                <div class="card-desc">คะแนนขั้นต่ำที่ผ่านเกณฑ์ปัจจุบัน: <?= $threshold ?> คะแนน</div>
                <form method="get" style="display:flex;gap:8px;margin-top:14px;">
                    <input type="number" name="threshold" value="<?= $threshold ?>"
                        style="flex:1;background:#f1f8f1;border:1.5px solid var(--border);border-radius:10px;
                               color:var(--text);padding:9px 12px;font-family:'Sarabun',sans-serif;font-size:.95rem;">
                    <button type="submit" class="btn" style="padding:9px 16px;white-space:nowrap;">
                        อัปเดต
                    </button>
                </form>
            </div>

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

<script>
    const CHART_DATA = {
        pass: <?= count($pass) ?>,
        fail: <?= count($fail) ?>
    };
</script>
<script src="dashboard.js"></script>
<script>
    initChart(CHART_DATA.pass, CHART_DATA.fail);
</script>

</body>
</html>