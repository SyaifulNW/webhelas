@extends('layouts.masteradmin')

@section('content')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<div class="container-fluid px-2 pb-5">
    <h3 class="mb-4 py-3 fw-bold text-dark text-center" style="letter-spacing: 2px;">
        <i class="fas fa-chart-line me-2 text-primary"></i> DASHBOARD ADMINISTRATOR HELAS CORPORATION
    </h3>

    <ul class="nav nav-tabs border-0 mt-2 mb-0 d-flex flex-wrap gap-0 justify-content-center px-4" id="adminDashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold premium-tab color-primary" id="penjualan-tab" data-toggle="tab" data-target="#penjualan" type="button" role="tab" aria-controls="penjualan" aria-selected="true">
                <i class="fas fa-shopping-cart"></i> <span>Penjualan</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold premium-tab color-danger" id="keuangan-tab" data-toggle="tab" data-target="#keuangan" type="button" role="tab" aria-controls="keuangan" aria-selected="false">
                <i class="fas fa-wallet"></i> <span>Keuangan</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold premium-tab color-warning" id="operasional-tab" data-toggle="tab" data-target="#operasional" type="button" role="tab" aria-controls="operasional" aria-selected="false">
                <i class="fas fa-tasks"></i> <span>Operasional</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold premium-tab color-info" id="marketing-tab" data-toggle="tab" data-target="#marketing" type="button" role="tab" aria-controls="marketing" aria-selected="false">
                <i class="fas fa-bullhorn"></i> <span>Marketing</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold premium-tab color-success" id="sdm-tab" data-toggle="tab" data-target="#sdm" type="button" role="tab" aria-controls="sdm" aria-selected="false">
                <i class="fas fa-users-cog"></i> <span>SDM</span>
            </button>
        </li>
    </ul>

    <!-- Premium Divider with Dynamic Indicator -->
    <div class="premium-divider-container">
        <div class="premium-divider">
            <div id="tab-indicator" class="divider-indicator"></div>
        </div>
    </div>

    <div class="tab-content bg-white shadow-sm border" id="dashboardTabsContent" style="border-radius: 10px; position: relative;">
        <!-- Loading Overlay -->
        <div id="iframe-loading" class="d-none" style="position: absolute; top:0; left:0; right:0; bottom:0; background: rgba(255,255,255,0.7); z-index: 10; display: flex; align-items: center; justify-content: center; height: 300px;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">Loading...</span>
            </div>
        </div>

        <!-- Penjualan -->
        <div class="tab-pane fade show active" id="penjualan" role="tabpanel" aria-labelledby="penjualan-tab">
            <iframe src="{{ route('penjualan.index') }}?embed=true" style="width:100%; height:950px; border:none; border-radius: 0 0 10px 10px;" onload="resizeIframe(this); hideLoading();"></iframe>
        </div>
        
        <!-- Keuangan -->
        <div class="tab-pane fade" id="keuangan" role="tabpanel" aria-labelledby="keuangan-tab">
            <div class="sub-nav-container d-flex gap-4 p-3 bg-white border-bottom">
                <button class="btn sub-tab-btn btn-laba-rugi active-sub" onclick="switchKeuanganSub(this, '{{ route('admin.keuangan.laba-rugi') }}?embed=true')">
                    <i class="fas fa-file-invoice-dollar me-1"></i> Laporan Laba Rugi
                </button>
                <button class="btn sub-tab-btn btn-anggaran" onclick="switchKeuanganSub(this, '{{ route('admin.keuangan.pengajuan-anggaran') }}?embed=true')">
                    <i class="fas fa-hand-holding-usd me-1"></i> Pengajuan Anggaran
                </button>
            </div>
            <iframe id="iframe-keuangan" data-src="{{ route('admin.keuangan.laba-rugi') }}?embed=true" style="width:100%; height:950px; border:none; border-radius: 0 0 10px 10px;" onload="resizeIframe(this); hideLoading();"></iframe>
        </div>
        
        <!-- Operasional -->
        <div class="tab-pane fade" id="operasional" role="tabpanel" aria-labelledby="operasional-tab">
            <iframe data-src="{{ route('admin.operasional') }}?embed=true" style="width:100%; height:950px; border:none; border-radius: 0 0 10px 10px;" onload="resizeIframe(this); hideLoading();"></iframe>
        </div>
        
        <!-- Marketing -->
        <div class="tab-pane fade" id="marketing" role="tabpanel" aria-labelledby="marketing-tab">
            <iframe data-src="{{ route('marketing') }}?embed=true" style="width:100%; height:950px; border:none; border-radius: 0 0 10px 10px;" onload="resizeIframe(this); hideLoading();"></iframe>
        </div>
        
        <!-- SDM -->
        <div class="tab-pane fade" id="sdm" role="tabpanel" aria-labelledby="sdm-tab">
            <iframe data-src="{{ route('hr') }}?embed=true" style="width:100%; height:950px; border:none; border-radius: 0 0 10px 10px;" onload="resizeIframe(this); hideLoading();"></iframe>
        </div>
    </div>
