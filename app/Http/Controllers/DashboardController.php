<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\HealthScore;
use App\Models\FactorScore;
use App\Models\AssessmentRecommendation;
use App\Models\Umkm;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Mengambil health score terbaru untuk UMKM
     */
    public function getLatestScore($umkmId)
    {
        $latestAssessment = Assessment::where('umkm_id', $umkmId)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestAssessment) {
            return response()->json(['message' => 'Belum ada data assessment.'], 404);
        }

        $healthScore = HealthScore::where('assessment_id', $latestAssessment->assessment_id)->first();

        return response()->json([
            'assessment' => $latestAssessment,
            'score' => $healthScore
        ]);
    }

    /**
     * Mengambil analisis mendalam (Radar Chart, Rekomendasi DSS)
     */
    public function getDetailedAnalysis($assessmentId)
    {
        $assessment = Assessment::with('umkm')->find($assessmentId);
        if (!$assessment) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json($this->getDetailedData($assessmentId));
    }

    /**
     * Logic inti untuk mendapatkan data detail analisis
     */
    private function getDetailedData($assessmentId, $customIndustry = null)
    {
        $assessment = Assessment::with('umkm')->find($assessmentId);
        if (!$assessment) {
            return null;
        }

        // 1. Ambil Skor & Kategori Utama (Hitung live jika belum ada atau masih in_progress)
        $healthScore = HealthScore::where('assessment_id', $assessmentId)->first();
        if (!$healthScore || $assessment->status === 'in_progress') {
            $healthService = app(\App\Services\HealthService::class);
            $healthScore = $healthService->calculate($assessmentId);
        }
        
        // 2. Ambil Skor per Faktor dari tabel factor_scores
        $factorScores = $healthScore 
            ? FactorScore::with('factor')->where('health_score_id', $healthScore->health_score_id)->get()
            : collect();

        // 5. Trend Analysis (Perkembangan Faktor dibanding assessment sebelumnya)
        $previousAssessment = Assessment::where('umkm_id', $assessment->umkm_id)
            ->where('assessment_id', '<', $assessmentId)
            ->whereIn('status', ['open', 'closed', 'completed']) // Mengakomodasi status yang ada di database
            ->orderBy('created_at', 'desc')
            ->first();

        $prevHealthScore = $previousAssessment ? HealthScore::where('assessment_id', $previousAssessment->assessment_id)->first() : null;
        $prevScores = $prevHealthScore 
            ? FactorScore::where('health_score_id', $prevHealthScore->health_score_id)->pluck('score', 'factor_id')
            : collect();

        // 6. Industry Benchmark
        $industry = $customIndustry ?: ($assessment->umkm->sektor_usaha ?? null);
        $industryAvgScores = collect();
        $peerCount = 0;
        
        if ($industry) {
            $industryAvgScores = DB::table('factor_scores as fs')
                ->join('health_scores as hs', 'fs.health_score_id', '=', 'hs.health_score_id')
                ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
                ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
                ->where('u.sektor_usaha', $industry)
                ->whereIn('a.status', ['open', 'closed', 'completed'])
                ->groupBy('fs.factor_id')
                ->select('fs.factor_id', DB::raw('AVG(fs.score) as avg_score'))
                ->pluck('avg_score', 'fs.factor_id');

            $peerCount = Assessment::join('umkms', 'assessments.umkm_id', '=', 'umkms.umkm_id')
                ->join('health_scores as hs', 'assessments.assessment_id', '=', 'hs.assessment_id')
                ->where('umkms.sektor_usaha', $industry)
                ->whereIn('assessments.status', ['open', 'closed', 'completed'])
                ->count();
        }

        $radarData = $factorScores->map(function($fs) use ($prevScores, $industryAvgScores) {
            $prevScore = $prevScores->get($fs->factor_id);
            $industryAvg = $industryAvgScores->get($fs->factor_id);
            $diff = $prevScore !== null ? round($fs->score - $prevScore, 2) : null;
            
            $changeLabel = "Data Baru";
            if ($diff !== null) {
                if ($diff > 0) $changeLabel = "Naik " . abs($diff) . " poin";
                elseif ($diff < 0) $changeLabel = "Turun " . abs($diff) . " poin";
                else $changeLabel = "Stabil";
            }

            return [
                'id' => $fs->factor_id,
                'factor' => $fs->factor?->nama_factor ?? 'Faktor Tidak Diketahui',
                'code' => $fs->factor?->nama_factor ?? 'N/A',
                'score' => round((float) $fs->score, 1),
                'prev_score' => $prevScore !== null ? round((float) $prevScore, 1) : null,
                'industry_avg' => $industryAvg !== null ? round((float) $industryAvg, 1) : null,
                'diff_prev' => $diff,
                'change_label' => $changeLabel,
                'trend' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'stable'),
                'category' => $fs->category
            ];
        });

        // 3. Cari Faktor Tertinggi & Terendah (Sorted List)
        $rankList = $radarData->sortByDesc('score')->values();
        $highestFactor = $rankList->first();
        $lowestFactor = $rankList->last();

        // 4. Ambil Rekomendasi DSS
        $recommendations = DB::table('assessment_recommendations as ar')
            ->join('recommendations as r', 'ar.recommendation_id', '=', 'r.recommendation_id')
            ->where('ar.assessment_id', $assessmentId)
            ->select('r.*')
            ->orderBy(DB::raw("CASE 
                WHEN r.category_level = 'KURANG_SEHAT' THEN 1
                WHEN r.category_level = 'CUKUP_SEHAT' THEN 2
                WHEN r.category_level = 'SEHAT' THEN 3
                WHEN r.category_level = 'SANGAT_SEHAT' THEN 4
                ELSE 5 END"))
            ->get();

        // Jika rekomendasi kosong (mungkin karena data master baru di-reset), lakukan trigger kalkulasi ulang otomatis
        if ($recommendations->isEmpty()) {
            $healthService = app(\App\Services\HealthService::class);
            $healthService->calculate($assessmentId);
            
            // Ambil ulang data setelah kalkulasi
            $recommendations = DB::table('assessment_recommendations as ar')
                ->join('recommendations as r', 'ar.recommendation_id', '=', 'r.recommendation_id')
                ->where('ar.assessment_id', $assessmentId)
                ->select('r.*')
                ->orderBy(DB::raw("CASE 
                    WHEN r.category_level = 'KURANG_SEHAT' THEN 1
                    WHEN r.category_level = 'CUKUP_SEHAT' THEN 2
                    WHEN r.category_level = 'SEHAT' THEN 3
                    WHEN r.category_level = 'SANGAT_SEHAT' THEN 4
                    ELSE 5 END"))
                ->get();
        }

        return [
            'assessment_info' => $assessment,
            'industry_benchmark' => [
                'sektor_usaha' => $industry,
                'peer_count' => $peerCount
            ],
            'previous_assessment_info' => $previousAssessment,
            'health_score' => $healthScore,
            'radar_chart' => $radarData,
            'rankings' => [
                'all' => $rankList,
                'top_3' => $rankList->take(3),
                'bottom_3' => $rankList->take(-3)->reverse()->values()
            ],
            'highlights' => [
                'highest' => $highestFactor,
                'lowest' => $lowestFactor
            ],
            'recommendations' => $recommendations
        ];
    }

    // ==========================================
    // WEB UI METHODS (MENGEMBALIKAN VIEW)
    // ==========================================

    private function getActiveData(Request $request)
    {
        // Gunakan guard owner atau default auth
        $owner = Auth::guard('owner')->user() ?? auth()->user();
        $allUmkms = $owner ? Umkm::where('owner_id', $owner->owner_id)->get() : collect();
        $hasUmkm = $allUmkms->count() > 0;
        
        $selectedUmkmId = $request->query('umkm_id');
        $activeUmkm = null;
        
        if ($hasUmkm) {
            if ($selectedUmkmId) {
                $activeUmkm = $allUmkms->where('umkm_id', $selectedUmkmId)->first();
            }
            if (!$activeUmkm) {
                $activeUmkm = $allUmkms->first();
            }
        }

        $latestAssessment = null;
        $assessmentWithScore = null;
        $hasAssessment = false;

        if ($activeUmkm) {
            $latestAssessment = Assessment::where('umkm_id', $activeUmkm->umkm_id)->latest()->first();
            // Cek apakah ada respon untuk assessment ini
            $hasAssessment = $latestAssessment && \Illuminate\Support\Facades\DB::table('responses')
                ->where('assessment_id', $latestAssessment->assessment_id)
                ->exists();
            
            $assessmentWithScore = $latestAssessment;
        }

        // Check if user has ANY UMKM or ANY Assessment at all in their account
        $hasAnyUmkm = $allUmkms->count() > 0;
        $hasAnyAssessment = false;
        if ($hasAnyUmkm) {
            $hasAnyAssessment = \Illuminate\Support\Facades\DB::table('responses')
                ->whereIn('assessment_id', function($query) use ($allUmkms) {
                    $query->select('assessment_id')->from('assessments')->whereIn('umkm_id', $allUmkms->pluck('umkm_id'));
                })->exists();
        }

        return [
            'owner' => $owner,
            'all_umkms' => $allUmkms,
            'active_umkm' => $activeUmkm,
            'has_umkm' => $hasUmkm,
            'has_assessment' => $hasAssessment,
            'latest_assessment' => $latestAssessment,
            'assessment_with_score' => $assessmentWithScore,
            'selected_umkm_id' => $activeUmkm ? $activeUmkm->umkm_id : null,
            
            // Global Popups (for new users)
            'show_umkm_popup' => !$hasAnyUmkm,
            'show_assessment_popup' => ($hasAnyUmkm && !$hasAnyAssessment),
            
            // In-page Notice (for existing users switching to an empty UMKM)
            'show_in_page_notice' => ($hasAnyAssessment && !$hasAssessment)
        ];
    }

    private function getDefaultAssessment()
    {
        // This is now redundant but kept for compatibility or internal logic if needed
        $data = $this->getActiveData(request());
        return $data['assessment_with_score'];
    }

    private function getUmkmStatus()
    {
        $data = $this->getActiveData(request());
        return ['has_umkm' => $data['has_umkm'], 'has_assessment' => $data['has_assessment']];
    }

    public function webDashboard(Request $request)
    {
        $data = $this->getActiveData($request);
        
        $myAvgHealth = 0;
        $myHealthCategory = 'N/A';
        $myFactors = collect();
        
        if ($data['assessment_with_score']) {
            $assessmentId = $data['assessment_with_score']->assessment_id;
            
            // Trigger recalculation if in_progress or forced by user
            if ($data['assessment_with_score']->status === 'in_progress' || $request->query('recalculate')) {
                $healthService = app(\App\Services\HealthService::class);
                $hs = $healthService->calculate($assessmentId);
            } else {
                $hs = HealthScore::where('assessment_id', $assessmentId)->first();
            }

            $myAvgHealth = $hs ? round($hs->overall_score, 1) : 0;
            $myHealthCategory = $hs ? $hs->category : 'PENDING';
            
            $myFactors = $hs ? DB::table('factor_scores as fs')
                ->join('factors as f', 'fs.factor_id', '=', 'f.factor_id')
                ->where('fs.health_score_id', $hs->health_score_id)
                ->select('f.nama_factor', 'fs.score as avg_score')
                ->get() : collect();
        }

        // Aggregate ecosystem statistics
        $totalUmkm = Umkm::count();
        $totalEmployees = DB::table('responses')
            ->where('respondent_type', 'employee')
            ->distinct('employee_code')
            ->count('employee_code');

        $totalOwners = DB::table('responses')
            ->join('assessments', 'responses.assessment_id', '=', 'assessments.assessment_id')
            ->join('umkms', 'assessments.umkm_id', '=', 'umkms.umkm_id')
            ->where('responses.respondent_type', 'owner')
            ->distinct('umkms.owner_id')
            ->count('umkms.owner_id');

        $totalRespondents = $totalEmployees + $totalOwners;
        
        $healthScores = HealthScore::all();
        $avgHealth = $healthScores->avg('overall_score') ?? 0;
        
        $sehatCount = $healthScores->where('overall_score', '>=', 60)->count();
        $kritisCount = $healthScores->where('overall_score', '<', 60)->count();

        // Business Type Distribution
        $bizTypes = Umkm::select('sektor_usaha', DB::raw('count(*) as count'))
            ->groupBy('sektor_usaha')
            ->pluck('count', 'sektor_usaha');

        // Gender Distribution
        $genderDist = Owner::select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->pluck('count', 'gender');

        // Top UMKM Ranking (Hanya mengambil asesmen terakhir untuk tiap UMKM)
        $topUmkms = DB::table('health_scores as hs')
            ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
            ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
            ->whereIn('a.assessment_id', function ($query) {
                $query->select(DB::raw('MAX(assessment_id)'))
                    ->from('assessments')
                    ->groupBy('umkm_id');
            })
            ->select('u.nama_umkm', 'u.sektor_usaha', 'hs.overall_score', 'hs.category as health_category')
            ->orderByDesc('hs.overall_score')
            ->limit(5)
            ->get();

        // Average Factor Scores
        $avgFactors = DB::table('factor_scores as fs')
            ->join('factors as f', 'fs.factor_id', '=', 'f.factor_id')
            ->select('f.nama_factor', DB::raw('AVG(fs.score) as avg_score'))
            ->groupBy('f.factor_id', 'f.nama_factor')
            ->get();

        // Company Age Distribution
        $ageDist = Umkm::select('umur_usaha', DB::raw('count(*) as count'))
            ->groupBy('umur_usaha')
            ->pluck('count', 'umur_usaha');

        // Sector & Factor Analysis (Grouped Bar Chart and Standard Deviation Stats)
        $sectorFactorDataQuery = DB::table('factor_scores as fs')
            ->join('health_scores as hs', 'fs.health_score_id', '=', 'hs.health_score_id')
            ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
            ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
            ->join('factors as f', 'fs.factor_id', '=', 'f.factor_id')
            ->whereNotNull('u.sektor_usaha')
            ->where('u.sektor_usaha', '!=', '');

        $allSectorFactorData = $sectorFactorDataQuery->select('u.sektor_usaha', 'f.factor_id', 'f.nama_factor', 'fs.score')->get();

        $sectors = $allSectorFactorData->pluck('sektor_usaha')->unique()->values()->all();
        $factorsList = DB::table('factors')->orderBy('urutan')->get();

        $selSector = $request->query('filter_sektor', 'global');

        // Build Chart Data
        $sectorChartData = [];
        // Palette: White, Gray, Light Slate/Indigo shades (Sleek dark theme look)
        $premiumColors = ['#ffffff', '#a3a3a3', '#818cf8', '#a78bfa', '#2dd4bf', '#38bdf8'];

        if ($selSector === 'global') {
            $sectorChartData = [
                'type' => 'global',
                'labels' => $sectors,
                'datasets' => []
            ];

            $groupedData = [];
            foreach ($allSectorFactorData as $row) {
                $groupedData[$row->sektor_usaha][$row->factor_id][] = (float)$row->score;
            }

            foreach ($factorsList as $index => $factor) {
                $datasetData = [];
                foreach ($sectors as $sector) {
                    $scores = $groupedData[$sector][$factor->factor_id] ?? [];
                    $datasetData[] = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
                }
                $sectorChartData['datasets'][] = [
                    'label' => $factor->nama_factor,
                    'data' => $datasetData,
                    'backgroundColor' => $premiumColors[$index % count($premiumColors)],
                    'borderRadius' => 4
                ];
            }
        } else {
            $sectorChartData = [
                'type' => 'sector',
                'labels' => $factorsList->pluck('nama_factor')->all(),
                'datasets' => [
                    [
                        'label' => 'Rata-rata Skor Sektor ' . $selSector,
                        'data' => [],
                        'backgroundColor' => $premiumColors,
                        'borderRadius' => 6
                    ]
                ]
            ];

            $groupedData = [];
            foreach ($allSectorFactorData as $row) {
                if ($row->sektor_usaha === $selSector) {
                    $groupedData[$row->factor_id][] = (float)$row->score;
                }
            }

            foreach ($factorsList as $factor) {
                $scores = $groupedData[$factor->factor_id] ?? [];
                $sectorChartData['datasets'][0]['data'][] = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
            }
        }

        // Build Std Dev Data
        $factorStdDevs = [];
        $factorScoresGrouped = [];
        $factorSectorsScoresGrouped = [];
        foreach ($allSectorFactorData as $row) {
            $score = (float)$row->score;
            $factorScoresGrouped[$row->factor_id][] = $score;
            $factorSectorsScoresGrouped[$row->factor_id][$row->sektor_usaha][] = $score;
        }

        $sectorScoresData = collect();
        if ($selSector !== 'global') {
            $sectorScoresData = DB::table('factor_scores as fs')
                ->join('health_scores as hs', 'fs.health_score_id', '=', 'hs.health_score_id')
                ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
                ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
                ->where('u.sektor_usaha', $selSector)
                ->select('u.nama_umkm', 'fs.factor_id', 'fs.score')
                ->get();
        }

        foreach ($factorsList as $factor) {
            $fId = $factor->factor_id;
            
            if ($selSector === 'global') {
                $allScores = $factorScoresGrouped[$fId] ?? [];
                $overallStdDev = $this->calculateStdDev($allScores);

                $highestStdSector = '-';
                $highestStdVal = -1.0;
                $lowestStdSector = '-';
                $lowestStdVal = 999999.0;

                foreach ($sectors as $sector) {
                    $scores = $factorSectorsScoresGrouped[$fId][$sector] ?? [];
                    if (count($scores) >= 2) {
                        $std = $this->calculateStdDev($scores);
                        if ($std > $highestStdVal) {
                            $highestStdVal = $std;
                            $highestStdSector = $sector;
                        }
                        if ($std < $lowestStdVal) {
                            $lowestStdVal = $std;
                            $lowestStdSector = $sector;
                        }
                    } elseif (count($scores) == 1) {
                        $std = 0.0;
                        if ($std > $highestStdVal) {
                            $highestStdVal = $std;
                            $highestStdSector = $sector;
                        }
                        if ($std < $lowestStdVal) {
                            $lowestStdVal = $std;
                            $lowestStdSector = $sector;
                        }
                    }
                }

                if ($highestStdVal === -1.0) $highestStdVal = 0.0;
                if ($lowestStdVal === 999999.0) $lowestStdVal = 0.0;

                $factorStdDevs[] = [
                    'nama_factor' => $factor->nama_factor,
                    'overall_std_dev' => round($overallStdDev / 20, 2),
                    'highest_std_sector' => $highestStdSector,
                    'highest_std_val' => round($highestStdVal / 20, 2),
                    'lowest_std_sector' => $lowestStdSector,
                    'lowest_std_val' => round($lowestStdVal / 20, 2)
                ];
            } else {
                $scores = $factorSectorsScoresGrouped[$fId][$selSector] ?? [];
                $sectorStdDev = count($scores) >= 2 ? $this->calculateStdDev($scores) : 0.0;

                $factorSectorScores = $sectorScoresData->where('factor_id', $fId);
                $highestFactorUmkm = $factorSectorScores->sortByDesc('score')->first();
                $lowestFactorUmkm = $factorSectorScores->sortBy('score')->first();

                $factorStdDevs[] = [
                    'nama_factor' => $factor->nama_factor,
                    'sector_std_dev' => round($sectorStdDev / 20, 2),
                    'highest_score' => $highestFactorUmkm ? round($highestFactorUmkm->score / 20, 2) : null,
                    'highest_umkm' => $highestFactorUmkm ? $highestFactorUmkm->nama_umkm : '-',
                    'lowest_score' => $lowestFactorUmkm ? round($lowestFactorUmkm->score / 20, 2) : null,
                    'lowest_umkm' => $lowestFactorUmkm ? $lowestFactorUmkm->nama_umkm : '-'
                ];
            }
        }

        // Highest and Lowest UMKM Query
        $umkmRankQuery = DB::table('health_scores as hs')
            ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
            ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
            ->select('u.nama_umkm', 'u.sektor_usaha', 'hs.overall_score');

        if ($selSector !== 'global') {
            $umkmRankQuery->where('u.sektor_usaha', $selSector);
        }

        $highestUmkmRecord = (clone $umkmRankQuery)->orderByDesc('hs.overall_score')->first();
        $lowestUmkmRecord = (clone $umkmRankQuery)->orderBy('hs.overall_score')->first();

        $highestUmkm = $highestUmkmRecord ? [
            'nama' => $highestUmkmRecord->nama_umkm,
            'score' => round($highestUmkmRecord->overall_score, 1),
            'sektor' => $highestUmkmRecord->sektor_usaha
        ] : null;

        $lowestUmkm = $lowestUmkmRecord ? [
            'nama' => $lowestUmkmRecord->nama_umkm,
            'score' => round($lowestUmkmRecord->overall_score, 1),
            'sektor' => $lowestUmkmRecord->sektor_usaha
        ] : null;

        // Find highest/lowest SD factor
        $highestSdFactor = null;
        $lowestSdFactor = null;
        if (count($factorStdDevs) > 0) {
            $sdKey = $selSector === 'global' ? 'overall_std_dev' : 'sector_std_dev';
            $sortedBySd = collect($factorStdDevs)->sortByDesc($sdKey);
            $highestRec = $sortedBySd->first();
            $lowestRec = $sortedBySd->last();
            
            $highestSdFactor = [
                'nama' => $highestRec['nama_factor'],
                'value' => $highestRec[$sdKey]
            ];
            $lowestSdFactor = [
                'nama' => $lowestRec['nama_factor'],
                'value' => $lowestRec[$sdKey]
            ];
        }

        return view('pages.dashboard', [
            'stats' => [
                'total_umkm' => $totalUmkm,
                'total_respondents' => $totalRespondents,
                'avg_health' => round($avgHealth, 1),
                'sehat_count' => $sehatCount,
                'kritis_count' => $kritisCount,
            ],
            'biz_types' => $bizTypes,
            'age_dist' => $ageDist,
            'gender_dist' => $genderDist,
            'top_umkms' => $topUmkms,
            'avg_factors' => $avgFactors,
            'has_umkm' => $data['has_umkm'],
            'has_assessment' => $data['has_assessment'],
            'my_avg_health' => round($myAvgHealth, 1),
            'my_health_category' => $myHealthCategory,
            'my_factors' => $myFactors,
            'all_umkms' => $data['all_umkms'],
            'selected_umkm_id' => $data['selected_umkm_id'],
            'show_umkm_popup' => $data['show_umkm_popup'],
            'show_assessment_popup' => $data['show_assessment_popup'],
            'show_in_page_notice' => $data['show_in_page_notice'],
            'all_sectors' => $sectors,
            'filter_sektor' => $selSector,
            'sector_chart_data' => $sectorChartData,
            'factor_std_devs' => $factorStdDevs,
            'highest_umkm' => $highestUmkm,
            'lowest_umkm' => $lowestUmkm,
            'highest_sd_factor' => $highestSdFactor,
            'lowest_sd_factor' => $lowestSdFactor
        ]);
    }

    public function webAssessment()
    {
        $owner = \Illuminate\Support\Facades\Auth::guard('owner')->user();
        $umkms = $owner ? $owner->umkms : collect();
        
        foreach ($umkms as $umkm) {
            // Ambil asesmen terbaru yang belum 'completed' atau 'closed'
            $activeAssessment = \App\Models\Assessment::where('umkm_id', $umkm->umkm_id)
                ->whereIn('status', ['draft', 'open'])
                ->latest()
                ->first();

            // Jika tidak ada yang aktif, ambil yang terakhir selesai (untuk histori/tombol baru)
            if (!$activeAssessment) {
                $activeAssessment = \App\Models\Assessment::where('umkm_id', $umkm->umkm_id)
                    ->where('status', 'completed')
                    ->latest()
                    ->first();
            }

            $status = 'belum_mulai';
            $progress = 0;
            $assessmentId = null;
            $employeeAnswered = 0;
            $employeeTarget = 0;

            if ($activeAssessment) {
                $assessmentId = $activeAssessment->assessment_id;
                $employeeTarget = $activeAssessment->jumlah_karyawan ?? 0;
                
                // Hitung progres berdasarkan jawaban owner di asesmen ini
                $totalQuestions = \App\Models\Question::where('question_role', 'owner')->count();
                $answeredQuestions = \Illuminate\Support\Facades\DB::table('responses')
                    ->where('assessment_id', $assessmentId)
                    ->where('respondent_type', 'owner')
                    ->count();

                // Hitung responden karyawan unik
                $employeeAnswered = \Illuminate\Support\Facades\DB::table('responses')
                    ->where('assessment_id', $assessmentId)
                    ->where('respondent_type', 'employee')
                    ->distinct('employee_code')
                    ->count('employee_code');

                if ($activeAssessment->status == 'draft') {
                    $status = $answeredQuestions > 0 ? 'sedang_berlangsung' : 'belum_mulai';
                    $progress = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;
                } elseif ($activeAssessment->status == 'open') {
                    if ($employeeAnswered == 0) {
                        $status = 'menunggu_karyawan';
                    } elseif ($employeeTarget > 0 && $employeeAnswered >= $employeeTarget) {
                        $status = 'selesai_karyawan';
                    } else {
                        $status = 'proses_karyawan';
                    }
                    $progress = 100;
                } else {
                    $status = 'selesai';
                    $progress = 100;
                }
            }

            $umkm->active_assessment_id = $assessmentId;
            $umkm->assessment_status = $status;
            $umkm->assessment_progress = $progress;
            $umkm->employee_answered = $employeeAnswered;
            $umkm->employee_target = $employeeTarget;
        }

        $questions = \App\Models\Question::where('question_role', 'owner')
            ->orderBy('urutan')
            ->get();

        return view('pages.assessment', compact('umkms', 'questions'));
    }

    public function webFillAssessment($umkmId)
    {
        $owner = \Illuminate\Support\Facades\Auth::guard('owner')->user();
        $umkm = \App\Models\Umkm::where('owner_id', $owner->owner_id)->findOrFail($umkmId);
        
        // Pastikan ada record assessment
        $assessment = \App\Models\Assessment::where('umkm_id', $umkmId)
            ->whereIn('status', ['draft', 'open'])
            ->latest()
            ->first();

        if (!$assessment) {
            return redirect()->route('assessment')->with('error', 'Silakan tentukan target jumlah karyawan terlebih dahulu untuk memulai asesmen baru.');
        }

        // Ambil pertanyaan dikelompokkan per faktor (Hanya yang ada pertanyaan untuk owner)
        $factors = \App\Models\Factor::whereHas('questions', function($q) {
            $q->where('question_role', 'owner');
        })->with(['questions' => function($q) {
            $q->where('question_role', 'owner')->orderBy('urutan');
        }])->get();

        // Ambil jawaban yang sudah ada
        $existingResponses = \Illuminate\Support\Facades\DB::table('responses')
            ->where('assessment_id', $assessment->assessment_id)
            ->where('respondent_type', 'owner')
            ->pluck('answer_value', 'question_id')
            ->toArray();

        return view('pages.fill-assessment', compact('umkm', 'assessment', 'factors', 'existingResponses'));
    }

    public function webFinishAssessment($id)
    {
        $assessment = \App\Models\Assessment::findOrFail($id);
        $assessment->update(['status' => 'open']);
        
        return response()->json(['message' => 'Asesmen berhasil diselesaikan dan kini terbuka untuk karyawan.']);
    }

    public function webCreateNewAssessment(Request $request, $umkmId)
    {
        $owner = \Illuminate\Support\Facades\Auth::guard('owner')->user();
        $umkm = \App\Models\Umkm::where('owner_id', $owner->owner_id)->findOrFail($umkmId);

        // Tutup asesmen lama yang masih aktif agar tidak tumpang tindih
        \App\Models\Assessment::where('umkm_id', $umkmId)
            ->whereIn('status', ['draft', 'open'])
            ->update(['status' => 'closed']);

        // Buat assessment baru
        $assessment = \App\Models\Assessment::create([
            'umkm_id' => $umkmId,
            'status' => 'draft',
            'tanggal_mulai' => now(),
            'assessment_name' => 'Asesmen ' . now()->format('M Y'),
            'jumlah_karyawan' => $request->input('jumlah_karyawan', 0)
        ]);

        return redirect()->route('assessment.fill', $umkmId);
    }

    public function webComparison(Request $request)
    {
        $data = $this->getActiveData($request);
        
        // Cek jika user memilih periode tertentu, jika tidak gunakan yang terbaru
        $requestedAssessmentId = $request->query('assessment_id');
        $assessment = null;
        
        if ($requestedAssessmentId) {
            $assessment = \App\Models\Assessment::where('umkm_id', $data['selected_umkm_id'])
                ->find($requestedAssessmentId);
        }
        
        if (!$assessment) {
            $assessment = $data['assessment_with_score'];
        }
        
        $history = collect();
        if ($data['active_umkm']) {
            $tempHistory = \App\Models\Assessment::where('umkm_id', $data['active_umkm']->umkm_id)
                ->orderBy('created_at', 'asc')
                ->get();
                
            $monthCounters = [];
            foreach ($tempHistory as $hist) {
                $monthYear = \Carbon\Carbon::parse($hist->tanggal_mulai ?? $hist->created_at)->format('Y-m');
                if (!isset($monthCounters[$monthYear])) {
                    $monthCounters[$monthYear] = 1;
                } else {
                    $monthCounters[$monthYear]++;
                }
                $hist->sequence_in_month = $monthCounters[$monthYear];
            }
            $history = $tempHistory->sortByDesc('created_at')->values();
        }

        $hasUmkm = $data['has_umkm'];
        $hasAssessment = $data['has_assessment'];

        $defaultData = [
            'assessment_info' => null,
            'health_score' => null,
            'radar_chart' => [],
            'rankings' => ['all' => []],
            'industry_benchmark' => ['peer_count' => 0, 'sektor_usaha' => 'N/A'],
            'previous_assessment_info' => null,
            'show_umkm_popup' => $data['show_umkm_popup'],
            'show_assessment_popup' => $data['show_assessment_popup'],
            'all_umkms' => $data['all_umkms'],
            'selected_umkm_id' => $data['selected_umkm_id'],
            'show_in_page_notice' => $data['show_in_page_notice'],
            'assessment_history' => $history,
            'selected_assessment_id' => $assessment ? $assessment->assessment_id : null
        ];

        $selectedIndustry = $request->query('industry') ?: ($assessment?->umkm?->sektor_usaha ?? null);
        $allIndustries = DB::table('umkms')
            ->whereNotNull('sektor_usaha')
            ->where('sektor_usaha', '<>', '')
            ->distinct()
            ->pluck('sektor_usaha');

        $defaultData['selected_industry'] = $selectedIndustry;
        $defaultData['all_industries'] = $allIndustries;

        if (!$assessment) {
            $defaultData['error'] = 'Belum ada data assessment.';
            return view('pages.comparison', $defaultData);
        }

        $detailedData = $this->getDetailedData($assessment->assessment_id, $selectedIndustry);
        $dataMerge = array_merge($defaultData, $detailedData ?? []);

        return view('pages.comparison', $dataMerge);
    }

    public function webProfilFaktor(Request $request)
    {
        $data = $this->getActiveData($request);
        
        // Cek jika user memilih periode tertentu, jika tidak gunakan yang terbaru
        $requestedAssessmentId = $request->query('assessment_id');
        $assessment = null;
        
        if ($requestedAssessmentId) {
            $assessment = Assessment::where('umkm_id', $data['selected_umkm_id'])
                ->find($requestedAssessmentId);
        }
        
        if (!$assessment) {
            $assessment = $data['assessment_with_score'];
        }
        
        $history = collect();
        if ($data['active_umkm']) {
            // Mengambil secara berurutan dari terlama ke terbaru untuk memberi nomor urut
            $tempHistory = Assessment::where('umkm_id', $data['active_umkm']->umkm_id)
                ->orderBy('created_at', 'asc')
                ->get();
                
            $monthCounters = [];
            foreach ($tempHistory as $hist) {
                $monthYear = \Carbon\Carbon::parse($hist->tanggal_mulai ?? $hist->created_at)->format('Y-m');
                if (!isset($monthCounters[$monthYear])) {
                    $monthCounters[$monthYear] = 1;
                } else {
                    $monthCounters[$monthYear]++;
                }
                $hist->sequence_in_month = $monthCounters[$monthYear];
            }
            
            // Urutkan kembali dari terbaru ke terlama untuk ditampilkan di dropdown
            $history = $tempHistory->sortByDesc('created_at')->values();
        }

        $defaultData = [
            'assessment_info' => $assessment,
            'health_score' => null,
            'radar_chart' => [],
            'rankings' => ['all' => []],
            'highlights' => ['highest' => null, 'lowest' => null],
            'recommendations' => collect(),
            'outliers' => [],
            'show_umkm_popup' => $data['show_umkm_popup'],
            'show_assessment_popup' => $data['show_assessment_popup'],
            'all_umkms' => $data['all_umkms'],
            'selected_umkm_id' => $data['selected_umkm_id'],
            'show_in_page_notice' => $data['show_in_page_notice'],
            'assessment_history' => $history,
            'selected_assessment_id' => $assessment ? $assessment->assessment_id : null
        ];

        if (!$assessment) {
            $defaultData['error'] = 'Belum ada data assessment.';
            return view('pages.profil-faktor', $defaultData);
        }

        $detailedData = $this->getDetailedData($assessment->assessment_id);
        $dataMerge = array_merge($defaultData, $detailedData ?? []);

        // Fetch outlier data directly without json response
        $factors = DB::table('factors')->get();
        $outlierResults = [];

        foreach ($factors as $factor) {
            $responses = DB::table('responses as r')
                ->join('questions as q', 'r.question_id', '=', 'q.question_id')
                ->where('r.assessment_id', $assessment->assessment_id)
                ->where('q.factor_id', $factor->factor_id)
                ->select('r.answer_value', 'q.max_score')
                ->get();

            $normalizedScores = [];
            foreach ($responses as $resp) {
                $max = $resp->max_score ?? 5;
                $normalizedScores[] = ($resp->answer_value / $max) * 100;
            }

            if (count($normalizedScores) > 0) {
                sort($normalizedScores);
                $outlierResults[] = [
                    'nama_factor' => $factor->nama_factor,
                    'code' => $factor->nama_factor,
                    'stats' => $this->calculateBoxplot($normalizedScores)
                ];
            }
        }
        $dataMerge['outliers'] = $outlierResults;

        return view('pages.profil-faktor', $dataMerge);
    }

    public function webRekomendasi(Request $request)
    {
        $owner = Auth::guard('owner')->user() ?? auth()->user();
        $allUmkms = $owner ? Umkm::where('owner_id', $owner->owner_id)->get() : collect();
        $hasUmkm = $allUmkms->count() > 0;

        $selectedUmkmId = $request->query('umkm_id');
        if (!$selectedUmkmId && $allUmkms->isNotEmpty()) {
            $selectedUmkmId = $allUmkms->first()->umkm_id;
        }

        // Get history of assessments with sequence count for the selected UMKM
        $assessmentHistory = collect();
        if ($selectedUmkmId) {
            $tempHistory = Assessment::where('umkm_id', $selectedUmkmId)
                ->orderBy('created_at', 'asc')
                ->get();
                
            $monthCounters = [];
            foreach ($tempHistory as $hist) {
                $monthYear = \Carbon\Carbon::parse($hist->tanggal_mulai ?? $hist->created_at)->format('Y-m');
                if (!isset($monthCounters[$monthYear])) {
                    $monthCounters[$monthYear] = 1;
                } else {
                    $monthCounters[$monthYear]++;
                }
                $hist->sequence_in_month = $monthCounters[$monthYear];
            }
            $assessmentHistory = $tempHistory->sortByDesc('created_at')->values();
        }

        // Find selected assessment from the history collection to retain sequence_in_month
        $requestedAssessmentId = $request->query('assessment_id');
        $assessment = null;
        if ($selectedUmkmId && $requestedAssessmentId) {
            $assessment = $assessmentHistory->firstWhere('assessment_id', $requestedAssessmentId);
        }
        if (!$assessment && $assessmentHistory->isNotEmpty()) {
            $assessment = $assessmentHistory->first();
        }

        // Derive period for section 2 rank table
        $selectedPeriod = null;
        if ($assessment) {
            $date = $assessment->tanggal_mulai ?? $assessment->created_at;
            $selectedPeriod = \Carbon\Carbon::parse($date)->format('Y-m');
        }

        $detailedData = null;
        if ($assessment) {
            $detailedData = $this->getDetailedData($assessment->assessment_id);
        }

        // Get unique periods for assessments belonging to the owner's UMKMs (for Section 2 dropdown)
        $umkmIds = $allUmkms->pluck('umkm_id')->toArray();
        $rankPeriods = collect();
        if (!empty($umkmIds)) {
            $allAssessments = Assessment::whereIn('umkm_id', $umkmIds)
                ->orderBy('created_at', 'desc')
                ->get();

            $rankPeriods = $allAssessments->map(function ($a) {
                $date = $a->tanggal_mulai ?? $a->created_at;
                return [
                    'val' => \Carbon\Carbon::parse($date)->format('Y-m'),
                    'label' => \Carbon\Carbon::parse($date)->format('F Y')
                ];
            })->unique('val')->values();
        }

        $selectedRankPeriod = $request->query('rank_period');
        if (!$selectedRankPeriod) {
            if ($assessment) {
                $date = $assessment->tanggal_mulai ?? $assessment->created_at;
                $selectedRankPeriod = \Carbon\Carbon::parse($date)->format('Y-m');
            } elseif ($rankPeriods->isNotEmpty()) {
                $selectedRankPeriod = $rankPeriods->first()['val'];
            }
        }

        // Section 2: Owner's UMKMs health score rank for the selected month-year period (latest completed assessment in that month)
        $ownerUmkmScores = collect();
        $allFactors = DB::table('factors')->orderBy('urutan')->get();
        $allFactorScores = [];

        if ($selectedRankPeriod && !empty($umkmIds)) {
            $year = substr($selectedRankPeriod, 0, 4);
            $month = substr($selectedRankPeriod, 5, 2);
            $ownerUmkmScores = DB::table('health_scores as hs')
                ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
                ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
                ->where('u.owner_id', $owner->owner_id)
                ->where(function ($query) use ($year, $month) {
                    $query->where(function ($q) use ($year, $month) {
                        $q->whereYear('a.tanggal_mulai', $year)->whereMonth('a.tanggal_mulai', $month);
                    })->orWhere(function ($q) use ($year, $month) {
                        $q->whereNull('a.tanggal_mulai')->whereYear('a.created_at', $year)->whereMonth('a.created_at', $month);
                    });
                })
                ->whereIn('a.assessment_id', function ($query) use ($year, $month) {
                    $query->select(DB::raw('MAX(a2.assessment_id)'))
                        ->from('assessments as a2')
                        ->join('health_scores as hs2', 'a2.assessment_id', '=', 'hs2.assessment_id')
                        ->where(function ($q2) use ($year, $month) {
                            $q2->where(function ($q) use ($year, $month) {
                                $q->whereYear('a2.tanggal_mulai', $year)->whereMonth('a2.tanggal_mulai', $month);
                            })->orWhere(function ($q) use ($year, $month) {
                                $q->whereNull('a2.tanggal_mulai')->whereYear('a2.created_at', $year)->whereMonth('a2.created_at', $month);
                            });
                        })
                        ->groupBy('a2.umkm_id');
                })
                ->select('u.umkm_id', 'u.nama_umkm', 'hs.health_score_id', 'hs.overall_score')
                ->orderBy('hs.overall_score', 'asc')
                ->get();

            $healthScoreIds = $ownerUmkmScores->pluck('health_score_id')->toArray();
            if (!empty($healthScoreIds)) {
                $allFactorScores = DB::table('factor_scores')
                    ->whereIn('health_score_id', $healthScoreIds)
                    ->get()
                    ->groupBy('health_score_id');
            }
        }

        // Set popup checks
        $hasAnyUmkm = $allUmkms->count() > 0;
        $hasAnyAssessment = false;
        if ($hasAnyUmkm) {
            $hasAnyAssessment = \Illuminate\Support\Facades\DB::table('responses')
                ->whereIn('assessment_id', function($query) use ($allUmkms) {
                    $query->select('assessment_id')->from('assessments')->whereIn('umkm_id', $allUmkms->pluck('umkm_id'));
                })->exists();
        }

        $dataMerge = [
            'assessment_info' => $assessment,
            'health_score' => $detailedData['health_score'] ?? null,
            'recommendations' => $detailedData['recommendations'] ?? collect(),
            'radar_chart' => $detailedData['radar_chart'] ?? [],
            'show_umkm_popup' => !$hasAnyUmkm,
            'show_assessment_popup' => ($hasAnyUmkm && !$hasAnyAssessment),
            'all_umkms' => $allUmkms,
            'selected_umkm_id' => $selectedUmkmId,
            'show_in_page_notice' => ($hasAnyAssessment && !$assessment),
            'assessment_history' => $assessmentHistory,
            'selected_period' => $selectedPeriod,
            'all_umkm_scores' => $ownerUmkmScores,
            'all_factors' => $allFactors,
            'all_factor_scores' => $allFactorScores,
            'selected_assessment_id' => $assessment ? $assessment->assessment_id : null,
            'rank_periods' => $rankPeriods,
            'selected_rank_period' => $selectedRankPeriod
        ];

        return view('pages.rekomendasi', $dataMerge);
    }

    public function webMonitoring(Request $request)
    {
        $data = $this->getActiveData($request);
        
        // Gunakan assessment terbaru yang sama dengan Dashboard agar angka konsisten
        $assessment = $data['assessment_with_score'];

        $hasUmkm = $data['has_umkm'];
        $hasAssessment = $data['has_assessment'];

        $defaultData = [
            'assessment' => $assessment,
            'total_respondents' => 0,
            'target_respondents' => 50,
            'completion_rate' => 0,
            'estimated_score' => 0,
            'estimated_category' => 'N/A',
            'recent_activity' => collect(),
            'show_umkm_popup' => $data['show_umkm_popup'],
            'show_assessment_popup' => $data['show_assessment_popup'],
            'all_umkms' => $data['all_umkms'],
            'selected_umkm_id' => $data['selected_umkm_id']
        ];

        if (!$assessment) {
            $defaultData['error'] = 'Belum ada data assessment.';
            return view('pages.monitoring', $defaultData);
        }

        $id = $assessment->assessment_id;

        $totalEmployees = \App\Models\Response::where('assessment_id', $id)
            ->where('respondent_type', 'employee')
            ->distinct('employee_code')
            ->count('employee_code');

        $hasOwner = \App\Models\Response::where('assessment_id', $id)
            ->where('respondent_type', 'owner')
            ->exists() ? 1 : 0;

        $totalRespondents = $totalEmployees + $hasOwner;

        $targetRespondents = ($assessment->jumlah_karyawan ?? 0) + 1;
        if ($targetRespondents <= 0) $targetRespondents = 1;

        $completionRate = min(round(($totalRespondents / $targetRespondents) * 100), 100);

        $healthService = app(\App\Services\HealthService::class);
        $estimatedScoreData = $healthService->calculate($id);

        $recentActivity = \App\Models\Response::where('assessment_id', $id)
            ->where('respondent_type', 'employee')
            ->select('employee_code', DB::raw('MAX(created_at) as last_active'))
            ->groupBy('employee_code')
            ->orderBy('last_active', 'desc')
            ->limit(3)
            ->get();

        return view('pages.monitoring', [
            'assessment' => $assessment,
            'total_respondents' => $totalRespondents,
            'target_respondents' => $targetRespondents,
            'completion_rate' => $completionRate,
            'estimated_score' => round($estimatedScoreData->overall_score ?? 0, 1),
            'estimated_category' => $estimatedScoreData->category ?? 'N/A',
            'recent_activity' => $recentActivity,
            'show_umkm_popup' => $data['show_umkm_popup'],
            'show_assessment_popup' => $data['show_assessment_popup'],
            'all_umkms' => $data['all_umkms'],
            'selected_umkm_id' => $data['selected_umkm_id'],
            'show_in_page_notice' => $data['show_in_page_notice']
        ]);
    }

    public function webUmkmRank(Request $request)
    {
        $data = $this->getActiveData($request);
        return view('pages.umkm-rank', $data);
    }

    public function apiUmkmRank(Request $request)
    {
        $search = $request->query('search');
        $sort = $request->query('sort', 'score_desc');
        $limit = $request->query('limit', '25');

        // Subquery untuk skor terbaru per UMKM
        $latestScores = DB::table('health_scores as hs')
            ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
            ->select('a.umkm_id', 'hs.overall_score', 'hs.category')
            ->whereIn('a.assessment_id', function($q) {
                $q->select(DB::raw('MAX(assessment_id)'))
                  ->from('assessments')
                  ->groupBy('umkm_id');
            });

        // Hitung Global Rank berdasarkan Skor Keseluruhan
        $globalRankQuery = DB::table('umkms as u')
            ->leftJoinSub($latestScores, 'ls', function ($join) {
                $join->on('u.umkm_id', '=', 'ls.umkm_id');
            })
            ->orderByDesc('ls.overall_score')
            ->pluck('u.umkm_id');

        $rankMap = [];
        foreach ($globalRankQuery as $index => $umkmId) {
            $rankMap[$umkmId] = $index + 1;
        }

        $query = DB::table('umkms as u')
            ->leftJoinSub($latestScores, 'ls', function ($join) {
                $join->on('u.umkm_id', '=', 'ls.umkm_id');
            })
            ->select('u.nama_umkm', 'u.sektor_usaha', 'u.created_at', 'u.umkm_id', 'ls.overall_score', 'ls.category as health_category');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('u.nama_umkm', 'like', "%{$search}%")
                  ->orWhere('u.sektor_usaha', 'like', "%{$search}%");
            });
        }

        switch ($sort) {
            case 'score_asc': $query->orderBy('ls.overall_score', 'asc'); break;
            case 'az': $query->orderBy('u.nama_umkm', 'asc'); break;
            case 'za': $query->orderBy('u.nama_umkm', 'desc'); break;
            case 'kategori': $query->orderBy('ls.category', 'asc'); break;
            case 'terbaru': $query->orderBy('u.created_at', 'desc'); break;
            case 'terlama': $query->orderBy('u.created_at', 'asc'); break;
            case 'score_desc': 
            default: 
                $query->orderByDesc('ls.overall_score'); 
                break;
        }

        if ($limit !== 'all') {
            $query->limit((int)$limit);
        }

        $results = $query->get();
        
        // Pasang rank global ke hasil pencarian
        foreach ($results as $result) {
            $result->global_rank = $rankMap[$result->umkm_id] ?? '-';
        }

        return response()->json($results);
    }

    /**
     * Mendapatkan data statistik Boxplot untuk deteksi Outlier per Faktor
     */
    public function getFactorOutliers($assessmentId)
    {
        $factors = DB::table('factors')->get();
        $results = [];

        foreach ($factors as $factor) {
            $scores = DB::table('responses as r')
                ->join('questions as q', 'r.question_id', '=', 'q.question_id')
                ->where('r.assessment_id', $assessmentId)
                ->where('q.factor_id', $factor->factor_id)
                ->select('r.answer_value', 'q.max_score')
                ->get();

            $normalizedScores = [];
            foreach ($scores as $resp) {
                $max = $resp->max_score ?? 5;
                $normalizedScores[] = ($resp->answer_value / $max) * 100;
            }

            if (count($normalizedScores) > 0) {
                sort($normalizedScores);
                $results[] = [
                    'nama_factor' => $factor->nama_factor,
                    'stats' => $this->calculateBoxplot($normalizedScores)
                ];
            }
        }

        return response()->json([
            'message' => 'Data outlier per faktor berhasil dihitung',
            'data' => $results
        ]);
    }

    /**
     * Fungsi helper untuk menghitung komponen Boxplot
     */
    private function calculateBoxplot(array $data)
    {
        $count = count($data);
        
        // Median (Q2)
        $median = $this->getPercentile($data, 0.5);
        
        // Q1 (25th percentile) dan Q3 (75th percentile)
        $q1 = $this->getPercentile($data, 0.25);
        $q3 = $this->getPercentile($data, 0.75);
        
        $iqr = $q3 - $q1;
        
        // Batas bawah dan atas untuk outlier (1.5 * IQR)
        $lowerFence = $q1 - (1.5 * $iqr);
        $upperFence = $q3 + (1.5 * $iqr);
        
        // Filter Outliers
        $outliers = array_values(array_filter($data, function($val) use ($lowerFence, $upperFence) {
            return $val < $lowerFence || $val > $upperFence;
        }));
        
        // Nilai Min dan Max (yang bukan outlier)
        $nonOutliers = array_filter($data, function($val) use ($lowerFence, $upperFence) {
            return $val >= $lowerFence && $val <= $upperFence;
        });

        return [
            'min' => !empty($nonOutliers) ? min($nonOutliers) : $data[0],
            'q1' => $q1,
            'median' => $median,
            'q3' => $q3,
            'max' => !empty($nonOutliers) ? max($nonOutliers) : $data[$count - 1],
            'outliers' => $outliers,
            'sample_size' => $count
        ];
    }

    private function getPercentile($data, $percentile)
    {
        $index = ($percentile * (count($data) - 1));
        if (floor($index) == $index) {
            return $data[$index];
        } else {
            $low = floor($index);
            $high = ceil($index);
            $fraction = $index - $low;
            return $data[$low] + $fraction * ($data[$high] - $data[$low]);
        }
    }

    public function apiRealtimeDashboard(Request $request)
    {
        $data = $this->getActiveData($request);
        
        $myAvgHealth = 0;
        $myHealthCategory = 'N/A';
        $myFactors = collect();
        
        if ($data['assessment_with_score']) {
            $assessmentId = $data['assessment_with_score']->assessment_id;
            $hs = HealthScore::where('assessment_id', $assessmentId)->first();

            $myAvgHealth = $hs ? round($hs->overall_score, 1) : 0;
            $myHealthCategory = $hs ? $hs->category : 'PENDING';
            
            $myFactors = $hs ? DB::table('factor_scores as fs')
                ->join('factors as f', 'fs.factor_id', '=', 'f.factor_id')
                ->where('fs.health_score_id', $hs->health_score_id)
                ->select('f.nama_factor', 'fs.score as avg_score')
                ->get() : collect();
        }

        $totalUmkm = Umkm::count();
        $totalEmployees = DB::table('responses')
            ->where('respondent_type', 'employee')
            ->distinct('employee_code')
            ->count('employee_code');

        $totalOwners = DB::table('responses')
            ->join('assessments', 'responses.assessment_id', '=', 'assessments.assessment_id')
            ->join('umkms', 'assessments.umkm_id', '=', 'umkms.umkm_id')
            ->where('responses.respondent_type', 'owner')
            ->distinct('umkms.owner_id')
            ->count('umkms.owner_id');

        $totalRespondents = $totalEmployees + $totalOwners;
        
        $healthScores = HealthScore::all();
        $avgHealth = $healthScores->avg('overall_score') ?? 0;
        
        $sehatCount = $healthScores->where('overall_score', '>=', 60)->count();
        $kritisCount = $healthScores->where('overall_score', '<', 60)->count();

        $bizTypes = Umkm::select('sektor_usaha', DB::raw('count(*) as count'))
            ->groupBy('sektor_usaha')
            ->pluck('count', 'sektor_usaha');

        $genderDist = Owner::select('gender', DB::raw('count(*) as count'))
            ->groupBy('gender')
            ->pluck('count', 'gender');

        $topUmkms = DB::table('health_scores as hs')
            ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
            ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
            ->whereIn('a.assessment_id', function ($query) {
                $query->select(DB::raw('MAX(assessment_id)'))
                    ->from('assessments')
                    ->groupBy('umkm_id');
            })
            ->select('u.nama_umkm', 'u.sektor_usaha', 'hs.overall_score', 'hs.category as health_category')
            ->orderByDesc('hs.overall_score')
            ->limit(5)
            ->get();

        $ageDist = Umkm::select('umur_usaha', DB::raw('count(*) as count'))
            ->groupBy('umur_usaha')
            ->pluck('count', 'umur_usaha');

        $selSector = $request->query('filter_sektor', 'global');

        // Sector & Factor Analysis (Grouped Bar Chart and Standard Deviation Stats)
        $sectorFactorDataQuery = DB::table('factor_scores as fs')
            ->join('health_scores as hs', 'fs.health_score_id', '=', 'hs.health_score_id')
            ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
            ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
            ->join('factors as f', 'fs.factor_id', '=', 'f.factor_id')
            ->whereNotNull('u.sektor_usaha')
            ->where('u.sektor_usaha', '!=', '');

        $allSectorFactorData = $sectorFactorDataQuery->select('u.sektor_usaha', 'f.factor_id', 'f.nama_factor', 'fs.score')->get();

        $sectors = $allSectorFactorData->pluck('sektor_usaha')->unique()->values()->all();
        $factorsList = DB::table('factors')->orderBy('urutan')->get();

        // Build Chart Data
        $sectorChartData = [];
        $premiumColors = ['#ffffff', '#a3a3a3', '#818cf8', '#a78bfa', '#2dd4bf', '#38bdf8'];

        if ($selSector === 'global') {
            $sectorChartData = [
                'type' => 'global',
                'labels' => $sectors,
                'datasets' => []
            ];

            $groupedData = [];
            foreach ($allSectorFactorData as $row) {
                $groupedData[$row->sektor_usaha][$row->factor_id][] = (float)$row->score;
            }

            foreach ($factorsList as $index => $factor) {
                $datasetData = [];
                foreach ($sectors as $sector) {
                    $scores = $groupedData[$sector][$factor->factor_id] ?? [];
                    $datasetData[] = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
                }
                $sectorChartData['datasets'][] = [
                    'label' => $factor->nama_factor,
                    'data' => $datasetData,
                    'backgroundColor' => $premiumColors[$index % count($premiumColors)],
                    'borderRadius' => 4
                ];
            }
        } else {
            $sectorChartData = [
                'type' => 'sector',
                'labels' => $factorsList->pluck('nama_factor')->all(),
                'datasets' => [
                    [
                        'label' => 'Rata-rata Skor Sektor ' . $selSector,
                        'data' => [],
                        'backgroundColor' => $premiumColors,
                        'borderRadius' => 6
                    ]
                ]
            ];

            $groupedData = [];
            foreach ($allSectorFactorData as $row) {
                if ($row->sektor_usaha === $selSector) {
                    $groupedData[$row->factor_id][] = (float)$row->score;
                }
            }

            foreach ($factorsList as $factor) {
                $scores = $groupedData[$factor->factor_id] ?? [];
                $sectorChartData['datasets'][0]['data'][] = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
            }
        }

        // Build Std Dev Data
        $factorStdDevs = [];
        $factorScoresGrouped = [];
        $factorSectorsScoresGrouped = [];
        foreach ($allSectorFactorData as $row) {
            $score = (float)$row->score;
            $groupedData[$row->sektor_usaha][$row->factor_id][] = $score;
            $factorScoresGrouped[$row->factor_id][] = $score;
            $factorSectorsScoresGrouped[$row->factor_id][$row->sektor_usaha][] = $score;
        }

        $sectorScoresData = collect();
        if ($selSector !== 'global') {
            $sectorScoresData = DB::table('factor_scores as fs')
                ->join('health_scores as hs', 'fs.health_score_id', '=', 'hs.health_score_id')
                ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
                ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
                ->where('u.sektor_usaha', $selSector)
                ->select('u.nama_umkm', 'fs.factor_id', 'fs.score')
                ->get();
        }

        foreach ($factorsList as $factor) {
            $fId = $factor->factor_id;
            
            if ($selSector === 'global') {
                $allScores = $factorScoresGrouped[$fId] ?? [];
                $overallStdDev = $this->calculateStdDev($allScores);

                $highestStdSector = '-';
                $highestStdVal = -1.0;
                $lowestStdSector = '-';
                $lowestStdVal = 999999.0;

                foreach ($sectors as $sector) {
                    $scores = $factorSectorsScoresGrouped[$fId][$sector] ?? [];
                    if (count($scores) >= 2) {
                        $std = $this->calculateStdDev($scores);
                        if ($std > $highestStdVal) {
                            $highestStdVal = $std;
                            $highestStdSector = $sector;
                        }
                        if ($std < $lowestStdVal) {
                            $lowestStdVal = $std;
                            $lowestStdSector = $sector;
                        }
                    } elseif (count($scores) == 1) {
                        $std = 0.0;
                        if ($std > $highestStdVal) {
                            $highestStdVal = $std;
                            $highestStdSector = $sector;
                        }
                        if ($std < $lowestStdVal) {
                            $lowestStdVal = $std;
                            $lowestStdSector = $sector;
                        }
                    }
                }

                if ($highestStdVal === -1.0) $highestStdVal = 0.0;
                if ($lowestStdVal === 999999.0) $lowestStdVal = 0.0;

                $factorStdDevs[] = [
                    'nama_factor' => $factor->nama_factor,
                    'overall_std_dev' => round($overallStdDev / 20, 2),
                    'highest_std_sector' => $highestStdSector,
                    'highest_std_val' => round($highestStdVal / 20, 2),
                    'lowest_std_sector' => $lowestStdSector,
                    'lowest_std_val' => round($lowestStdVal / 20, 2)
                ];
            } else {
                $scores = $factorSectorsScoresGrouped[$fId][$selSector] ?? [];
                $sectorStdDev = count($scores) >= 2 ? $this->calculateStdDev($scores) : 0.0;

                $factorSectorScores = $sectorScoresData->where('factor_id', $fId);
                $highestFactorUmkm = $factorSectorScores->sortByDesc('score')->first();
                $lowestFactorUmkm = $factorSectorScores->sortBy('score')->first();

                $factorStdDevs[] = [
                    'nama_factor' => $factor->nama_factor,
                    'sector_std_dev' => round($sectorStdDev / 20, 2),
                    'highest_score' => $highestFactorUmkm ? round($highestFactorUmkm->score / 20, 2) : null,
                    'highest_umkm' => $highestFactorUmkm ? $highestFactorUmkm->nama_umkm : '-',
                    'lowest_score' => $lowestFactorUmkm ? round($lowestFactorUmkm->score / 20, 2) : null,
                    'lowest_umkm' => $lowestFactorUmkm ? $lowestFactorUmkm->nama_umkm : '-'
                ];
            }
        }

        // Highest and Lowest UMKM Query
        $umkmRankQuery = DB::table('health_scores as hs')
            ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
            ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
            ->select('u.nama_umkm', 'u.sektor_usaha', 'hs.overall_score');

        if ($selSector !== 'global') {
            $umkmRankQuery->where('u.sektor_usaha', $selSector);
        }

        $highestUmkmRecord = (clone $umkmRankQuery)->orderByDesc('hs.overall_score')->first();
        $lowestUmkmRecord = (clone $umkmRankQuery)->orderBy('hs.overall_score')->first();

        $highestUmkm = $highestUmkmRecord ? [
            'nama' => $highestUmkmRecord->nama_umkm,
            'score' => round($highestUmkmRecord->overall_score, 1),
            'sektor' => $highestUmkmRecord->sektor_usaha
        ] : null;

        $lowestUmkm = $lowestUmkmRecord ? [
            'nama' => $lowestUmkmRecord->nama_umkm,
            'score' => round($lowestUmkmRecord->overall_score, 1),
            'sektor' => $lowestUmkmRecord->sektor_usaha
        ] : null;

        // Find highest/lowest SD factor
        $highestSdFactor = null;
        $lowestSdFactor = null;
        if (count($factorStdDevs) > 0) {
            $sdKey = $selSector === 'global' ? 'overall_std_dev' : 'sector_std_dev';
            $sortedBySd = collect($factorStdDevs)->sortByDesc($sdKey);
            $highestRec = $sortedBySd->first();
            $lowestRec = $sortedBySd->last();
            
            $highestSdFactor = [
                'nama' => $highestRec['nama_factor'],
                'value' => $highestRec[$sdKey]
            ];
            $lowestSdFactor = [
                'nama' => $lowestRec['nama_factor'],
                'value' => $lowestRec[$sdKey]
            ];
        }

        return response()->json([
            'stats' => [
                'total_umkm' => $totalUmkm,
                'total_respondents' => $totalRespondents,
                'avg_health' => round($avgHealth, 1),
                'sehat_count' => $sehatCount,
                'kritis_count' => $kritisCount,
            ],
            'biz_types' => $bizTypes,
            'age_dist' => $ageDist,
            'gender_dist' => $genderDist,
            'top_umkms' => $topUmkms,
            'has_umkm' => $data['has_umkm'],
            'has_assessment' => $data['has_assessment'],
            'my_avg_health' => round($myAvgHealth, 1),
            'my_health_category' => $myHealthCategory,
            'my_factors' => $myFactors,
            'selected_umkm_id' => $data['selected_umkm_id'],
            'all_sectors' => $sectors,
            'filter_sektor' => $selSector,
            'sector_chart_data' => $sectorChartData,
            'factor_std_devs' => $factorStdDevs,
            'highest_umkm' => $highestUmkm,
            'lowest_umkm' => $lowestUmkm,
            'highest_sd_factor' => $highestSdFactor,
            'lowest_sd_factor' => $lowestSdFactor
        ]);
    }

    public function apiRealtimeAssessment(Request $request)
    {
        $owner = \Illuminate\Support\Facades\Auth::guard('owner')->user();
        $umkms = $owner ? $owner->umkms : collect();
        
        $result = [];
        foreach ($umkms as $umkm) {
            $activeAssessment = \App\Models\Assessment::where('umkm_id', $umkm->umkm_id)
                ->whereIn('status', ['draft', 'open'])
                ->latest()
                ->first();

            if (!$activeAssessment) {
                $activeAssessment = \App\Models\Assessment::where('umkm_id', $umkm->umkm_id)
                    ->where('status', 'completed')
                    ->latest()
                    ->first();
            }

            $status = 'belum_mulai';
            $progress = 0;
            $assessmentId = null;
            $employeeAnswered = 0;
            $employeeTarget = 0;

            if ($activeAssessment) {
                $assessmentId = $activeAssessment->assessment_id;
                $employeeTarget = $activeAssessment->jumlah_karyawan ?? 0;
                
                $totalQuestions = \App\Models\Question::where('question_role', 'owner')->count();
                $answeredQuestions = \Illuminate\Support\Facades\DB::table('responses')
                    ->where('assessment_id', $assessmentId)
                    ->where('respondent_type', 'owner')
                    ->count();

                $employeeAnswered = \Illuminate\Support\Facades\DB::table('responses')
                    ->where('assessment_id', $assessmentId)
                    ->where('respondent_type', 'employee')
                    ->distinct('employee_code')
                    ->count('employee_code');

                if ($activeAssessment->status == 'draft') {
                    $status = $answeredQuestions > 0 ? 'sedang_berlangsung' : 'belum_mulai';
                    $progress = $totalQuestions > 0 ? round(($answeredQuestions / $totalQuestions) * 100) : 0;
                } elseif ($activeAssessment->status == 'open') {
                    if ($employeeAnswered == 0) {
                        $status = 'menunggu_karyawan';
                    } elseif ($employeeAnswered < $employeeTarget) {
                        $status = 'proses_karyawan';
                    } else {
                       $status = 'selesai_karyawan';
                    }
                    $progress = 100;
                } elseif ($activeAssessment->status == 'completed' || $activeAssessment->status == 'closed') {
                    $status = 'selesai';
                    $progress = 100;
                }
            }

            $result[] = [
                'umkm_id' => $umkm->umkm_id,
                'nama_umkm' => $umkm->nama_umkm,
                'sektor_usaha' => $umkm->sektor_usaha ?? 'Sektor Umum',
                'active_assessment_id' => $assessmentId,
                'assessment_status' => $status,
                'assessment_progress' => $progress,
                'employee_answered' => $employeeAnswered,
                'employee_target' => $employeeTarget
            ];
        }

        return response()->json([
            'umkms' => $result
        ]);
    }

    public function apiRealtimeProfilFaktor(Request $request)
    {
        $data = $this->getActiveData($request);
        $requestedAssessmentId = $request->query('assessment_id');
        $assessment = null;
        if ($requestedAssessmentId) {
            $assessment = Assessment::where('umkm_id', $data['selected_umkm_id'])->find($requestedAssessmentId);
        }
        if (!$assessment) {
            $assessment = $data['assessment_with_score'];
        }
        if (!$assessment) {
            return response()->json(['error' => 'Belum ada data assessment.'], 404);
        }
        
        $detailedData = $this->getDetailedData($assessment->assessment_id);
        
        $factors = DB::table('factors')->get();
        $outlierResults = [];
        foreach ($factors as $factor) {
            $responses = DB::table('responses as r')
                ->join('questions as q', 'r.question_id', '=', 'q.question_id')
                ->where('r.assessment_id', $assessment->assessment_id)
                ->where('q.factor_id', $factor->factor_id)
                ->select('r.answer_value', 'q.max_score')
                ->get();

            $normalizedScores = [];
            foreach ($responses as $resp) {
                $max = $resp->max_score ?? 5;
                $normalizedScores[] = ($resp->answer_value / $max) * 100;
            }

            if (count($normalizedScores) > 0) {
                sort($normalizedScores);
                $outlierResults[] = [
                    'nama_factor' => $factor->nama_factor,
                    'code' => $factor->nama_factor,
                    'stats' => $this->calculateBoxplot($normalizedScores)
                ];
            }
        }

        return response()->json([
            'health_score' => $detailedData['health_score'] ?? null,
            'radar_chart' => $detailedData['radar_chart'] ?? [],
            'rankings' => $detailedData['rankings'] ?? ['all' => []],
            'highlights' => $detailedData['highlights'] ?? ['highest' => null, 'lowest' => null],
            'recommendations' => $detailedData['recommendations'] ?? [],
            'outliers' => $outlierResults,
            'selected_assessment_id' => $assessment->assessment_id
        ]);
    }

    public function apiRealtimeComparison(Request $request)
    {
        $data = $this->getActiveData($request);
        $requestedAssessmentId = $request->query('assessment_id');
        $assessment = null;
        if ($requestedAssessmentId) {
            $assessment = \App\Models\Assessment::where('umkm_id', $data['selected_umkm_id'])->find($requestedAssessmentId);
        }
        if (!$assessment) {
            $assessment = $data['assessment_with_score'];
        }
        if (!$assessment) {
            return response()->json(['error' => 'Belum ada data assessment.'], 404);
        }

        $selectedIndustry = $request->query('industry') ?: ($assessment->umkm->sektor_usaha ?? null);
        $detailedData = $this->getDetailedData($assessment->assessment_id, $selectedIndustry);

        return response()->json([
            'health_score' => $detailedData['health_score'] ?? null,
            'radar_chart' => $detailedData['radar_chart'] ?? [],
            'industry_benchmark' => $detailedData['industry_benchmark'] ?? ['peer_count' => 0, 'sektor_usaha' => 'N/A'],
            'previous_assessment_info' => $detailedData['previous_assessment_info'] ?? null,
            'selected_assessment_id' => $assessment->assessment_id
        ]);
    }

    public function apiRealtimeRekomendasi(Request $request)
    {
        $owner = Auth::guard('owner')->user() ?? auth()->user();
        $selectedUmkmId = $request->query('umkm_id');
        $selectedPeriod = $request->query('period');

        $assessment = null;
        if ($selectedUmkmId && $selectedPeriod) {
            $year = substr($selectedPeriod, 0, 4);
            $month = substr($selectedPeriod, 5, 2);
            $assessment = Assessment::where('umkm_id', $selectedUmkmId)
                ->where(function ($query) use ($year, $month) {
                    $query->where(function ($q) use ($year, $month) {
                        $q->whereYear('tanggal_mulai', $year)->whereMonth('tanggal_mulai', $month);
                    })->orWhere(function ($q) use ($year, $month) {
                        $q->whereNull('tanggal_mulai')->whereYear('created_at', $year)->whereMonth('created_at', $month);
                    });
                })
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$assessment && $request->query('assessment_id')) {
            $assessment = Assessment::find($request->query('assessment_id'));
        }

        if (!$assessment) {
            $data = $this->getActiveData($request);
            $assessment = $data['assessment_with_score'];
        }

        if (!$assessment) {
            return response()->json(['error' => 'Belum ada data assessment.'], 404);
        }

        $detailedData = $this->getDetailedData($assessment->assessment_id);

        return response()->json([
            'health_score' => $detailedData['health_score'] ?? null,
            'recommendations' => $detailedData['recommendations'] ?? [],
            'radar_chart' => $detailedData['radar_chart'] ?? [],
            'selected_assessment_id' => $assessment->assessment_id
        ]);
    }

    public function apiRealtimeMonitoring(Request $request)
    {
        $data = $this->getActiveData($request);
        $assessment = $data['assessment_with_score'];
        if (!$assessment) {
            return response()->json(['error' => 'Belum ada data assessment.'], 404);
        }

        $id = $assessment->assessment_id;
        $totalEmployees = \App\Models\Response::where('assessment_id', $id)
            ->where('respondent_type', 'employee')
            ->distinct('employee_code')
            ->count('employee_code');

        $hasOwner = \App\Models\Response::where('assessment_id', $id)
            ->where('respondent_type', 'owner')
            ->exists() ? 1 : 0;

        $totalRespondents = $totalEmployees + $hasOwner;
        $targetRespondents = ($assessment->jumlah_karyawan ?? 0) + 1;
        if ($targetRespondents <= 0) $targetRespondents = 1;

        $completionRate = min(round(($totalRespondents / $targetRespondents) * 100), 100);

        $healthService = app(\App\Services\HealthService::class);
        $estimatedScoreData = $healthService->calculate($id);

        $recentActivity = \App\Models\Response::where('assessment_id', $id)
            ->where('respondent_type', 'employee')
            ->select('employee_code', DB::raw('MAX(created_at) as last_active'))
            ->groupBy('employee_code')
            ->orderBy('last_active', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($act) {
                return [
                    'employee_code' => $act->employee_code,
                    'last_active' => \Carbon\Carbon::parse($act->last_active)->diffForHumans()
                ];
            });

        return response()->json([
            'total_respondents' => $totalRespondents,
            'target_respondents' => $targetRespondents,
            'completion_rate' => $completionRate,
            'estimated_score' => round($estimatedScoreData->overall_score ?? 0, 1),
            'estimated_category' => $estimatedScoreData->category ?? 'N/A',
            'recent_activity' => $recentActivity,
            'selected_assessment_id' => $id
        ]);
    }

    public function webExportManagement(Request $request)
    {
        $owner = Auth::guard('owner')->user() ?? auth()->user();
        $allUmkms = $owner ? Umkm::where('owner_id', $owner->owner_id)->get() : collect();
        $hasUmkm = $allUmkms->count() > 0;
        
        $selectedUmkmId = $request->query('umkm_id');
        $activeUmkm = null;
        
        if ($hasUmkm) {
            if ($selectedUmkmId) {
                $activeUmkm = $allUmkms->where('umkm_id', $selectedUmkmId)->first();
            }
            if (!$activeUmkm) {
                $activeUmkm = $allUmkms->first();
            }
        }

        // Get assessment history for dropdown
        $history = collect();
        if ($activeUmkm) {
            $tempHistory = Assessment::where('umkm_id', $activeUmkm->umkm_id)
                ->whereIn('status', ['open', 'closed', 'completed'])
                ->orderBy('created_at', 'asc')
                ->get();
                
            $monthCounters = [];
            foreach ($tempHistory as $hist) {
                $monthYear = \Carbon\Carbon::parse($hist->tanggal_mulai ?? $hist->created_at)->format('Y-m');
                if (!isset($monthCounters[$monthYear])) {
                    $monthCounters[$monthYear] = 1;
                } else {
                    $monthCounters[$monthYear]++;
                }
                $hist->sequence_in_month = $monthCounters[$monthYear];
            }
            $history = $tempHistory->sortByDesc('created_at')->values();
        }

        // Get unique industry sectors from database
        $allSectors = DB::table('umkms')
            ->whereNotNull('sektor_usaha')
            ->where('sektor_usaha', '<>', '')
            ->distinct()
            ->pluck('sektor_usaha');

        // Defaults for sectors
        $defaultSector = $activeUmkm ? $activeUmkm->sektor_usaha : null;
        $sdSector = $request->query('sd_sector', $defaultSector);
        $comparisonSector = $request->query('comparison_sector', $defaultSector);
        
        // Selected assessment
        $selectedAssessmentId = $request->query('assessment_id');
        if (!$selectedAssessmentId && $history->isNotEmpty()) {
            $selectedAssessmentId = $history->first()->assessment_id;
        }

        return view('pages.export-pdf', [
            'owner' => $owner,
            'all_umkms' => $allUmkms,
            'active_umkm' => $activeUmkm,
            'has_umkm' => $hasUmkm,
            'assessment_history' => $history,
            'selected_umkm_id' => $activeUmkm ? $activeUmkm->umkm_id : null,
            'selected_assessment_id' => $selectedAssessmentId,
            'all_sectors' => $allSectors,
            'selected_sd_sector' => $sdSector,
            'selected_comparison_sector' => $comparisonSector
        ]);
    }

    public function webExportPrint(Request $request)
    {
        $owner = Auth::guard('owner')->user() ?? auth()->user();
        if (!$owner) {
            abort(403, 'Unauthorized');
        }

        $umkmId = $request->query('umkm_id');
        $assessmentId = $request->query('assessment_id');
        $sdSector = $request->query('sd_sector', 'global');
        $comparisonSector = $request->query('comparison_sector');

        $activeUmkm = Umkm::where('owner_id', $owner->owner_id)->where('umkm_id', $umkmId)->first();
        if (!$activeUmkm) {
            abort(404, 'UMKM not found or does not belong to this owner.');
        }

        $assessment = Assessment::where('umkm_id', $activeUmkm->umkm_id)
            ->where('assessment_id', $assessmentId)
            ->first();
        if (!$assessment) {
            abort(404, 'Assessment not found.');
        }

        // Retrieve standard detailed data using helper method
        $detailedData = $this->getDetailedData($assessment->assessment_id, $comparisonSector);
        if (!$detailedData) {
            abort(500, 'Failed to calculate detailed data.');
        }

        // 1. Calculate Standard Deviation for selected sd_sector
        $sectorFactorDataQuery = DB::table('factor_scores as fs')
            ->join('health_scores as hs', 'fs.health_score_id', '=', 'hs.health_score_id')
            ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
            ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
            ->join('factors as f', 'fs.factor_id', '=', 'f.factor_id')
            ->whereNotNull('u.sektor_usaha')
            ->where('u.sektor_usaha', '!=', '');

        $allSectorFactorData = $sectorFactorDataQuery->select('u.sektor_usaha', 'f.factor_id', 'f.nama_factor', 'fs.score')->get();
        $sectors = $allSectorFactorData->pluck('sektor_usaha')->unique()->values()->all();
        $factorsList = DB::table('factors')->orderBy('urutan')->get();

        $factorScoresGrouped = [];
        $factorSectorsScoresGrouped = [];
        foreach ($allSectorFactorData as $row) {
            $score = (float)$row->score;
            $factorScoresGrouped[$row->factor_id][] = $score;
            $factorSectorsScoresGrouped[$row->factor_id][$row->sektor_usaha][] = $score;
        }

        $sectorScoresData = collect();
        if ($sdSector !== 'global') {
            $sectorScoresData = DB::table('factor_scores as fs')
                ->join('health_scores as hs', 'fs.health_score_id', '=', 'hs.health_score_id')
                ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
                ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
                ->where('u.sektor_usaha', $sdSector)
                ->select('u.nama_umkm', 'fs.factor_id', 'fs.score')
                ->get();
        }

        $factorStdDevs = [];
        foreach ($factorsList as $factor) {
            $fId = $factor->factor_id;
            if ($sdSector === 'global') {
                $allScores = $factorScoresGrouped[$fId] ?? [];
                $overallStdDev = $this->calculateStdDev($allScores);

                $highestStdSector = '-';
                $highestStdVal = -1.0;
                $lowestStdSector = '-';
                $lowestStdVal = 999999.0;

                foreach ($sectors as $sector) {
                    $scores = $factorSectorsScoresGrouped[$fId][$sector] ?? [];
                    if (count($scores) >= 2) {
                        $std = $this->calculateStdDev($scores);
                        if ($std > $highestStdVal) {
                            $highestStdVal = $std;
                            $highestStdSector = $sector;
                        }
                        if ($std < $lowestStdVal) {
                            $lowestStdVal = $std;
                            $lowestStdSector = $sector;
                        }
                    } elseif (count($scores) == 1) {
                        $std = 0.0;
                        if ($std > $highestStdVal) {
                            $highestStdVal = $std;
                            $highestStdSector = $sector;
                        }
                        if ($std < $lowestStdVal) {
                            $lowestStdVal = $std;
                            $lowestStdSector = $sector;
                        }
                    }
                }

                if ($highestStdVal === -1.0) $highestStdVal = 0.0;
                if ($lowestStdVal === 999999.0) $lowestStdVal = 0.0;

                $factorStdDevs[] = [
                    'nama_factor' => $factor->nama_factor,
                    'overall_std_dev' => round($overallStdDev / 20, 2),
                    'highest_std_sector' => $highestStdSector,
                    'highest_std_val' => round($highestStdVal / 20, 2),
                    'lowest_std_sector' => $lowestStdSector,
                    'lowest_std_val' => round($lowestStdVal / 20, 2)
                ];
            } else {
                $scores = $factorSectorsScoresGrouped[$fId][$sdSector] ?? [];
                $sectorStdDev = count($scores) >= 2 ? $this->calculateStdDev($scores) : 0.0;

                $factorSectorScores = $sectorScoresData->where('factor_id', $fId);
                $highestFactorUmkm = $factorSectorScores->sortByDesc('score')->first();
                $lowestFactorUmkm = $factorSectorScores->sortBy('score')->first();

                $factorStdDevs[] = [
                    'nama_factor' => $factor->nama_factor,
                    'sector_std_dev' => round($sectorStdDev / 20, 2),
                    'highest_score' => $highestFactorUmkm ? round($highestFactorUmkm->score / 20, 2) : null,
                    'highest_umkm' => $highestFactorUmkm ? $highestFactorUmkm->nama_umkm : '-',
                    'lowest_score' => $lowestFactorUmkm ? round($lowestFactorUmkm->score / 20, 2) : null,
                    'lowest_umkm' => $lowestFactorUmkm ? $lowestFactorUmkm->nama_umkm : '-'
                ];
            }
        }

        // Find highest/lowest SD factor
        $highestSdFactor = null;
        $lowestSdFactor = null;
        if (count($factorStdDevs) > 0) {
            $sdKey = $sdSector === 'global' ? 'overall_std_dev' : 'sector_std_dev';
            $sortedBySd = collect($factorStdDevs)->sortByDesc($sdKey);
            $highestRec = $sortedBySd->first();
            $lowestRec = $sortedBySd->last();
            
            $highestSdFactor = [
                'nama' => $highestRec['nama_factor'],
                'value' => $highestRec[$sdKey]
            ];
            $lowestSdFactor = [
                'nama' => $lowestRec['nama_factor'],
                'value' => $lowestRec[$sdKey]
            ];
        }

        // 2. Fetch Boxplot outliers for Profil 6 Faktor
        $outlierResults = [];
        foreach ($factorsList as $factor) {
            $responses = DB::table('responses as r')
                ->join('questions as q', 'r.question_id', '=', 'q.question_id')
                ->where('r.assessment_id', $assessment->assessment_id)
                ->where('q.factor_id', $factor->factor_id)
                ->select('r.answer_value', 'q.max_score')
                ->get();

            $normalizedScores = [];
            foreach ($responses as $resp) {
                $max = $resp->max_score ?? 5;
                $normalizedScores[] = ($resp->answer_value / $max) * 100;
            }

            if (count($normalizedScores) > 0) {
                sort($normalizedScores);
                $outlierResults[] = [
                    'nama_factor' => $factor->nama_factor,
                    'code' => $factor->nama_factor,
                    'stats' => $this->calculateBoxplot($normalizedScores)
                ];
            }
        }

        // 3. Derive period for section 2 rank table
        $date = $assessment->tanggal_mulai ?? $assessment->created_at;
        $selectedRankPeriod = \Carbon\Carbon::parse($date)->format('Y-m');
        $year = substr($selectedRankPeriod, 0, 4);
        $month = substr($selectedRankPeriod, 5, 2);

        $ownerUmkmScores = collect();
        $allFactorScores = [];
        $umkmIds = Umkm::where('owner_id', $owner->owner_id)->pluck('umkm_id')->toArray();

        if ($selectedRankPeriod && !empty($umkmIds)) {
            $ownerUmkmScores = DB::table('health_scores as hs')
                ->join('assessments as a', 'hs.assessment_id', '=', 'a.assessment_id')
                ->join('umkms as u', 'a.umkm_id', '=', 'u.umkm_id')
                ->where('u.owner_id', $owner->owner_id)
                ->where(function ($query) use ($year, $month) {
                    $query->where(function ($q) use ($year, $month) {
                        $q->whereYear('a.tanggal_mulai', $year)->whereMonth('a.tanggal_mulai', $month);
                    })->orWhere(function ($q) use ($year, $month) {
                        $q->whereNull('a.tanggal_mulai')->whereYear('a.created_at', $year)->whereMonth('a.created_at', $month);
                    });
                })
                ->whereIn('a.assessment_id', function ($query) use ($year, $month) {
                    $query->select(DB::raw('MAX(a2.assessment_id)'))
                        ->from('assessments as a2')
                        ->join('health_scores as hs2', 'a2.assessment_id', '=', 'hs2.assessment_id')
                        ->where(function ($q2) use ($year, $month) {
                            $q2->where(function ($q) use ($year, $month) {
                                $q->whereYear('a2.tanggal_mulai', $year)->whereMonth('a2.tanggal_mulai', $month);
                            })->orWhere(function ($q) use ($year, $month) {
                                $q->whereNull('a2.tanggal_mulai')->whereYear('a2.created_at', $year)->whereMonth('a2.created_at', $month);
                            });
                        })
                        ->groupBy('a2.umkm_id');
                })
                ->select('u.umkm_id', 'u.nama_umkm', 'hs.health_score_id', 'hs.overall_score')
                ->orderBy('hs.overall_score', 'asc')
                ->get();

            $healthScoreIds = $ownerUmkmScores->pluck('health_score_id')->toArray();
            if (!empty($healthScoreIds)) {
                $allFactorScores = DB::table('factor_scores')
                    ->whereIn('health_score_id', $healthScoreIds)
                    ->get()
                    ->groupBy('health_score_id');
            }
        }

        // Get sequence number in month for the assessment
        $tempHistory = Assessment::where('umkm_id', $activeUmkm->umkm_id)
            ->orderBy('created_at', 'asc')
            ->get();
            
        $monthCounters = [];
        $sequenceInMonth = 1;
        foreach ($tempHistory as $hist) {
            $mY = \Carbon\Carbon::parse($hist->tanggal_mulai ?? $hist->created_at)->format('Y-m');
            if (!isset($monthCounters[$mY])) {
                $monthCounters[$mY] = 1;
            } else {
                $monthCounters[$mY]++;
            }
            if ($hist->assessment_id == $assessment->assessment_id) {
                $sequenceInMonth = $monthCounters[$mY];
            }
        }

        $payload = [
            'owner' => $owner,
            'active_umkm' => $activeUmkm,
            'assessment' => $assessment,
            'sequence_in_month' => $sequenceInMonth,
            'sd_sector' => $sdSector,
            'comparison_sector' => $comparisonSector,
            
            // Dashboard page data
            'health_score' => $detailedData['health_score'] ?? null,
            'factor_std_devs' => $factorStdDevs,
            'highest_sd_factor' => $highestSdFactor,
            'lowest_sd_factor' => $lowestSdFactor,

            // Profil 6 Faktor page data
            'radar_chart' => $detailedData['radar_chart'] ?? [],
            'highlights' => $detailedData['highlights'] ?? ['highest' => null, 'lowest' => null],
            'outliers' => $outlierResults,

            // Perbandingan Faktor data
            'previous_assessment_info' => $detailedData['previous_assessment_info'] ?? null,
            'rankings' => $detailedData['rankings'] ?? ['all' => []],
            'industry_benchmark' => $detailedData['industry_benchmark'] ?? ['peer_count' => 0, 'sektor_usaha' => 'N/A'],

            // Rekomendasi data
            'recommendations' => $detailedData['recommendations'] ?? collect(),
            'selected_period' => $selectedRankPeriod,
            'all_umkm_scores' => $ownerUmkmScores,
            'all_factors' => $factorsList,
            'all_factor_scores' => $allFactorScores
        ];

        return view('pages.export-print', $payload);
    }

    private function calculateStdDev(array $numbers)
    {
        $count = count($numbers);
        if ($count < 2) {
            return 0.0;
        }
        $mean = array_sum($numbers) / $count;
        $sumOfSquares = 0.0;
        foreach ($numbers as $number) {
            $sumOfSquares += pow($number - $mean, 2);
        }
        return sqrt($sumOfSquares / ($count - 1));
    }
}

