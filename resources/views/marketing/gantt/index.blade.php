@extends('layouts.masteradmin')

@section('content')

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- ========================================= --}}
    {{-- HEADER PDF + GANTT EXPORT WRAP --}}
    {{-- ========================================= --}}
    @php
        $userRole = strtolower(Auth::user()->role);
        $userName = Auth::user()->name;
        $isFelmi = stripos($userName, 'Felmi') !== false;
        $isMonitoringChapter = ($viewRole === 'chapter');
        $isReadOnly = ($userRole === 'administrator') || $isFelmi;
        
        $currentMonth = request('month', date('n'));
        $currentYear = request('year', date('Y'));
    @endphp

    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-2">
                @if($isMonitoringChapter)
                    <span class="text-primary">(Monitoring Chapter - Read Only)</span>
                @elseif($isReadOnly)
                    <span class="text-secondary">(ReadOnly)</span>
                @endif
                
                @if(isset($targetUser) && $targetUser->id !== Auth::id())
                    - <span class="text-info" style="font-size: 0.85em;">{{ strtoupper($targetUser->name) }}{{ $targetUser->chapter ? ' - '.strtoupper($targetUser->chapter) : '' }}</span>
                @endif
            </h2>
        </div>
    </div>

    @if(($userRole === 'administrator' || $userRole === 'admin') && $isMonitoringChapter)
        <div class="card shadow-lg mb-5 border-0" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header py-3 bg-gradient-primary text-white d-flex align-items-center" style="background: linear-gradient(90deg, #1e3a8a, #2563eb);">
                <h6 class="m-0 font-weight-bold" style="font-size: 1.1rem;"><i class="fas fa-filter me-2"></i> Filter Monitoring & Periode</h6>
            </div>
            <div class="card-body bg-light">
                <form action="{{ route('gantt.index') }}" method="GET" class="row align-items-center">
                    <input type="hidden" name="view_role" value="{{ $viewRole }}">
                    
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label class="d-block text-dark text-uppercase mb-2" style="font-weight: 800; letter-spacing: 0.5px; font-size: 0.85rem;">Pilih Chapter:</label>
                        <select name="user_id" class="form-select select2-custom shadow-sm" style="border-radius: 8px; border: 1px solid #d1d3e2; height: 42px; font-weight: 600;">
                            <option value="">-- Semua Chapter --</option>
                            @foreach($chapters as $chap)
                                <option value="{{ $chap->id }}" {{ request('user_id') == $chap->id ? 'selected' : '' }}>
                                    {{ $chap->name }}{{ $chap->chapter ? ' - '.$chap->chapter : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-3 mb-3">
                        <label class="d-block text-dark text-uppercase mb-2" style="font-weight: 800; letter-spacing: 0.5px; font-size: 0.85rem;">Bulan:</label>
                        <select name="month" class="form-select shadow-sm" style="border-radius: 8px; border: 1px solid #d1d3e2; height: 42px; font-weight: 600;">
                            @for($i=1; $i<=12; $i++)
                                <option value="{{ $i }}" {{ $currentMonth == $i ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-3 mb-3">
                        <label class="d-block text-dark text-uppercase mb-2" style="font-weight: 800; letter-spacing: 0.5px; font-size: 0.85rem;">Tahun:</label>
                        <select name="year" class="form-select shadow-sm" style="border-radius: 8px; border: 1px solid #d1d3e2; height: 42px; font-weight: 600;">
                            @for($y=date('Y')-1; $y<=date('Y')+1; $y++)
                                <option value="{{ $y }}" {{ $currentYear == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-lg-5 col-md-12 mb-3 d-flex gap-2 align-items-end pt-2">
                        <button type="submit" class="btn btn-primary shadow-sm d-flex align-items-center justify-content-center px-4" 
                                style="border-radius: 8px; height: 42px; font-weight: 800; background: #2563eb; border: none; transition: 0.3s; white-space: nowrap;">
                            <i class="fas fa-search me-2"></i> Tampilkan Data
                        </button>
                        <button type="button" class="btn btn-success shadow-sm d-flex align-items-center justify-content-center px-4" id="exportPDF" 
                                style="border-radius: 8px; height: 42px; font-weight: 800; background: #10b981; border: none; transition: 0.3s; white-space: nowrap;">
                            <i class="fas fa-file-pdf me-2"></i> EXPORT PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="d-flex gap-3 mb-4 flex-wrap align-items-center">
        <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
            <button class="btn btn-white border" id="prevMonth" style="background: #fff; font-weight: 600;"><i class="fas fa-chevron-left text-primary"></i> Prev</button>
            <button class="btn btn-white border" id="nextMonth" style="background: #fff; font-weight: 600;">Next <i class="fas fa-chevron-right text-primary"></i></button>
        </div>
        <button class="btn btn-white border shadow-sm" id="todayBtn" style="background: #fff; border-radius: 8px; font-weight: 600;">
            <i class="fas fa-map-marker-alt text-danger me-1"></i> Hari Ini
        </button>

        @if(!$isMonitoringChapter)
            <button type="button" class="btn btn-success shadow-sm ms-auto" id="exportPDF" 
                style="border-radius: 8px; font-weight: 700; background: #10b981; border: none;">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </button>
        @endif
    </div>
    <br>

    @php
        $userRole = strtolower(Auth::user()->role);
        $hideRequester = ($userRole === 'chapter' || $isMonitoringChapter);
    @endphp

    <div id="ganttExportArea" class="bg-white p-3 rounded shadow-sm">


        <!-- ======================= HEADER PDF ======================= -->
        <div id="pdfHeader" style="text-align:center; margin-bottom:20px;">
            <div id="pdfMonthYear" style="font-size:14px; margin-top:4px; color: #000;"></div>

            <div
                style="margin-top:15px; display:flex; gap:20px; justify-content:center; font-size:13px; align-items: center;">
                <span>
                    <span
                        style="display:inline-block;width:18px;height:12px;background:#4ade80;border-radius:3px;vertical-align: middle;"></span>
                    Done
                </span>
                <span>
                    <span
                        style="display:inline-block;width:18px;height:12px;background:#fde68a;border-radius:3px;vertical-align: middle;"></span>
                    Progress
                </span>
                <span>
                    <span
                        style="display:inline-block;width:18px;height:12px;background:#A40000;border-radius:3px;vertical-align: middle;"></span>
                    Overdue
                </span>
                <span>
                    <span
                        style="display:inline-block;width:18px;height:12px;background:#ffff00;border-radius:3px;border: 1px solid #ccc;vertical-align: middle;"></span>
                    Hari Ini
                </span>
                <span>
                    <span
                        style="display:inline-block;width:18px;height:12px;background:#ff0000;border-radius:3px;vertical-align: middle;"></span>
                    Hari Minggu
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
                        <th style="width:200px">Task</th>
                        @if(!$hideRequester)
                            <th style="width:120px">Requester</th>
                        @endif
                        <th style="width:120px">PIC</th>
                        <th style="width:80px">Status</th>
                        <th colspan="31" id="monthLabel"></th>
                    </tr>
                    <tr id="dayRow">
                        <th colspan="{{ $hideRequester ? 4 : 5 }}"></th>
                    </tr>
                </thead>

                <tbody id="ganttBody"></tbody>

            </table>
        </div>

    </div> <!-- END ganttExportArea -->


    {{-- ======================= UI BIASA (TIDAK MASUK PDF) ======================= --}}



    <style>
        /* ——— Gaya Gantt Chart ——— */
        body {
            font-family: 'Inter', sans-serif;
            font-size: 13px;
        }

        .gantt-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 8px;
            overflow: hidden;
            table-layout: fixed;
        }

        .gantt-table th,
        .gantt-table td {
            border: 2px solid #000;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
            position: relative;
            height: 32px;
            min-width: 28px;
            overflow: visible;
        }

        .gantt-table th {
            background: #2563eb;
            color: #fff;
            font-weight: 700;
            padding: 6px 4px;
        }

        .legend-box {
            width: 20px;
            height: 14px;
            border-radius: 4px;
            display: inline-block;
        }

        /* Bar */
        .gantt-bar {
            position: absolute;
            top: 4px;
            left: 0;
            height: calc(100% - 8px);
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .gantt-bar.green {
            background: #4ade80;
        }

        .gantt-bar.yellow {
            background: #fde68a;
        }

        .gantt-bar.red {
            background: #A40000;
        }
    </style>


    {{-- ======================= SCRIPT RENDER GANTT ======================= --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const tasks = @json($ganttData);
            const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

            let current = new Date({{ $currentYear }}, {{ $currentMonth - 1 }}, 1);

            const monthLabel = document.getElementById('monthLabel');
            const dayRow = document.getElementById('dayRow');
            const ganttBody = document.getElementById('ganttBody');

            function parseDateLocal(d) { return d ? new Date(d + "T00:00:00") : null; }

            const isReadOnly = {{ $isReadOnly ? 'true' : 'false' }};

            function render() {

                const y = current.getFullYear();
                const m = current.getMonth();
                const daysInMonth = new Date(y, m + 1, 0).getDate();

                monthLabel.innerText = monthNames[m] + " " + y;

                dayRow.innerHTML = '<th colspan="5"></th>';
                const today = new Date();
                const isTodayMonth = (y === today.getFullYear() && m === today.getMonth());

                for (let d = 1; d <= daysInMonth; d++) {
                    const th = document.createElement('th');
                    th.innerText = d;

                    const checkDate = new Date(y, m, d);
                    const isSunday = checkDate.getDay() === 0;
                    const isToday = isTodayMonth && (d === today.getDate());

                    if (isToday) {
                        th.style.setProperty('background', '#ffff00', 'important');
                        th.style.color = '#000';
                    } else if (isSunday) {
                        th.style.setProperty('background', '#ff0000', 'important');
                        th.style.color = '#fff';
                    }

                    dayRow.appendChild(th);
                }

                const filtered = tasks.filter(t => {
                    const s = parseDateLocal(t.start);
                    const e = parseDateLocal(t.end);
                    if (!s || !e) return false;

                    return (
                        (s.getMonth() == m && s.getFullYear() == y) ||
                        (e.getMonth() == m && e.getFullYear() == y) ||
                        (s < new Date(y, m + 1, 1) && e >= new Date(y, m, 1))
                    );
                });

                ganttBody.innerHTML = '';

                let lastProgram = null;
                let globalIndex = 0;

                filtered.forEach((t, i) => {

                    // Cek jika Program Berubah -> Print Header
                    if (t.program !== lastProgram) {
                        const headerTr = document.createElement('tr');
                        headerTr.style.background = '#e7f1ff';

                        headerTr.innerHTML = `
                                <td style="font-weight:bold;"></td>
                                <td colspan="4" style="font-weight:bold; color:#0d6efd; text-align:left; padding-left:10px;">
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

                    let statusBtn = "";
                    if (t.status === 'done') {
                        statusBtn = `<span class="badge bg-success">✔ Done</span>`;
                    } else {
                        if (isReadOnly) {
                            statusBtn = `<span class="badge bg-warning text-dark">Progress</span>`;
                        } else {
                            statusBtn = `<button data-id="${t.id}" class="btn btn-primary btn-sm done-btn">Done</button>`;
                        }
                    }

                    tr.innerHTML = `
                            <td style="color:#000; font-weight:600;">${globalIndex}</td>
                            <td class="text-start" style="color:#000; font-weight:600; padding-left: 20px;">${t.name}</td>
                            @if(!$hideRequester)
                                <td style="color:#000; font-weight:600;">${t.requester || '-'}</td>
                            @endif
                            <td style="color:#000; font-weight:600;">${t.pic || '-'}</td>
                            <td style="color:#000; font-weight:600;">${statusBtn}</td>
                        `;

                    for (let d = 1; d <= daysInMonth; d++) {
                        const checkDate = new Date(y, m, d);
                        const isSunday = checkDate.getDay() === 0;
                        const isToday = isTodayMonth && (d === today.getDate());

                        let style = "";
                        if (isToday) style = 'style="background: #ffff00 !important;"';
                        else if (isSunday) style = 'style="background: #ff0000 !important;"';

                        tr.innerHTML += `<td ${style}></td>`;
                    }
                    ganttBody.appendChild(tr);

                    const s = parseDateLocal(t.start);
                    const e = parseDateLocal(t.end);
                    if (!s || !e) return;

                    const start = Math.max(1, s.getMonth() == m ? s.getDate() : 1);
                    const end = Math.min(daysInMonth, e.getMonth() == m ? e.getDate() : daysInMonth);

                    setTimeout(() => {
                        const columnOffset = {{ $hideRequester ? 4 : 5 }};
                        const cell = tr.children[start + (columnOffset - 1)]; 

                        const bar = document.createElement('div');

                        bar.className = "gantt-bar " + (
                            t.status == "done" ? "green" :
                                t.status == "progress" ? "yellow" : "red"
                        );

                        bar.style.width = `${(end - start + 1) * (cell.offsetWidth || 28)}px`;
                        cell.appendChild(bar);
                    }, 10);
                });

            }

            // Tombol DONE
            document.body.addEventListener("click", function (e) {
                if (e.target.classList.contains("done-btn")) {
                    if (isReadOnly) return;

                    const id = e.target.dataset.id;

                    e.target.outerHTML = `<span class="badge bg-success">✔ Done</span>`;

                    const t = tasks.find(x => x.id == id);
                    if (t) t.status = "done";

                    render();

                    fetch(`/gantt/inisiatif/${id}/done`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                }
            });

            // Navigasi
            document.getElementById('prevMonth').onclick = () => { current.setMonth(current.getMonth() - 1); render(); };
            document.getElementById('nextMonth').onclick = () => { current.setMonth(current.getMonth() + 1); render(); };
            document.getElementById('todayBtn').onclick = () => { current = new Date(); render(); };

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
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember"
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