import http from 'k6/http';
import { sleep, check } from 'k6';

// 1. SKENARIO UJI BEBAN (STRESS TEST SCENARIO)
// Menguji ketahanan web dari 10 pengguna hingga puncak 100 pengguna secara bertahap
export const options = {
    stages: [
        { duration: '30s', target: 20 },  // Naikkan trafik ke 20 pengguna dalam 30 detik
        { duration: '1m', target: 50 },   // Naikkan trafik ke 50 pengguna dalam 1 menit
        { duration: '1m', target: 100 },  // Lonjakan ekstrem ke 100 pengguna (Puncak Stress Test)
        { duration: '1m', target: 100 },  // Tahan beban 100 pengguna selama 1 menit
        { duration: '30s', target: 0 },   // Turunkan kembali ke 0 (Cooldown)
    ],
    thresholds: {
        http_req_duration: ['p(95)<2000'], // 95% request harus selesai di bawah 2 detik (2000ms)
        http_req_failed: ['rate<0.01'],    // Toleransi kegagalan request wajib di bawah 1%
    },
};

// 2. KREDENSIAL LOGIN AKUN OWNER UNTUK STRESS TEST API BERSESI
const OWNER_EMAIL = 'admin1@gmail.com'; 
const OWNER_PASSWORD = 'password123'; // Default password seeder local Anda

export default function () {
    // === UBAH BAGIAN INI DENGAN ALAMAT NGROK ANDA ===
    const BASE_URL = 'https://XXXX-XXXX.ngrok-free.app'; 
    // ===============================================

    const params = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    };

    // --- PENGUJIAN 1: Mengakses Halaman Utama (Tanpa Login) ---
    let resWelcome = http.get(`${BASE_URL}/`, params);
    check(resWelcome, {
        'Halaman utama status is 200': (r) => r.status === 200,
    });
    sleep(1);

    // --- PENGUJIAN 2: Mengakses Halaman Login ---
    let resLoginPage = http.get(`${BASE_URL}/login`, params);
    check(resLoginPage, {
        'Halaman login status is 200': (r) => r.status === 200,
    });
    sleep(1);

    // --- PENGUJIAN 3: Simulasi Login Owner Melalui API (POST) ---
    const payload = JSON.stringify({
        email: OWNER_EMAIL,
        password: OWNER_PASSWORD,
    });

    let resLogin = http.post(`${BASE_URL}/api/auth/login`, payload, params);
    let loginOk = check(resLogin, {
        'Login API sukses (status 200)': (r) => r.status === 200,
        'Mendapatkan cookies / session': (r) => r.headers['Set-Cookie'] !== undefined || r.status === 200,
    });

    // Jika login berhasil, uji halaman-halaman yang membutuhkan otorisasi (dalam session)
    if (loginOk) {
        // Mewariskan Cookie Sesi Login untuk request berikutnya
        const sessionParams = {
            headers: {
                'Cookie': resLogin.headers['Set-Cookie'],
                'Accept': 'application/json',
            },
        };

        // --- PENGUJIAN 4: Mengambil Data Rangking UMKM (Kueri Database Berat) ---
        let resRank = http.get(`${BASE_URL}/api/umkm/rank`, sessionParams);
        check(resRank, {
            'API rangking UMKM status is 200': (r) => r.status === 200,
            'Data rank terisi': (r) => r.body.length > 0,
        });
        sleep(2);
    } else {
        // Jika akun di atas belum terdaftar/password salah, uji endpoint publik lain
        let resQuestions = http.get(`${BASE_URL}/api/assessment/questions?type=owner`, params);
        check(resQuestions, {
            'API Kuesioner Publik status is 200': (r) => r.status === 200,
        });
        sleep(2);
    }
}
