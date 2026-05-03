@extends('layouts.masteradmin')

@section('content')
<!-- Bootstrap 5 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap 5 JS Bundle (termasuk Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="container-fluid py-4">

    {{-- Header --}}
    <h2 class="fw-bold text-primary mb-4">
        <i class="fa-solid fa-cart-shopping me-2"></i> Dashboard Penjualan
    </h2>

    <form id="filterForm" action="{{ route('penjualan.index') }}" method="GET">
        <div class="row g-3" id="table-container">
            @include('penjualan.table')
        </div>
    </form>

</div>
<style>
    /* HIDE SIDEBAR & TOPBAR as requested */
    #accordionSidebar { display: none !important; }
    #content-wrapper { margin-left: 0 !important; }
    .topbar { display: none !important; }
</style>

<script>
function applyFilters() {
    const filterForm = document.getElementById('filterForm');
    if (!filterForm) return;

    const url = new URL(filterForm.action);
    const formData = new FormData(filterForm);
    const params = new URLSearchParams(formData);

    const tableContainer = document.getElementById('table-container');
    if (tableContainer) tableContainer.style.opacity = '0.5';

    fetch(url.pathname + '?' + params.toString(), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        if (tableContainer) {
            tableContainer.innerHTML = html;
            tableContainer.style.opacity = '1';

            // 🚀 Force execution of scripts inside reloaded HTML (important for Chart initialization)
            const scripts = tableContainer.querySelectorAll('script');
            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');
                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                newScript.appendChild(document.createTextNode(oldScript.innerHTML));
                oldScript.parentNode.replaceChild(newScript, oldScript);
            });
        }
    })

    .catch(error => {
        console.error('Error fetching data:', error);
        if (tableContainer) tableContainer.style.opacity = '1';
    });
}

var salesChart = null;

function initSalesChart(datasets) {
    const ctx = document.getElementById('salesCsChart');
    if (!ctx) return;

    if (salesChart) {
        salesChart.destroy();
    }

    // Chart.js 2.x Syntax (Standard for SB Admin 2)
    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            datasets: datasets
        },
        plugins: [],
        options: {
            maintainAspectRatio: false,
            responsive: true,
            layout: {
                padding: {
                    left: 10,
                    right: 25,
                    top: 35, // Increased top padding to avoid text clipping
                    bottom: 0
                }
            },
            scales: {
                xAxes: [{
                    time: { unit: 'date' },
                    gridLines: { display: false, drawBorder: false },
                    ticks: { maxTicksLimit: 12 }
                }],
                yAxes: [{
                    ticks: {
                        maxTicksLimit: 8,
                        padding: 10,
                        callback: function(value) { return 'Rp ' + number_format(value); }
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: true,
                position: 'bottom',
                labels: { boxWidth: 12, padding: 20 }
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: true,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
                callbacks: {
                    label: function(tooltipItem, data) {
                        var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
                        return datasetLabel + ': Rp ' + number_format(tooltipItem.yLabel);
                    }
                }
            }
        }
    });
}

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(',', '').replace(' ', '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? '.' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? ',' : dec_point,
        s = '',
        toFixedFix = function(n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) { s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep); }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

document.addEventListener('DOMContentLoaded', function() {
    // Check if there's pending chart data
    if (window.pendingChartData) {
        initSalesChart(window.pendingChartData);
        delete window.pendingChartData;
    }

    // Event delegation for dynamically added selects
    document.addEventListener('change', function(e) {
        if (e.target && (e.target.tagName === 'SELECT')) {
            const form = e.target.closest('#filterForm');
            if (form) {
                applyFilters();
            }
        }
    });
});

</script>
<style>
    /* Custom Table Enhancements */
    .custom-target-table th {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #eaeaea;
    }
    
    .custom-target-table td {
        border-bottom: 1px solid #f2f2f2;
        padding-top: 1rem;
        padding-bottom: 1rem;
    }

    .hover-lift {
        transition: transform 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .hover-lift:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        background-color: #fbfcff;
        z-index: 10;
        position: relative;
    }
    
    .bg-gradient.bg-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%) !important;
    }

    /* Make table borders thicker */
    .table-bordered th, 
    .table-bordered td,
    .table-bordered {
        border-width: 2px !important;
        border-color: #d1d3e2 !important;
    }
</style>
@endsection
