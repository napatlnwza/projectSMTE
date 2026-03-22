// ── Search real-time + อัปเดต counter ──
const searchInput = document.getElementById('search');
const showing     = document.getElementById('showing');

searchInput.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    let count = 0;
    document.querySelectorAll('#studentTable tbody tr').forEach(tr => {
        const match = tr.innerText.toLowerCase().includes(q);
        tr.style.display = match ? '' : 'none';
        if (match) count++;
    });
    showing.textContent = count;
});

// ── Sort: เรียงลำดับตาม column ──
// sortState เก็บ column ปัจจุบันและทิศทาง (asc/desc)
const sortState = { col: null, dir: 'asc' };

function sortTable(col) {
    const tbody = document.querySelector('#studentTable tbody');
    const rows  = Array.from(tbody.querySelectorAll('tr'));

    // สลับทิศทางถ้ากด column เดิม
    if (sortState.col === col) {
        sortState.dir = sortState.dir === 'asc' ? 'desc' : 'asc';
    } else {
        sortState.col = col;
        sortState.dir = 'asc';
    }

    // เรียงแถว
    rows.sort((a, b) => {
        let valA = a.dataset[col]; // data-id หรือ data-score
        let valB = b.dataset[col];

        // score เป็นตัวเลข, id เป็น string
        if (col === 'score') {
            valA = parseInt(valA);
            valB = parseInt(valB);
        }

        if (valA < valB) return sortState.dir === 'asc' ? -1 : 1;
        if (valA > valB) return sortState.dir === 'asc' ? 1 : -1;
        return 0;
    });

    // ใส่แถวกลับเข้า tbody ตามลำดับใหม่
    rows.forEach(tr => tbody.appendChild(tr));

    // อัปเดต arrow icon ใน header
    document.querySelectorAll('.sort-th').forEach(th => {
        th.classList.remove('asc', 'desc');
        th.querySelector('.sort-arrow').textContent = '↕';
    });
    const activeTh = col === 'id'
        ? document.querySelector('.sort-th:nth-child(1)')
        : document.querySelector('.sort-th:nth-child(4)');
    activeTh.classList.add(sortState.dir);
    activeTh.querySelector('.sort-arrow').textContent = sortState.dir === 'asc' ? '↑' : '↓';
}