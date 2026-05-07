<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORMULIR PENDAFTARAN PESERTA M1T CHAPTER</title>
    <!-- Use Tailwind via CDN for quick styling 'mirip google form' or custom CSS. Let's use simple Bootstrap. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f0ebf8;
            font-family: 'Roboto', sans-serif;
            color: #202124;
            padding-bottom: 50px;
        }
        .form-container {
            max-width: 640px;
            margin: 0 auto;
        }
        .form-header {
            background-color: #fff;
            border-radius: 8px;
            border-top: 10px solid #673ab7;
            padding: 24px;
            margin-top: 15px;
            margin-bottom: 12px;
            box-shadow: 0 1px 4px 0 rgba(0,0,0,0.14);
        }
        .form-header h1 {
            font-size: 32px;
            margin-bottom: 12px;
            font-weight: 500;
        }
        .card-question {
            background-color: #fff;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 12px;
            box-shadow: 0 1px 4px 0 rgba(0,0,0,0.14);
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        .card-question.active {
            border-left: 3px solid #4285f4;
        }
        .question-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 16px;
        }
        .section-title {
            background-color: #673ab7;
            color: white;
            border-radius: 8px;
            padding: 16px 24px;
            margin-bottom: 12px;
            margin-top: 24px;
            box-shadow: 0 1px 4px 0 rgba(0,0,0,0.14);
            font-size: 20px;
        }
        .form-control, .form-select {
            border: none;
            border-bottom: 1px solid #dcdcdc;
            border-radius: 0;
            padding-left: 0;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: none;
            border-bottom: 2px solid #673ab7;
        }
        .form-check-input {
            width: 1.25em;
            height: 1.25em;
            border: 2px solid #dcdcdc;
            cursor: pointer;
        }
        .form-check-input:checked {
            background-color: #673ab7;
            border-color: #673ab7;
        }
        .form-check-label {
            cursor: pointer;
            padding-left: 8px;
            font-weight: 500;
        }
        .btn-submit {
            background-color: #673ab7;
            color: white;
            font-weight: 500;
            padding: 10px 24px;
        }
        .btn-submit:hover {
            background-color: #5e35b1;
            color: white;
        }
    </style>
</head>
<body>

