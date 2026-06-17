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

    /* Dashboard Specific Media Queries */
    @media (max-width: 1024px) {
        .stat-row { grid-template-columns: repeat(3, 1fr); gap: 12px; }
        .main-grid { grid-template-columns: 1fr; }
        .bottom-grid { grid-template-columns: 1fr; }
    }
    
    @media (max-width: 768px) {
        .dash-header { flex-direction: column; align-items: flex-start; gap: 16px; }
        .stat-row { 
            display: flex; 
            overflow-x: auto; 
            scroll-snap-type: x mandatory; 
            -webkit-overflow-scrolling: touch; 
            padding-bottom: 12px; 
            margin-bottom: 20px;
        }
        .stat-card2 { 
            min-width: 200px; 
            flex-shrink: 0; 
            scroll-snap-align: start; 
        }
        .factor-bar-row { grid-template-columns: 1fr; gap: 16px; }
        .health-gauge-wrap { width: 100%; max-width: 280px; height: 140px; }
        .health-pct { font-size: 36px; }
        .donut-center-wrap { width: 120px !important; height: 120px !important; }
    }
    
    .sector-analysis-grid {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    @media (max-width: 600px) {
        #average-highlights-section, #std-dev-highlights-section {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }
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
            <span class="stat-val" id="stat-total-umkm">{{ number_format($stats['total_umkm']) }}</span>
        </div>
    </div>
    <div class="stat-card2">
        <div class="stat-top">
            <div class="stat-icon-box"><i class="fa-solid fa-users"></i></div>
        </div>
        <div class="stat-bottom">
            <span class="stat-label-txt">Total Responden</span>
            <span class="stat-val" id="stat-total-respondents">{{ number_format($stats['total_respondents']) }}</span>
        </div>
    </div>
    <div class="stat-card2">
        <div class="stat-top">
            <div class="stat-icon-box"><i class="fa-solid fa-heart-pulse"></i></div>
        </div>
        <div class="stat-bottom">
            <span class="stat-label-txt">Kesehatan Rata-rata</span>
            <span class="stat-val" id="stat-avg-health">{{ $stats['avg_health'] }} <small>/ 100</small></span>
        </div>
    </div>
    <div class="stat-card2">
        <div class="stat-top">
            <div class="stat-icon-box"><i class="fa-solid fa-circle-check"></i></div>
            <span class="badge badge-stabil">SEHAT</span>
        </div>
        <div class="stat-bottom">
            <span class="stat-label-txt">UMKM Sehat</span>
            <span class="stat-val" id="stat-sehat-count">{{ number_format($stats['sehat_count']) }}</span>
        </div>
    </div>
    <div class="stat-card2">
        <div class="stat-top">
            <div class="stat-icon-box"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <span class="badge badge-kritis">KRITIS</span>
        </div>
        <div class="stat-bottom">
            <span class="stat-label-txt">UMKM Krisis</span>
            <span class="stat-val" id="stat-kritis-count">{{ number_format($stats['kritis_count']) }}</span>
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
            
            <ul id="biz-type-legend" style="list-style:none; font-size:13px; display:flex; flex-direction:column; justify-content:center; gap:14px; flex:1;">
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

        <div id="health-card-body" style="display:flex; flex-direction:column; flex:1;">
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
        <div style="font-size: 10px; color: var(--text-secondary); line-height: 1.4; margin-top: 14px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 6px; padding: 8px 10px;">
            <i class="fa-solid fa-circle-info" style="margin-right: 4px; color: #818cf8;"></i>
            <strong>Fungsi & Perhitungan:</strong> Menampilkan tingkat kesehatan operasional organisasi secara umum (Health Score) dan skor 6 sub-faktor bagi UMKM terpilih. Skor dihitung dari rata-rata tertimbang seluruh jawaban kuesioner yang disubmit oleh Owner dan Karyawan UMKM tersebut.
        </div>
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
            <ul id="gender-legend" style="list-style:none; font-size:12px; display:flex; flex-direction:column; justify-content:center; gap:12px; flex:1;">
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

{{-- Section Baru: Analisis Faktor per Sektor & Standar Deviasi --}}
<div class="card2" style="margin-bottom: 20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <div style="font-size:15px;font-weight:600;">Analisis Faktor per Sektor Usaha & Deviasi Standar</div>
            <div style="font-size:11px;color:var(--text-secondary);margin-top:2px;">Perbandingan rata-rata skor 6 faktor per sektor usaha dan analisis variabilitas data (standar deviasi).</div>
        </div>
        <div>
            <select id="filterSektorSelect" class="umkm-select" style="font-size: 11px; padding: 6px 12px; background: #1a1a1a; border-radius: 6px; border: 1px solid var(--border-color); color: #fff;" onchange="updateSectorFilter(this.value)">
                <option value="global" {{ $filter_sektor == 'global' ? 'selected' : '' }}>Global (Semua Sektor)</option>
                @foreach($all_sectors as $sector)
                    <option value="{{ $sector }}" {{ $filter_sektor == $sector ? 'selected' : '' }}>Sektor: {{ $sector }}</option>
                @endforeach
            </select>
        </div>
    </div>
    
    <div class="sector-analysis-grid">
        {{-- Chart container --}}
        <div>
            <div style="font-size:12px; font-weight:600; margin-bottom:12px; color:#fff; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-chart-column" style="color:var(--text-secondary);"></i> 
                <span id="chart-section-title">
                    @if($filter_sektor == 'global')
                        Perbandingan Rata-rata Skor 6 Faktor per Sektor Usaha
                    @else
                        Rata-rata Skor 6 Faktor Sektor {{ $filter_sektor }}
                    @endif
                </span>
            </div>
            <div style="min-height: 320px; position: relative; background: #141414; border: 1px solid var(--border-color); border-radius: 10px; padding: 16px;">
                <canvas id="sectorFactorChart"></canvas>
            </div>
            
            {{-- Explanation for Averages --}}
            <div id="average-explanation-text" style="font-size: 11px; color: var(--text-secondary); line-height: 1.5; margin-top: 12px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px;">
                <i class="fa-solid fa-circle-info" style="margin-right: 4px; color: #818cf8;"></i>
                @if($filter_sektor == 'global')
                    <strong>Fungsi & Perhitungan Rata-rata Global:</strong> Rata-rata ini berfungsi sebagai tolok ukur (benchmark) untuk membandingkan rata-rata skor kesehatan operasional dari 6 faktor utama antar sektor usaha secara global. Nilai rata-rata diperoleh dengan menjumlahkan seluruh skor faktor kesehatan organisasi dari kuesioner Owner dan Karyawan dari semua UMKM di seluruh sektor usaha, lalu dibagi dengan total jumlah responden.
                @else
                    <strong>Fungsi & Perhitungan Rata-rata Sektor ({{ $filter_sektor }}):</strong> Rata-rata ini berfungsi untuk mengetahui profil kekuatan dan kelemahan kinerja kesehatan operasional khusus pada sektor <strong>{{ $filter_sektor }}</strong>. Nilai rata-rata diperoleh dari total skor kuesioner Owner dan Karyawan dari seluruh UMKM yang terdaftar dalam sektor ini, lalu dibagi dengan jumlah responden di sektor tersebut.
                @endif
            </div>

            {{-- Highlights Section for Averages --}}
            <div id="average-highlights-section" style="margin-top: 16px; margin-bottom: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div style="background: rgba(74, 222, 128, 0.05); border: 1px solid rgba(74, 222, 128, 0.2); border-radius: 8px; padding: 12px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(74, 222, 128, 0.1); color: #4ade80; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">UMKM Tertinggi</div>
                        <div id="highest-umkm-name" style="font-size: 13px; font-weight: 600; color: #fff;">{{ $highest_umkm['nama'] ?? '-' }}</div>
                        <div id="highest-umkm-score" style="font-size: 11px; color: #4ade80; font-weight: 500;">
                            Skor: <span class="val">{{ $highest_umkm['score'] ?? '-' }}</span> 
                            <span class="sub" style="color: var(--text-secondary);">({{ $highest_umkm['sektor'] ?? '-' }})</span>
                        </div>
                    </div>
                </div>
                
                <div style="background: rgba(248, 113, 113, 0.05); border: 1px solid rgba(248, 113, 113, 0.2); border-radius: 8px; padding: 12px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(248, 113, 113, 0.1); color: #f87171; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                        <i class="fa-solid fa-arrow-down-long"></i>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">UMKM Terendah</div>
                        <div id="lowest-umkm-name" style="font-size: 13px; font-weight: 600; color: #fff;">{{ $lowest_umkm['nama'] ?? '-' }}</div>
                        <div id="lowest-umkm-score" style="font-size: 11px; color: #f87171; font-weight: 500;">
                            Skor: <span class="val">{{ $lowest_umkm['score'] ?? '-' }}</span> 
                            <span class="sub" style="color: var(--text-secondary);">({{ $lowest_umkm['sektor'] ?? '-' }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Standard Deviation Table --}}
        <div style="margin-top: 48px;">
            <div style="font-size:12px; font-weight:600; margin-bottom:12px; color:#fff; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-calculator" style="color:var(--text-secondary);"></i> Detail Standar Deviasi per Faktor
            </div>
            <div class="table-responsive" style="border: 1px solid var(--border-color); border-radius: 10px; padding: 16px; background: #141414;">
                <table class="rank-table" style="font-size: 12px; width:100%;">
                    <thead>
                        <tr id="std-dev-table-header">
                            @if($filter_sektor == 'global')
                                <th style="padding: 8px 12px; text-transform:none; font-size:11px;">Faktor</th>
                                <th style="padding: 8px 12px; text-align:right; text-transform:none; font-size:11px;">Overall SD</th>
                                <th style="padding: 8px 12px; text-align:right; text-transform:none; font-size:11px;">Tertinggi (Sektor)</th>
                                <th style="padding: 8px 12px; text-align:right; text-transform:none; font-size:11px;">Terendah (Sektor)</th>
                            @else
                                <th style="padding: 8px 12px; text-transform:none; font-size:11px;">Faktor</th>
                                <th style="padding: 8px 12px; text-align:right; text-transform:none; font-size:11px;">SD Sektor</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="std-dev-table-body">
                        @foreach($factor_std_devs as $std)
                        <tr>
                            <td style="padding: 10px 12px; font-weight: 600; color: #fff;">{{ $std['nama_factor'] }}</td>
                            @if($filter_sektor == 'global')
                                <td style="padding: 10px 12px; text-align:right; font-weight: 700; color: #818cf8;">{{ number_format($std['overall_std_dev'], 2) }}</td>
                                <td style="padding: 10px 12px; text-align:right; color: #f87171;">
                                    <span style="font-weight: 700;">{{ number_format($std['highest_std_val'], 2) }}</span>
                                    <br><small style="color:var(--text-secondary); font-size:10px;">{{ $std['highest_std_sector'] }}</small>
                                </td>
                                <td style="padding: 10px 12px; text-align:right; color: #4ade80;">
                                    <span style="font-weight: 700;">{{ number_format($std['lowest_std_val'], 2) }}</span>
                                    <br><small style="color:var(--text-secondary); font-size:10px;">{{ $std['lowest_std_sector'] }}</small>
                                </td>
                            @else
                                <td style="padding: 10px 12px; text-align:right; font-weight: 700; color: #818cf8;">{{ number_format($std['sector_std_dev'], 2) }}</td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Explanation for Std Dev --}}
            <div id="std-dev-explanation-text" style="font-size: 11px; color: var(--text-secondary); line-height: 1.5; margin-top: 12px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 12px;">
                <i class="fa-solid fa-circle-info" style="margin-right: 4px; color: #818cf8;"></i>
                @if($filter_sektor == 'global')
                    <strong>Fungsi & Perhitungan Standar Deviasi Global:</strong> Standar deviasi berfungsi untuk mengukur variabilitas, konsistensi, atau kesenjangan skor kesehatan organisasi antar UMKM di seluruh ekosistem. Kolom <strong>Overall SD</strong> menunjukkan tingkat sebaran data secara keseluruhan, sedangkan kolom <strong>Tertinggi</strong> dan <strong>Terendah</strong> menunjukkan sektor dengan kesenjangan terbesar dan terkonsisten. Nilai diperoleh melalui perhitungan simpangan baku (akar kuadrat dari varians) dari skor tiap UMKM terhadap rata-rata global.
                @else
                    <strong>Fungsi & Perhitungan Standar Deviasi Sektor ({{ $filter_sektor }}):</strong> Standar deviasi sektor berfungsi untuk melihat tingkat kesenjangan atau pemerataan skor kesehatan operasional khusus antar UMKM di dalam sektor <strong>{{ $filter_sektor }}</strong>. Standar deviasi yang rendah (rendah kesenjangan) menunjukkan kinerja antar UMKM cenderung seragam/konsisten, sedangkan standar deviasi yang tinggi (tinggi kesenjangan) menunjukkan ketimpangan yang besar. Nilai diperoleh melalui perhitungan simpangan baku berdasarkan selisih skor tiap UMKM di dalam sektor ini terhadap nilai rata-rata sektor.
                @endif
            </div>

            {{-- Highlights Section for Std Dev --}}
            <div id="std-dev-highlights-section" style="margin-top: 16px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div style="background: rgba(248, 113, 113, 0.05); border: 1px solid rgba(248, 113, 113, 0.2); border-radius: 8px; padding: 12px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(248, 113, 113, 0.1); color: #f87171; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Kesenjangan Terbesar (SD Tertinggi)</div>
                        <div id="highest-sd-factor-name" style="font-size: 13px; font-weight: 600; color: #fff;">{{ $highest_sd_factor['nama'] ?? '-' }}</div>
                        <div id="highest-sd-factor-value" style="font-size: 11px; color: #f87171; font-weight: 500;">
                            Nilai SD: <span class="val">{{ isset($highest_sd_factor['value']) ? number_format($highest_sd_factor['value'], 2) : '-' }}</span>
                        </div>
                    </div>
                </div>
                
                <div style="background: rgba(74, 222, 128, 0.05); border: 1px solid rgba(74, 222, 128, 0.2); border-radius: 8px; padding: 12px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 50%; background: rgba(74, 222, 128, 0.1); color: #4ade80; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div style="font-size: 10px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px;">Paling Konsisten (SD Terendah)</div>
                        <div id="lowest-sd-factor-name" style="font-size: 13px; font-weight: 600; color: #fff;">{{ $lowest_sd_factor['nama'] ?? '-' }}</div>
                        <div id="lowest-sd-factor-value" style="font-size: 11px; color: #4ade80; font-weight: 500;">
                            Nilai SD: <span class="val">{{ isset($lowest_sd_factor['value']) ? number_format($lowest_sd_factor['value'], 2) : '-' }}</span>
                        </div>
                    </div>
                </div>
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

    <div class="table-responsive">
        <table class="rank-table">
            <thead>
                <tr>
                    <th>Rank</th><th>Nama UMKM</th><th>Sektor Bisnis</th><th>Status</th><th style="text-align:right;">Skor Total</th>
                </tr>
            </thead>
            <tbody id="rank-table-body">
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