</div>

<style>
    /* Premium Colored Tabs Styling (Horizontal - Attached) */
    .premium-tab {
        border-radius: 0px !important; /* Square for joined look */
        padding: 12px 25px !important;
        font-size: 1rem !important;
        font-weight: 700 !important;
        background: #f1f3f9;
        color: #4e73df;
        border: none !important;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 12px;
        margin-right: 2px !important; /* Tiny gap for border-left visibility */
        justify-content: center;
        min-width: 160px;
        height: 100%;
        border-left: 4px solid #ddd !important; /* Default border */
    }

    /* Round ends of the whole tab bar */
    .nav-item:first-child .premium-tab { border-top-left-radius: 10px !important; border-left: none !important; }
    .nav-item:last-child .premium-tab { border-top-right-radius: 10px !important; }

    .premium-tab i {
        font-size: 1.2rem;
    }

    /* Active State & Specific Colors - "Melekat" Fixed */
    .premium-tab.active {
        color: white !important;
        border-bottom: none !important;
        transform: none !important; /* Don't float */
        box-shadow: none !important;
        margin-bottom: -1px !important; /* Touch the line below */
        z-index: 10;
    }

    /* Menjadikan warna dasar semua tab solid dan tegas */
    .premium-tab.color-primary   { background: #4e73df !important; border-left-color: #2e59d9 !important; color: white !important; }
    .premium-tab.color-danger    { background: #e74a3b !important; border-left-color: #be2617 !important; color: white !important; }
    .premium-tab.color-warning   { background: #f6c23e !important; border-left-color: #df9c00 !important; color: #111 !important; }
    .premium-tab.color-info      { background: #6610f2 !important; border-left-color: #520dc2 !important; color: white !important; }
    .premium-tab.color-success   { background: #28a745 !important; border-left-color: #1e7e34 !important; color: white !important; }

    /* Active State: Sedikit lebih terang dan menonjol */
    .premium-tab.active {
        filter: brightness(1.1) !important;
        box-shadow: 0 -3px 8px rgba(0,0,0,0.1) !important;
    }
    
    /* Warna teks khusus untuk active tab agar tetap terbaca */
    .premium-tab.active.color-warning { color: #111 !important; }

    /* Inactive State: Sedikit lebih gelap agar membedakan dengan active */
    .premium-tab:not(.active) { 
        filter: brightness(0.85) contrast(1.1);
        opacity: 0.95;
    }

    .premium-tab:hover:not(.active) {
        filter: brightness(0.95);
        transform: translateY(-2px);
    }

    .premium-divider-container {
        padding: 0;
        margin-top: -1px; /* Overlap with tabs */
        margin-bottom: 2rem;
        z-index: 2;
    }
    
    /* Divider Styling */
    .premium-divider-container {
        padding: 0 2rem;
        margin-bottom: 2.5rem;
    }
    .premium-divider {
        height: 2px;
        background: #e3e6f0;
        border-radius: 10px;
        position: relative;
    }
    .divider-indicator {
        position: absolute;
        height: 4px;
        background: #4e73df;
        border-radius: 10px;
        top: -1px;
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        box-shadow: 0 2px 8px rgba(78, 115, 223, 0.4);
        width: 100px;
        left: 0;
    }

    /* Color-specific indicators (optional, matching the active tab color) */
    .bg-primary-indicator { background: #4e73df !important; box-shadow: 0 2px 8px rgba(78, 115, 223, 0.4); }
    .bg-danger-indicator  { background: #e74a3b !important; box-shadow: 0 2px 8px rgba(231, 74, 59, 0.4); }
    .bg-warning-indicator { background: #f6c23e !important; box-shadow: 0 2px 8px rgba(246, 194, 62, 0.4); }
    .bg-info-indicator    { background: #36b9cc !important; box-shadow: 0 2px 8px rgba(54, 185, 204, 0.4); }
    .bg-success-indicator { background: #1cc88a !important; box-shadow: 0 2px 8px rgba(28, 200, 138, 0.4); }

    /* Sub-Navigation Styling */
    .sub-nav-container {
        border-radius: 10px 10px 0 0;
        background: #f8f9fc !important;
    }
    .sub-tab-btn {
        background: white;
        font-weight: 700;
        font-size: 0.9rem;
        padding: 10px 25px;
        border-radius: 12px !important;
        transition: all 0.3s ease;
        border: 2px solid !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .sub-tab-btn i {
        font-size: 1.1rem;
    }

    /* Warna Tombol Laba Rugi (Merah) */
    .btn-laba-rugi {
        border-color: #e74a3b !important;
        color: #e74a3b !important;
    }
    .btn-laba-rugi:hover, .btn-laba-rugi.active-sub {
        background: #e74a3b !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(231, 74, 59, 0.4) !important;
        transform: translateY(-2px);
    }

    /* Warna Tombol Anggaran (Ungu/Indigo) */
    .btn-anggaran {
        border-color: #6610f2 !important;
        color: #6610f2 !important;
    }
    .btn-anggaran:hover, .btn-anggaran.active-sub {
        background: #6610f2 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(102, 16, 242, 0.4) !important;
        transform: translateY(-2px);
    }

    /* Ensure no scrollbar appears around iframes unnecessarily */
    iframe {
        display: block;
        background-color: #f8f9fc;
        border-radius: 10px;
    }
</style>

<script>
    function switchKeuanganSub(btn, url) {
        showLoading();
        // Update active class
        document.querySelectorAll('.sub-tab-btn').forEach(b => b.classList.remove('active-sub'));
        btn.classList.add('active-sub');
        
        // Update iframe source
        document.getElementById('iframe-keuangan').src = url;
    }

    function showLoading() {
        document.getElementById('iframe-loading').classList.remove('d-none');
    }
    
    function hideLoading() {
        document.getElementById('iframe-loading').classList.add('d-none');
    }
    
    function resizeIframe(obj) {
        // Attempt to adjust height based on content to prevent double scrollbars
        try {
            if(obj.contentWindow.document.body.scrollHeight > 500) {
                obj.style.height = obj.contentWindow.document.body.scrollHeight + 100 + 'px';
            }
        } catch(e) {
            // Cannot access cross-origin, but we are on same origin here so it should work
            // unless page hasn't fully painted.
            console.log("Resize applied");
        }
    }

    function moveIndicator(target) {
        const indicator = document.getElementById('tab-indicator');
        const divider = document.querySelector('.premium-divider');
        const tab = target instanceof jQuery ? target[0] : target;
        
        if (!indicator || !divider || !tab) return;

        const tabRect = tab.getBoundingClientRect();
        const dividerRect = divider.getBoundingClientRect();
        
        // Calculate position relative to divider
        const leftPos = tabRect.left - dividerRect.left + (tabRect.width / 2) - 50;
        
        indicator.style.left = leftPos + 'px';
        
        // Match color with tab
        indicator.className = 'divider-indicator'; // reset
        if (tab.classList.contains('color-primary')) indicator.classList.add('bg-primary-indicator');
        if (tab.classList.contains('color-danger'))  indicator.classList.add('bg-danger-indicator');
        if (tab.classList.contains('color-warning')) indicator.classList.add('bg-warning-indicator');
        if (tab.classList.contains('color-info'))    indicator.classList.add('bg-info-indicator');
        if (tab.classList.contains('color-success')) indicator.classList.add('bg-success-indicator');
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Initial indicator position
        setTimeout(() => {
            const activeTab = document.querySelector('.premium-card-tab.active');
            if (activeTab) moveIndicator(activeTab);
        }, 500);

        // Handle tab switching
        $('button[data-toggle="tab"]').on('show.bs.tab', function (e) {
            moveIndicator(e.target);
            
            let targetDiv = $(e.target).data('target');
            let iframe = $(targetDiv).find('iframe');
            
            // If iframe hasn't loaded its actual src yet
            if(iframe.attr('data-src')) {
                showLoading(); // show loading indicator
                iframe.attr('src', iframe.attr('data-src'));
                iframe.removeAttr('data-src');
            }
        });
    });
</script>
@endsection