<div class="container form-container">
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    icon: 'success',
                    confirmButtonColor: '#673ab7'
                });
            });
        </script>
    @endif

    <form action="{{ route('form.m1t.store') }}" method="POST">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user->id }}">
        <input type="hidden" name="total_score" id="total_score" value="0">

        <div class="form-header">
            <h1>
                FORMULIR PENDAFTARAN PESERTA M1T
                @if(strtolower($user->role) === 'chapter' && !empty($user->chapter))
                    CHAPTER {{ strtoupper($user->chapter) }}
                @else
                    CHAPTER - {{ strtoupper($user->name) }}
                @endif
            </h1>
            <p>Silakan isi form di bawah ini dengan data yang sebenarnya.</p>
            <p class="text-danger small">* Wajib</p>
        </div>

        <div class="section-title">A. DATA DIRI</div>
        
        <div class="card-question">
            <div class="question-title">1. Nama Lengkap <span class="text-danger">*</span></div>
            <input type="text" class="form-control" name="nama" required placeholder="Jawaban Anda">
        </div>

        <div class="card-question">
            <div class="question-title">2. No. WhatsApp <span class="text-danger">*</span></div>
            <input type="number" class="form-control" name="no_wa" required placeholder="Jawaban Anda">
        </div>

        <div class="card-question" style="background-color: #f8f9fa;">
            <div class="question-title">3. Domisili (Provinsi & Kota) <span class="text-danger">*</span></div>
            <div class="mb-3">
                <label class="form-label small text-muted">Provinsi</label>
                <select class="form-select" id="provinsi" name="provinsi" required onchange="updateKota(this.value, this.options[this.selectedIndex].text)">
                    <option value="">Pilih Provinsi...</option>
                </select>
                <input type="hidden" name="provinsi_nama" id="provinsi_nama">
            </div>
            <div>
                <label class="form-label small text-muted">Kota/Kabupaten</label>
                <select class="form-select" id="kota" name="kota" required onchange="document.getElementById('kota_nama').value = this.options[this.selectedIndex].text">
                    <option value="">Pilih Kota...</option>
                </select>
                <input type="hidden" name="kota_nama" id="kota_nama">
            </div>
        </div>

        <div class="card-question">
            <div class="question-title">4. Nama Usaha / Bidang Usaha <span class="text-danger">*</span></div>
            <input type="text" class="form-control" name="nama_usaha" required placeholder="Jawaban Anda">
        </div>

        <div class="section-title">B. KONDISI BISNIS SAAT INI</div>

        <div class="card-question">
            <div class="question-title">5. Sudah berapa lama Anda menjalankan bisnis? <span class="text-danger">*</span></div>
            <input type="hidden" name="lama_bisnis_label" id="lama_bisnis_label">
            <div class="form-check">
                <input class="form-check-input score-radio" type="radio" name="lama_bisnis" id="lama1" value="1" data-label="< 1 tahun" required>
                <label class="form-check-label" for="lama1">&lt; 1 tahun</label>
            </div>
            <div class="form-check">
                <input class="form-check-input score-radio" type="radio" name="lama_bisnis" id="lama2" value="3" data-label="1–3 tahun" required>
                <label class="form-check-label" for="lama2">1–3 tahun</label>
            </div>
            <div class="form-check">
                <input class="form-check-input score-radio" type="radio" name="lama_bisnis" id="lama3" value="5" data-label="> 3 tahun" required>
                <label class="form-check-label" for="lama3">&gt; 3 tahun</label>
            </div>
        </div>

        <div class="card-question">
            <div class="question-title">6. Omset rata-rata per bulan saat ini: <span class="text-danger">*</span></div>
            <input type="hidden" name="omset_label" id="omset_label">
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="omset" id="omset1" value="1" data-label="10-50 Juta" required>
                <label class="form-check-label" for="omset1">10-50 Juta</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="omset" id="omset2" value="3" data-label="50 - 100 Juta" required>
                <label class="form-check-label" for="omset2">50 - 100 Juta</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="omset" id="omset3" value="4" data-label="100 - 500 Juta" required>
                <label class="form-check-label" for="omset3">100 - 500 Juta</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="omset" id="omset4" value="5" data-label=">500 Juta" required>
                <label class="form-check-label" for="omset4">&gt;500 Juta</label>
            </div>
        </div>

        <div class="card-question">
            <div class="question-title">7. Berapa Jumlah Karyawan di Bisnis Anda <span class="text-danger">*</span></div>
            <input type="hidden" name="jumlah_karyawan_label" id="jumlah_karyawan_label">
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="jumlah_karyawan" id="karyawan1" value="1" data-label="Belum ada" required>
                <label class="form-check-label" for="karyawan1">Belum ada</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="jumlah_karyawan" id="karyawan2" value="2" data-label="1- 5" required>
                <label class="form-check-label" for="karyawan2">1- 5</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="jumlah_karyawan" id="karyawan3" value="3" data-label="6 - 10" required>
                <label class="form-check-label" for="karyawan3">6 - 10</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="jumlah_karyawan" id="karyawan4" value="4" data-label="10 - 15" required>
                <label class="form-check-label" for="karyawan4">10 - 15</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="jumlah_karyawan" id="karyawan5" value="5" data-label="> 15" required>
                <label class="form-check-label" for="karyawan5">&gt; 15</label>
            </div>
        </div>

        <div class="section-title">C. MASALAH & KEBUTUHAN</div>

        <div class="card-question">
            <div class="question-title">8. Tantangan utama dalam bisnis Anda saat ini: <span class="text-danger">*</span></div>
            <input type="hidden" name="tantangan_label" id="tantangan_label">
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="tantangan" id="t1" value="1" data-label="Masih mencari model bisnis yang tepat" required>
                <label class="form-check-label" for="t1">Masih mencari model bisnis yang tepat</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="tantangan" id="t2" value="2" data-label="Marketing belum konsisten" required>
                <label class="form-check-label" for="t2">Marketing belum konsisten</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="tantangan" id="t3" value="3" data-label="Omset stagnan" required>
                <label class="form-check-label" for="t3">Omset stagnan</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="tantangan" id="t4" value="4" data-label="Sulit scale bisnis" required>
                <label class="form-check-label" for="t4">Sulit scale bisnis</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="tantangan" id="t5" value="5" data-label="Sudah punya tim tapi belum berkembang" required>
                <label class="form-check-label" for="t5">Sudah punya tim tapi belum berkembang</label>
            </div>
        </div>

        <div class="section-title">D. TARGET & AMBISI</div>

        <div class="card-question">
            <div class="question-title">9. Target utama Anda dalam 1 tahun ke depan: <span class="text-danger">*</span></div>
            <input type="hidden" name="target_label" id="target_label">
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="target" id="tar1" value="1" data-label="Belum punya target yang jelas" required>
                <label class="form-check-label" for="tar1">Belum punya target yang jelas</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="target" id="tar2" value="2" data-label="Ingin ada peningkatan kecil" required>
                <label class="form-check-label" for="tar2">Ingin ada peningkatan kecil</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="target" id="tar3" value="3" data-label="Ingin minimal 2x dari kondisi sekarang" required>
                <label class="form-check-label" for="tar3">Ingin minimal 2x dari kondisi sekarang</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="target" id="tar4" value="4" data-label="Ingin scale bisnis secara signifikan" required>
                <label class="form-check-label" for="tar4">Ingin scale bisnis secara signifikan</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="target" id="tar5" value="5" data-label="Ingin membangun bisnis besar & sistem yang kuat" required>
                <label class="form-check-label" for="tar5">Ingin membangun bisnis besar & sistem yang kuat</label>
            </div>
        </div>

        <div class="card-question">
            <div class="question-title">10. Seberapa kuat alasan Anda untuk mencapai target tersebut: <span class="text-danger">*</span></div>
            <input type="hidden" name="alasan_label" id="alasan_label">
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="alasan" id="al1" value="1" data-label="Sekadar ingin mencoba" required>
                <label class="form-check-label" for="al1">Sekadar ingin mencoba</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="alasan" id="al2" value="2" data-label="Supaya lebih stabil" required>
                <label class="form-check-label" for="al2">Supaya lebih stabil</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="alasan" id="al3" value="3" data-label="Ingin berkembang" required>
                <label class="form-check-label" for="al3">Ingin berkembang</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="alasan" id="al4" value="4" data-label="Ada tuntutan kebutuhan / tanggung jawab" required>
                <label class="form-check-label" for="al4">Ada tuntutan kebutuhan / tanggung jawab</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="alasan" id="al5" value="5" data-label="Punya alasan kuat (keluarga, visi besar, dll)" required>
                <label class="form-check-label" for="al5">Punya alasan kuat (keluarga, visi besar, dll)</label>
            </div>
        </div>

        <div class="section-title">E. KESIAPAN & KOMITMEN</div>

        <div class="card-question">
            <div class="question-title">11. Jika ada program pendampingan bisnis yang tepat, posisi Anda saat ini: <span class="text-danger">*</span></div>
            <input type="hidden" name="posisi_label" id="posisi_label">
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="posisi" id="pos1" value="1" data-label="Hanya ingin belajar dulu" required>
                <label class="form-check-label" for="pos1">Hanya ingin belajar dulu</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="posisi" id="pos2" value="2" data-label="Tertarik tapi masih ragu" required>
                <label class="form-check-label" for="pos2">Tertarik tapi masih ragu</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="posisi" id="pos3" value="3" data-label="Akan dipertimbangkan" required>
                <label class="form-check-label" for="pos3">Akan dipertimbangkan</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="posisi" id="pos5" value="5" data-label="Siap bergabung jika memang cocok" required>
                <label class="form-check-label" for="pos5">Siap bergabung jika memang cocok</label>
            </div>
        </div>

        <div class="card-question">
            <div class="question-title">12. Untuk pengembangan bisnis, kisaran investasi yang realistis bagi Anda: <span class="text-danger">*</span></div>
            <input type="hidden" name="investasi_label" id="investasi_label">
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="investasi" id="inv1" value="1" data-label="< 1 juta / bulan" required>
                <label class="form-check-label" for="inv1">&lt; 1 juta / bulan</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="investasi" id="inv2" value="3" data-label="1 – 2 juta / bulan" required>
                <label class="form-check-label" for="inv2">1 – 2 juta / bulan</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="investasi" id="inv3" value="4" data-label="2 – 5 juta / bulan" required>
                <label class="form-check-label" for="inv3">2 – 5 juta / bulan</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="investasi" id="inv4" value="5" data-label="> 5 juta / bulan" required>
                <label class="form-check-label" for="inv4">&gt; 5 juta / bulan</label>
            </div>
        </div>

        <div class="card-question">
            <div class="question-title">13. Kesiapan anda untuk mengikuti seminar bisnis / open house sampai selesai: <span class="text-danger">*</span></div>
            <input type="hidden" name="kesiapan_hadir_label" id="kesiapan_hadir_label">
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="kesiapan_hadir" id="hadir1" value="1" data-label="Tidak yakin bisa hadir penuh" required>
                <label class="form-check-label" for="hadir1">Tidak yakin bisa hadir penuh</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="kesiapan_hadir" id="hadir2" value="3" data-label="Siap hadir sampai selesai" required>
                <label class="form-check-label" for="hadir2">Siap hadir sampai selesai</label>
            </div>
        </div>

        <div class="card-question">
            <div class="question-title">14. Jika merasa program Mentoring 1 Tahun ini cocok, apakah Anda siap mengambil keputusan: <span class="text-danger">*</span></div>
            <input type="hidden" name="keputusan_label" id="keputusan_label">
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="keputusan" id="kep1" value="1" data-label="Tidak" required>
                <label class="form-check-label" for="kep1">Tidak</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="keputusan" id="kep2" value="5" data-label="Ya" required>
                <label class="form-check-label" for="kep2">Ya</label>
            </div>
        </div>

        <div class="card-question">
            <div class="question-title">15. Sudah pernah mendengar atau mengenal coach Fitra Jaya Saleh <span class="text-danger">*</span></div>
            <input type="hidden" name="mengenal_coach_label" id="mengenal_coach_label">
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="mengenal_coach" id="coach1" value="1" data-label="Belum sama sekali" required>
                <label class="form-check-label" for="coach1">Belum sama sekali</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="mengenal_coach" id="coach2" value="2" data-label="sudah tau tapi tidak mendalam" required>
                <label class="form-check-label" for="coach2">sudah tau tapi tidak mendalam</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input score-radio" type="radio" name="mengenal_coach" id="coach3" value="3" data-label="Sudah Kenal" required>
                <label class="form-check-label" for="coach3">Sudah Kenal</label>
            </div>
        </div>

        <div class="card-question text-center bg-light d-none">
            <h5 class="fw-bold mb-0">REKAP PENILAIAN</h5>
            <p class="text-muted small mb-1">Total Skor: <span id="display_score" class="fw-bold text-primary">0</span> / 51</p>
            <p class="mb-0 fw-bold">Kategori: <span id="display_category" class="text-secondary">-</span></p>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <button type="submit" class="btn btn-submit rounded">Kirim Form</button>
            <p class="small text-muted align-self-center mb-0">Jangan kirim sandi melalui form ini.</p>
        </div>

    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const radios = document.querySelectorAll('.score-radio');
        const scoreDisplay = document.getElementById('display_score');
        const scoreInput = document.getElementById('total_score');
        const categoryDisplay = document.getElementById('display_category');

        // Questions input blocks to act like active focus in google form
        const cards = document.querySelectorAll('.card-question');
        cards.forEach(card => {
            card.addEventListener('click', () => {
                cards.forEach(c => c.classList.remove('active'));
                card.classList.add('active');
            });
        });

        function calculateScore() {
            let total = 0;
            const groups = ['lama_bisnis', 'omset', 'tantangan', 'target', 'alasan', 'posisi', 'investasi', 'kesiapan_hadir', 'keputusan', 'jumlah_karyawan', 'mengenal_coach'];
            
            groups.forEach(group => {
                const checked = document.querySelector(`input[name="${group}"]:checked`);
                if(checked) {
                    total += parseInt(checked.value);
                    // Set the label directly into hidden input
                    document.getElementById(group + '_label').value = checked.getAttribute('data-label');
                }
            });

            scoreDisplay.textContent = total;
            scoreInput.value = total;

            let category = '-';
            let color = '#6c757d';
            if (total > 0) {
                if (total < 25) {
                    category = 'COLD';
                    color = '#6c757d';
                } else if (total <= 40) {
                    category = 'WARM';
                    color = '#fd7e14'; // Orange
                } else {
                    category = 'HOT';
                    color = '#dc3545'; // Red
                }
            }
            categoryDisplay.textContent = category;
            categoryDisplay.style.color = color;
        }

        radios.forEach(radio => {
            radio.addEventListener('change', calculateScore);
        });

        // Load Provinsi
        fetch('/wilayah/provinsi')
            .then(response => response.json())
            .then(data => {
                const provinsiSelect = document.getElementById('provinsi');
                data.forEach(prov => {
                    const option = document.createElement('option');
                    option.value = prov.id;
                    option.textContent = prov.name;
                    provinsiSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching provinsi:', error));
    });

    function updateKota(provinsiId, provinsiNama) {
        document.getElementById('provinsi_nama').value = provinsiNama;
        const kotaSelect = document.getElementById('kota');
        kotaSelect.innerHTML = '<option value="">Memuat Kota...</option>';
        document.getElementById('kota_nama').value = '';

        if (!provinsiId) {
            kotaSelect.innerHTML = '<option value="">Pilih Kota...</option>';
            return;
        }

        fetch('/wilayah/kota/' + provinsiId)
            .then(response => response.json())
            .then(data => {
                kotaSelect.innerHTML = '<option value="">Pilih Kota...</option>';
                data.forEach(kota => {
                    const option = document.createElement('option');
                    option.value = kota.id;
                    option.textContent = kota.name;
                    kotaSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching kota:', error);
                kotaSelect.innerHTML = '<option value="">Gagal memuat kota</option>';
            });
    }
</script>

</body>
</html>
