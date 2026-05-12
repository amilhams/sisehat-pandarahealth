@extends('layouts.app')
@section('title', 'Ikhtisar Ekosistem')

@section('styles')
<style>
/* --- Header --- */
.dash-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:32px; }
.dash-header h1 { font-size:26px; font-weight:700; margin-bottom:4px; }
.dash-header p { font-size:13px; color:var(--text-secondary); max-width:500px; }
.header-actions { display:flex; gap:10px; flex-shrink:0; align-items:center; }
.btn-sm { display:inline-flex; align-items:center; gap:6px; padding:9px 16px; font-size:12px; font-weight:600; border-radius:8px; border:none; cursor:pointer; transition:all 0.2s; white-space:nowrap; }
.btn-sm-outline { background:transparent; border:1px solid var(--border-color); color:var(--text-primary); }
.btn-sm-outline:hover { background:var(--card-color); }
.btn-sm-solid { background:var(--text-primary); color:#000; }
.btn-sm-solid:hover { background:#e5e5e5; }

/* --- Stat Cards --- */
.stat-row { display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:32px; }
.stat-card2 {
    background:var(--card-color);
    border: 1px solid var(--border-color);
    border-radius:14px;
    padding:20px;
    display:flex;
    flex-direction:column;
    gap:14px;
    transition: border-color 0.2s;
}
.stat-card2:hover { border-color: #3a3a3a; }
.stat-top { display:flex; justify-content:space-between; align-items:flex-start; }
.stat-icon-box {
    width:42px; height:42px;
    background:#1e1e1e;
    border:1px solid var(--border-color);
    border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:18px;
    color:var(--text-secondary);
}
.stat-bottom { display:flex; flex-direction:column; gap:5px; }
.stat-label-txt { font-size:10px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:.6px; }
.stat-val { font-size:26px; font-weight:700; line-height:1.1; }
.stat-val small { font-size:13px; font-weight:400; color:var(--text-secondary); margin-left:2px; }
.badge { display:inline-flex; align-items:center; font-size:10px; font-weight:600; padding:3px 8px; border-radius:6px; gap:3px; }
.badge-up   { background:rgba(74,222,128,.12); color:#4ade80; }
.badge-stabil { background:rgba(74,222,128,.12); color:#4ade80; }
.badge-kritis { background:rgba(248,113,113,.12); color:#f87171; }
.badge-warn { background:rgba(250,204,21,.12); color:#facc15; }

/* --- Main Grid --- */
.main-grid { display:grid; grid-template-columns:1fr 1.5fr; gap:20px; margin-bottom:20px; }
.left-col { display:flex; flex-direction:column; gap:16px; }
.card2 { background:var(--card-color); border:1px solid var(--border-color); border-radius:14px; padding:22px; }
.card2-title { font-size:14px; font-weight:600; margin-bottom:18px; display:flex; justify-content:space-between; align-items:center; }

/* --- Health Indicator --- */
.health-card { background:var(--card-color); border:1px solid var(--border-color); border-radius:14px; padding:24px; display:flex; flex-direction:column; }
.health-gauge-wrap { position:relative; width:320px; height:160px; margin:10px auto 30px; }
.health-gauge-label { position:absolute; bottom:0; left:50%; transform:translateX(-50%); text-align:center; }
.health-pct { font-size:48px; font-weight:800; line-height:1; }
.health-sub { font-size:11px; color:var(--text-secondary); letter-spacing:.8px; margin-top:2px; }
/* Factor bars — full width 2-col */
.factor-bar-row { display:grid; grid-template-columns:1fr 1fr; gap:12px 24px; margin-top:auto; }
.f-item { }
.f-label { display:flex; justify-content:space-between; font-size:10px; text-transform:uppercase; color:var(--text-secondary); letter-spacing:.3px; margin-bottom:5px; }
.f-track { height:5px; background:#222; border-radius:3px; }
.f-fill { height:100%; border-radius:3px; transition: width 1s ease; }

/* --- Bottom Grid --- */
.bottom-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
.h-bar-row { display:flex; align-items:center; gap:10px; margin-bottom:11px; font-size:12px; }
.h-bar-label { min-width:65px; color:var(--text-secondary); text-align:right; }
.h-bar-track { flex:1; height:6px; background:#222; border-radius:3px; }
.h-bar-fill { height:100%; border-radius:3px; background:#fff; }
.h-bar-val { min-width:32px; text-align:right; color:var(--text-secondary); }

/* --- Table --- */
.rank-table { width:100%; border-collapse:collapse; font-size:13px; }
.rank-table th { padding:12px 16px; color:var(--text-secondary); font-size:10px; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid var(--border-color); text-align:left; }
.rank-table td { padding:15px 16px; border-bottom:1px solid var(--border-color); }
.rank-table tr:last-child td { border-bottom:none; }
.rank-num { width:28px; height:28px; background:#222; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; }
.rank-score { font-size:20px; font-weight:700; }

/* Doughnut center text via plugin */
.donut-center-wrap { position:relative; }
.donut-center-wrap canvas { display:block; }
.donut-center-label { position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none; }
.donut-center-label .dc-val { font-size:14px; font-weight:700; }
.donut-center-label .dc-sub { font-size:9px; color:var(--text-secondary); letter-spacing:.3px; margin-top:1px; }

/* --- UMKM Dropdown --- */
.umkm-select {
    background: #1a1a1a;
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 6px;
    outline: none;
    cursor: pointer;
}
.umkm-select:hover { border-color: #444; }
    /* Skeleton Loader Animation */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
</style>
@endsection

@section('content')

{{-- Header --}}
<div class="dash-header">
    <div>
        <h1>Ikhtisar Ekosistem</h1>
        <p>Analisis kesehatan operasional dari seluruh mitra UMKM terdaftar dalam platform Pandara Health.</p>
    </div>
    <div class="header-actions">
        <button class="btn-sm btn-sm-outline" onclick="window.location='{{ route('assessment') }}'">
            <i class="fa-solid fa-file-pen"></i> Isi Assessment
        </button>
        <button class="btn-sm btn-sm-solid" onclick="window.location='{{ route('comparison') }}'">
            <i class="fa-solid fa-chart-simple"></i> Lihat Analisis Detail
        </button>
    </div>
</div>

{{-- 5 Stat Cards --}}
<div class="stat-row">
    <div class="stat-card2">
        <div class="stat-top">
            <div class="stat-icon-box"><i class="fa-solid fa-building"></i></div>
            <span class="badge badge-up">↑ AKTIF</span>
        </div>
        <div class="stat-bottom">
            <span class="stat-label-txt">Total UMKM Terdaftar</span>
            <span class="stat-val">{{ number_format($stats['total_umkm']) }}</span>
        </div>
    </div>
    <div class="stat-card2">
        <div class="stat-top">
            <div class="stat-icon-box"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="stat-bottom">
            <span class="stat-label-txt">Total Responden</span>
            <span class="stat-val">{{ number_format($stats['total_respondents']) }}</span>
        </div>
    </div>
    <div class="stat-card2">
        <div class="stat-top">
            <div class="stat-icon-box"><i class="fa-solid fa-heart-pulse"></i></div>
        </div>
        <div class="stat-bottom">
            <span class="stat-label-txt">Kesehatan Rata-rata</span>
            <span class="stat-val">{{ $stats['avg_health'] }} <small>/ 100</small></span>
        </div>
    </div>
    <div class="stat-card2">
        <div class="stat-top">
            <div class="stat-icon-box"><i class="fa-solid fa-circle-check"></i></div>
            <span class="badge badge-stabil">SEHAT</span>
        </div>
        <div class="stat-bottom">
            <span class="stat-label-txt">UMKM Sehat</span>
            <span class="stat-val">{{ number_format($stats['sehat_count']) }}</span>
        </div>
    </div>
    <div class="stat-card2">
        <div class="stat-top">
            <div class="stat-icon-box"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <span class="badge badge-kritis">KRITIS</span>
        </div>
        <div class="stat-bottom">
            <span class="stat-label-txt">UMKM Krisis</span>
            <span class="stat-val">{{ number_format($stats['kritis_count']) }}</span>
        </div>
    </div>
</div>

{{-- Main 2-col Grid --}}
<div class="main-grid" style="align-items: stretch;">
    {{-- Distribusi Jenis Bisnis --}}
    <div class="card2" style="display: flex; flex-direction: column;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <div style="font-size:15px; font-weight:600;">Distribusi Jenis Bisnis</div>
            <i class="fa-solid fa-ellipsis" style="color:var(--text-secondary); cursor:pointer;"></i>
        </div>
        
        @php
            $colors = ['#ffffff', '#a3a3a3', '#555555', '#222222', '#888888'];
            $totalBiz = $biz_types->sum();
            $i = 0;
        @endphp
        
        <div style="display:flex; align-items:center; gap:40px; padding:10px 0; flex: 1;">
            <div class="donut-center-wrap" style="width:160px; height:160px; flex-shrink:0; position:relative;">
                <canvas id="bizTypeChart"></canvas>
                <div class="donut-center-label" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); text-align:center;">
                    <div style="font-size:18px; font-weight:800; line-height:1;">{{ $totalBiz }}</div>
                    <div style="font-size:10px; color:var(--text-secondary); margin-top:4px; font-weight:500;">Total UMKM</div>
                </div>
            </div>
            
            <ul style="list-style:none; font-size:13px; display:flex; flex-direction:column; justify-content:center; gap:14px; flex:1;">
                @forelse($biz_types as $label => $count)
                    @php 
                        $color = $colors[$i % count($colors)];
                        $i++;
                    @endphp
                    <li style="display:flex; align-items:center; gap:12px;">
                        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:{{ $color }}; flex-shrink:0;"></span>
                        <span style="color:var(--text-primary); font-weight:500;">
                            {{ $label ?: 'Lainnya' }} 
                            <span style="color:var(--text-secondary); font-size:12px; font-weight:400; margin-left:4px;">({{ $count }} UMKM)</span>
                        </span>
                    </li>
                @empty
                    <li style="color:var(--text-secondary); font-style:italic;">Belum ada data</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- Indikator Kesehatan Organisasi --}}
    <div class="health-card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
            <div>
                <div style="font-size:14px;font-weight:600;display:flex;align-items:center;gap:8px;">
                    Indikator Kesehatan Organisasi
                    @if(isset($all_umkms) && $all_umkms->count() > 1)
                        <select class="umkm-select" onchange="window.location.href='?umkm_id=' + this.value">
                            @foreach($all_umkms as $u)
                                <option value="{{ $u->umkm_id }}" {{ $selected_umkm_id == $u->umkm_id ? 'selected' : '' }}>
                                    {{ $u->nama_umkm }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">Data dari UMKM Anda</div>
            </div>
            @if($has_assessment && $my_avg_health > 0)
                @php
                    if ($my_avg_health > 75) { $bClass = 'badge-stabil'; $bColor = '#4ade80'; $bBg = 'rgba(74,222,128,.12)'; }
                    elseif ($my_avg_health > 50) { $bClass = 'badge-stabil'; $bColor = '#818cf8'; $bBg = 'rgba(129,140,248,.12)'; }
                    elseif ($my_avg_health > 25) { $bClass = 'badge-warn'; $bColor = '#facc15'; $bBg = 'rgba(250,204,21,.12)'; }
                    else { $bClass = 'badge-kritis'; $bColor = '#f87171'; $bBg = 'rgba(248,113,113,.12)'; }
                @endphp
                <span class="badge {{ $bClass }}" style="padding:4px 12px; font-size:10px; background:{{ $bBg }}; color:{{ $bColor }};">
                    {{ str_replace('_', ' ', strtoupper($my_health_category)) }}
                </span>
            @endif
        </div>

        @if(!$has_umkm)
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height: 100%; text-align: center; padding: 40px 0;">
                <i class="fa-solid fa-store-slash" style="font-size: 40px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">UMKM anda belum terdaftar</div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 24px;">daftarkan segera umkm anda</div>
                <button class="btn-sm btn-sm-solid" onclick="window.location='{{ route('tambah-umkm') }}'">Daftar Sekarang</button>
            </div>
        @elseif(!$has_assessment)
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height: 100%; text-align: center; padding: 40px 0;">
                <i class="fa-solid fa-file-circle-exclamation" style="font-size: 40px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Anda belum melakukan assessment</div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 24px;">Lakukan evaluasi untuk mengetahui kesehatan UMKM Anda</div>
                <button class="btn-sm btn-sm-solid" onclick="window.location='{{ route('assessment') }}'">Buat Assessment</button>
            </div>
        @elseif($my_avg_health == 0)
            <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height: 100%; text-align: center; padding: 40px 0;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 40px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Assessment sedang diproses</div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 24px;">Grafik akan muncul secara otomatis setelah data mencukupi dan dikalkulasi.</div>
                <button class="btn-sm btn-sm-outline" onclick="window.location='{{ route('monitoring') }}'">Pantau Respon</button>
            </div>
        @else
            <div class="health-gauge-wrap">
                <canvas id="healthGauge"></canvas>
                <div class="health-gauge-label">
                    <div class="health-pct">{{ $my_avg_health }}%</div>
                    <div class="health-sub">HEALTH SCORE</div>
                </div>
            </div>

            <div class="factor-bar-row">
                @foreach($my_factors as $f)
                <div class="f-item">
                    @php
                        $sc = round($f->avg_score, 1);
                        // Logika Warna Baru (Interval 25%)
                        if ($sc > 75) $color = '#4ade80';      // SANGAT SEHAT
                        elseif ($sc > 50) $color = '#818cf8';  // SEHAT (Biru Indigo)
                        elseif ($sc > 25) $color = '#facc15';  // CUKUP SEHAT
                        else $color = '#f87171';               // KURANG SEHAT
                    @endphp
                    <div class="f-label"><span>{{ $f->nama_factor }}</span><span style="color:{{ $color }};">{{ $sc }}%</span></div>
                    <div class="f-track"><div class="f-fill" style="width:{{ $sc }}%;background:{{ $color }};"></div></div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

{{-- Bottom 2-col Grid --}}
<div class="bottom-grid" style="align-items: stretch;">
    {{-- Distribusi Usia Perusahaan --}}
    <div class="card2" style="display: flex; flex-direction: column;">
        <div class="card2-title">
            Distribusi Usia Perusahaan
            <span style="font-size:10px;padding:2px 8px;background:#1a1a1a;border:1px solid var(--border-color);border-radius:4px;color:var(--text-secondary);">TAHUN</span>
        </div>
        <div style="flex: 1; min-height: 140px; display: flex; align-items: center; justify-content: center; position: relative;">
            <canvas id="companyAgeChart"></canvas>
        </div>
    </div>

    {{-- Distribusi Gender Pemilik UMKM --}}
    <div class="card2" style="display: flex; flex-direction: column;">
        <div class="card2-title">Distribusi Gender Pemilik UMKM</div>
        <div style="display:flex; align-items:center; gap:24px; flex: 1;">
            <ul style="list-style:none; font-size:12px; display:flex; flex-direction:column; justify-content:center; gap:12px; flex:1;">
                @php
                    $totalOwners = $gender_dist->sum();
                    $genderColors = ['laki-laki' => '#fff', 'perempuan' => '#a3a3a3', 'lainnya' => '#444'];
                @endphp
                @foreach($gender_dist as $g => $count)
                    @php 
                        $pct = $totalOwners > 0 ? round(($count / $totalOwners) * 100) : 0;
                        $color = $genderColors[$g] ?? '#888';
                    @endphp
                    <li><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $color }};margin-right:8px;"></span>{{ ucfirst($g) }} ({{ $pct }}%)</li>
                @endforeach
            </ul>
            <div class="donut-center-wrap" style="width:130px; height:130px; flex-shrink:0;">
                <canvas id="genderChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Ranking Table --}}
<div class="card2">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div>
            <div style="font-size:15px;font-weight:600;">Ranking UMKM Teratas</div>
            <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">Peringkat berdasarkan skor kesehatan dari {{ number_format($stats['total_umkm']) }} UMKM terdaftar.</div>
        </div>
        <a href="{{ route('umkm-rank') }}" class="btn-sm btn-sm-outline" style="text-decoration:none;">LIHAT SEMUA</a>
    </div>

    <table class="rank-table">
        <thead>
            <tr>
                <th>Rank</th><th>Nama UMKM</th><th>Sektor Bisnis</th><th>Status</th><th style="text-align:right;">Skor Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($top_umkms as $idx => $top)
            <tr>
                <td><div class="rank-num">{{ $idx + 1 }}</div></td>
                <td style="font-weight:600;">{{ $top->nama_umkm }}</td>
                <td style="color:var(--text-secondary);">{{ $top->sektor_usaha ?? '-' }}</td>
                <td>
                    @if($top->overall_score > 75)
                        <span class="badge badge-stabil" style="padding:4px 10px;font-size:10px;background:rgba(74,222,128,.12);color:#4ade80;">SANGAT SEHAT</span>
                    @elseif($top->overall_score > 50)
                        <span class="badge badge-stabil" style="padding:4px 10px;font-size:10px;background:rgba(129,140,248,.12);color:#818cf8;">SEHAT</span>
                    @elseif($top->overall_score > 25)
                        <span class="badge badge-warn" style="padding:4px 10px;font-size:10px;">CUKUP SEHAT</span>
                    @else
                        <span class="badge badge-kritis" style="padding:4px 10px;font-size:10px;">KURANG SEHAT</span>
                    @endif
                </td>
                <td style="text-align:right;"><span class="rank-score">{{ number_format($top->overall_score, 1) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection

@section('scripts')
<script>
const tooltipPlugin = {
    plugins: {
        legend: { display: false },
        tooltip: {
            enabled: true,
            backgroundColor: '#1a1a1a',
            borderColor: '#333',
            borderWidth: 1,
            titleColor: '#a3a3a3',
            bodyColor: '#ffffff',
            padding: 10,
            callbacks: {
                label: ctx => ` ${ctx.label || ''}: ${ctx.parsed}%`
            }
        }
    },
    maintainAspectRatio: false
};

// Jenis Bisnis — 3 kategori, tooltip aktif
const bizData = @json($biz_types);
const bizLabels = Object.keys(bizData);
const bizCounts = Object.values(bizData);

const bizColors = ['#ffffff', '#a3a3a3', '#555555', '#222222', '#888888'];

new Chart(document.getElementById('bizTypeChart'), {
    type: 'doughnut',
    data: {
        labels: bizLabels.length ? bizLabels : ['Tidak Diketahui'],
        datasets: [{ data: bizCounts.length ? bizCounts : [1], backgroundColor: bizColors, borderWidth: 0, cutout: '78%' }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: {
                enabled: true,
                backgroundColor: '#1a1a1a',
                borderColor: '#333',
                borderWidth: 1,
                titleColor: '#a3a3a3',
                bodyColor: '#ffffff',
                padding: 10,
                callbacks: {
                    label: ctx => ` ${ctx.label || ''}: ${ctx.parsed} UMKM`
                }
            }
        },
        maintainAspectRatio: false
    }
});

// Usia Perusahaan
const ageData = @json($age_dist);
const ageLabels = ['< 1 tahun', '1 - 3 tahun', '> 3 tahun'];
const ageValues = ageLabels.map(label => ageData[label] || 0);

new Chart(document.getElementById('companyAgeChart'), {
    type: 'bar',
    data: {
        labels: ageLabels,
        datasets: [{ label: 'UMKM', data: ageValues, backgroundColor: '#ffffff', borderRadius: 4 }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: {
                enabled: true,
                backgroundColor: '#1a1a1a',
                borderColor: '#333',
                borderWidth: 1,
                titleColor: '#a3a3a3',
                bodyColor: '#ffffff',
                padding: 10,
                callbacks: { label: ctx => ` ${ctx.parsed.y} UMKM` }
            }
        },
        maintainAspectRatio: false,
        scales: { y:{ display:false }, x:{ grid:{ display:false }, ticks:{ color:'#555', font:{ size:10 } } } }
    }
});

@if($has_umkm && $has_assessment)
const avgHealth = {{ $my_avg_health }};
let gaugeHealthColor = '#f87171';
if (avgHealth > 75) gaugeHealthColor = '#4ade80';
else if (avgHealth > 50) gaugeHealthColor = '#818cf8';
else if (avgHealth > 25) gaugeHealthColor = '#facc15';
// Health Gauge
new Chart(document.getElementById('healthGauge'), {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [avgHealth, 100 - avgHealth],
            backgroundColor: [gaugeHealthColor,'#1e1e1e'],
            borderWidth: 0,
            cutout: '82%',
            circumference: 200,
            rotation: 260
        }]
    },
    options: { plugins: { legend:{ display:false }, tooltip:{ enabled:false } }, maintainAspectRatio: false, animation: { duration: 1200 } }
});
@endif

// Gender
const gData = @json($gender_dist);
const gLabels = Object.keys(gData).map(l => l.charAt(0).toUpperCase() + l.slice(1));
const gValues = Object.values(gData);
const gColors = Object.keys(gData).map(l => l === 'laki-laki' ? '#ffffff' : (l === 'perempuan' ? '#a3a3a3' : '#444444'));

new Chart(document.getElementById('genderChart'), {
    type: 'doughnut',
    data: {
        labels: gLabels,
        datasets: [{ data: gValues, backgroundColor: gColors, borderWidth: 0, cutout: '72%' }]
    },
    options: {
        plugins: {
            legend: { display: false },
            tooltip: {
                enabled: true,
                backgroundColor: '#1a1a1a',
                borderColor: '#333',
                borderWidth: 1,
                padding: 10,
                callbacks: {
                    label: ctx => {
                        const val = ctx.parsed;
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const pct = Math.round((val / total) * 100);
                        return ` ${ctx.label}: ${pct}%`;
                    }
                }
            }
        },
        maintainAspectRatio: false
    }
});


</script>
@endsection
