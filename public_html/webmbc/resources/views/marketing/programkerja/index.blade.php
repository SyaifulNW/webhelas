@extends('layouts.masteradmin')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">


<style>
/* --- Global Styling --- */
.table-wrapper {
    overflow-x: auto;
    margin-top: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

thead {
    background: linear-gradient(135deg, #1b6fa8, #3a8bc2);
    color: white;
    font-weight: 600;
}

th, td {
    padding: 14px 12px;
    border: 1px solid #e0e0e0;
    vertical-align: middle;
    text-align: left;
}

th {
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

tr.program-row {
    background: linear-gradient(135deg, #eaf6ff, #d1e7ff);
    font-weight: bold;
    font-size: 16px;
    color: #1b6fa8;
    border-bottom: 2px solid #1b6fa8;
}

tr.program-row td:first-child {
    border-radius: 12px 0 0 0;
}

tr.program-row td:last-child {
    border-radius: 0 12px 0 0;
}

tr.inisiatif-row {
    background: #f9f9f9;
    transition: background-color 0.3s ease;
}

tr.inisiatif-row:hover {
    background: #f0f8ff;
}

td[contenteditable="true"] {
    background: #fffefc;
    cursor: text;
    border: 1px solid #ddd;
    border-radius: 6px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

td[contenteditable="true"]:focus {
    outline: none;
    border-color: #1b6fa8;
    box-shadow: 0 0 8px rgba(27, 111, 168, 0.3);
}

td[contenteditable="true"]:hover {
    border-color: #1b6fa8;
}

/* Buttons */
.btn-add {
    background: linear-gradient(135deg, #1b6fa8, #3a8bc2);
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-add:hover {
    background: linear-gradient(135deg, #15557f, #2a6b9c);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-check {
    background: linear-gradient(135deg, #28a745, #4caf50);
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-check:hover {
    background: linear-gradient(135deg, #1e7d34, #388e3c);
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-primary {
    background: linear-gradient(135deg, #007bff, #0056b3);
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-primary:hover {
    background: linear-gradient(135deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-success {
    background: linear-gradient(135deg, #28a745, #4caf50);
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.btn-success:hover {
    background: linear-gradient(135deg, #1e7d34, #388e3c);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* Modal */
.modal-content {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.modal-header {
    background: linear-gradient(135deg, #007bff, #00a0e9);
    color: white;
    border-bottom: none;
}

.modal-body {
    padding: 24px;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #ddd;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #1b6fa8;
    box-shadow: 0 0 8px rgba(27, 111, 168, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
    th, td {
        padding: 10px 8px;
        font-size: 12px;
    }
    .btn-add, .btn-check, .btn-primary, .btn-success {
        padding: 6px 12px;
        font-size: 12px;
    }
    h3 {
        font-size: 1.5rem;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.table-wrapper {
    animation: fadeIn 0.5s ease-in-out;
}
</style>

<style>
    .persentase-field {
        min-width: 90px !important;
        text-align: left !important;
    }
</style>

@php 
    $userRole = strtolower(Auth::user()->role);
    $isProduksi = $userRole === 'produksi' || ($viewRole ?? '') === 'produksi'; 
    $isReadOnly = ($userRole === 'administrator' && isset($viewRole));
@endphp

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary">
            <i class="fas fa-clipboard-list me-2"></i> 
            {{ $isReadOnly ? 'Monitoring' : '' }} Program Kerja & Inisiatif {{ $isReadOnly ? '(Produksi)' : '' }}
        </h3>
        @if(!$isReadOnly)
        <button class="btn btn-primary" id="add-program-btn">
            <i class="fas fa-plus me-1"></i> Tambah Program Kerja
        </button>
        @endif
    </div>

<div class="table-wrapper">
    <table id="program-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                @if($isProduksi)
                    <th width="25%">Pekerjaan</th>
                    <th width="10%">PIC</th>
                    <th width="10%">Tanggal Mulai</th>
                    <th width="10%">Dateline</th>
                    <th width="35%">Keterangan</th>
                @else
                    <th width="25%">Inisiatif</th>
                    <th width="10%">PIC</th>
                    <th width="10%">Target</th>
                    <th width="10%">Realisasi</th>
                    <th width="14%"> Nilai</th>
                    <th width="10%">Mulai</th>
                    <th width="10%">Selesai</th>
                @endif
                <th></th>
            </tr>
        </thead>

        <tbody>

            @foreach($programs as $index => $program)

            <!-- ======================== -->
            <!-- BARIS PROGRAM KERJA -->
            <!-- ======================== -->
            <tr class="program-row" style="background:#f3f6ff; font-weight:bold;">
                <td>{{ $index + 1 }}</td>
                <td colspan="{{ $isProduksi ? 6 : 8 }}" 
                    contenteditable="{{ $isReadOnly ? 'false' : 'true' }}" 
                    data-field="judul"
                    data-id="{{ $program->id }}"
                    style="font-size:15px;">
                    {{ $program->judul }}
                </td>
            </tr>

            <!-- ======================== -->
            <!-- BARIS INISIATIF -->
            <!-- ======================== -->
            @foreach($program->inisiatifs as $no => $inisiatif)
            <tr class="inisiatif-row" data-id="{{ $inisiatif->id }}">
                <td>{{ $no + 1 }}</td>

                <td contenteditable="{{ $isReadOnly ? 'false' : 'true' }}" data-field="judul">
                    {{ $inisiatif->judul }}
                </td>

                <td>
                    <select class="form-select form-select-sm" data-field="pic" {{ $isReadOnly ? 'disabled' : '' }}>
                        @foreach(['Rofi','Rida'] as $name)
                            <option value="{{ $name }}" {{ $inisiatif->pic == $name ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </td>

                @if($isProduksi)
                    <td>
                        <input type="date" class="form-control form-control-sm" data-field="tanggal_mulai"
                               value="{{ $inisiatif->tanggal_mulai }}">
                    </td>
                    <td>
                        <input type="date" class="form-control form-control-sm" data-field="tanggal_selesai"
                               value="{{ $inisiatif->tanggal_selesai }}">
                    </td>
                    <td contenteditable="true" data-field="deskripsi">
                        {{ $inisiatif->deskripsi }}
                    </td>
                @else
                    <td>
                        <input type="number" class="form-control form-control-sm target-field" 
                               data-field="target" min="1" max="100" 
                               value="{{ $inisiatif->target ?? 1 }}">
                    </td>

                    <td>
                        <input type="number" class="form-control form-control-sm realisasi-field" 
                               data-field="realisasi" min="0" max="100" 
                               value="{{ $inisiatif->realisasi ?? 0 }}">
                    </td>

                    <td>
                        <input type="text"
                               class="form-control form-control-sm persentase-field"
                               readonly
                               value="{{ $inisiatif->target > 0 ? round(($inisiatif->realisasi / $inisiatif->target) * 100) : 0 }}">
                    </td>

                    <td>
                        <input type="date" class="form-control form-control-sm" data-field="tanggal_mulai"
                               value="{{ $inisiatif->tanggal_mulai }}">
                    </td>

                    <td>
                        <input type="date" class="form-control form-control-sm" data-field="tanggal_selesai"
                               value="{{ $inisiatif->tanggal_selesai }}">
                    </td>
                @endif
                @if(!$isReadOnly)
                <td>
                    <button class="btn btn-danger btn-sm delete-inisiatif" data-id="{{ $inisiatif->id }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
                @endif
            </tr>
            @endforeach

             <!-- Tambah Inisiatif -->
             @if(!$isReadOnly)
             <tr>
                <td></td>
                <td colspan="{{ $isProduksi ? 6 : 8 }}">
                    <button class="btn btn-sm btn-link text-primary p-0 add-row-btn" data-program-id="{{ $program->id }}">
                        <i class="fas fa-plus me-1"></i> Tambah Inisiatif...
                    </button>
                </td>
             </tr>
             @endif


            @endforeach

        </tbody>
    </table>
</div>

</div>


<!-- Consolidated Inline Update & Add Program -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    // === TAMBAH PROGRAM KERJA BARU (AJAX) ===
    document.getElementById('add-program-btn')?.addEventListener('click', async function() {
        const btn = this;
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        try {
            const res = await fetch("{{ route('programkerja.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ judul: 'Program Baru' })
            });
            const data = await res.json();
            
            if (data.success) {
                const program = data.program;
                const index = document.querySelectorAll('.program-row').length + 1;
                const isProduksi = {{ $isProduksi ? 'true' : 'false' }};
                const colspanHeader = isProduksi ? 6 : 8;

                const html = `
                    <tr class="program-row" style="background:#f3f6ff; font-weight:bold;">
                        <td>${index}</td>
                        <td colspan="${colspanHeader}" 
                            contenteditable="true" 
                            data-field="judul"
                            data-id="${program.id}"
                            style="font-size:15px;">
                            ${program.judul}
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="${colspanHeader}">
                            <button type="button" class="btn-add add-row-btn" data-program-id="${program.id}">
                                <i class="fas fa-plus me-1"></i> Tambah Inisiatif
                            </button>
                        </td>
                    </tr>`;
                
                document.querySelector('#program-table tbody').insertAdjacentHTML('beforeend', html);
                
                // Focus the new header
                const newHeader = document.querySelector(`.program-row [data-id="${program.id}"]`);
                if (newHeader) {
                    newHeader.focus();
                    // place caret at end
                    const range = document.createRange();
                    range.selectNodeContents(newHeader);
                    range.collapse(false);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }
        } catch (err) {
            console.error(err);
            alert('Gagal menambah program');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
    });

    // === DELEGATED UPDATE UNTUK PROGRAM KERJA (HEADER) ===
    document.addEventListener('blur', async function(e) {
        const target = e.target;
        if (target.matches('.program-row td[contenteditable="true"][data-id][data-field="judul"]')) {
            const newValue = target.innerText.trim();
            const id = target.dataset.id;
            const field = target.dataset.field;

            try {
                const res = await fetch("{{ route('programkerja.updateInline') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ id, field, value: newValue, type: 'program' })
                });
                const data = await res.json();
                if (data.success) {
                    target.style.background = "#d4f8d4";
                    setTimeout(() => target.style.background = "", 600);
                }
            } catch(err) { console.error(err); }
        }
    }, true);
});
</script>


<!--Hapus Inisiatif-->
<!-- Hapus Inisiatif -->
<script>
document.addEventListener("click", function(e) {
    const btn = e.target.closest(".delete-inisiatif");
    if (!btn) return;

    const id = btn.dataset.id;

    if (!confirm("Yakin ingin menghapus inisiatif ini?")) return;

    fetch("{{ route('inisiatif.delete') }}", {
        method: "DELETE",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
            "Accept": "application/json"  // penting supaya Laravel return JSON
        },
        body: JSON.stringify({ id })
    })
    .then(async res => {
        // Pastikan response JSON, jika HTML tangkap error
        const contentType = res.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
            const text = await res.text();
            throw new Error("Server tidak merespon JSON: " + text);
        }
        return res.json();
    })
    .then(data => {
        if (data.success) {
            // Efek animasi fade-out sebelum dihapus
            const row = btn.closest("tr");
            row.style.transition = "opacity 0.4s";
            row.style.opacity = "0";

            setTimeout(() => row.remove(), 400);
        } else {
            alert(data.message || "Gagal menghapus data!");
        }
    })
    .catch(err => {
        console.error(err);
        alert("Terjadi error: " + err.message);
    });
});
</script>




<script>
document.addEventListener('DOMContentLoaded', function () {
  const isProduksi = {{ $isProduksi ? 'true' : 'false' }};
  
  try {
    document.body.addEventListener('click', function(e) {
      const btn = e.target.closest('.add-row-btn');
      if (!btn) return;

      e.preventDefault();
      const programId = btn.dataset.programId || '';

      let rowHtml = '';
      if (isProduksi) {
          rowHtml = `
            <tr class="inisiatif-row new" data-program-id="${programId}">
              <td></td>
              <td contenteditable="true" data-field="judul">Pekerjaan Baru</td>
              <td>
                <select class="form-select form-select-sm" data-field="pic">
                  ${['Rofi','Rida']
                    .map(n => `<option value="${n}">${n}</option>`).join('')}
                </select>
              </td>
              <td><input type="date" class="form-control form-control-sm" data-field="tanggal_mulai"></td>
              <td><input type="date" class="form-control form-control-sm" data-field="tanggal_selesai"></td>
              <td contenteditable="true" data-field="deskripsi">Keterangan...</td>
              <td>
                <button type="button" class="btn btn-success btn-sm save-new" title="Simpan Inisiatif">
                    <i class="fas fa-save"></i>
                </button>
              </td>
            </tr>`;
      } else {
          rowHtml = `
            <tr class="inisiatif-row new" data-program-id="${programId}">
              <td></td>
              <td contenteditable="true" data-field="judul">Inisiatif Baru</td>
              <td>
                <select class="form-select form-select-sm" data-field="pic">
                  ${['Rofi','Rida']
                    .map(n => `<option value="${n}">${n}</option>`).join('')}
                </select>
              </td>
              <td><input type="number" class="form-control form-control-sm target-field" data-field="target" value="1"></td>
              <td><input type="number" class="form-control form-control-sm realisasi-field" data-field="realisasi" value="0"></td>
              <td><input type="text" class="form-control form-control-sm persentase-field" readonly value="0"></td>
              <td><input type="date" class="form-control form-control-sm" data-field="tanggal_mulai"></td>
              <td>
                <div style="display:flex; gap:6px; align-items:center;">
                  <input type="date" class="form-control form-control-sm" data-field="tanggal_selesai">
                  <button type="button" class="btn btn-success btn-sm save-new" title="Simpan Inisiatif">
                    <i class="fas fa-save"></i>
                  </button>
                </div>
              </td>
            </tr>`;
      }

      const containerRow = btn.closest('tr');
      containerRow.insertAdjacentHTML('beforebegin', rowHtml);

      const newRow = containerRow.previousElementSibling;
      const judulCell = newRow && newRow.querySelector('[data-field="judul"]');
      if (judulCell) {
        setTimeout(() => {
          judulCell.focus();
          const range = document.createRange();
          range.selectNodeContents(judulCell);
          range.collapse(false);
          const sel = window.getSelection();
          sel.removeAllRanges();
          sel.addRange(range);
        }, 50);
      }
    });
  } catch (err) {
    console.error('Init add-row handler failed:', err);
  }
});
</script>


<!--Presentase Otomatis-->
<script>
  document.querySelectorAll('.target-field, .realisasi-field').forEach(input => {

    // Hitung persentase langsung saat user ketik
    input.addEventListener('input', function() {
        const row = this.closest('tr');
        const target = parseFloat(row.querySelector('.target-field').value) || 0;
        const realisasi = parseFloat(row.querySelector('.realisasi-field').value) || 0;
        const persenField = row.querySelector('.persentase-field');

        persenField.value = target > 0 ? Math.round((realisasi / target) * 100) : 0;
    });

    // Simpan ke server saat blur (edit selesai)
    input.addEventListener('blur', async function() {
        const row = this.closest('tr');
        const id = row.dataset.id;
        if (!id) return; // baris baru belum tersimpan
        const field = this.dataset.field;
        const value = this.value;

        try {
            const res = await fetch("{{ route('programkerja.updateInline') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ id, field, value })
            });
            const data = await res.json();
            if (!data.success) alert('Gagal update inisiatif');
        } catch(err) {
            console.error(err);
        }
    });

});
  
</script>


<!--Simpan-->
<script>
document.addEventListener("click", async function(e) {
    const btn = e.target.closest(".save-new");
    if (!btn) return;

    const row = btn.closest("tr");
    const getField = (f) => row.querySelector(`[data-field="${f}"]`);

    const payload = {
        program_kerja_id: row.dataset.programId,
        judul: getField('judul')?.innerText.trim() || 'Baru',
        pic: getField('pic')?.value || '',
        target: parseInt(getField('target')?.value || getField('target')?.innerText || 0),
        realisasi: parseInt(getField('realisasi')?.value || getField('realisasi')?.innerText || 0),
        nilai: parseFloat(row.querySelector(".persentase-field")?.value || 0),
        tanggal_mulai: getField('tanggal_mulai')?.value || null,
        tanggal_selesai: getField('tanggal_selesai')?.value || null,
        deskripsi: getField('deskripsi')?.innerText.trim() || '',
        status: "progress"
    };

    try {
        const res = await fetch("{{ route('inisiatif.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (data.success) {
            btn.classList.remove("btn-success");
            btn.classList.add("btn-secondary");
            btn.innerHTML = '<i class="fas fa-check"></i>';
            btn.disabled = true;
            row.dataset.id = data.id;
            row.classList.remove('new');
            // Refresh page or update row to allow further editing if needed
        } else {
            alert("Gagal menyimpan: " + (data.error || 'Terjadi kesalahan'));
        }

    } catch (err) {
        console.error("FETCH ERROR:", err);
        alert("Terjadi kesalahan: " + err);
    }
});
</script>

<!-- Inline Update for all Inisiatif fields -->
<script>
document.addEventListener('change', handleInisiatifUpdate);
document.addEventListener('blur', function(e) {
    if (e.target.matches('[contenteditable="true"][data-field]')) {
        handleInisiatifUpdate(e);
    }
}, true);

async function handleInisiatifUpdate(e) {
    const target = e.target;
    if (!target.matches('.inisiatif-row input, .inisiatif-row select, .inisiatif-row [contenteditable="true"]')) return;
    
    const row = target.closest('tr');
    if (!row || row.classList.contains('new')) return;

    const id = row.dataset.id;
    const field = target.dataset.field;
    if (!id || !field) return;

    let value = target.matches('[contenteditable="true"]') ? target.innerText.trim() : target.value;

    try {
        const res = await fetch("{{ route('programkerja.updateInline') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id, field, value })
        });
        const data = await res.json();
        if (data.success) {
            if (target.matches('[contenteditable="true"]')) {
                target.style.background = "#d4f8d4";
                setTimeout(() => target.style.background = "", 600);
            }
        }
    } catch(err) {
        console.error(err);
    }
}
</script>




<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@endsection

