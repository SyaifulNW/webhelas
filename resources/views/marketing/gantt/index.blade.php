@extends('layouts.masteradmin')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- ========================================= --}}
{{--        HEADER PDF + GANTT EXPORT WRAP     --}}
{{-- ========================================= --}}



<div class="d-flex align-items-center mb-4 mt-4">
    <h2 class="mb-2">📊 Gantt Chart</h2>
</div>

<div class="d-flex gap-2 mb-3 flex-wrap">
    <button class="btn btn-sm btn-outline-primary" id="prevMonth">◀ Prev</button>
    <select id="monthSelect" class="form-select form-select-sm w-auto"></select>
    <select id="yearSelect" class="form-select form-select-sm w-auto"></select>
    <button class="btn btn-sm btn-outline-primary" id="nextMonth">Next ▶</button>

    <button class="btn btn-sm btn-outline-danger" id="todayBtn">📍 Hari Ini</button>

    &nbsp;

    <button class="btn btn-sm btn-success" id="exportPDF">📄 Export PDF</button>
</div>
 <br>

<div id="ganttExportArea">

    <!-- ======================= HEADER PDF ======================= -->
    <div id="pdfHeader" style="text-align:center; margin-bottom:20px;">
        <h2 style="margin:0; font-weight:700; color: #000;">
           Timeline Program Kerja {{ Auth::user()->name }} - {{ Auth::user()->role }}
        </h2>
        <div id="pdfMonthYear" style="font-size:14px; margin-top:4px; color: #000;"></div>

        <div style="margin-top:15px; display:flex; gap:20px; justify-content:center; font-size:13px;">
            <span>
                <span style="display:inline-block;width:18px;height:12px;background:#4ade80;border-radius:3px;"></span>
                Done
            </span>
            <span>
                <span style="display:inline-block;width:18px;height:12px;background:#fde68a;border-radius:3px;"></span>
                Progress
            </span>
            <span>
                <span style="display:inline-block;width:18px;height:12px;background:#fca5a5;border-radius:3px;"></span>
                Overdue
            </span>
        </div>
    </div>
    <!-- ======================= END HEADER ======================= -->

    <!-- ======================= GANTT TABLE ======================= -->
    <div class="table-responsive" id="ganttPrintOnly">
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

</div> <!-- END ganttExportArea -->


{{-- ======================= UI BIASA (TIDAK MASUK PDF) ======================= --}}