window.bizTypeChart = new Chart(document.getElementById('bizTypeChart'), {
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

window.companyAgeChart = new Chart(document.getElementById('companyAgeChart'), {
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
window.healthGauge = new Chart(document.getElementById('healthGauge'), {
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

window.genderChart = new Chart(document.getElementById('genderChart'), {
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

let currentSectorFilter = '{{ $filter_sektor }}';

// Sector Factor Grouped Bar Chart
const sectorChartRaw = @json($sector_chart_data);
window.sectorFactorChart = new Chart(document.getElementById('sectorFactorChart'), {
    type: 'bar',
    data: sectorChartRaw,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: currentSectorFilter === 'global',
                position: 'top',
                labels: {
                    color: '#a3a3a3',
                    font: { size: 10 }
                }
            },
            tooltip: {
                enabled: true,
                backgroundColor: '#1a1a1a',
                borderColor: '#333',
                borderWidth: 1,
                titleColor: '#a3a3a3',
                bodyColor: '#ffffff',
                padding: 10
            }
        },
        scales: {
            y: {
                min: 0,
                max: 100,
                grid: { color: '#222' },
                ticks: { color: '#a3a3a3', font: { size: 10 } }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#a3a3a3', font: { size: 10 } }
            }
        }
    }
});

function updateSectorFilter(sector) {
    currentSectorFilter = sector;
    
    // Update titles and explanations immediately (optimistic UI update)
    const chartTitleEl = document.getElementById('chart-section-title');
    if (chartTitleEl) {
        chartTitleEl.innerText = sector === 'global' 
            ? 'Perbandingan Rata-rata Skor 6 Faktor per Sektor Usaha' 
            : `Rata-rata Skor 6 Faktor Sektor ${sector}`;
    }
    
    const avgExplEl = document.getElementById('average-explanation-text');
    if (avgExplEl) {
        avgExplEl.innerHTML = sector === 'global'
            ? `<i class="fa-solid fa-circle-info" style="margin-right: 4px; color: #818cf8;"></i><strong>Fungsi & Perhitungan Rata-rata Global:</strong> Rata-rata ini berfungsi sebagai tolok ukur (benchmark) untuk membandingkan rata-rata skor kesehatan operasional dari 6 faktor utama antar sektor usaha secara global. Nilai rata-rata diperoleh dengan menjumlahkan seluruh skor faktor kesehatan organisasi dari kuesioner Owner dan Karyawan dari semua UMKM di seluruh sektor usaha, lalu dibagi dengan total jumlah responden.`
            : `<i class="fa-solid fa-circle-info" style="margin-right: 4px; color: #818cf8;"></i><strong>Fungsi & Perhitungan Rata-rata Sektor (${sector}):</strong> Rata-rata ini berfungsi untuk mengetahui profil kekuatan dan kelemahan kinerja kesehatan operasional khusus pada sektor <strong>${sector}</strong>. Nilai rata-rata diperoleh dari total skor kuesioner Owner dan Karyawan dari seluruh UMKM yang terdaftar dalam sektor ini, lalu dibagi dengan jumlah responden di sektor tersebut.`;
    }

    const stdExplEl = document.getElementById('std-dev-explanation-text');
    if (stdExplEl) {
        stdExplEl.innerHTML = sector === 'global'
            ? `<i class="fa-solid fa-circle-info" style="margin-right: 4px; color: #818cf8;"></i><strong>Fungsi & Perhitungan Standar Deviasi Global:</strong> Standar deviasi berfungsi untuk mengukur variabilitas, konsistensi, atau kesenjangan skor kesehatan organisasi antar UMKM di seluruh ekosistem. Kolom <strong>Overall SD</strong> menunjukkan tingkat sebaran data secara keseluruhan, sedangkan kolom <strong>Tertinggi</strong> dan <strong>Terendah</strong> menunjukkan sektor dengan kesenjangan terbesar dan terkonsisten. Nilai diperoleh melalui perhitungan simpangan baku (akar kuadrat dari varians) dari skor tiap UMKM terhadap rata-rata global.`
            : `<i class="fa-solid fa-circle-info" style="margin-right: 4px; color: #818cf8;"></i><strong>Fungsi & Perhitungan Standar Deviasi Sektor (${sector}):</strong> Standar deviasi sektor berfungsi untuk melihat tingkat kesenjangan atau pemerataan skor kesehatan operasional khusus antar UMKM di dalam sektor <strong>${sector}</strong>. Standar deviasi yang rendah (rendah kesenjangan) menunjukkan kinerja antar UMKM cenderung seragam/konsisten, sedangkan standar deviasi yang tinggi (tinggi kesenjangan) menunjukkan ketimpangan yang besar. Nilai diperoleh melalui perhitungan simpangan baku berdasarkan selisih skor tiap UMKM di dalam sektor ini terhadap nilai rata-rata sektor.`;
    }

    fetchDashboardRealtimeData();
}

function fetchDashboardRealtimeData() {
    fetch(`{{ route('api.realtime.dashboard') }}?umkm_id={{ $selected_umkm_id }}&filter_sektor=${currentSectorFilter}`)
        .then(res => res.json())
        .then(data => {
            // Update stats
            document.getElementById('stat-total-umkm').innerText = parseInt(data.stats.total_umkm).toLocaleString();
            document.getElementById('stat-total-respondents').innerText = parseInt(data.stats.total_respondents).toLocaleString();
            document.getElementById('stat-avg-health').innerHTML = parseFloat(data.stats.avg_health).toFixed(1) + ' <small>/ 100</small>';
            document.getElementById('stat-sehat-count').innerText = parseInt(data.stats.sehat_count).toLocaleString();
            document.getElementById('stat-kritis-count').innerText = parseInt(data.stats.kritis_count).toLocaleString();

            // Update Biz Types Chart & Legend
            const bizLabels = Object.keys(data.biz_types);
            const bizCounts = Object.values(data.biz_types);
            const bizColors = ['#ffffff', '#a3a3a3', '#555555', '#222222', '#888888'];
            if (window.bizTypeChart) {
                window.bizTypeChart.data.labels = bizLabels.length ? bizLabels : ['Tidak Diketahui'];
                window.bizTypeChart.data.datasets[0].data = bizCounts.length ? bizCounts : [1];
                window.bizTypeChart.update();
            }
            const dcVal = document.querySelector('.donut-center-label div');
            if (dcVal) {
                dcVal.innerText = bizCounts.reduce((a, b) => a + b, 0);
            }
            let bizLegendHtml = '';
            bizLabels.forEach((label, idx) => {
                const color = bizColors[idx % bizColors.length];
                const count = bizCounts[idx];
                bizLegendHtml += `
                    <li style="display:flex; align-items:center; gap:12px;">
                        <span style="display:inline-block; width:10px; height:10px; border-radius:50%; background:${color}; flex-shrink:0;"></span>
                        <span style="color:var(--text-primary); font-weight:500;">
                            ${label || 'Lainnya'} 
                            <span style="color:var(--text-secondary); font-size:12px; font-weight:400; margin-left:4px;">(${count} UMKM)</span>
                        </span>
                    </li>`;
            });
            const bizLegendEl = document.getElementById('biz-type-legend');
            if (bizLegendEl) {
                bizLegendEl.innerHTML = bizLegendHtml || '<li style="color:var(--text-secondary); font-style:italic;">Belum ada data</li>';
            }

            // Update Company Age Chart
            const ageLabels = ['< 1 tahun', '1 - 3 tahun', '> 3 tahun'];
            const ageValues = ageLabels.map(l => data.age_dist[l] || 0);
            if (window.companyAgeChart) {
                window.companyAgeChart.data.datasets[0].data = ageValues;
                window.companyAgeChart.update();
            }

            // Update Gender Chart
            const gLabels = Object.keys(data.gender_dist).map(l => l.charAt(0).toUpperCase() + l.slice(1));
            const gValues = Object.values(data.gender_dist);
            const gColors = Object.keys(data.gender_dist).map(l => l === 'laki-laki' ? '#ffffff' : (l === 'perempuan' ? '#a3a3a3' : '#444444'));
            if (window.genderChart) {
                window.genderChart.data.labels = gLabels;
                window.genderChart.data.datasets[0].data = gValues;
                window.genderChart.data.datasets[0].backgroundColor = gColors;
                window.genderChart.update();
            }
            let genderHtml = '';
            const totalOwners = gValues.reduce((a, b) => a + b, 0);
            Object.keys(data.gender_dist).forEach((g) => {
                const count = data.gender_dist[g];
                const pct = totalOwners > 0 ? Math.round((count / totalOwners) * 100) : 0;
                const color = g === 'laki-laki' ? '#fff' : (g === 'perempuan' ? '#a3a3a3' : '#444');
                genderHtml += `<li><span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${color};margin-right:8px;"></span>${g.charAt(0).toUpperCase() + g.slice(1)} (${pct}%)</li>`;
            });
            const genderLegendEl = document.getElementById('gender-legend');
            if (genderLegendEl) {
                genderLegendEl.innerHTML = genderHtml;
            }

            // Update Health Card Body
            const healthCardBody = document.getElementById('health-card-body');
            if (healthCardBody) {
                if (!data.has_umkm) {
                    healthCardBody.innerHTML = `
                        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height: 100%; text-align: center; padding: 40px 0;">
                            <i class="fa-solid fa-store-slash" style="font-size: 40px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                            <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">UMKM anda belum terdaftar</div>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 24px;">daftarkan segera umkm anda</div>
                            <button class="btn-sm btn-sm-solid" onclick="window.location='{{ route('tambah-umkm') }}'">Daftar Sekarang</button>
                        </div>`;
                } else if (!data.has_assessment) {
                    healthCardBody.innerHTML = `
                        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height: 100%; text-align: center; padding: 40px 0;">
                            <i class="fa-solid fa-file-circle-exclamation" style="font-size: 40px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                            <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Anda belum melakukan assessment</div>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 24px;">Lakukan evaluasi untuk mengetahui kesehatan UMKM Anda</div>
                            <button class="btn-sm btn-sm-solid" onclick="window.location='{{ route('assessment') }}'">Buat Assessment</button>
                        </div>`;
                } else if (data.my_avg_health == 0) {
                    healthCardBody.innerHTML = `
                        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height: 100%; text-align: center; padding: 40px 0;">
                            <i class="fa-solid fa-spinner fa-spin" style="font-size: 40px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                            <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Assessment sedang diproses</div>
                            <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 24px;">Grafik akan muncul secara otomatis setelah data mencukupi dan dikalkulasi.</div>
                            <button class="btn-sm btn-sm-outline" onclick="window.location='{{ route('monitoring') }}'">Pantau Respon</button>
                        </div>`;
                } else {
                    const canvas = document.getElementById('healthGauge');
                    if (!canvas) {
                        let factorBarsHtml = '';
                        data.my_factors.forEach(f => {
                            const sc = Math.round(f.avg_score);
                            let color = '#f87171';
                            if (sc > 75) color = '#4ade80';
                            else if (sc > 50) color = '#818cf8';
                            else if (sc > 25) color = '#facc15';

                            factorBarsHtml += `
                                <div class="f-item">
                                    <div class="f-label"><span>${f.nama_factor}</span><span style="color:${color};">${sc}%</span></div>
                                    <div class="f-track"><div class="f-fill" style="width:${sc}%;background:${color};"></div></div>
                                </div>`;
                        });

                        healthCardBody.innerHTML = `
                            <div class="health-gauge-wrap">
                                <canvas id="healthGauge"></canvas>
                                <div class="health-gauge-label">
                                    <div class="health-pct">${data.my_avg_health}%</div>
                                    <div class="health-sub">HEALTH SCORE</div>
                                </div>
                            </div>
                            <div class="factor-bar-row">${factorBarsHtml}</div>`;

                        const avgHealth = data.my_avg_health;
                        let gaugeHealthColor = '#f87171';
                        if (avgHealth > 75) gaugeHealthColor = '#4ade80';
                        else if (avgHealth > 50) gaugeHealthColor = '#818cf8';
                        else if (avgHealth > 25) gaugeHealthColor = '#facc15';

                        window.healthGauge = new Chart(document.getElementById('healthGauge'), {
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
                    } else {
                        document.querySelector('.health-pct').innerText = data.my_avg_health + '%';
                        const avgHealth = data.my_avg_health;
                        let gaugeHealthColor = '#f87171';
                        if (avgHealth > 75) gaugeHealthColor = '#4ade80';
                        else if (avgHealth > 50) gaugeHealthColor = '#818cf8';
                        else if (avgHealth > 25) gaugeHealthColor = '#facc15';

                        if (window.healthGauge) {
                            window.healthGauge.data.datasets[0].data = [avgHealth, 100 - avgHealth];
                            window.healthGauge.data.datasets[0].backgroundColor = [gaugeHealthColor, '#1e1e1e'];
                            window.healthGauge.update();
                        }

                        let factorBarsHtml = '';
                        data.my_factors.forEach(f => {
                            const sc = Math.round(f.avg_score);
                            let color = '#f87171';
                            if (sc > 75) color = '#4ade80';
                            else if (sc > 50) color = '#818cf8';
                            else if (sc > 25) color = '#facc15';

                            factorBarsHtml += `
                                <div class="f-item">
                                    <div class="f-label"><span>${f.nama_factor}</span><span style="color:${color};">${sc}%</span></div>
                                    <div class="f-track"><div class="f-fill" style="width:${sc}%;background:${color};"></div></div>
                                </div>`;
                        });
                        const factorRow = document.querySelector('.factor-bar-row');
                        if (factorRow) factorRow.innerHTML = factorBarsHtml;
                    }

                    const badgeContainer = document.querySelector('.health-card .badge');
                    if (badgeContainer) {
                        let bColor = '#f87171';
                        let bBg = 'rgba(248,113,113,.12)';
                        const categoryClean = data.my_health_category.replace('_', ' ').toUpperCase();
                        if (data.my_avg_health > 75) { bColor = '#4ade80'; bBg = 'rgba(74,222,128,.12)'; }
                        else if (data.my_avg_health > 50) { bColor = '#818cf8'; bBg = 'rgba(129,140,248,.12)'; }
                        else if (data.my_avg_health > 25) { bColor = '#facc15'; bBg = 'rgba(250,204,21,.12)'; }
                        
                        badgeContainer.style.backgroundColor = bBg;
                        badgeContainer.style.color = bColor;
                        badgeContainer.innerText = categoryClean;
                    }
                }
            }

            // Update Sector Factor Chart
            if (window.sectorFactorChart && data.sector_chart_data) {
                window.sectorFactorChart.data.labels = data.sector_chart_data.labels;
                window.sectorFactorChart.data.datasets = data.sector_chart_data.datasets;
                if (window.sectorFactorChart.options.plugins && window.sectorFactorChart.options.plugins.legend) {
                    window.sectorFactorChart.options.plugins.legend.display = (currentSectorFilter === 'global');
                }
                window.sectorFactorChart.update();
            }

            // Update Standard Deviation Header and Table Body
            const stdHeader = document.getElementById('std-dev-table-header');
            if (stdHeader) {
                if (currentSectorFilter === 'global') {
                    stdHeader.innerHTML = `
                        <th style="padding: 8px 12px; text-transform:none; font-size:11px;">Faktor</th>
                        <th style="padding: 8px 12px; text-align:right; text-transform:none; font-size:11px;">Overall SD</th>
                        <th style="padding: 8px 12px; text-align:right; text-transform:none; font-size:11px;">Tertinggi (Sektor)</th>
                        <th style="padding: 8px 12px; text-align:right; text-transform:none; font-size:11px;">Terendah (Sektor)</th>
                    `;
                } else {
                    stdHeader.innerHTML = `
                        <th style="padding: 8px 12px; text-transform:none; font-size:11px;">Faktor</th>
                        <th style="padding: 8px 12px; text-align:right; text-transform:none; font-size:11px;">SD Sektor</th>
                    `;
                }
            }

            if (data.factor_std_devs) {
                let stdDevHtml = '';
                data.factor_std_devs.forEach(std => {
                    if (currentSectorFilter === 'global') {
                        stdDevHtml += `
                            <tr>
                                <td style="padding: 10px 12px; font-weight: 600; color: #fff;">${std.nama_factor}</td>
                                <td style="padding: 10px 12px; text-align:right; font-weight: 700; color: #818cf8;">${parseFloat(std.overall_std_dev).toFixed(2)}</td>
                                <td style="padding: 10px 12px; text-align:right; color: #f87171;">
                                    <span style="font-weight: 700;">${parseFloat(std.highest_std_val).toFixed(2)}</span>
                                    <br><small style="color:var(--text-secondary); font-size:10px;">${std.highest_std_sector}</small>
                                </td>
                                <td style="padding: 10px 12px; text-align:right; color: #4ade80;">
                                    <span style="font-weight: 700;">${parseFloat(std.lowest_std_val).toFixed(2)}</span>
                                    <br><small style="color:var(--text-secondary); font-size:10px;">${std.lowest_std_sector}</small>
                                </td>
                            </tr>`;
                    } else {
                        stdDevHtml += `
                            <tr>
                                <td style="padding: 10px 12px; font-weight: 600; color: #fff;">${std.nama_factor}</td>
                                <td style="padding: 10px 12px; text-align:right; font-weight: 700; color: #818cf8;">${parseFloat(std.sector_std_dev).toFixed(2)}</td>
                            </tr>`;
                    }
                });
                const stdDevBody = document.getElementById('std-dev-table-body');
                if (stdDevBody) {
                    stdDevBody.innerHTML = stdDevHtml;
                }
            }

            // Update Highest / Lowest SD Factor
            const highestSdNameEl = document.getElementById('highest-sd-factor-name');
            const highestSdValEl = document.getElementById('highest-sd-factor-value');
            const lowestSdNameEl = document.getElementById('lowest-sd-factor-name');
            const lowestSdValEl = document.getElementById('lowest-sd-factor-value');

            if (data.highest_sd_factor) {
                if (highestSdNameEl) highestSdNameEl.innerText = data.highest_sd_factor.nama;
                if (highestSdValEl) {
                    highestSdValEl.innerHTML = `Nilai SD: <span class="val">${parseFloat(data.highest_sd_factor.value).toFixed(2)}</span>`;
                }
            } else {
                if (highestSdNameEl) highestSdNameEl.innerText = '-';
                if (highestSdValEl) highestSdValEl.innerHTML = 'Nilai SD: -';
            }

            if (data.lowest_sd_factor) {
                if (lowestSdNameEl) lowestSdNameEl.innerText = data.lowest_sd_factor.nama;
                if (lowestSdValEl) {
                    lowestSdValEl.innerHTML = `Nilai SD: <span class="val">${parseFloat(data.lowest_sd_factor.value).toFixed(2)}</span>`;
                }
            } else {
                if (lowestSdNameEl) lowestSdNameEl.innerText = '-';
                if (lowestSdValEl) lowestSdValEl.innerHTML = 'Nilai SD: -';
            }

            // Update Highest / Lowest UMKM
            const highestNameEl = document.getElementById('highest-umkm-name');
            const highestScoreEl = document.getElementById('highest-umkm-score');
            const lowestNameEl = document.getElementById('lowest-umkm-name');
            const lowestScoreEl = document.getElementById('lowest-umkm-score');

            if (data.highest_umkm) {
                if (highestNameEl) highestNameEl.innerText = data.highest_umkm.nama;
                if (highestScoreEl) {
                    highestScoreEl.innerHTML = `Skor: <span class="val">${data.highest_umkm.score}</span> <span class="sub" style="color: var(--text-secondary);">(${data.highest_umkm.sektor})</span>`;
                }
            } else {
                if (highestNameEl) highestNameEl.innerText = '-';
                if (highestScoreEl) highestScoreEl.innerHTML = 'Skor: -';
            }

            if (data.lowest_umkm) {
                if (lowestNameEl) lowestNameEl.innerText = data.lowest_umkm.nama;
                if (lowestScoreEl) {
                    lowestScoreEl.innerHTML = `Skor: <span class="val">${data.lowest_umkm.score}</span> <span class="sub" style="color: var(--text-secondary);">(${data.lowest_umkm.sektor})</span>`;
                }
            } else {
                if (lowestNameEl) lowestNameEl.innerText = '-';
                if (lowestScoreEl) lowestScoreEl.innerHTML = 'Skor: -';
            }

            // Update Top UMKM Table
            let tableHtml = '';
            data.top_umkms.forEach((top, idx) => {
                let badgeHtml = '';
                if (top.overall_score > 75) {
                    badgeHtml = '<span class="badge badge-stabil" style="padding:4px 10px;font-size:10px;background:rgba(74,222,128,.12);color:#4ade80;">SANGAT SEHAT</span>';
                } else if (top.overall_score > 50) {
                    badgeHtml = '<span class="badge badge-stabil" style="padding:4px 10px;font-size:10px;background:rgba(129,140,248,.12);color:#818cf8;">SEHAT</span>';
                } else if (top.overall_score > 25) {
                    badgeHtml = '<span class="badge badge-warn" style="padding:4px 10px;font-size:10px;">CUKUP SEHAT</span>';
                } else {
                    badgeHtml = '<span class="badge badge-kritis" style="padding:4px 10px;font-size:10px;">KURANG SEHAT</span>';
                }

                tableHtml += `
                    <tr>
                        <td><div class="rank-num">${idx + 1}</div></td>
                        <td style="font-weight:600;">${top.nama_umkm}</td>
                        <td style="color:var(--text-secondary);">${top.sektor_usaha || '-'}</td>
                        <td>${badgeHtml}</td>
                        <td style="text-align:right;"><span class="rank-score">${parseFloat(top.overall_score).toFixed(1)}</span></td>
                    </tr>`;
            });
            const rBody = document.getElementById('rank-table-body');
            if (rBody) {
                rBody.innerHTML = tableHtml;
            }
        })
        .catch(err => console.error('Realtime fetch error:', err));
}

function startDashboardPoller() {
    setInterval(() => {
        fetchDashboardRealtimeData();
    }, 10000);
}

// Run Poller on load
document.addEventListener('DOMContentLoaded', () => {
    startDashboardPoller();
});


</script>
@endsection
