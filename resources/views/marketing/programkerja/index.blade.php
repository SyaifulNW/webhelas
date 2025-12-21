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

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary">
            <i class="fas fa-clipboard-list me-2"></i> Program Kerja & Inisiatif
        </h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProgram">
            <i class="fas fa-plus me-1"></i> Tambah Program Kerja
        </button>
    </div>
    
    <style>
        .persentase-field {
    min-width: 90px !important;
    text-align: left !important;
}

    </style>

<div class="table-wrapper">
    <table id="program-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Inisiatif</th>
                <th width="10%">PIC</th>
                <th width="10%">Target</th>
                <th width="10%">Realisasi</th>
                <th width="14%"> Nilai</th>
                <th width="10%">Mulai</th>
                <th width="10%">Selesai</th>
            </tr>
        </thead>

        <tbody>

            @foreach($programs as $index => $program)

            <!-- ======================== -->
            <!-- BARIS PROGRAM KERJA -->
            <!-- ======================== -->
            <tr class="program-row" style="background:#f3f6ff; font-weight:bold;">
                <td>{{ $index + 1 }}</td>
                <td colspan="8" 
                    contenteditable="true" 
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

                <!-- KOSONG karena inisiatif sudah di bawah program -->
    

                <td contenteditable="true" data-field="judul">
                    {{ $inisiatif->judul }}
                </td>

                <td>
                    <select class="form-select form-select-sm" data-field="pic">
                        @foreach([
                            'Rofi','Felmi','Rida','Nisa','Linda','Yasmin','Shafa','Qiyya',
                            'Eko','Tursia','Latifah','Agus','Syaiful'
                        ] as $name)
                            <option value="{{ $name }}" {{ $inisiatif->pic == $name ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </td>
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
                <td>
    <!--<button class="btn btn-danger btn-sm delete-inisiatif" data-id="{{ $inisiatif->id }}">-->
    <!--    <i class="fas fa-trash"></i>-->
    <!--</button>-->
</td>


            </tr>
            @endforeach

            <!-- Tambah Inisiatif -->
       <tr>
    
<tr>
    <td></td>
    <td colspan="8">
        <button type="button" class="btn-add add-row-btn" data-program-id="{{ $program->id }}">
            <i class="fas fa-plus me-1"></i> Tambah Inisiatif
        </button>
    </td>
</tr>



</tr>


            @endforeach

        </tbody>
    </table>
</div>

</div>


<!--Edit Inline-->
<script>
document.addEventListener("DOMContentLoaded", () => {

    // === INLINE UPDATE UNTUK PROGRAM KERJA ===
    document.querySelectorAll('td[contenteditable="true"][data-id]').forEach(cell => {

        let original = cell.innerText;

        cell.addEventListener("blur", function() {
            let newValue = this.innerText.trim();

            // Jika tidak berubah, tidak usah kirim
            if (newValue === original) return;

            let id = this.dataset.id;
            let field = this.dataset.field;

            fetch("{{ route('programkerja.updateInline') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    id: id,
                    field: field,
                    value: newValue
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    original = newValue;

                    // Efek hijau ketika sukses
                    cell.style.background = "#d4f8d4";
                    setTimeout(() => {
                        cell.style.background = "";
                    }, 600);
                } else {
                    alert("Gagal memperbarui");
                }
            })
            .catch(err => {
                alert("Error: " + err);
            });
        });

    });

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
  // cegah bila ada error JS sebelumnya: wrap dalam try
  try {
    document.body.addEventListener('click', function(e) {
      const btn = e.target.closest('.add-row-btn');
      if (!btn) return;

      e.preventDefault();

      // ambil programId dari button
      const programId = btn.dataset.programId || '';

      // siapkan row baru (pastikan jumlah <td> sesuai header tabel)
      const rowHtml = `
        <tr class="inisiatif-row new" data-program-id="${programId}">
          <td></td>
          <td contenteditable="true" data-field="judul">Inisiatif Baru</td>
          <td>
            <select class="form-select form-select-sm" data-field="pic">
              ${['Rofi','Felmi','Rida','Nisa','Linda','Yasmin','Shafa','Qiyya','Eko','Tursia','Latifah','Agus','Syaiful']
                .map(n => `<option value="${n}">${n}</option>`).join('')}
            </select>
          </td>
<td>
    <input type="number" class="form-control form-control-sm target-field" data-field="target" min="1" max="100" value="0">
</td>
<td>
    <input type="number" class="form-control form-control-sm realisasi-field" data-field="realisasi" min="0" max="100" value="0">
</td>

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

      // sisipkan sebelum baris tombol (so the add button row stays last)
      const containerRow = btn.closest('tr');
      containerRow.insertAdjacentHTML('beforebegin', rowHtml);

      // fokus ke cell judul baru (opsional)
      const newRow = containerRow.previousElementSibling;
      const judulCell = newRow && newRow.querySelector('[data-field="judul"]');
      if (judulCell) {
        // beri sedikit delay supaya browser render dulu
        setTimeout(() => {
          judulCell.focus();
          // place caret at end (works in contenteditable)
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

    const payload = {
        program_kerja_id: row.dataset.programId,
        judul: row.querySelector('[data-field="judul"]').innerText.trim(),
        pic: row.querySelector('[data-field="pic"]').value,
        target: parseInt(row.querySelector('[data-field="target"]').innerText.trim()) || 0,
        realisasi: parseInt(row.querySelector('[data-field="realisasi"]').innerText.trim()) || 0,
        nilai: parseFloat(row.querySelector(".persentase-field").value) || 0,
        tanggal_mulai: row.querySelector('[data-field="tanggal_mulai"]').value,
        tanggal_selesai: row.querySelector('[data-field="tanggal_selesai"]').value,
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
            btn.disabled = true;
        } else {
            alert("Gagal menyimpan: " + data.error);
        }

    } catch (err) {
        console.error("FETCH ERROR:", err);
        alert("Terjadi kesalahan: " + err);
    }
});
</script>




<div class="modal fade" id="modalProgram" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Tambah Program Kerja
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="formProgram" action="{{ route('programkerja.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Judul Program</label>
                        <input type="text" name="judul" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="text-end mt-3">

                        <button type="button" 
                                class="btn btn-secondary me-2" 
                                data-bs-dismiss="modal">
                            <i class="fas fa-times-circle me-1"></i> Batal
                        </button>

                        <button class="btn btn-success" type="submit">
                            <i class="fas fa-save me-1"></i> Simpan
                        </button>

                    </div>

                </form>

            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

@endsection

