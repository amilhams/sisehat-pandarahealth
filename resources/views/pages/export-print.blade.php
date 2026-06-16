<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kesehatan Organisasi - {{ $active_umkm->nama_umkm }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #111;
            --secondary: #666;
            --border: #ddd;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #ca8a04;
            --info: #2563eb;
            --light-bg: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--primary);
            background: #fff;
            line-height: 1.4;
            font-size: 12px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .print-page {
            width: 210mm;
            height: 297mm;
            padding: 10mm 15mm 20mm;
            margin: 10px auto;
            position: relative;
            background: #fff;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #111;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-section img {
            height: 32px;
            width: auto;
        }

        .logo-section h2 {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .report-meta {
            text-align: right;
            font-size: 10px;
            color: var(--secondary);
        }

        .report-title-box {
            text-align: center;
            margin: 20px 0;
        }

        .report-title-box h1 {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-title-box p {
            font-size: 11px;
            color: var(--secondary);
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 6px;
            text-transform: uppercase;
            color: #111;
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .card {
            background: var(--light-bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px;
        }

        .card-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--secondary);
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .health-score-large {
            display: flex;
            align-items: baseline;
            gap: 6px;
            margin-bottom: 8px;
        }

        .health-score-large .num {
            font-size: 36px;
            font-weight: 800;
        }

        .health-badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-info { background: #e0e7ff; color: #4338ca; }
        .badge-warning { background: #fef9c3; color: #a16207; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }

        .table-custom th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 600;
            text-align: left;
            padding: 6px 10px;
            border: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.3px;
        }

        .table-custom td {
            padding: 6px 10px;
            border: 1px solid var(--border);
        }

        .chart-container {
            width: 100%;
            height: 200px;
            position: relative;
            margin: 5px 0;
        }

        .insight-box {
            background: #fff;
            border-left: 4px solid var(--info);
            padding: 12px;
            border-radius: 4px;
            margin-top: 4px;
            font-size: 11px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .priority-badge {
            background: #fee2e2;
            color: #b91c1c;
            font-size: 8px;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .page-footer {
            position: absolute;
            bottom: 10mm;
            left: 15mm;
            right: 15mm;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: var(--secondary);
            border-top: 1px solid var(--border);
            padding-top: 6px;
        }

        @media print {
            body {
                background: #fff;
            }
            .print-page {
                margin: 0;
                padding: 10mm 15mm 20mm;
                width: 210mm;
                height: 297mm;
                page-break-after: always;
                page-break-inside: avoid;
                position: relative;
                overflow: hidden;
                box-sizing: border-box;
                box-shadow: none;
            }
            .print-page:last-child {
                page-break-after: avoid;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- PAGE 1: COVER & DASHBOARD -->
    <div class="print-page">
        <div class="page-header">
            <div class="logo-section">
                <img src="{{ asset('images/logo_pandara.png') }}" alt="Pandara Logo">
                <h2>Pandara Health</h2>
            </div>
            <div class="report-meta">
                <p>Owner: {{ $owner->name }}</p>
                <p>Tanggal Ekspor: {{ now()->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <div class="report-title-box">
            <h1>Laporan Kesehatan Organisasi</h1>
            <p>UMKM: <strong>{{ $active_umkm->nama_umkm }}</strong> | Sektor: {{ $active_umkm->sektor_usaha ?? 'Umum' }}</p>
            <p>Periode Asesmen: {{ \Carbon\Carbon::parse($assessment->tanggal_mulai ?? $assessment->created_at)->translatedFormat('F Y') }} (Ke-{{ $sequence_in_month }})</p>
        </div>

        <div class="section-title">
            <i class="fa-solid fa-chart-line"></i> Dashboard Ringkasan
        </div>

        <div class="grid-2">
            <div class="card" style="display: flex; flex-direction: column; justify-content: center; height: 100%;">
                <div class="card-title">Skor Kesehatan Organisasi</div>
                <div class="health-score-large">
                    <span class="num">{{ number_format($health_score->overall_score ?? 0, 1) }}</span>
                    <span style="color: var(--secondary); font-size: 15px;">/ 100</span>
                </div>
                <div>
                    @php
                        $scoreVal = $health_score->overall_score ?? 0;
                        if ($scoreVal > 75) {
                            $badgeClass = 'badge-success';
                        } elseif ($scoreVal > 50) {
                            $badgeClass = 'badge-info';
                        } elseif ($scoreVal > 25) {
                            $badgeClass = 'badge-warning';
                        } else {
                            $badgeClass = 'badge-danger';
                        }
                    @endphp
                    <span class="health-badge {{ $badgeClass }}">{{ str_replace('_', ' ', strtoupper($health_score->category ?? 'N/A')) }}</span>
                </div>
            </div>

            <div class="card">
                <div class="card-title">Status Standar Deviasi</div>
                <p style="font-size: 10px; margin-bottom: 6px;">Acuan Sektor: <strong>{{ $sd_sector === 'global' ? 'Global (Semua Sektor)' : $sd_sector }}</strong></p>
                <div style="display: flex; flex-direction: column; gap: 6px; font-size: 11px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed var(--border); padding-bottom: 4px;">
                        <span>Faktor Paling Bervariasi:</span>
                        <strong>{{ $highest_sd_factor ? $highest_sd_factor['nama'] : '-' }} ({{ $highest_sd_factor ? number_format($highest_sd_factor['value'], 2) : '0' }})</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Faktor Paling Konsisten:</span>
                        <strong>{{ $lowest_sd_factor ? $lowest_sd_factor['nama'] : '-' }} ({{ $lowest_sd_factor ? number_format($lowest_sd_factor['value'], 2) : '0' }})</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 16px;">
            <div class="card-title">Rincian Nilai Standar Deviasi Faktor</div>
            <table class="table-custom" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Faktor</th>
                        <th>{{ $sd_sector === 'global' ? 'Nilai Standar Deviasi Sektor' : 'SD Sektor' }}</th>
                        @if($sd_sector === 'global')
                            <th>Sektor Paling Bervariasi</th>
                            <th>Sektor Paling Konsisten</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($factor_std_devs as $row)
                        <tr>
                            <td>{{ $row['nama_factor'] }}</td>
                            <td>
                                <strong>
                                    {{ number_format($sd_sector === 'global' ? $row['overall_std_dev'] : $row['sector_std_dev'], 2) }}
                                </strong>
                            </td>
                            @if($sd_sector === 'global')
                                <td>{{ $row['highest_std_sector'] }} ({{ number_format($row['highest_std_val'], 2) }})</td>
                                <td>{{ $row['lowest_std_sector'] }} ({{ number_format($row['lowest_std_val'], 2) }})</td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="page-footer">
            <span>SiSehat Laporan PDF</span>
            <span>Halaman 1 dari 4</span>
        </div>
    </div>

    <!-- PAGE 2: PROFIL 6 FAKTOR -->
    <div class="print-page">
        <div class="page-header">
            <div class="logo-section">
                <img src="{{ asset('images/logo_pandara.png') }}" alt="Pandara Logo">
                <h2>Pandara Health</h2>
            </div>
            <div class="report-meta">
                <p>UMKM: {{ $active_umkm->nama_umkm }}</p>
                <p>Periode: {{ \Carbon\Carbon::parse($assessment->tanggal_mulai ?? $assessment->created_at)->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <div class="section-title">
            <i class="fa-solid fa-users-gear"></i> Profil 6 Faktor
        </div>

        <div class="grid-2" style="margin-bottom: 12px;">
            <div class="card" style="padding: 10px 14px;">
                <div class="card-title" style="margin-bottom: 4px;">Faktor Tertinggi</div>
                <h3 style="font-size: 13px; margin-bottom: 2px;">{{ $highlights['highest']['factor'] ?? 'N/A' }}</h3>
                <div style="font-size: 16px; font-weight: 700; color: var(--success);">{{ number_format($highlights['highest']['score'] ?? 0, 1) }}%</div>
            </div>
            <div class="card" style="padding: 10px 14px;">
                <div class="card-title" style="margin-bottom: 4px;">Faktor Terendah</div>
                <h3 style="font-size: 13px; margin-bottom: 2px;">{{ $highlights['lowest']['factor'] ?? 'N/A' }}</h3>
                <div style="font-size: 16px; font-weight: 700; color: var(--danger);">{{ number_format($highlights['lowest']['score'] ?? 0, 1) }}%</div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 12px; padding: 10px 14px;">
            <div class="card-title" style="margin-bottom: 2px;">Active Insight / Rekomendasi Utama</div>
            <div class="insight-box" style="margin-top: 0; padding: 10px;">
                {!! $recommendations->first()->recommendation_text ?? 'Tidak ada wawasan aktif.' !!}
            </div>
        </div>

        <div class="grid-2" style="margin-bottom: 0; align-items: stretch;">
            <!-- Radar Chart -->
            <div class="card" style="padding: 10px 14px; display: flex; flex-direction: column;">
                <div class="card-title" style="margin-bottom: 4px;">Factor Connectivity Map (Radar Chart)</div>
                <div class="chart-container" style="height: 230px; margin-top: auto;">
                    <canvas id="radarPrintChartPage2"></canvas>
                </div>
            </div>
            
            <!-- Structured Text Outlier Analysis -->
            <div class="card" style="padding: 10px 14px; display: flex; flex-direction: column;">
                <div class="card-title" style="margin-bottom: 6px;">Analisis Sebaran & Anomali Respon (Outliers)</div>
                <div style="display: flex; flex-direction: column; gap: 4px; font-size: 9.5px; overflow-y: auto;">
                    @foreach($outliers as $out)
                        @php
                            $stats = $out['stats'];
                            $hasOut = !empty($stats['outliers']);
                            $outText = '';
                            if ($hasOut) {
                                $outText = 'Terdapat respon anomali pada skor: ' . implode('%, ', array_map(function($v) { return number_format($v, 0); }, $stats['outliers'])) . '%';
                            } else {
                                $outText = 'Respon karyawan konsisten/homogen.';
                            }
                        @endphp
                        <div style="border-bottom: 1px dashed var(--border); padding-bottom: 4px; margin-bottom: 2px;">
                            <div style="display: flex; justify-content: space-between; font-weight: 700; margin-bottom: 1px;">
                                <span style="font-size: 9.5px;">{{ $out['nama_factor'] }}</span>
                                <span style="color: var(--info);">Median: {{ number_format($stats['median'], 1) }}%</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; color: var(--secondary); font-size: 8px;">
                                <span>Konsensus: {{ number_format($stats['min'], 0) }}% - {{ number_format($stats['max'], 0) }}%</span>
                                <span>Kuartil (Q1-Q3): {{ number_format($stats['q1'], 0) }}% - {{ number_format($stats['q3'], 0) }}%</span>
                            </div>
                            <div style="font-size: 8px; color: {{ $hasOut ? 'var(--danger)' : 'var(--success)' }}; font-style: italic;">
                                <i class="fa-solid {{ $hasOut ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i> {{ $outText }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="page-footer">
            <span>SiSehat Laporan PDF</span>
            <span>Halaman 2 dari 4</span>
        </div>
    </div>

    <!-- PAGE 3: PERBANDINGAN FAKTOR & BENCHMARK -->
    <div class="print-page">
        <div class="page-header">
            <div class="logo-section">
                <img src="{{ asset('images/logo_pandara.png') }}" alt="Pandara Logo">
                <h2>Pandara Health</h2>
            </div>
            <div class="report-meta">
                <p>UMKM: {{ $active_umkm->nama_umkm }}</p>
                <p>Periode: {{ \Carbon\Carbon::parse($assessment->tanggal_mulai ?? $assessment->created_at)->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <div class="section-title">
            <i class="fa-solid fa-layer-group"></i> Perbandingan Faktor & Tolok Ukur Industri
        </div>

        <div class="grid-2" style="grid-template-columns: 1.25fr 0.75fr; margin-bottom: 12px;">
            <div class="card" style="padding: 10px 14px;">
                <div class="card-title" style="margin-bottom: 4px;">Perbandingan Faktor & Rata-rata Industri (Bar Chart)</div>
                <div class="chart-container" style="height: 170px;">
                    <canvas id="industryComparisonPrintChart"></canvas>
                </div>
            </div>

            <div class="card" style="padding: 10px;">
                <div class="card-title" style="margin-bottom: 4px;">Peringkat Faktor UMKM</div>
                <div style="display: flex; flex-direction: column; gap: 3px;">
                    @foreach($rankings['all'] as $index => $item)
                        @php
                            $barBg = $item['score'] >= 75 ? '#16a34a' : ($item['score'] >= 50 ? '#2563eb' : ($item['score'] >= 25 ? '#ca8a04' : '#dc2626'));
                        @endphp
                        <div style="margin-bottom: 1px;">
                            <div style="display: flex; justify-content: space-between; font-size: 9px; line-height: 1.1;">
                                <span><strong>#{{ $index + 1 }}</strong> {{ $item['factor'] }}</span>
                                <strong>{{ number_format($item['score'], 1) }}%</strong>
                            </div>
                            <div style="height: 2px; background: #e2e8f0; border-radius: 1px; margin-top: 1px;">
                                <div style="width: {{ $item['score'] }}%; height: 100%; background: {{ $barBg }}; border-radius: 1px;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 12px; padding: 10px 14px;">
            <div class="card-title" style="margin-bottom: 2px;">Penjelasan Perbandingan Industri</div>
            <p style="font-size: 10px; line-height: 1.4; color: var(--secondary);">
                Grafik di atas membandingkan performa UMKM <strong>{{ $active_umkm->nama_umkm }}</strong> (Saat Ini & Sebelumnya) dengan rata-rata <strong>{{ $industry_benchmark['peer_count'] }}</strong> UMKM sejenis pada sektor <strong>{{ $comparison_sector }}</strong>. Dimensi dengan skor di bawah industri disarankan menjadi fokus perhatian dalam perbaikan prioritas.
            </p>
        </div>

        <div class="card" style="padding: 10px 14px; margin-bottom: 0;">
            <div class="card-title" style="margin-bottom: 6px;">Tabel Rincian Perbandingan Faktor (%)</div>
            <table class="table-custom" style="margin-bottom: 0;">
                <thead>
                    <tr>
                        <th>Faktor</th>
                        <th>Skor Saat Ini</th>
                        <th>Skor Sebelumnya</th>
                        <th>Rata-rata Industri ({{ $comparison_sector }})</th>
                        <th>Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($radar_chart as $row)
                        <tr>
                            <td>{{ $row['factor'] }}</td>
                            <td><strong>{{ number_format($row['score'], 1) }}%</strong></td>
                            <td>{{ $row['prev_score'] !== null ? number_format($row['prev_score'], 1) . '%' : '-' }}</td>
                            <td>{{ $row['industry_avg'] !== null ? number_format($row['industry_avg'], 1) . '%' : '-' }}</td>
                            <td>
                                @if($row['diff_prev'] !== null)
                                    @if($row['diff_prev'] > 0)
                                        <span style="color: var(--success); font-weight: 600;">+{{ number_format($row['diff_prev'], 1) }}</span>
                                    @elseif($row['diff_prev'] < 0)
                                        <span style="color: var(--danger); font-weight: 600;">{{ number_format($row['diff_prev'], 1) }}</span>
                                    @else
                                        <span style="color: var(--secondary);">Stabil</span>
                                    @endif
                                @else
                                    <span style="color: var(--secondary);">Baru</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="page-footer">
            <span>SiSehat Laporan PDF</span>
            <span>Halaman 3 dari 4</span>
        </div>
    </div>

    <!-- PAGE 4: REKOMENDASI & RANKING -->
    <div class="print-page">
        <div class="page-header">
            <div class="logo-section">
                <img src="{{ asset('images/logo_pandara.png') }}" alt="Pandara Logo">
                <h2>Pandara Health</h2>
            </div>
            <div class="report-meta">
                <p>UMKM: {{ $active_umkm->nama_umkm }}</p>
                <p>Periode: {{ \Carbon\Carbon::parse($assessment->tanggal_mulai ?? $assessment->created_at)->translatedFormat('F Y') }}</p>
            </div>
        </div>

        <div class="section-title">
            <i class="fa-solid fa-gauge-high"></i> Kesehatan Keseluruhan & Status
        </div>

        <div class="grid-2" style="grid-template-columns: 0.75fr 1.25fr; margin-bottom: 12px;">
            <!-- Gauge Card -->
            <div class="card" style="text-align: center; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 10px;">
                <div class="card-title" style="margin-bottom: 4px;">Kesehatan Keseluruhan</div>
                <div style="position: relative; width: 120px; height: 65px; margin: 0 auto;">
                    <canvas id="healthGaugePrintChart" style="width: 120px; height: 65px;"></canvas>
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; text-align: center;">
                        <span style="font-size: 18px; font-weight: 800;">{{ number_format($health_score->overall_score ?? 0, 0) }}%</span>
                    </div>
                </div>
                <div style="margin-top: 4px;">
                    <span class="health-badge {{ $badgeClass }}" style="font-size: 8px;">Status: {{ str_replace('_', ' ', strtoupper($health_score->category ?? 'N/A')) }}</span>
                </div>
            </div>
            
            <!-- Context card -->
            <div class="card" style="display: flex; align-items: center; padding: 10px 14px;">
                <p style="font-size: 10px; line-height: 1.5; color: var(--secondary);">
                    Berdasarkan analisis terbaru pada periode terpilih, berikut adalah tindakan yang disarankan untuk meningkatkan kesehatan organisasi secara keseluruhan. Prioritaskan perbaikan dimensi dengan status kritis dan waspada terlebih dahulu.
                </p>
            </div>
        </div>

        <div class="section-title">
            <i class="fa-solid fa-circle-exclamation"></i> Prioritas Perbaikan
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 8px;">
            @forelse($recommendations as $rec)
                @php
                    $level = strtolower($rec->category_level);
                    $prioText = 'RENDAH';
                    $borderColor = '#16a34a';
                    $badgeStyle = 'background: #dcfce7; color: #15803d;';

                    if ($level == 'kurang_sehat') {
                        $prioText = 'KRITIS';
                        $borderColor = '#dc2626';
                        $badgeStyle = 'background: #fee2e2; color: #b91c1c;';
                    } elseif ($level == 'cukup_sehat') {
                        $prioText = 'WASPADA';
                        $borderColor = '#fb923c';
                        $badgeStyle = 'background: #ffedd5; color: #c2410c;';
                    } elseif ($level == 'sehat') {
                        $prioText = 'SEHAT';
                        $borderColor = '#2563eb';
                        $badgeStyle = 'background: #dbeafe; color: #1d4ed8;';
                    } elseif ($level == 'sangat_sehat') {
                        $prioText = 'SANGAT SEHAT';
                        $borderColor = '#16a34a';
                        $badgeStyle = 'background: #dcfce7; color: #15803d;';
                    }

                    $factorName = collect($radar_chart)->firstWhere('id', $rec->factor_id)['factor'] ?? 'Faktor ' . $rec->factor_id;
                @endphp
                <div class="card" style="padding: 6px 10px; border-left: 4px solid {{ $borderColor }}; margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;">
                            <span style="font-weight: 700; font-size: 9.5px;">{{ $factorName }}</span>
                            <span style="font-size: 7.5px; font-weight: 700; padding: 1px 3px; border-radius: 2px; {{ $badgeStyle }}">{{ $prioText }}</span>
                        </div>
                        <p style="font-size: 8.5px; line-height: 1.25; color: #334155; margin-bottom: 4px;">{{ $rec->recommendation_text }}</p>
                    </div>
                    @if($rec->suggested_action)
                        <div style="font-size: 7.5px; color: var(--secondary); border-top: 1px dashed #e2e8f0; padding-top: 2px; margin-top: auto;">
                            <i class="fa-solid fa-bolt" style="color: var(--warning);"></i> Tindakan: <strong>{{ $rec->suggested_action }}</strong>
                        </div>
                    @endif
                </div>
            @empty
                <div class="card" style="grid-column: span 2; text-align: center; padding: 16px; color: var(--success);">
                    <i class="fa-solid fa-circle-check" style="font-size: 16px; margin-bottom: 4px;"></i>
                    <p style="font-size: 9.5px;">Tidak ada prioritas perbaikan mendesak. Kondisi UMKM sangat sehat!</p>
                </div>
            @endforelse
        </div>

        <div class="section-title" style="margin-top: 4px;">
            <i class="fa-solid fa-ranking-star"></i> Peringkat Skor Kesehatan UMKM Anda
        </div>

        <div class="card" style="padding: 10px 14px; margin-bottom: 0;">
            <div class="card-title" style="margin-bottom: 4px;">Peringkat Internal Owner (Periode: {{ \Carbon\Carbon::parse($selected_period)->translatedFormat('F Y') }})</div>
            @if($all_umkm_scores->isEmpty())
                <p style="font-size: 11px; color: var(--secondary); text-align: center; padding: 12px;">Tidak ada data peringkat pada periode ini.</p>
            @else
                <table class="table-custom" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">Rank</th>
                            <th>Nama UMKM</th>
                            <th style="text-align: center;">Skor Kesehatan</th>
                            @foreach($all_factors as $f)
                                <th style="text-align: center; font-size: 7px;">{{ strtoupper(substr($f->nama_factor, 0, 3)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($all_umkm_scores as $index => $scoreRow)
                            <tr style="{{ $scoreRow->umkm_id == $active_umkm->umkm_id ? 'background-color: #f1f5f9; font-weight: 600;' : '' }}">
                                <td style="text-align: center;">#{{ $index + 1 }}</td>
                                <td>{{ $scoreRow->nama_umkm }}</td>
                                <td style="text-align: center;"><strong>{{ number_format($scoreRow->overall_score, 1) }}</strong></td>
                                @foreach($all_factors as $f)
                                    @php
                                        $fScoreObj = isset($all_factor_scores[$scoreRow->health_score_id])
                                            ? $all_factor_scores[$scoreRow->health_score_id]->firstWhere('factor_id', $f->factor_id)
                                            : null;
                                        $fVal = $fScoreObj ? number_format($fScoreObj->score, 1) : '-';
                                    @endphp
                                    <td style="text-align: center; font-size: 9px;">{{ $fVal }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="page-footer">
            <span>SiSehat Laporan PDF</span>
            <span>Halaman 4 dari 4</span>
        </div>
    </div>

    <!-- Script rendering and auto print -->
    <script>
        // Data for Charts
        const radarRawData = @json($radar_chart);
        const radarLabelsPage2 = radarRawData.map(d => [d.factor, d.score + '%']);
        const radarLabelsBase = radarRawData.map(d => d.factor);
        const radarScores = radarRawData.map(d => d.score);
        const radarPrevScores = radarRawData.map(d => d.prev_score);
        const radarIndustryScores = radarRawData.map(d => d.industry_avg);

        // ── Radar Chart Page 2 (Connectivity Map) ──
        const radarCtxPage2 = document.getElementById('radarPrintChartPage2').getContext('2d');
        const radarChartPage2 = new Chart(radarCtxPage2, {
            type: 'radar',
            data: {
                labels: radarLabelsPage2,
                datasets: [{
                    label: 'Skor Saat Ini',
                    data: radarScores,
                    fill: true,
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    borderColor: '#2563eb',
                    pointBackgroundColor: '#2563eb',
                }]
            },
            options: {
                animation: false,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: '#e2e8f0' },
                        grid: { color: '#e2e8f0' },
                        pointLabels: { color: '#334155', font: { size: 7, weight: '600' } },
                        ticks: { display: false },
                        suggestedMin: 0,
                        suggestedMax: 100
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // ── Industry Comparison Bar Chart Page 3 (Unified 3 Bars) ──
        const industryComparisonCtx = document.getElementById('industryComparisonPrintChart').getContext('2d');
        
        const datasetsPage3 = [
            {
                label: 'Skor Saat Ini',
                data: radarScores,
                backgroundColor: '#2563eb',
                borderRadius: 3
            }
        ];

        // Add previous score dataset if previous assessment exists
        const hasPrevVal = radarPrevScores.some(s => s !== null);
        datasetsPage3.push({
            label: 'Skor Sebelumnya',
            data: radarPrevScores.map(v => v === null ? 0 : v),
            backgroundColor: '#94a3b8',
            borderRadius: 3
        });

        // Add industry average dataset
        const hasIndustryVal = radarIndustryScores.some(s => s !== null);
        datasetsPage3.push({
            label: 'Rata-rata Industri',
            data: radarIndustryScores.map(v => v === null ? 0 : v),
            backgroundColor: 'rgba(202, 138, 4, 0.7)',
            borderRadius: 3
        });

        const industryComparisonChart = new Chart(industryComparisonCtx, {
            type: 'bar',
            data: {
                labels: radarLabelsBase,
                datasets: datasetsPage3
            },
            options: {
                animation: false,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 8 } }
                    }
                },
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { color: '#f1f5f9' }, ticks: { font: { size: 8 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 6, weight: '600' } } }
                }
            }
        });

        // ── Overall Health Gauge Page 4 ──
        const healthGaugeCtx = document.getElementById('healthGaugePrintChart').getContext('2d');
        const avgHealthVal = {{ $health_score->overall_score ?? 0 }};
        let healthGaugeColor = '#f87171';
        if (avgHealthVal > 75) healthGaugeColor = '#16a34a';
        else if (avgHealthVal > 50) healthGaugeColor = '#2563eb';
        else if (avgHealthVal > 25) healthGaugeColor = '#ca8a04';

        const healthGaugeChart = new Chart(healthGaugeCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [avgHealthVal, 100 - avgHealthVal],
                    backgroundColor: [healthGaugeColor, '#e2e8f0'],
                    borderWidth: 0,
                    cutout: '80%',
                    circumference: 180,
                    rotation: 270
                }]
            },
            options: {
                animation: false,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });

        // Automatically trigger print dialog once all rendering is done
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>
