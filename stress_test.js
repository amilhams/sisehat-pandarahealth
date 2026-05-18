import http from 'k6/http';
import { sleep, check } from 'k6';

/* =========================================================================
   PANDUAN PEMILIHAN TARGET UJI BEBAN (STRESS TEST TARGET CONFIGURATION)
   ========================================================================= */

// --- TARGET PILIHAN A: NGROK / LOCALHOST (DIREKOMENDASIKAN UNTUK TRAFIK TINGGI) ---
const TARGET_ENV = 'INFINITY_FREE'; // Ubah ke 'INFINITY_FREE' jika ingin menguji web online
const BASE_URL = 'http://pandarahealth.infinityfree.me'; // Ganti dengan link Ngrok asli Anda

// --- TARGET PILIHAN B: INFINITYFREE ONLINE (WAJIB BYPASS FIREWALL) ---
// Trik Bypass: Buka web Anda di Chrome -> Tekan F12 -> Application -> Cookies -> Salin Value "__test"
const INFINITYFREE_COOKIE_TEST = 'GANTI_DENGAN_COOKIE_TEST_ANDA_DI_SINI'; 

/* =========================================================================
   PENGATURAN VIRTUAL USERS (VUs) & DURASI UJI BEBAN
   ========================================================================= */
export const options = (TARGET_ENV === 'NGROK') 
    ? {
        // Skenario Ngrok/Lokal (SANGAT KUAT, Bisa menampung trafik ekstrim)
        stages: [
            { duration: '20s', target: 20 },  // Naikkan trafik ke 20 pengguna dalam 20 detik
            { duration: '30s', target: 50 },  // Naikkan trafik ke 50 pengguna dalam 30 detik
            { duration: '30s', target: 100 }, // Puncak ekstrim ke 100 pengguna
            { duration: '20s', target: 0 },   // Cooldown ke 0
        ],
        thresholds: {
            http_req_duration: ['p(95)<2000'],
            http_req_failed: ['rate<0.01'],
        }
    }
    : {
        // Skenario Online InfinityFree (AMAN, Trafik dibatasi agar tidak di-suspend 24 jam)
        stages: [
            { duration: '20s', target: 5 },   // Cukup 5 pengguna aktif bersamaan
            { duration: '30s', target: 10 },  // Maksimal 10 pengguna aktif
            { duration: '20s', target: 0 },   // Cooldown ke 0
        ],
        thresholds: {
            http_req_duration: ['p(95)<4000'], // Batas toleransi latensi hosting gratis 4 detik
            http_req_failed: ['rate<0.05'],    // Toleransi error maksimal 5%
        }
    };

// Kredensial Akun Owner default lokal Anda
const OWNER_EMAIL = 'admin1@gmail.com'; 
const OWNER_PASSWORD = 'password123'; 

export default function () {
    // Siapkan parameter header untuk bypass firewall & penyamaran User-Agent
    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        },
    };

    // Jika menguji InfinityFree, suntikkan cookie bypass keamanan
    if (TARGET_ENV === 'INFINITY_FREE') {
        params.headers['Cookie'] = `__test=${INFINITYFREE_COOKIE_TEST}`;
    }

    // --- TAHAP 1: Mengakses Halaman Utama ---
    let resWelcome = http.get(`${BASE_URL}/`, params);
    check(resWelcome, {
        'Halaman utama status 200': (r) => r.status === 200,
    });
    sleep(1);

    // --- TAHAP 2: Mengakses Halaman Login ---
    let resLoginPage = http.get(`${BASE_URL}/login`, params);
    check(resLoginPage, {
        'Halaman login status 200': (r) => r.status === 200,
    });
    sleep(1);

    // --- TAHAP 3: Simulasi Login Owner (Menguji Kecepatan Autentikasi API) ---
    const payload = JSON.stringify({
        email: OWNER_EMAIL,
        password: OWNER_PASSWORD,
    });

    let resLogin = http.post(`${BASE_URL}/api/auth/login`, payload, params);
    let loginOk = check(resLogin, {
        'API Login sukses (200)': (r) => r.status === 200,
    });

    // Jika login sukses, uji kueri database berat dengan cookie sesi
    if (loginOk) {
        const sessionParams = {
            headers: Object.assign({}, params.headers, {
                'Cookie': (TARGET_ENV === 'INFINITY_FREE') 
                    ? `__test=${INFINITYFREE_COOKIE_TEST}; ${resLogin.headers['Set-Cookie']}`
                    : resLogin.headers['Set-Cookie']
            })
        };

        // --- TAHAP 4: Mengambil Data Urutan UMKM (Kueri Hitung Bobot DSS Berat) ---
        let resRank = http.get(`${BASE_URL}/api/umkm/rank`, sessionParams);
        check(resRank, {
            'API rangking UMKM status 200': (r) => r.status === 200,
        });
        sleep(2);
    } else {
        // Fallback: Menguji kuesioner publik jika login gagal
        let resQuestions = http.get(`${BASE_URL}/api/assessment/questions?type=owner`, params);
        check(resQuestions, {
            'API Kuesioner status 200': (r) => r.status === 200,
        });
        sleep(2);
    }
}
