# Panduan Deployment ke Laravel Cloud

Panduan ini akan membantu Anda men-deploy aplikasi ini ke **Laravel Cloud**.

## 1. Persiapan Repository (Git)
Aplikasi Anda harus tersimpan di repository Git (GitHub, GitLab, atau Bitbucket).

1. Pastikan semua perubahan sudah di-commit:
   ```bash
   git add .
   git commit -m "Siap untuk deploy"
   ```
2. Pastikan file `.env` **TIDAK** ikut ter-upload (cek `.gitignore`), tetapi Anda akan membutuhkan isinya nanti.
3. Push kode Anda ke repository remote (misal GitHub).

## 2. Setup di Laravel Cloud
1. Login ke [console.laravel.cloud](https://console.laravel.cloud) (atau URL dashboard resmi saat ini).
2. Buat **Project Baru**.
3. **Hubungkan Repository**: Pilih provider (GitHub/GitLab) dan pilih repository aplikasi ini.
4. **Konfigurasi Region**: Pilih lokasi server yang paling dekat dengan pengguna Anda (misal: Singapore jika tersedia).

## 3. Konfigurasi Environment (PENTING)
Laravel Cloud akan otomatis mendeteksi aplikasi Laravel, tetapi Anda perlu mengatur **Environment Variables**. Masukkan data berikut di menu **Environment** atau **Settings** di dashboard:

*   `APP_NAME`: Nama Aplikasi Anda
*   `APP_ENV`: `production`
*   `APP_DEBUG`: `false` (Penting untuk keamanan)
*   `APP_URL`: URL yang diberikan oleh Laravel Cloud (misal: `https://projx.laravel.cloud`)

### Database
Laravel Cloud biasanya menyediakan **Managed Database**.
1. Buat Database baru di dashboard Laravel Cloud.
2. Link database tersebut ke aplikasi Anda. Platform biasanya akan otomatis mengisi variabel `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`. Jika tidak, salin kredensial database ke Environment Variables.

### Google OAuth (Login Akun)
Karena aplikasi ini menggunakan **Socialite (Google Login)**, Anda harus mengupdate kredensial Google di Environment Variables Laravel Cloud:

*   `GOOGLE_CLIENT_ID`: (Salin dari Google Console)
*   `GOOGLE_CLIENT_SECRET`: (Salin dari Google Console)
*   `GOOGLE_REDIRECT_URI`: Pastikan formatnya sesuai dengan domain produksi, misal:
    `https://nama-project-anda.laravel.cloud/auth/google/callback`

**PENTING**: Jangan lupa menambahkan URL produksi tersebut ke **Authorized redirect URIs** di Google Cloud Console (tempat Anda membuat Client ID).

## 4. Proses Build & Deploy
Laravel Cloud menggunakan file konfigurasi otomatis atau `build hooks`. Secara default untuk aplikasi Laravel modern:

1. Platform akan menjalankan `composer install --optimize-autoloader --no-dev`.
2. Platform akan menjalankan `npm install && npm run build` untuk meng-compile asset CSS/JS (Vite).
3. Platform akan menjalankan `php artisan migrate --force` (Anda mungkin perlu mengaktifkan opsi ini di settings deployment agar database otomatis terupdate).

## 5. Cek Hasil
Setelah deployment selesai (biasanya status "Healthy" atau "Deployed"):
1. Buka URL aplikasi.
2. Coba login (pastikan Google Auth sudah dikonfigurasi).
3. Cek fitur history dan export Excel.

## Troubleshooting Umum
*   **Error 500**: Cek **Logs** di dashboard Laravel Cloud. Biasanya karena salah input Environment Variable.
*   **Asset tidak muncul**: Pastikan perintah `npm run build` berjalan sukses saat deployment.
*   **Google Login Error**: Pastikan `GOOGLE_REDIRECT_URI` di Env Vars sama persis dengan yang ada di Google Console.
