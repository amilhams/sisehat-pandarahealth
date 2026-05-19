<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiSehat - API Engine Status</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Fira+Code:wght@400;500&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-color: #050505;
            --surface-color: #0a0a0a;
            --card-color: #111111;
            --border-color: #1e1e1e;
            --text-primary: #ffffff;
            --text-secondary: #8e8e93;
            --accent: #6366f1;
            --success: #10b981;
            --warning: #f59e0b;
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
            justify-content: center;
            align-items: center;
            overflow-x: hidden;
            padding: 24px;
        }

        /* Ambient Glow Behind */
        .ambient-glow {
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.08) 0%, rgba(0, 0, 0, 0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            pointer-events: none;
        }

        .container {
            max-width: 800px;
            width: 100%;
            background: var(--surface-color);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            padding: 40px;
            z-index: 10;
            position: relative;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.8);
        }

        /* Status Badge */
        .status-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 24px;
        }
        .service-title {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .icon-box {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, var(--accent) 0%, #4f46e5 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
        }
        .title-text h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .title-text p {
            font-size: 13px;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .status-badge {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: var(--success);
            font-size: 12px;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: var(--success);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 1.6s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Metrics Grid */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }
        .metric-card {
            background: var(--card-color);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 20px;
            text-align: left;
        }
        .metric-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .metric-value {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
        }

        /* Terminal Window */
        .terminal-window {
            background: #020202;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 32px;
        }
        .terminal-header {
            background: #090909;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .terminal-dots {
            display: flex;
            gap: 6px;
        }
        .dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .dot-red { background: #ef4444; }
        .dot-yellow { background: #f59e0b; }
        .dot-green { background: #10b981; }
        .terminal-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-secondary);
            font-family: 'Fira Code', monospace;
        }
        .terminal-body {
            padding: 20px;
            height: 220px;
            overflow-y: auto;
            font-family: 'Fira Code', monospace;
            font-size: 12px;
            line-height: 1.6;
            color: #d1d5db;
        }
        .terminal-body::-webkit-scrollbar {
            width: 4px;
        }
        .terminal-body::-webkit-scrollbar-thumb {
            background: #111;
        }

        /* Log colors */
        .log-time { color: var(--text-secondary); }
        .log-method-get { color: var(--success); font-weight: bold; }
        .log-method-post { color: var(--accent); font-weight: bold; }
        .log-path { color: #fff; }
        .log-status { color: var(--success); }
        .log-ms { color: var(--warning); }

        /* Action Footer */
        .action-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }
        .db-status {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-secondary);
        }
        .db-status i {
            color: var(--success);
        }
        .btn-portal {
            background: #fff;
            color: #000;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.1);
        }
        .btn-portal:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
        .btn-portal:active {
            transform: translateY(0);
        }
        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            box-shadow: none;
        }
        .btn-secondary:hover {
            color: #fff;
            border-color: #333;
            background: rgba(255, 255, 255, 0.02);
        }
    </style>
</head>
<body>

    <div class="ambient-glow"></div>

    <div class="container">
        <!-- Status Header -->
        <div class="status-header">
            <div class="service-title">
                <div class="icon-box">
                    <i class="fa-solid fa-server"></i>
                </div>
                <div class="title-text">
                    <h1>SiSehat API Gateway</h1>
                    <p>Layanan backend mesin utama & database DSS</p>
                </div>
            </div>
            <div class="status-badge">
                <span class="pulse-dot"></span>
                <span>SYSTEM ONLINE</span>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-label">Rata-rata Latensi</div>
                <div class="metric-value" id="val-latency">42 ms</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Spesifikasi Engine</div>
                <div class="metric-value" style="font-size: 16px; margin-top: 4px;">Laravel 10 + PHP 8.2</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Uptime Bulanan</div>
                <div class="metric-value">99.98%</div>
            </div>
        </div>

        <!-- Terminal Logs simulation -->
        <div class="terminal-window">
            <div class="terminal-header">
                <div class="terminal-dots">
                    <span class="dot dot-red"></span>
                    <span class="dot dot-yellow"></span>
                    <span class="dot dot-green"></span>
                </div>
                <div class="terminal-title">sisehat_api_stream.log</div>
                <div style="width: 40px;"></div>
            </div>
            <div class="terminal-body" id="log-body">
                <!-- Logs will be populated here -->
            </div>
        </div>

        <!-- Footer -->
        <div class="action-footer">
            <div class="db-status">
                <i class="fa-solid fa-circle-check"></i>
                <span>Database MySQL Terhubung</span>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="{{ route('beranda') }}" class="btn-portal btn-secondary">
                    <i class="fa-solid fa-house"></i> Halaman Web Utama
                </a>
                <a href="{{ route('api.docs') }}" class="btn-portal">
                    <i class="fa-solid fa-code"></i> Jelajahi API Docs
                </a>
            </div>
        </div>
    </div>

    <!-- Script to simulate live server logs -->
    <script>
        const logBody = document.getElementById('log-body');
        const paths = [
            { path: '/api/auth/me', method: 'GET', status: 200 },
            { path: '/api/umkm?owner_id=1', method: 'GET', status: 200 },
            { path: '/api/assessment/questions?type=employee', method: 'GET', status: 200 },
            { path: '/api/dashboard/umkm/1/latest', method: 'GET', status: 200 },
            { path: '/api/dashboard/assessment/1/outliers', method: 'GET', status: 200 },
            { path: '/api/dashboard/umkm/1/trend', method: 'GET', status: 200 },
            { path: '/api/assessment/1/calculate', method: 'POST', status: 201 },
            { path: '/api/auth/login', method: 'POST', status: 200 },
            { path: '/api/umkm', method: 'POST', status: 201 }
        ];

        function getCurrentTime() {
            const now = new Date();
            return now.toISOString().replace('T', ' ').substring(0, 19);
        }

        function generateLog() {
            const randomRoute = paths[Math.floor(Math.random() * paths.length)];
            const time = getCurrentTime();
            const latency = Math.floor(Math.random() * 60) + 15; // 15ms - 75ms
            
            const methodSpan = randomRoute.method === 'GET' 
                ? `<span class="log-method-get">GET</span>` 
                : `<span class="log-method-post">POST</span>`;
                
            const logLine = document.createElement('div');
            logLine.innerHTML = `
                <span class="log-time">[${time}]</span> 
                ${methodSpan} 
                <span class="log-path">${randomRoute.path}</span> - 
                <span class="log-status">${randomRoute.status} OK</span> 
                (<span class="log-ms">${latency}ms</span>)
            `;
            
            logBody.appendChild(logLine);
            logBody.scrollTop = logBody.scrollHeight;

            // Randomize average latency metric slightly
            document.getElementById('val-latency').textContent = `${Math.floor(Math.random() * 10) + 38} ms`;
        }

        // Generate initial logs
        for(let i = 0; i < 6; i++) {
            generateLog();
        }

        // Keep generating logs
        setInterval(generateLog, 3000);
    </script>
</body>
</html>
