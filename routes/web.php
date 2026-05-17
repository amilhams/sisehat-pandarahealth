<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UmkmController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeAssessmentController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('pages.welcome');
})->name('welcome');

// Web App UI Routes (Frontend Integration)
Route::middleware(['auth:owner'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'webDashboard'])->name('dashboard');
    Route::get('/assessment', [DashboardController::class, 'webAssessment'])->name('assessment');
    Route::get('/assessment/fill/{umkm_id}', [DashboardController::class, 'webFillAssessment'])->name('assessment.fill');
    Route::post('/assessment/finish/{id}', [DashboardController::class, 'webFinishAssessment'])->name('assessment.finish');
    Route::post('/assessment/new/{umkm_id}', [DashboardController::class, 'webCreateNewAssessment'])->name('assessment.new');
    Route::get('/monitoring', [DashboardController::class, 'webMonitoring'])->name('monitoring');
    Route::get('/comparison', [DashboardController::class, 'webComparison'])->name('comparison');
    Route::get('/rekomendasi', [DashboardController::class, 'webRekomendasi'])->name('rekomendasi');
    Route::get('/profil-faktor', [DashboardController::class, 'webProfilFaktor'])->name('profil-faktor');
    Route::get('/umkm-rank', [DashboardController::class, 'webUmkmRank'])->name('umkm-rank');
    Route::get('/api/umkm/rank', [DashboardController::class, 'apiUmkmRank'])->name('api.umkm.rank');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/name', [ProfileController::class, 'showChangeName'])->name('profile.name');
    Route::post('/profile/name', [ProfileController::class, 'updateName'])->name('profile.name.post');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.post');

    Route::get('/beranda', function () { return view('pages.beranda'); })->name('beranda');
    Route::get('/tambah-umkm', function () { return view('pages.tambah-umkm'); })->name('tambah-umkm');
    Route::post('/tambah-umkm', [UmkmController::class, 'webStore'])->name('tambah-umkm.post');
    Route::delete('/umkm/{id}', [UmkmController::class, 'webDestroy'])->name('umkm.destroy');
});

// Routes untuk Ganti Password (bisa diakses user login maupun guest)
Route::get('/profile/password', [ProfileController::class, 'showChangePassword'])->name('profile.password');
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.post');

Route::post('/assessment/generate', [AssessmentController::class, 'createAssessment'])->name('assessment.generate');
Route::post('/api/responses/submit', [AssessmentController::class, 'submitResponse'])->name('api.responses.submit');
Route::get('/employee-assessment/{token}', [EmployeeAssessmentController::class, 'show'])->name('employee.assessment');
Route::post('/employee-assessment/{token}', [EmployeeAssessmentController::class, 'store'])->name('employee.assessment.submit');
// Route Rahasia untuk Migrasi & Impor Data via Browser (Berguna untuk cPanel / Hosting tanpa SSH)
Route::get('/run-migration', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $outputMigrate = \Illuminate\Support\Facades\Artisan::output();
        
        \Illuminate\Support\Facades\Artisan::call('import:csv');
        $outputImport = \Illuminate\Support\Facades\Artisan::output();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Migrasi database dan impor CSV berhasil dilakukan!',
            'migrate_output' => $outputMigrate,
            'import_output' => $outputImport
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/login', function () { return view('pages.login'); })->name('login');
Route::get('/register', function () { return view('pages.register'); })->name('register');

// Authentication Routes (Khusus Owner)
Route::prefix('api/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout.post');
    Route::get('/me', [AuthController::class, 'me']);
});

// UMKM Routes
Route::prefix('api/umkm')->group(function () {
    Route::get('/', [UmkmController::class, 'index']);
    Route::post('/', [UmkmController::class, 'store']);
    Route::get('/{id}', [UmkmController::class, 'show']);
    Route::put('/{id}', [UmkmController::class, 'update']);
});

// Assessment & Survey Routes
Route::prefix('api/assessment')->group(function () {
    Route::post('/create', [AssessmentController::class, 'createAssessment']);
    Route::get('/questions', [AssessmentController::class, 'getQuestions']); // ?type=owner|employee
    Route::post('/submit', [AssessmentController::class, 'submitResponse']);
    Route::post('/{id}/calculate', [AssessmentController::class, 'calculateScore']);
    Route::get('/{id}/monitoring', [AssessmentController::class, 'getLiveMonitoring']);
});

// Dashboard & Charts Routes
Route::prefix('api/dashboard')->group(function () {
    Route::get('/umkm/{id}/latest', [DashboardController::class, 'getLatestScore']);
    Route::get('/umkm/{id}/trend', [DashboardController::class, 'getHistoricalTrend']);
    Route::get('/assessment/{id}/factors', [DashboardController::class, 'getFactorBreakdown']);
    Route::get('/assessment/{id}/details', [DashboardController::class, 'getDetailedAnalysis']);
    Route::get('/assessment/{id}/outliers', [DashboardController::class, 'getFactorOutliers']);
});
