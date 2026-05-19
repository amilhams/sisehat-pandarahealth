<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiSehat API - Portal Pengembang</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Fira+Code:wght@400;500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-color: #050505;
            --surface-color: #0d0d0d;
            --card-color: #121212;
            --border-color: #222222;
            --text-primary: #ffffff;
            --text-secondary: #a3a3a3;
            --primary: #ffffff;
            --accent: #6366f1;
            
            --get-color: #10b981;
            --post-color: #3b82f6;
            --put-color: #f59e0b;
            --delete-color: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Header */
        header {
            border-bottom: 1px solid var(--border-color);
            background-color: rgba(5, 5, 5, 0.8);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #fff;
        }
        .logo-icon {
            font-size: 24px;
            color: #fff;
        }
        .logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .dev-badge {
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #a5b4fc;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .back-btn {
            background: transparent;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 16px;
            color: var(--text-secondary);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .back-btn:hover {
            color: #fff;
            border-color: #444;
            background: rgba(255, 255, 255, 0.02);
        }

        /* Container */
        .main-layout {
            max-width: 1300px;
            width: 100%;
            margin: 0 auto;
            padding: 40px 24px;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 40px;
            flex: 1;
        }

        /* Sidebar Navigation */
        .sidebar {
            position: sticky;
            top: 100px;
            height: calc(100vh - 140px);
            overflow-y: auto;
            padding-right: 16px;
        }
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar::-webkit-scrollbar-thumb {
            background: #222;
            border-radius: 4px;
        }
        .sidebar-title {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }
        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .nav-item a {
            display: block;
            padding: 10px 14px;
            border-radius: 8px;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            transition: all 0.2s;
            border-left: 2px solid transparent;
        }
        .nav-item a:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.02);
        }
        .nav-item.active a {
            color: #fff;
            background: rgba(255, 255, 255, 0.04);
            border-left-color: var(--accent);
        }

        /* API Content Area */
        .api-content {
            display: flex;
            flex-direction: column;
            gap: 48px;
        }

        .section-header {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 6px;
        }
        .section-desc {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        /* Endpoint Card */
        .endpoint-card {
            background: var(--card-color);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            margin-bottom: 24px;
            overflow: hidden;
            transition: border-color 0.2s;
        }
        .endpoint-card:hover {
            border-color: #333;
        }

        /* Card Header */
        .endpoint-header {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            cursor: pointer;
            user-select: none;
            background: rgba(255, 255, 255, 0.01);
        }
        .endpoint-route {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1;
            min-width: 0;
        }
        .method-badge {
            font-size: 11px;
            font-weight: 800;
            padding: 5px 10px;
            border-radius: 6px;
            width: 70px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            flex-shrink: 0;
        }
        .method-get { background: rgba(16, 185, 129, 0.12); color: var(--get-color); border: 1px solid rgba(16, 185, 129, 0.2); }
        .method-post { background: rgba(59, 130, 246, 0.12); color: var(--post-color); border: 1px solid rgba(59, 130, 246, 0.2); }
        .method-put { background: rgba(245, 158, 11, 0.12); color: var(--put-color); border: 1px solid rgba(245, 158, 11, 0.2); }
        .method-delete { background: rgba(239, 68, 68, 0.12); color: var(--delete-color); border: 1px solid rgba(239, 68, 68, 0.2); }

        .route-path {
            font-family: 'Fira Code', monospace;
            font-size: 14px;
            font-weight: 500;
            color: #fff;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .route-desc {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Card Details Body */
        .endpoint-details {
            padding: 24px;
            border-top: 1px solid var(--border-color);
            background: rgba(0, 0, 0, 0.15);
        }

        .detail-section {
            margin-bottom: 20px;
        }
        .detail-section:last-child {
            margin-bottom: 0;
        }
        .detail-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        /* Codeblock styling */
        .code-container {
            position: relative;
            background: #080808;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 16px;
            overflow-x: auto;
        }
        .code-block {
            font-family: 'Fira Code', monospace;
            font-size: 12.5px;
            color: #a5b4fc;
            line-height: 1.5;
            white-space: pre;
        }

        /* Input / Parameter Tables */
        .param-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .param-table th, .param-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        .param-table th {
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }
        .param-name {
            font-family: 'Fira Code', monospace;
            font-weight: 600;
            color: #f43f5e;
        }
        .param-type {
            font-family: 'Fira Code', monospace;
            font-size: 11px;
            color: var(--text-secondary);
        }
        .param-req {
            font-size: 11px;
            font-weight: 700;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
            padding: 2px 6px;
            border-radius: 4px;
        }
        .param-opt {
            font-size: 11px;
            color: var(--text-secondary);
            background: rgba(255, 255, 255, 0.05);
            padding: 2px 6px;
            border-radius: 4px;
        }

        /* Try API Panel */
        .btn-try {
            background: #fff;
            color: #000;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-try:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
        .btn-try:active {
            transform: translateY(0);
        }

        .api-tester {
            margin-top: 16px;
            border-top: 1px dashed var(--border-color);
            padding-top: 16px;
            display: none;
        }
        .tester-inputs {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 16px;
        }
        .tester-input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .tester-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
        }
        .tester-input {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 10px 14px;
            color: #fff;
            font-size: 13px;
            outline: none;
        }
        .tester-input:focus {
            border-color: var(--accent);
        }

        .test-result-wrapper {
            margin-top: 12px;
            display: none;
        }
        .result-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .status-pill {
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 700;
        }
        .status-success { background: rgba(16, 185, 129, 0.15); color: var(--get-color); }
        .status-error { background: rgba(239, 68, 68, 0.15); color: var(--delete-color); }

        .spinner {
            display: none;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(0, 0, 0, 0.1);
            border-top: 2px solid #000;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 900px) {
            .main-layout { grid-template-columns: 1fr; }
            .sidebar { display: none; }
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header>
        <div class="header-container">
            <div style="display: flex; align-items: center; gap: 14px;">
                <a href="{{ route('beranda') }}" class="logo">
                    <i class="fa-solid fa-heart-pulse logo-icon"></i>
                    <span class="logo-text">SiSehat API</span>
                </a>
                <span class="dev-badge">Portal Developer</span>
            </div>
            <a href="{{ route('beranda') }}" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Web Utama
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <div class="main-layout">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <h3 class="sidebar-title">Kategori API</h3>
            <ul class="nav-list">
                <li class="nav-item active"><a href="#authentication">Authentication</a></li>
                <li class="nav-item"><a href="#umkm">UMKM Management</a></li>
                <li class="nav-item"><a href="#assessment">Assessment & DSS</a></li>
                <li class="nav-item"><a href="#analytics">Analytics Dashboard</a></li>
                <li class="nav-item"><a href="#system">System Admin</a></li>
            </ul>
        </aside>

        <!-- API Content List -->
        <main class="api-content">
            
            <!-- Section: Authentication -->
            <section id="authentication">
                <div class="section-header">
                    <h2 class="section-title">Authentication API</h2>
                    <p class="section-desc">API untuk menangani registrasi akun Owner baru, login autentikasi, logout sesi, dan pengambilan data owner aktif.</p>
                </div>

                <!-- API 1: Register -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-route">
                            <span class="method-badge method-post">POST</span>
                            <span class="route-path">/api/auth/register</span>
                        </div>
                        <span class="route-desc">Registrasi Owner baru</span>
                    </div>
                    <div class="endpoint-details">
                        <div class="detail-section">
                            <div class="detail-title">Request Body Parameters (JSON)</div>
                            <table class="param-table">
                                <thead>
                                    <tr>
                                        <th>Nama Parameter</th>
                                        <th>Tipe</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="param-name">name</td>
                                        <td class="param-type">string</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td>Nama lengkap pendaftar Owner</td>
                                    </tr>
                                    <tr>
                                        <td class="param-name">email</td>
                                        <td class="param-type">string</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td>Alamat email unik (untuk login)</td>
                                    </tr>
                                    <tr>
                                        <td class="param-name">gender</td>
                                        <td class="param-type">enum</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td><code>laki-laki</code> atau <code>perempuan</code></td>
                                    </tr>
                                    <tr>
                                        <td class="param-name">password</td>
                                        <td class="param-type">string</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td>Kata sandi akun (min. 6 karakter)</td>
                                    </tr>
                                    <tr>
                                        <td class="param-name">password_confirmation</td>
                                        <td class="param-type">string</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td>Konfirmasi kata sandi harus persis sama</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="detail-section">
                            <div class="detail-title">Response Sukses (201 Created)</div>
                            <div class="code-container">
                                <code class="code-block">{
    "message": "Registrasi berhasil",
    "data": {
        "owner_id": 4,
        "name": "Budi Santoso",
        "email": "budi@perusahaan.com",
        "gender": "laki-laki",
        "created_at": "2026-05-19T04:14:32.000000Z"
    }
}</code>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API 2: Login -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-route">
                            <span class="method-badge method-post">POST</span>
                            <span class="route-path">/api/auth/login</span>
                        </div>
                        <span class="route-desc">Autentikasi masuk akun Owner</span>
                    </div>
                    <div class="endpoint-details">
                        <div class="detail-section">
                            <div class="detail-title">Request Body Parameters (JSON)</div>
                            <table class="param-table">
                                <thead>
                                    <tr>
                                        <th>Nama Parameter</th>
                                        <th>Tipe</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="param-name">email</td>
                                        <td class="param-type">string</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td>Alamat email terdaftar</td>
                                    </tr>
                                    <tr>
                                        <td class="param-name">password</td>
                                        <td class="param-type">string</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td>Kata sandi akun</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="detail-section">
                            <div class="detail-title">Response Sukses (200 OK)</div>
                            <div class="code-container">
                                <code class="code-block">{
    "message": "Login berhasil",
    "data": {
        "owner_id": 4,
        "name": "Budi Santoso",
        "email": "budi@perusahaan.com",
        "gender": "laki-laki"
    }
}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section: UMKM Management -->
            <section id="umkm">
                <div class="section-header">
                    <h2 class="section-title">UMKM Management API</h2>
                    <p class="section-desc">API untuk mengelola data UMKM (Unit Usaha) organisasi, seperti mengambil seluruh UMKM, menambah unit baru, dan melihat informasi terperinci.</p>
                </div>

                <!-- API 3: List UMKM -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-route">
                            <span class="method-badge method-get">GET</span>
                            <span class="route-path">/api/umkm</span>
                        </div>
                        <span class="route-desc">Mengambil daftar UMKM berdasarkan Owner</span>
                    </div>
                    <div class="endpoint-details">
                        <div class="detail-section">
                            <div class="detail-title">Query Parameters</div>
                            <table class="param-table">
                                <thead>
                                    <tr>
                                        <th>Nama Parameter</th>
                                        <th>Tipe</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="param-name">owner_id</td>
                                        <td class="param-type">integer</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td>ID unik Owner yang ingin ditarik datanya</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="detail-section">
                            <button class="btn-try" onclick="showTester(this, '/api/umkm', 'GET', ['owner_id'])">
                                <i class="fa-solid fa-play"></i> Uji Coba Endpoint
                            </button>
                            
                            <!-- Api Tester Panel -->
                            <div class="api-tester">
                                <div class="tester-inputs">
                                    <div class="tester-input-group">
                                        <label class="tester-label">owner_id</label>
                                        <input type="number" class="tester-input param-field" data-param="owner_id" placeholder="Masukkan owner_id (Contoh: 1)" value="1">
                                    </div>
                                </div>
                                <button class="btn-try" style="background:#6366f1; color:#fff;" onclick="executeTest(this)">
                                    <span class="btn-test-text">Kirim Request</span>
                                    <div class="spinner"></div>
                                </button>
                                
                                <div class="test-result-wrapper">
                                    <div class="result-meta">
                                        <span>Response:</span>
                                        <span class="status-pill status-success">200 OK</span>
                                    </div>
                                    <div class="code-container">
                                        <code class="code-block result-code">// Data hasil request akan muncul disini</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API 4: Create UMKM -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-route">
                            <span class="method-badge method-post">POST</span>
                            <span class="route-path">/api/umkm</span>
                        </div>
                        <span class="route-desc">Menambahkan UMKM baru</span>
                    </div>
                    <div class="endpoint-details">
                        <div class="detail-section">
                            <div class="detail-title">Request Body Parameters (JSON)</div>
                            <table class="param-table">
                                <thead>
                                    <tr>
                                        <th>Nama Parameter</th>
                                        <th>Tipe</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="param-name">owner_id</td>
                                        <td class="param-type">integer</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td>ID unik Owner pemilik usaha</td>
                                    </tr>
                                    <tr>
                                        <td class="param-name">nama_umkm</td>
                                        <td class="param-type">string</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td>Nama unit bisnis (maks 150 karakter)</td>
                                    </tr>
                                    <tr>
                                        <td class="param-name">sektor_usaha</td>
                                        <td class="param-type">string</td>
                                        <td><span class="param-opt">Opsional</span></td>
                                        <td>Contoh: Retail / Makanan & Minuman / Jasa</td>
                                    </tr>
                                    <tr>
                                        <td class="param-name">umur_usaha</td>
                                        <td class="param-type">string</td>
                                        <td><span class="param-opt">Opsional</span></td>
                                        <td>Contoh: 2 Tahun</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section: Assessment & DSS -->
            <section id="assessment">
                <div class="section-header">
                    <h2 class="section-title">Assessment & DSS API</h2>
                    <p class="section-desc">API untuk membuat sesi kuesioner, memicu perhitungan skor 6 faktor kesehatan organisasi via DSS, dan memantau respons karyawan.</p>
                </div>

                <!-- API 5: Get Questions -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-route">
                            <span class="method-badge method-get">GET</span>
                            <span class="route-path">/api/assessment/questions</span>
                        </div>
                        <span class="route-desc">Mengambil daftar pertanyaan kuesioner</span>
                    </div>
                    <div class="endpoint-details">
                        <div class="detail-section">
                            <div class="detail-title">Query Parameters</div>
                            <table class="param-table">
                                <thead>
                                    <tr>
                                        <th>Nama Parameter</th>
                                        <th>Tipe</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="param-name">type</td>
                                        <td class="param-type">string</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td><code>owner</code> (untuk kuesioner Owner) atau <code>employee</code> (untuk Karyawan)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="detail-section">
                            <button class="btn-try" onclick="showTester(this, '/api/assessment/questions', 'GET', ['type'])">
                                <i class="fa-solid fa-play"></i> Uji Coba Endpoint
                            </button>
                            
                            <!-- Api Tester Panel -->
                            <div class="api-tester">
                                <div class="tester-inputs">
                                    <div class="tester-input-group">
                                        <label class="tester-label">type</label>
                                        <input type="text" class="tester-input param-field" data-param="type" placeholder="Masukkan type (owner / employee)" value="owner">
                                    </div>
                                </div>
                                <button class="btn-try" style="background:#6366f1; color:#fff;" onclick="executeTest(this)">
                                    <span class="btn-test-text">Kirim Request</span>
                                    <div class="spinner"></div>
                                </button>
                                
                                <div class="test-result-wrapper">
                                    <div class="result-meta">
                                        <span>Response:</span>
                                        <span class="status-pill status-success">200 OK</span>
                                    </div>
                                    <div class="code-container">
                                        <code class="code-block result-code">// Data hasil request akan muncul disini</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section: Analytics Dashboard -->
            <section id="analytics">
                <div class="section-header">
                    <h2 class="section-title">Analytics Dashboard API</h2>
                    <p class="section-desc">API penyuplai data visual grafik dashboard, seperti nilai radar 6 faktor, tren historis, dan analisis pencilan Boxplot.</p>
                </div>

                <!-- API 6: Outliers Boxplot -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-route">
                            <span class="method-badge method-get">GET</span>
                            <span class="route-path">/api/dashboard/assessment/{id}/outliers</span>
                        </div>
                        <span class="route-desc">Mengambil parameter statistik Boxplot (Q1, Q2, Q3, Outliers)</span>
                    </div>
                    <div class="endpoint-details">
                        <div class="detail-section">
                            <div class="detail-title">Path Parameters</div>
                            <table class="param-table">
                                <thead>
                                    <tr>
                                        <th>Nama Parameter</th>
                                        <th>Tipe</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="param-name">id</td>
                                        <td class="param-type">integer</td>
                                        <td><span class="param-req">Wajib</span></td>
                                        <td>ID unik assessment yang ingin dihitung nilai pencilan boxplot-nya</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="detail-section">
                            <button class="btn-try" onclick="showTester(this, '/api/dashboard/assessment/{id}/outliers', 'GET', ['id'], true)">
                                <i class="fa-solid fa-play"></i> Uji Coba Endpoint
                            </button>
                            
                            <!-- Api Tester Panel -->
                            <div class="api-tester">
                                <div class="tester-inputs">
                                    <div class="tester-input-group">
                                        <label class="tester-label">id (Assessment ID)</label>
                                        <input type="number" class="tester-input param-field" data-param="id" placeholder="Masukkan ID Assessment (Contoh: 1)" value="1">
                                    </div>
                                </div>
                                <button class="btn-try" style="background:#6366f1; color:#fff;" onclick="executeTest(this)">
                                    <span class="btn-test-text">Kirim Request</span>
                                    <div class="spinner"></div>
                                </button>
                                
                                <div class="test-result-wrapper">
                                    <div class="result-meta">
                                        <span>Response:</span>
                                        <span class="status-pill status-success">200 OK</span>
                                    </div>
                                    <div class="code-container">
                                        <code class="code-block result-code">// Data hasil request akan muncul disini</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section: System Admin -->
            <section id="system">
                <div class="section-header">
                    <h2 class="section-title">System Administration API</h2>
                    <p class="section-desc">API internal sistem untuk pengelolaan lingkungan hosting database tanpa SSH.</p>
                </div>

                <!-- API 7: Run Migration -->
                <div class="endpoint-card">
                    <div class="endpoint-header">
                        <div class="endpoint-route">
                            <span class="method-badge method-get">GET</span>
                            <span class="route-path">/run-migration</span>
                        </div>
                        <span class="route-desc">Memicu migrasi database & impor kuesioner CSV otomatis</span>
                    </div>
                    <div class="endpoint-details">
                        <div class="detail-section">
                            <div class="detail-title">Respons Sukses (200 OK)</div>
                            <div class="code-container">
                                <code class="code-block">{
    "status": "success",
    "message": "Migrasi database dan impor CSV berhasil dilakukan!",
    "migrate_output": "Migration table created successfully...",
    "import_output": "CSV Question data seeded successfully..."
}</code>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </main>
    </div>

    <!-- Script Tester Handler -->
    <script>
        // Smooth scroll spy navigation
        const sections = document.querySelectorAll("section");
        const navLi = document.querySelectorAll(".nav-item");

        window.addEventListener("scroll", () => {
            let current = "";
            sections.forEach((section) => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (pageYOffset >= sectionTop - 120) {
                    current = section.getAttribute("id");
                }
            });

            navLi.forEach((li) => {
                li.classList.remove("active");
                if (li.querySelector("a").getAttribute("href") === `#${current}`) {
                    li.classList.add("active");
                }
            });
        });

        // Expand/Collapse cards on header click
        const cardHeaders = document.querySelectorAll(".endpoint-header");
        cardHeaders.forEach(header => {
            header.addEventListener("click", () => {
                const details = header.nextElementSibling;
                if (details.style.display === "none" || !details.style.display) {
                    details.style.display = "block";
                } else {
                    details.style.display = "none";
                }
            });
        });

        // Initialize collapsed states
        document.querySelectorAll(".endpoint-details").forEach(detail => {
            detail.style.display = "none";
        });
        // Open the first one
        document.querySelector(".endpoint-details").style.display = "block";

        // Show Testing Panel
        function showTester(btn, path, method, params, isPathParams = false) {
            const cardBody = btn.closest(".endpoint-details");
            const tester = cardBody.querySelector(".api-tester");
            
            // Set data attributes
            tester.setAttribute("data-path", path);
            tester.setAttribute("data-method", method);
            tester.setAttribute("data-is-path-param", isPathParams ? "true" : "false");
            
            if (tester.style.display === "none" || !tester.style.display) {
                tester.style.display = "block";
                btn.innerHTML = `<i class="fa-solid fa-chevron-up"></i> Sembunyikan Panel`;
            } else {
                tester.style.display = "none";
                btn.innerHTML = `<i class="fa-solid fa-play"></i> Uji Coba Endpoint`;
            }
        }

        // Execute API Test
        async function executeTest(btn) {
            const tester = btn.closest(".api-tester");
            const pathTemplate = tester.getAttribute("data-path");
            const method = tester.getAttribute("data-method");
            const isPathParam = tester.getAttribute("data-is-path-param") === "true";
            
            const spinner = btn.querySelector(".spinner");
            const btnText = btn.querySelector(".btn-test-text");
            const resultWrapper = tester.querySelector(".test-result-wrapper");
            const resultPill = tester.querySelector(".status-pill");
            const resultCode = tester.querySelector(".result-code");

            // Reset States
            spinner.style.display = "block";
            btnText.textContent = "Mengirim...";
            btn.disabled = true;
            resultWrapper.style.display = "none";

            // Build Target URL
            let finalUrl = pathTemplate;
            const paramInputs = tester.querySelectorAll(".param-field");
            let queryParams = new URLSearchParams();

            paramInputs.forEach(input => {
                const paramName = input.getAttribute("data-param");
                const paramVal = input.value;

                if (isPathParam) {
                    finalUrl = finalUrl.replace(`{${paramName}}`, paramVal);
                } else {
                    if (paramVal) {
                        queryParams.append(paramName, paramVal);
                    }
                }
            });

            if (!isPathParam && queryParams.toString()) {
                finalUrl = `${finalUrl}?${queryParams.toString()}`;
            }

            try {
                const response = await fetch(finalUrl, {
                    method: method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                const data = await response.json();
                
                // Show Result
                resultWrapper.style.display = "block";
                resultPill.textContent = `${response.status} ${response.statusText}`;
                
                if (response.ok) {
                    resultPill.className = "status-pill status-success";
                } else {
                    resultPill.className = "status-pill status-error";
                }

                resultCode.textContent = JSON.stringify(data, null, 4);

            } catch (err) {
                resultWrapper.style.display = "block";
                resultPill.textContent = "ERROR CONNECTING";
                resultPill.className = "status-pill status-error";
                resultCode.textContent = JSON.stringify({ error: err.message }, null, 4);
            } finally {
                spinner.style.display = "none";
                btnText.textContent = "Kirim Request";
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
