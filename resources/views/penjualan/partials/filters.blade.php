<!-- Tahun -->
<select name="tahun" id="tahun" class="form-select form-select-sm border-0 rounded-pill text-dark fw-bold shadow-sm" style="width: 85px; font-size:0.75rem;" onchange="applyFilters()">
    @php $currentYear = date('Y'); @endphp
    @for($y = $currentYear; $y >= $currentYear - 3; $y--)
        <option value="{{ $y }}" {{ request('tahun', $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
    @endfor
</select>

<!-- Bulan -->
<select name="bulan" id="bulan" class="form-select form-select-sm border-0 rounded-pill text-dark fw-bold shadow-sm" style="width: 120px; font-size:0.75rem;" onchange="applyFilters()">
    <option value="all" {{ request('bulan') == 'all' ? 'selected' : '' }}>Semua Bulan</option>
    @foreach(range(1, 12) as $m)
        <option value="{{ $m }}" {{ request('bulan', date('n')) == $m ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
        </option>
    @endforeach
</select>

<!-- Kategori Program -->
<select name="type" id="type" class="form-select form-select-sm border-0 rounded-pill text-dark fw-bold shadow-sm" style="width: 160px; font-size:0.75rem;" onchange="applyFilters()">
    <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>Semua Program</option>
    <option value="mbc"  {{ request('type') == 'mbc'  ? 'selected' : '' }}>MBC</option>
    <option value="m1t"  {{ request('type') == 'm1t'  ? 'selected' : '' }}>M1T</option>
</select>
