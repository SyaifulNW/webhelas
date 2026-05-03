@extends('layouts.masteradmin')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow mb-4">
            <div class="card-body p-5">
                <!-- Header -->
                <div class="d-flex align-items-center mb-4">
                    <img src="{{ asset('backend/logosmi.png') }}" alt="SMI Logo" style="height: 80px; width: auto;" class="mr-4">
                    <div class="text-center w-100">
                        <h2 class="font-weight-bold text-success mb-1">SCALE UP MUSLIM INSTITUTE (SMI)</h2>
                        <p class="mb-0">Jl Majapahit V No 35 Nusukan, Banjarsari Surakarta, Jawa Tengah</p>
                    </div>
                </div>

                <div class="text-center mb-4">
                    <h4 class="font-weight-bold">KARTU RENCANA STUDI (KRS)</h4>
                    <h4 class="font-weight-bold">SCALE UP MUSLIM INSTITUTE (SMI)</h4>
                </div>

                <!-- Info Fields -->
                <div class="mb-4">
                    <table class="table table-borderless table-sm w-auto">
                        <tr>
                            <td class="font-weight-bold" style="width: 150px;">Nama</td>
                            <td>: 
                                <select class="form-control d-inline-block p-1 border-bottom border-top-0 border-left-0 border-right-0" style="width: 300px; height: auto; border-radius: 0; outline: none; background: transparent;" id="selectNama">
                                    <option value="">- Pilih Nama Peserta -</option>
                                    @foreach($peserta as $p)
                                        <option value="{{ $p->id }}" data-induk="{{ $p->id }}">{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">No Induk</td>
                            <td>: <input type="text" id="noInduk" class="border-bottom border-0" style="width: 300px; outline: none; background: transparent;" readonly></td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Program</td>
                            <td>: <span class="border-bottom d-inline-block" style="width: 300px;">SMI Semester 1 Tahun 2026</span></td>
                        </tr>
                    </table>
                </div>

                <!-- Table Content -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm" style="font-size: 0.9rem; border: 1px solid #000;">
                        <thead class="bg-light text-center font-weight-bold">
                            <tr>
                                <th style="width: 5%; border: 1px solid #000;">NO</th>
                                <th style="width: 25%; border: 1px solid #000;">MATERI UTAMA</th>
                                <th style="width: 40%; border: 1px solid #000;">TUJUAN PEMBELAJARAN</th>
                                <th style="width: 15%; border: 1px solid #000;">FASILITATOR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- A. BISNIS EXPERT -->
                            <tr class="bg-light font-weight-bold"><td colspan="4" class="text-center" style="border: 1px solid #000;">A. BISNIS EXPERT</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">1</td><td style="border: 1px solid #000;">Lean Canvas</td><td style="border: 1px solid #000;">Pemetaan ide bisnis dalam 1 halaman.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">2</td><td style="border: 1px solid #000;">Value Proposition Canvas</td><td style="border: 1px solid #000;">Merumuskan nilai unik produk/jasa yang ditawarkan.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">3</td><td style="border: 1px solid #000;">Personal Branding Canvas</td><td style="border: 1px solid #000;">Membentuk citra diri sebagai founder yang kredibel dan dikenali.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">4</td><td style="border: 1px solid #000;">Persona Canvas</td><td style="border: 1px solid #000;">Identifikasi profil pelanggan ideal (Customer Persona).</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">5</td><td style="border: 1px solid #000;">Customer Journey Map</td><td style="border: 1px solid #000;">Memetakan pengalaman pelanggan dari awal hingga loyalitas.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">6</td><td style="border: 1px solid #000;">Product Market Canvas</td><td style="border: 1px solid #000;">Menyesuaikan produk dengan kebutuhan pasar yang spesifik.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">7</td><td style="border: 1px solid #000;">BMC Versi Jobs To Be Done</td><td style="border: 1px solid #000;">Penyusunan model bisnis dengan pendekatan "Jobs To Be Done".</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">8</td><td style="border: 1px solid #000;">Perhitungan Laba Rugi Untuk Ukm</td><td style="border: 1px solid #000;">Teknik menghitung profitabilitas usaha skala kecil-menengah.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">9</td><td style="border: 1px solid #000;">Cara Mengelola Cash Untuk Ukm</td><td style="border: 1px solid #000;">Manajemen arus kas operasional harian/bulanan.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">10</td><td style="border: 1px solid #000;">Cara Ukm Membaca Keuangan</td><td style="border: 1px solid #000;">Interpretasi laporan keuangan sederhana untuk pengambilan keputusan.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">11</td><td style="border: 1px solid #000;">Creating A Brand Canvas</td><td style="border: 1px solid #000;">Membangun identitas dan positioning merek.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">12</td><td style="border: 1px solid #000;">SOP Builder</td><td style="border: 1px solid #000;">Alat/template untuk membuat prosedur operasi standar secara sistematis</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">13</td><td style="border: 1px solid #000;">Pitch Deck</td><td style="border: 1px solid #000;">Presentasi singkat (biasanya 10-15 slide) untuk "menjual" ide bisnis kepada investor</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>

                            <!-- B. DIGITAL MARKETING -->
                            <tr class="bg-light font-weight-bold"><td colspan="4" class="text-center" style="border: 1px solid #000;">B. DIGITAL MARKETING</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">14</td><td style="border: 1px solid #000;">Meta Ads: Facebook dan IG</td><td style="border: 1px solid #000;">Strategi pembuatan dan optimasi iklan di platform Meta.</td><td style="border: 1px solid #000;">Eko Sulistiono</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">15</td><td style="border: 1px solid #000;">TikTok Ads</td><td style="border: 1px solid #000;">Teknik pembuatan konten iklan dan kampanye di TikTok.</td><td style="border: 1px solid #000;">Eko Sulistiono</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">16</td><td style="border: 1px solid #000;">Marketplace</td><td style="border: 1px solid #000;">Strategi listing, ranking, dan konversi penjualan di marketplace.</td><td style="border: 1px solid #000;">Eko Sulistiono</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">17</td><td style="border: 1px solid #000;">Artificial Intelligence (AI)</td><td style="border: 1px solid #000;">Pemanfaatan tool AI (ChatGPT, Midjourney, dll.) untuk efisiensi operasi dan marketing.</td><td style="border: 1px solid #000;">Lirvania Felmi N.</td></tr>

                            <!-- C. LEADERSHIP -->
                            <tr class="bg-light font-weight-bold"><td colspan="4" class="text-center" style="border: 1px solid #000;">C. LEADERSHIP</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">18</td><td style="border: 1px solid #000;">Growth Mindset</td><td style="border: 1px solid #000;">Mengembangkan pola pikir bertumbuh dan adaptif.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">19</td><td style="border: 1px solid #000;">7 Habits for Highly Effective People</td><td style="border: 1px solid #000;">Membangun kebiasaan produktif dan efektif dalam kepemimpinan.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">20</td><td style="border: 1px solid #000;">Grit Mentality</td><td style="border: 1px solid #000;">Membangun ketahanan mental dan konsistensi dalam menghadapi tantangan.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">21</td><td style="border: 1px solid #000;">Rich Dad Poor Dad</td><td style="border: 1px solid #000;">Memahami literasi keuangan dan mindset investasi dari perspektif entrepreneur.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">22</td><td style="border: 1px solid #000;">Ikigai</td><td style="border: 1px solid #000;">Menemukan tujuan hidup dan passion yang sejalan dengan bisnis.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">23</td><td style="border: 1px solid #000;">Integrasi Project: Business Pitch</td><td style="border: 1px solid #000;">Penyusunan presentasi bisnis lengkap (menggabungkan Lean Canvas, BMC, VPC, dll.).</td><td style="border: 1px solid #000;">Tim Fasilitator</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">24</td><td style="border: 1px solid #000;">E-Course Review & Aplikasi</td><td style="border: 1px solid #000;">Sesi tanya jawab dan panduan aplikasi materi e-course (Landing Page, Copywriting, dll.).</td><td style="border: 1px solid #000;">Tim Fasilitator</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">25</td><td style="border: 1px solid #000;">Final Project Presentation & Review</td><td style="border: 1px solid #000;">Presentasi akhir proyek bisnis peserta dan penutupan program.</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">26</td><td style="border: 1px solid #000;">Internship di Perusahaan</td><td style="border: 1px solid #000;">Magang di Perusahaan Mitra SMI</td><td style="border: 1px solid #000;">Tim SMI</td></tr>
                            <tr><td class="text-center" style="border: 1px solid #000;">27</td><td style="border: 1px solid #000;">Review</td><td style="border: 1px solid #000;">Bisnis Expert, Literasi Digital dan Leadership</td><td style="border: 1px solid #000;">Fitra Jaya Saleh</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tambahan Materi -->
                <div class="mt-4 border" style="border: 2px solid #000 !important;">
                    <h6 class="font-weight-bold text-center border-bottom mb-0 py-2 bg-light" style="border-bottom: 2px solid #000 !important;">TAMBAHAN MATERI (E-COURSE)</h6>
                    <div class="row no-gutters" style="font-size: 0.9rem;">
                        <div class="col-md-6 border-right" style="border-right: 2px solid #000 !important;">
                            <table class="table table-sm mb-0">
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">1</td><td>Kelas FB PRO</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">2</td><td>Sosial media marketing Bonus Super WP</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">3</td><td>Instagram Hack</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">4</td><td>Whatsapp Marketing</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">5</td><td>Video Pembelajaran Capcut</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">6</td><td>Belajar Editing Menggunakan Capcut</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">7</td><td>Jago Membuat Landing Page</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm mb-0">
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">8</td><td>Copywriting</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">9</td><td>Desain via Powerpoint</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">10</td><td>Youtube Master</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">11</td><td>SEO Mastery</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">12</td><td>Web Mastery</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">13</td><td>Affiliate Marketing</td></tr>
                                <tr><td class="text-center" style="width: 30px; border-right: 1px solid #000;">14</td><td>Traffic Mendatangkan Calon Pembeli via Medsos</td></tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Signatures -->
                <div class="row pt-5 mt-4">
                    <div class="col-6 text-center">
                        <br>
                        <p class="mb-5 font-weight-bold">Menyetujui</p>
                        <br><br>
                        <p class="mb-0">( .................................................... )</p>
                        <p class="font-weight-bold">Koordinator Program SMI</p>
                    </div>
                    <div class="col-6 text-center">
                        <p>Surakarta, .................... 2025</p>
                        <p class="mb-5 font-weight-bold">Diisi</p>
                        <br><br>
                        <p class="mb-0">( .................................................... )</p>
                        <p class="font-weight-bold">Peserta</p>
                    </div>
                </div>

                <!-- Print Button -->
                <div class="text-center mt-5 no-print">
                    <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Cetak PDF</button>
                    <a href="{{ url()->previous() }}" class="btn btn-secondary ml-2">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-sm td, .table-sm th { padding: 4px 8px; }
    @media print {
        .no-print { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
        .card-body { padding: 0 !important; }
        .form-control { border: none !important; -webkit-appearance: none; -moz-appearance: none; appearance: none; padding: 0 !important; }
        select { border: none !important; }
        body { background-color: white !important; }
        .main-panel { padding: 0 !important; }
        .content-wrapper { padding: 0 !important; background: white !important; }
        .row { margin: 0 !important; }
        .col-lg-10 { width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectNama = document.getElementById('selectNama');
        const noIndukInput = document.getElementById('noInduk');

        selectNama.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const noInduk = selectedOption.getAttribute('data-induk');
            noIndukInput.value = noInduk || '';
        });
    });
</script>
@endsection
