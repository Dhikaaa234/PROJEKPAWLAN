# Frontend-Backend Integration Guide

## ✅ Status: FULLY CONNECTED

Frontend Vue.js sekarang **sudah terhubung sepenuhnya** dengan backend Laravel. Berikut adalah daftar perubahan dan cara menggunakannya.

---

## 🔧 Perubahan yang Dilakukan

### Frontend (Vue.js)

1. **Buat API Service** (`src/services/api.js`)
   - Service untuk handle semua HTTP requests ke backend
   - Automatic token management (Bearer token)
   - Error interceptors untuk handling connection failures
   - CORS-ready configuration

2. **Update Auth Store** (`src/stores/auth.js`)
   - Menggunakan actual API calls (tidak mock lagi)
   - Proper error handling dengan pesan error spesifik
   - Token & user state management
   - Support untuk "Remember Me" feature

3. **Add Error Display** di views:
   - `Login.vue` - Tampilkan error message jika login gagal
   - `Register.vue` - Tampilkan error message jika registrasi gagal
   - `ForgotPassword.vue` - Tampilkan error message jika request gagal

4. **Environment Config**
   - `.env.example` - Template konfigurasi
   - `.env.local` - Config untuk development (sudah di-create)

### Backend (Laravel)

1. **Update Routes** (`routes/api.php`)
   - Tambah endpoint `/forgot-password` untuk reset password

2. **Add Forgot Password Controller** (`AuthController.php`)
   - Endpoint untuk request reset password link
   - Secure message (tidak reveal apakah email ada atau tidak)

3. **Enable CORS** (`config/cors.php` & `bootstrap/app.php`)
   - Configure CORS untuk accept request dari frontend
   - Allow multiple localhost ports untuk development

---

## 🚀 Cara Setup & Test

### Prerequisites
- PHP 8.2+
- MySQL (sedang berjalan di localhost:3306)
- Node.js 18+

### Backend Setup

```bash
cd backend

# 1. Install dependencies (jika belum)
composer install

# 2. Setup environment
cp .env.example .env

# 3. Generate app key (sudah dilakukan, tapi cek)
php artisan key:generate

# 4. Setup database
php artisan migrate:refresh --seed

# 5. Jalankan backend server
php artisan serve
# Backend akan berjalan di: http://localhost:8000
```

### Frontend Setup

```bash
cd frontend

# 1. Install dependencies (jika belum)
npm install

# 2. Setup environment (sudah di-create)
# File .env.local sudah ada dengan:
# VITE_API_URL=http://localhost:8000/api

# 3. Jalankan frontend dev server
npm run dev
# Frontend akan berjalan di: http://localhost:5173 (atau port lain)
```

---

## 🧪 Testing Integration

### Test 1: Login dengan Kredensial Valid
1. Buka http://localhost:5173 (atau sesuai output npm run dev)
2. Masuk di Login page
3. **Email**: akun yang sudah di-register
4. **Password**: password yang benar
5. **Expected Result**: 
   - ✅ Muncul pesan sukses "Selamat datang, [nama]"
   - ✅ Redirect ke home/dashboard
   - ✅ Token disimpan di localStorage

### Test 2: Login dengan Kredensial Salah
1. Buka Login page
2. Email & password yang tidak valid
3. **Expected Result**:
   - ❌ Muncul pesan error merah: "Email atau password salah"
   - ❌ Tidak redirect, tetap di halaman login

### Test 3: Backend Connection Error
1. **Matikan backend** server (`Ctrl+C` di terminal backend)
2. Buka Login page
3. Coba login
4. **Expected Result**:
   - ❌ Muncul pesan error: "Tidak dapat terhubung ke server. Pastikan backend sedang berjalan di http://localhost:8000"
   - ❌ Status indication yang jelas

### Test 4: Register dengan Email Baru
1. Buka Register page
2. Isi form:
   - Name: "Budi Santoso"
   - NIM: "225150200111000"
   - Email: "budi@filkom.edu" (email baru)
   - Password: "password123"
3. **Expected Result**:
   - ✅ Pesan sukses: "Akun berhasil dibuat! Selamat datang, Budi Santoso"
   - ✅ Auto login
   - ✅ Token disimpan
   - ✅ Data tersimpan di database

### Test 5: Register dengan Email yang Sudah Ada
1. Buka Register page
2. Isi dengan email yang sudah registered
3. **Expected Result**:
   - ❌ Pesan error: "Email sudah terdaftar"

### Test 6: Forgot Password
1. Buka Forgot Password page
2. Masukkan email
3. **Expected Result**:
   - ✅ Pesan: "Link reset password telah dikirim ke [email]"
   - ✅ (Dalam production, email akan dikirimkan dengan link reset)

---

## 📊 API Endpoints

### Public Endpoints (No Auth Required)
```
POST /api/register
  Body: { nama, email, password }
  Response: { token, user, message }

POST /api/login
  Body: { email, password }
  Response: { token, user, message }

POST /api/forgot-password
  Body: { email }
  Response: { message }
```

### Protected Endpoints (Auth Required - add Bearer token)
```
POST /api/logout
  Headers: Authorization: Bearer <token>
  Response: { message }

GET /api/me
  Headers: Authorization: Bearer <token>
  Response: { user_data }
```

---

## 🔒 Security Features

1. **Token Management**
   - Tokens stored di localStorage
   - Automatic inclusion dalam setiap API request
   - Auto logout jika token expired (401 response)

2. **Error Handling**
   - Tidak show server internals error
   - User-friendly error messages
   - Network error detection

3. **Validation**
   - Frontend validation (email format, password length)
   - Backend validation (comprehensive)
   - Password hashing dengan Bcrypt

---

## 🐛 Troubleshooting

### "Tidak dapat terhubung ke server"
- ✅ Pastikan backend running: `php artisan serve`
- ✅ Backend harus di http://localhost:8000
- ✅ Cek `.env.local` memiliki URL yang benar

### CORS Error di browser console
- ✅ Backend sudah di-configure dengan CORS middleware
- ✅ Jika tetap error, restart backend: `php artisan serve`

### Token tidak tersimpan
- ✅ Check browser's localStorage: F12 > Application > LocalStorage
- ✅ Lihat apakah response dari API include `token` field

### Database tidak ter-sync
- ✅ Run migrations: `php artisan migrate:fresh --seed`
- ✅ Pastikan database name di `.env` sesuai: `filkom_care`

---

## 📝 Database Schema

Frontend-Backend sudah terhubung ke database dengan schema:
- **users** table: Store user credentials & info
- **logs** table: Track semua aktivitas (login, register, logout)
- **roles** table: User role management (admin, user)
- **categories**, **reports**, **statuses**: Untuk reporting feature

---

## ✨ Next Steps

1. Implement password reset email sending (forgot password feature)
2. Add role-based access control (RBAC) untuk admin features
3. Implement refresh token untuk better security
4. Add SSO integration (University SSO)
5. Setup production environment (.env.production)

---

**Setup selesai! 🎉 Frontend dan Backend sudah terhubung dan siap untuk development.**