<style>
/* ——— Gaya Gantt Chart ——— */
body { font-family: 'Inter', sans-serif; font-size: 13px; }
.gantt-table { border-collapse: collapse; width: 100%; font-size: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden; table-layout: fixed; }
.gantt-table th, .gantt-table td { border: 2px solid #000; padding: 4px; text-align: center; vertical-align: middle; position: relative; height: 32px; min-width: 28px; overflow: visible; }
.gantt-table th { background: #2563eb; color: #fff; font-weight: 700; padding: 6px 4px; }

.legend-box { width: 20px; height: 14px; border-radius: 4px; display: inline-block; }

/* Bar */
.gantt-bar { position: absolute; top: 4px; left: 0; height: calc(100% - 8px); border-radius: 6px; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
.gantt-bar.green { background:#4ade80; }
.gantt-bar.yellow { background:#fde68a; }
.gantt-bar.red { background:#fca5a5; }
</style>


{{-- ======================= SCRIPT RENDER GANTT ======================= --}}
<script>
document.addEventListener('DOMContentLoaded', function(){

    const tasks = @json($ganttData);
    const monthNames = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];

    let current = new Date();

    const monthLabel = document.getElementById('monthLabel');
    const dayRow = document.getElementById('dayRow');
    const ganttBody = document.getElementById('ganttBody');

    function parseDateLocal(d){ return d ? new Date(d+"T00:00:00") : null; }

    function render(){

        const y = current.getFullYear();
        const m = current.getMonth();
        const daysInMonth = new Date(y, m + 1, 0).getDate();

        monthLabel.innerText = monthNames[m] + " " + y;

        dayRow.innerHTML = '<th colspan="4"></th>';
        for(let d=1; d<=daysInMonth; d++){
            const th = document.createElement('th');
            th.innerText = d;
            dayRow.appendChild(th);
        }

        const filtered = tasks.filter(t => {
            const s = parseDateLocal(t.start);
            const e = parseDateLocal(t.end);
            if(!s || !e) return false;

            return (
                (s.getMonth()==m && s.getFullYear()==y) ||
                (e.getMonth()==m && e.getFullYear()==y) ||
                (s < new Date(y,m+1,1) && e >= new Date(y,m,1))
            );
        });

        ganttBody.innerHTML = '';
        
        let lastProgram = null;
        let globalIndex = 0;

        filtered.forEach((t,i)=>{
            
            // Cek jika Program Berubah -> Print Header
            if (t.program !== lastProgram) {
                const headerTr = document.createElement('tr');
                headerTr.style.background = '#e7f1ff'; 
                
                headerTr.innerHTML = `
                    <td style="font-weight:bold;"></td>
                    <td colspan="3" style="font-weight:bold; color:#0d6efd; text-align:left; padding-left:10px;">
                        📂 ${t.program}
                    </td>
                `;
                // Isi sisa kolom hari dengan sel kosong (opsional, agar garis vertikal tetap ada/tidak)
                // Disini kita colspan saja agar bersih
                headerTr.innerHTML += `<td colspan="${daysInMonth}"></td>`;
                
                ganttBody.appendChild(headerTr);
                lastProgram = t.program;
            }

            globalIndex++; // Nomor urut lanjut terus atau reset? Biasanya lanjut terus atau reset per grup. Kita lanjut terus sesuai gambar (8)

            const tr = document.createElement('tr');

            const statusBtn =
                t.status==='done'
                ? `<span class="badge bg-success">✔ Done</span>`
                : `<button data-id="${t.id}" class="btn btn-primary btn-sm done-btn">Done</button>`;

            tr.innerHTML = `
                <td style="color:#000; font-weight:600;">${globalIndex}</td>
                <td class="text-start" style="color:#000; font-weight:600; padding-left: 20px;">${t.name}</td>
                <td style="color:#000; font-weight:600;">${t.pic || '-'}</td>
                <td style="color:#000; font-weight:600;">${statusBtn}</td>
            `;

            for(let d=1; d<=daysInMonth; d++) tr.innerHTML += `<td></td>`;
            ganttBody.appendChild(tr);

            const s = parseDateLocal(t.start);
            const e = parseDateLocal(t.end);
            if(!s || !e) return;

            const start = Math.max(1, s.getMonth()==m ? s.getDate() : 1);
            const end = Math.min(daysInMonth, e.getMonth()==m ? e.getDate() : daysInMonth);

            setTimeout(()=>{
                const cell = tr.children[start+3]; // +3 karena ada 4 kolom tetap (index 0,1,2,3) -> Start Date masuk ke kolom index 4 (tanggal 1)
                
                const bar = document.createElement('div');

                bar.className = "gantt-bar " + (
                    t.status=="done" ? "green" :
                    t.status=="progress" ? "yellow" : "red"
                );

                bar.style.width = `${(end-start+1) * (cell.offsetWidth||28)}px`;
                cell.appendChild(bar);
            },10);
        });

    }

    // Tombol DONE
    document.body.addEventListener("click", function(e){
        if(e.target.classList.contains("done-btn")){
            const id = e.target.dataset.id;

            e.target.outerHTML = `<span class="badge bg-success">✔ Done</span>`;

            const t = tasks.find(x => x.id == id);
            if(t) t.status = "done";

            render();

            fetch(`/gantt/inisiatif/${id}/done`, {
                method:"POST",
                headers:{
                    "X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content
                }
            });
        }
    });

    // Navigasi
    document.getElementById('prevMonth').onclick = ()=>{ current.setMonth(current.getMonth()-1); render(); };
    document.getElementById('nextMonth').onclick = ()=>{ current.setMonth(current.getMonth()+1); render(); };
    document.getElementById('todayBtn').onclick = ()=>{ current=new Date(); render(); };

    render();
});
</script>


{{-- ======================= SCRIPT EXPORT PDF ======================= --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>

<script>
document.getElementById("exportPDF").addEventListener("click", function () {

    const now = new Date();
    const y = now.getFullYear();
    const m = now.getMonth();

    const monthNames = [
        "Januari","Februari","Maret","April","Mei","Juni",
        "Juli","Agustus","September","Oktober","November","Desember"
    ];

    document.getElementById("pdfMonthYear").innerText = monthNames[m] + " " + y;

    const ganttArea = document.getElementById("ganttExportArea");

    html2pdf()
        .from(ganttArea)
        .set({
            margin: 10,
            filename: "GanttChart-" + monthNames[m] + "-" + y + ".pdf",
            html2canvas: { scale: 2 },
            jsPDF: { unit: "mm", format: "a4", orientation: "landscape" }
        })
        .save();
});
</script>


@endsection
