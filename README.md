# Sistem Pelaporan Kerusakan & Gangguan Fasilitas Fakultas

Sistem Pelaporan Kerusakan & Gangguan Fasilitas Fakultas merupakan sebuah sistem untuk membantu mahasiswa, dosen, ataupun staf untuk melaporkan kerusakan atau gangguan fasilitas di lingkungan fakultas Ilmu Komputer, seperti ruang kelas, laboratorium, dan area umum lainnya. Laporan yang dimasukkan diproses oleh admin dan dapat dipantau statusnya secara real-time oleh pelapor.

## 👥 Anggota kelompok
1. 235150707111008 - ALFATA DZAKY AL AFFAAFY
2. 235150707111025 - TANTRY PURBA
3. 235150707111029 - ANDREAN NOVIANDI
4. 235150707111028 - ANDHIKA ANANTA PUTRA

## 🎯 Fitur-fitur

### Fitur Wajib
1. Autentikasi pengguna (Login & Register menggunakan JWT)
2. Membuat laporan kerusakan (dengan deskripsi, bukti gambar, kategori dan lokasi)
3. Melihat daftar laporan & status laporan
4. Pencarian laporan berdasarkan judul dan filter berdasarkan kategori
5. Admin mengelola laporan (update status)
6. Role management (user, admin)
7. REST API
8. Logging aktivitas

### Fitur Opsional
1. Dashboard statistik laporan
2. Penentuan prioritas laporan (urgent / normal)

## 👤 _Role_
| Role   | Hak Akses                      |
| -------| -------------------------------|
| Admin  | Mengelola semua laporan & user |
| User   | Membuat & melihat laporan      |

## 🔄 Alur Sistem

Alur 1: User membuat laporan
1. User login
2. Mengisi form laporan
3. Data dikirim ke REST API
4. Data disimpan di database
5. Status awal: “Dikirim”

Alur 2: Admin memproses laporan
1. Admin login
2. Melihat daftar laporan
3. Admin melihat detail laporan
4. Mengubah status menjadi:
    - Dikirim → Diproses
    - Diproses → Selesai
  
Alur 3: User memantau laporan
1. User login
2. Melihat daftar laporan miliknya
3. Melihat perubahan status

## 🗂️ Desain _Database_

1. Tabel users
  * id INT (PK, AUTO_INCREMENT)
  * nama VARCHAR(100)
  * email VARCHAR(100) (UNIQUE)
  * password VARCHAR(255)
  * role ENUM('admin', 'user')
  * created_at TIMESTAMP
  * updated_at TIMESTAMP

2. Tabel categories
  * id INT (PK, AUTO_INCREMENT)
  * nama_kategori VARCHAR(100)

3. Tabel reports
  * id INT (PK, AUTO_INCREMENT)
  * user_id INT (FK → users.id)
  * category_id INT (FK → categories.id)
  * judul VARCHAR(150)
  * deskripsi TEXT
  * lokasi VARCHAR(150)
  * gambar VARCHAR(255) (path file)
  * status ENUM('dikirim', 'diproses', 'selesai')
  * created_at TIMESTAMP
  * updated_at TIMESTAMP

4. Tabel logs
  * id INT (PK, AUTO_INCREMENT)
  * user_id INT (FK → users.id)
  * aktivitas TEXT
  * created_at TIMESTAMP
