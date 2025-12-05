@extends('layouts.masteradmin')

@section('content')


<div class="d-flex align-items-center mb-4">
    <h2 class="mb-2">📊 Gantt Chart</h2>

</div>

<div class="d-flex gap-2 mb-3 flex-wrap">
    <button class="btn btn-sm btn-outline-primary" id="prevMonth">◀ Prev</button>
    <select id="monthSelect" class="form-select form-select-sm w-auto"></select>
    <select id="yearSelect" class="form-select form-select-sm w-auto"></select>
    <button class="btn btn-sm btn-outline-primary" id="nextMonth">Next ▶</button>
    <button class="btn btn-sm btn-outline-danger" id="todayBtn">📍 Hari Ini</button>
</div>

<div class="table-responsive">
    <table class="gantt-table w-100" id="ganttTable">
        <thead>
            <tr>
                <th style="width:40px">No</th>
                <th style="width:200px">Inisiatif</th>
                <th style="width:120px">PIC</th>
                <th style="width:80px">Status</th>
<th colspan="31" id="monthLabel"></th>


            </tr>
            <tr id="dayRow"><th colspan="4"></th></tr>
        </thead>
        <tbody id="ganttBody"></tbody>
    </table>
</div>

<div class="legend mt-3">
    <span><span class="legend-box" style="background: linear-gradient(135deg, #4ade80, #16a34a)"></span> Done</span>
    <span><span class="legend-box" style="background: linear-gradient(135deg, #fde68a, #facc15)"></span> Progress</span>
    <span><span class="legend-box" style="background: linear-gradient(135deg, #fca5a5, #dc2626)"></span> Overdue</span>
</div>

