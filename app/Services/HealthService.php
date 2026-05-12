<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\HealthScore;
use App\Models\Factor;
use App\Models\Question;
use App\Models\Response;
use App\Models\FactorScore;
use App\Models\Recommendation;
use App\Models\AssessmentRecommendation;

class HealthService
{
    /**
     * Menghitung Health Score Lengkap untuk sebuah Assessment (DSS Logic)
     */
    public function calculate($assessmentId)
    {
        $factors = Factor::with('questions')->get();
        
        $totalNormalizedScore = 0;
        $validFactorsCount = 0;

        // 1. Buat Health Score (kosong dulu)
        $healthScore = HealthScore::updateOrCreate(
            ['assessment_id' => $assessmentId],
            [
                'overall_score' => 0,
                'category' => 'Pending',
                'calculated_at' => now()
            ]
        );

        // 2. Simpan Factor Scores dan Rekomendasi
        foreach ($factors as $factor) {
            $normalizedQuestionScores = [];

            foreach ($factor->questions as $question) {
                $ownerScore = $this->getOwnerScore($assessmentId, $question->question_id);
                $employeeScore = $this->getMeanEmployeeScore($assessmentId, $question->question_id);

                $qRawScore = null;
                // Logic 50:50 penggabungan Owner & Employee
                if ($ownerScore !== null && $employeeScore !== null) {
                    $qRawScore = ($ownerScore + $employeeScore) / 2;
                } elseif ($ownerScore !== null) {
                    $qRawScore = $ownerScore;
                } elseif ($employeeScore !== null) {
                    $qRawScore = $employeeScore;
                }

                if ($qRawScore !== null) {
                    $max = $question->max_score ?? 5;
                    $normScore = ($qRawScore / $max) * 100;
                    $normalizedQuestionScores[] = min(100, $normScore);
                }
            }

            if (count($normalizedQuestionScores) > 0) {
                $avgNormalizedScore = array_sum($normalizedQuestionScores) / count($normalizedQuestionScores);
                
                // Konversi kembali ke skala 5 untuk penentuan kategori agar konsisten dengan rule DSS
                $category = $this->determineCategory(($avgNormalizedScore / 100) * 5);

                FactorScore::updateOrCreate(
                    ['health_score_id' => $healthScore->health_score_id, 'factor_id' => $factor->factor_id],
                    ['score' => $avgNormalizedScore]
                );

                $this->generateRecommendation($assessmentId, $factor->factor_id, $category);

                $totalNormalizedScore += $avgNormalizedScore;
                $validFactorsCount++;
            }
        }

        // Tentukan Kategori Overall
        $overallNormalizedScore = $validFactorsCount > 0 ? $totalNormalizedScore / $validFactorsCount : 0;
        $overallRawScore = ($overallNormalizedScore / 100) * 5;
        $overallCategory = $this->determineCategory($overallRawScore);

        $healthScore->update([
            'overall_score' => $overallNormalizedScore,
            'category' => $overallCategory,
            'calculated_at' => now()
        ]);

        return $healthScore;
    }

    /**
     * Mencari rekomendasi yang cocok dari Knowledge Base dan menyimpannya sebagai Snapshot
     */
    private function generateRecommendation($assessmentId, $factorId, $category)
    {
        // Cari rekomendasi dari knowledge base berdasarkan faktor dan level kategori
        $recommendation = Recommendation::where('factor_id', $factorId)
            ->where('category_level', $category)
            ->first();

        if ($recommendation) {
            // Simpan snapshot rekomendasi untuk history assessment ini
            AssessmentRecommendation::updateOrCreate(
                ['assessment_id' => $assessmentId, 'recommendation_id' => $recommendation->recommendation_id ?? $recommendation->id],
                ['generated_at' => now()]
            );
        }
    }

    private function getOwnerScore($assessmentId, $questionId)
    {
        $response = Response::where('assessment_id', $assessmentId)
            ->where('question_id', $questionId)
            ->where('respondent_type', 'owner')
            ->first();
        return $response ? (float) $response->answer_value : null;
    }

    private function getMeanEmployeeScore($assessmentId, $questionId)
    {
        $mean = Response::where('assessment_id', $assessmentId)
            ->where('question_id', $questionId)
            ->where('respondent_type', 'employee')
            ->avg('answer_value');
        return $mean !== null ? (float) $mean : null;
    }

    public function determineCategory($score)
    {
        if ($score <= 1.25) return 'KURANG_SEHAT';
        if ($score <= 2.50) return 'CUKUP_SEHAT';
        if ($score <= 3.75) return 'SEHAT';
        return 'SANGAT_SEHAT';
    }
}
