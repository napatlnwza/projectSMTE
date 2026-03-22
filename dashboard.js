// ── initChart: สร้างกราฟโดนัทแสดงสัดส่วนผ่าน/ไม่ผ่าน ──
// รับค่า pass และ fail จาก PHP ผ่าน CHART_DATA
function initChart(pass, fail) {
    new Chart(document.getElementById('donut').getContext('2d'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [pass, fail],
                backgroundColor: ['#388e3c', '#e57373'], // เขียว = ผ่าน, แดง = ไม่ผ่าน
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            cutout: '72%', // ความหนาของวงโดนัท
            plugins: {
                legend: { display: false }, // ซ่อน legend ของ Chart.js (ใช้ legend ที่ทำเองแทน)
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.raw} คน` // format tooltip
                    }
                }
            },
            animation: { duration: 800 } // animation 0.8 วินาทีตอนโหลด
        }
    });
}

// ── openEdit: เปิด modal พร้อมข้อมูลนักเรียนที่เลือก ──
// ถูกเรียกจากปุ่ม "แก้ไข" ในแต่ละแถว
function openEdit(id, name, score, cls) {
    document.getElementById('edit_id').value      = id;
    document.getElementById('edit_id_show').value = id;
    document.getElementById('edit_name').value    = name;
    document.getElementById('edit_score').value   = score;
    document.getElementById('edit_class').value   = cls;
    document.getElementById('editModal').classList.add('active');
}

// ── closeModal: ปิด modal ──
function closeModal() {
    document.getElementById('editModal').classList.remove('active');
}

// ── closeEdit: ปิด modal เมื่อคลิก overlay ด้านนอก ──
// เช็คว่า target คือ overlay เอง ไม่ใช่ modal-box ข้างใน
function closeEdit(e) {
    if (e.target === document.getElementById('editModal')) closeModal();
}