<style>
body { font-family: 'Inter', sans-serif; font-size: 13px; }
.gantt-table { border-collapse: collapse; width: 100%; font-size: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden; table-layout: fixed; }
.gantt-table th, .gantt-table td { border: 2px solid #000; padding: 4px; text-align: center; vertical-align: middle; position: relative; height: 32px; min-width: 28px; overflow: visible; }
.gantt-table th { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; font-weight: 700; text-shadow: 0 1px 2px rgba(0,0,0,0.5); padding: 6px 4px; border-bottom: 2px solid #1e40af; }
.gantt-table th.date-header { background: linear-gradient(135deg, #60a5fa, #3b82f6); color: #fff; font-weight: 600; text-shadow: 0 1px 1px rgba(0,0,0,0.2); border-bottom: 1px solid #2563eb; }
.gantt-table th.today { background: #2563eb !important; color: #fff !important; font-weight: bold; border-left: 2px solid #1e40af; border-right: 2px solid #1e40af; }

.gantt-bar { position: absolute; top: 4px; left: 0; height: calc(100% - 8px); border-radius: 6px; cursor: pointer; transition: transform 0.2s; box-shadow: 0 2px 6px rgba(0,0,0,0.15); z-index: 5; }
.gantt-bar.done { background: linear-gradient(135deg, #4ade80, #16a34a); }
.gantt-bar.progress { background: linear-gradient(135deg, #fde68a, #facc15); }
.gantt-bar.overdue { background: linear-gradient(135deg, #fca5a5, #dc2626); }

.legend { display: flex; gap: 15px; font-size: 13px; flex-wrap: wrap; }
.legend span { display: flex; align-items: center; gap: 6px; }
.legend-box { width: 20px; height: 14px; border-radius: 4px; display: inline-block; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
.gantt-table td.today-col { background-color: rgba(37, 99, 235, 0.15); box-shadow: 0 2px 6px rgba(0,0,0,0.15) inset; border-left: 2px solid #2563eb; border-right: 2px solid #2563eb; z-index: 1; }
.done-btn { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; font-weight: 600; border: none; border-radius: 6px; padding: 4px 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); cursor: pointer; transition: transform 0.2s, box-shadow 0.2s; }
.done-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.25); }
.done-btn:disabled { cursor: not-allowed; opacity: 0.6; }
</style>


<style>
    .gantt-bar {
    height: 18px;
    border-radius: 4px;
    position: absolute;
    left: 0;
    top: 3px;
}

.gantt-bar.green { background: #28a745; }   /* Done */
.gantt-bar.yellow { background: #ffc107; }  /* Progress */
.gantt-bar.red { background: #dc3545; }     /* Overdue */

</style>


<script>
document.addEventListener('DOMContentLoaded', function(){

    const tasks = @json($ganttData);
    const monthNames = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];

    let current = new Date();

    const monthLabel = document.getElementById('monthLabel');
    const dayRow = document.getElementById('dayRow');
    const ganttBody = document.getElementById('ganttBody');
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');

    function parseDateLocal(dateStr){ 
        return dateStr ? new Date(dateStr+"T00:00:00") : null; 
    }

    // ====================== RENDER ======================
    function render(){

        const y = current.getFullYear();
        const m = current.getMonth();
        const daysInMonth = new Date(y, m + 1, 0).getDate();

        monthSelect.value = m;
        yearSelect.value = y;
        monthLabel.innerText = monthNames[m] + " " + y;

        // ----------- HEADER HARI --------------
        dayRow.innerHTML = '<th colspan="4"></th>';

        for(let d = 1; d <= daysInMonth; d++){
            const th = document.createElement('th');
            th.innerText = d;
            th.classList.add('date-header');

            const today = new Date();
            if(d === today.getDate() && m === today.getMonth() && y === today.getFullYear()){
                th.classList.add('today');
            }
            dayRow.appendChild(th);
        }

        // ----------- FILTER TASK --------------
        const filteredTasks = tasks.filter(t => {
            const start = parseDateLocal(t.start);
            const end = parseDateLocal(t.end);
            if(!start || !end) return false;

            return (
                (start.getFullYear() === y && start.getMonth() === m) ||
                (end.getFullYear() === y && end.getMonth() === m) ||
                (start < new Date(y, m+1, 1) && end >= new Date(y, m, 1))
            );
        });

        ganttBody.innerHTML = '';

        // ----------- RENDER TIAP ROW --------------
        filteredTasks.forEach((t, i) => {

            const tr = document.createElement('tr');

            // Tombol / status
  const statusCell = t.status === 'done'
    ? `<span class="badge bg-success">✔ Done</span>`
    : `
        <form action="/gantt/inisiatif/${t.id}/done" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-primary">Done</button>
        </form>
    `;


            tr.innerHTML = `
                <td>${i + 1}</td>
                <td class="text-start">${t.name}</td>
                <td>${t.pic || '-'}</td>
                <td>${statusCell}</td>
            `;

            for(let d = 1; d <= daysInMonth; d++){
                tr.innerHTML += `<td></td>`;
            }

            ganttBody.appendChild(tr);

            // ====== Gambar bar ======
            const startDate = parseDateLocal(t.start);
            const endDate = parseDateLocal(t.end);

            if(startDate && endDate){

                const start = Math.max(1, startDate.getMonth() === m ? startDate.getDate() : 1);
                const end = Math.min(daysInMonth, endDate.getMonth() === m ? endDate.getDate() : daysInMonth);

                setTimeout(() => {

                    const startCell = tr.children[start + 3];
                    if(startCell){

                        // HAPUS BAR LAMA
                        startCell.innerHTML = "";

                        const bar = document.createElement('div');

                        let status = (t.status || 'progress').toLowerCase();
                        if(status === "done") bar.className = "gantt-bar green";
                        else if(status === "progress") bar.className = "gantt-bar yellow";
                        else bar.className = "gantt-bar red";

                        const cellWidth = startCell.offsetWidth || 28;
                        bar.style.width = `${(end - start + 1) * cellWidth}px`;

                        startCell.style.position = "relative";
                        startCell.appendChild(bar);
                    }

                }, 10);
            }

        });

    } // END RENDER



    // =======================================================
    //  EVENT TOMBOL DONE **DILUAR** RENDER (FIX PENTING!)
    // =======================================================

document.body.addEventListener('click', function(e){

    if(e.target.classList.contains('done-btn')){

        const id = e.target.dataset.id; // FIX
        const el = e.target;

        // ================================
        // 1. Ubah UI langsung
        // ================================
        el.outerHTML = `<span class="badge bg-success text-white">✓ Done</span>`;

        // Update data lokal
        const task = tasks.find(x => x.id == id); // FIX
        if(task){
            task.status = "done";
    
        }

        render(); // refresh bar hijau

        // ================================
        // 2. Kirim ke server
        // ================================
fetch(`/gantt/inisiatif/${id}/done`, {
  method: "POST",
  headers: {
    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
    "Accept": "application/json"
  }
})
.then(async res => {
    const text = await res.text();
    console.log("SERVER RESPONSE:", text);
})
.catch(err => console.error("Request gagal:", err));


    }

});




    // ================= NAVIGATION =================
    document.getElementById('prevMonth').onclick = () => { current.setMonth(current.getMonth() - 1); render(); };
    document.getElementById('nextMonth').onclick = () => { current.setMonth(current.getMonth() + 1); render(); };
    document.getElementById('todayBtn').onclick    = () => { current = new Date(); render(); };
    monthSelect.onchange = () => { current.setMonth(parseInt(monthSelect.value)); render(); };
    yearSelect.onchange  = () => { current.setFullYear(parseInt(yearSelect.value)); render(); };

    render();

});
</script>


@endsection

<script src="assets/demo/chart-area-demo.js"></script>
<script src="assets/demo/chart-pie-demo.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>