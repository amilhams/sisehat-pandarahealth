@extends('layouts.app')

@section('title', 'Monitoring Live')

@section('styles')
<style>
    .monitoring-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }
    @media (max-width: 768px) {
        .monitoring-grid { grid-template-columns: 1fr; }
        .header-section { flex-direction: column; align-items: flex-start; gap: 16px; }
        .header-section > div:last-child { width: 100%; justify-content: space-between; }
    }
</style>
@endsection

@section('content')
<div class="header-section mb-32 flex-between">
    <div>
        <h1 style="font-size: 28px; margin-bottom: 8px;">Monitoring Live</h1>
        <p style="color: var(--text-secondary);">Pantau partisipasi karyawan secara real-time selama periode assessment.</p>
    </div>
    <div style="display: flex; align-items: center; gap: 12px;">
        @if(isset($all_umkms) && $all_umkms->count() > 1)
            <select onchange="window.location.href='?umkm_id=' + this.value" style="background: var(--card-color); border: 1px solid var(--border-color); padding: 8px 16px; border-radius: 8px; color: white; font-size: 14px; outline: none; cursor: pointer;">
                @foreach($all_umkms as $u)
                    <option value="{{ $u->umkm_id }}" {{ $selected_umkm_id == $u->umkm_id ? 'selected' : '' }}>
                        {{ $u->nama_umkm }}
                    </option>
                @endforeach
            </select>
        @endif
        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; background: rgba(74, 222, 128, 0.1); color: var(--success); padding: 8px 16px; border-radius: 20px;">
            <span style="width: 8px; height: 8px; background: var(--success); border-radius: 50%;"></span> Koneksi Aktif
        </div>
    </div>
</div>

<div class="monitoring-grid">
    <!-- Total Responden -->
    <div class="card">
        <span class="stat-label">Total Responden</span>
        <div class="flex-between" style="margin-top: 12px;">
            <div>
                <div style="font-size: 36px; font-weight: 700;">{{ number_format($total_respondents) }} <small style="font-size: 16px; font-weight: 400; color: var(--text-secondary);">/ {{ number_format($target_respondents) }}</small></div>
                <div style="font-size: 12px; color: var(--success); margin-top: 8px;"><i class="fa-solid fa-chart-line"></i> Sedang Berlangsung</div>
            </div>
            <i class="fa-solid fa-users" style="font-size: 32px; color: var(--text-secondary); opacity: 0.3;"></i>
        </div>
    </div>

    <!-- Tingkat Penyelesaian -->
    <div class="card">
        <span class="stat-label">Tingkat Penyelesaian</span>
        <div style="display: flex; align-items: center; gap: 24px; margin-top: 12px;">
            <div style="width: 80px; height: 80px; position: relative; display: flex; align-items: center; justify-content: center;">
                <canvas id="completionChart"></canvas>
                <div style="position: absolute; font-size: 16px; font-weight: 700; color: #fff;">{{ $completion_rate }}%</div>
            </div>
            <div>
                <div style="font-size: 14px; margin-bottom: 4px;">Target minimal {{ number_format($target_respondents) }} responden.</div>
                @if($completion_rate >= 80)
                    <div style="font-size: 12px; color: var(--success);"><i class="fa-solid fa-circle-check"></i> Optimal</div>
                @else
                    <div style="font-size: 12px; color: var(--warning);"><i class="fa-solid fa-clock"></i> Belum Tercapai</div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Skor Kesehatan Estimasi -->
<div class="card">
    <div class="flex-between mb-32">
        <span class="stat-label">Skor Kesehatan (Estimasi)</span>
        @php
            // Logika Warna Baru (Interval 25%)
            if ($estimated_score > 75) {
                $catColor = 'var(--success)';
                $catBg = 'rgba(74,222,128,0.1)';
            } elseif ($estimated_score > 50) {
                $catColor = '#818cf8'; // Biru Indigo
                $catBg = 'rgba(129, 140, 248, 0.1)';
            } elseif ($estimated_score > 25) {
                $catColor = 'var(--warning)';
                $catBg = 'rgba(250, 204, 21, 0.1)';
            } else {
                $catColor = 'var(--danger)';
                $catBg = 'rgba(248, 113, 113, 0.1)';
            }
        @endphp
        <span style="font-size: 10px; padding: 4px 8px; background: {{ $catBg }}; color: {{ $catColor }}; border-radius: 4px; font-weight: bold;">{{ str_replace('_', ' ', strtoupper($estimated_category)) }}</span>
    </div>
    <div style="font-size: 48px; font-weight: 700; margin-bottom: 8px;">{{ $estimated_score }} <small style="font-size: 20px; font-weight: 400; color: var(--text-secondary);">/ 100</small></div>
    <p style="font-size: 14px; color: var(--text-secondary);">Data fluktuatif hingga assessment ditutup. Skor ini adalah estimasi dari jawaban saat ini.</p>
</div>

<!-- Activity Log -->
<div class="card">
    <h3 class="card-title">Log Aktivitas ({{ count($recent_activity) }} Terbaru)</h3>
    <div class="activity-list">
        @forelse($recent_activity as $act)
        <div class="flex-between" style="padding: 16px 0; border-bottom: 1px solid var(--border-color);">
            <div style="display: flex; gap: 16px; align-items: center;">
                <div style="width: 32px; height: 32px; background: #222; border-radius: 50%; display: flex; align-items: center; justify-content: center;"><i class="fa-solid fa-user" style="font-size: 12px;"></i></div>
                <div>
                    <div style="font-size: 14px;">Responden {{ $act->employee_code ?: 'Anonim' }} menyelesaikan assessment.</div>
                    <div style="font-size: 12px; color: var(--text-secondary);">{{ \Carbon\Carbon::parse($act->last_active)->diffForHumans() }}</div>
                </div>
            </div>
        </div>
        @empty
        <div class="flex-between" style="padding: 16px 0; border-bottom: 1px solid var(--border-color);">
            <div style="font-size: 14px; color: var(--text-secondary);">Belum ada aktivitas.</div>
        </div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
    const compRate = {{ $completion_rate }};
    new Chart(document.getElementById('completionChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [compRate, 100 - compRate],
                backgroundColor: [compRate >= 80 ? '#4ade80' : '#facc15', '#222222'],
                borderWidth: 0,
                cutout: '80%'
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            maintainAspectRatio: false
        }
    });
</script>
@endsection
