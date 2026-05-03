@extends('layouts.masteradmin')

@section('content')
<style>
    /* ✨ Tampilan Umum */
    .page-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e3a8a;
        letter-spacing: 0.5px;
    }

    /* 🎯 Filter Section */
    .filter-container {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .filter-container .form-control {
        font-size: 0.9rem;
    }

    .filter-container .btn-danger {
        font-size: 0.85rem;
        padding: 0.45rem 0.8rem;
    }

    /* 🖼️ PDF Preview */
    .pdf-preview {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 3px 8px rgba(0,0,0,0.05);
        margin-top: 1rem;
        background: #f8fafc;
    }

    iframe {
        width: 100%;
        height: 80vh;
        border: none;
        border-radius: 8px;
    }

    @media (max-width: 768px) {
        iframe {
            height: 60vh;
        }
    }
</style>

<div class="container my-4">
    <div class="text-center mb-4">
        <div class="page-title"> Monitoring Daily Activity CS</div>
    </div>

{{-- 🔍 Filter Bulan & CS --}}
<style>
    .filter-container {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.8rem 1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #1e3a8a;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        white-space: nowrap;
    }

    .filter-label i {
        color: #475569;
        font-size: 0.9rem;
    }

    .form-control {
        font-size: 0.9rem;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 0.35rem 0.75rem;
        min-width: 180px;
        transition: 0.2s ease;
    }

    .form-control:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 0.15rem rgba(37, 99, 235, 0.25);
    }

    @media (max-width: 768px) {
        .filter-container {
            flex-direction: column;
            align-items: flex-start;
        }
        .form-control {
            width: 100%;
        }
    }
</style>

<div class="filter-container mb-3">
    <form action="" method="get" class="d-flex flex-wrap align-items-center gap-3">

        {{-- Bulan --}}
        <div class="filter-group">

            <input type="month" id="bulan" name="bulan" value="{{ $bulan }}"
                class="form-control shadow-sm"
                onchange="this.form.submit()">
        </div>
        &nbsp;     &nbsp;     &nbsp;
        {{-- CS --}}
        <div class="filter-group">
         <!--   <label for="cs_id" class="filter-label">-->
         <!--Pilih CS-->
         <!--   </label>-->
            <select id="cs_id" name="cs_id" class="form-control shadow-sm"
                onchange="this.form.submit()">
                <option value="">-- Pilih CS --</option>
                @foreach($csList as $cs)
                    <option value="{{ $cs->id }}" {{ $csId == $cs->id ? 'selected' : '' }}>
                        {{ $cs->name }}
                    </option>
                @endforeach
            </select>
        </div>

    </form>
</div>

{{-- Font Awesome --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- 📄 Preview PDF --}}
    @if($csId)
    <div class="pdf-preview">
        <iframe 
            src="{{ route('admin.activity-cs.viewPdfBulanan', ['bulan' => $bulan, 'cs_id' => $csId]) }}" 
            frameborder="0">
        </iframe>
    </div>
    @else
        <p class="text-center text-muted mt-3">Silakan pilih bulan dan CS untuk melihat laporan.</p>
    @endif
</div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
