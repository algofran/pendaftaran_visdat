# Sistem Pendaftaran Kerja PT. Visdat Teknik Utama

Sistem pendaftaran kerja online untuk PT. Visdat Teknik Utama yang memungkinkan pelamar untuk mendaftar secara digital dengan upload dokumen dan foto.

## Fitur Utama

### 🎯 Form Pendaftaran Lengkap
- Data pribadi (nama, email, telepon, dll.)
- Informasi pendidikan dan pengalaman
- Upload dokumen (CV, Foto, Sertifikat K3, SIM)
- Pertanyaan khusus untuk posisi teknis
- Visi, misi, dan motivasi kerja

### 📁 Upload File dengan Kompresi
- Kompresi otomatis gambar menggunakan Compressor.js
- Validasi format dan ukuran file
- Preview file sebelum upload
- Petunjuk upload yang jelas untuk setiap jenis file

### 🎨 Interface Modern
- Desain responsif dengan Bootstrap 5
- Animasi dan transisi yang smooth
- Modal konfirmasi berhasil/error
- Petunjuk file yang informatif

### 🔧 Panel Admin
- Login admin dengan autentikasi
- Dashboard untuk melihat semua lamaran
- Update status lamaran
- Export data lamaran

## Struktur File

```
visdatrekrut/
├── index.html              # Form pendaftaran utama
├── process.php             # Backend untuk memproses lamaran
├── config.php              # Konfigurasi database
├── database.sql            # Struktur database
├── script.js               # JavaScript untuk frontend
├── style.css               # CSS styling
├── uploads/                # Folder penyimpanan file
├── admin/                  # Panel admin
│   ├── index.php          # Dashboard admin
│   ├── login.php          # Halaman login
│   ├── view.php           # Detail lamaran
│   └── update-status.php  # Update status
└── README.md              # Dokumentasi ini
```

## Persyaratan Sistem

### Server Requirements
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Web server (Apache/Nginx)
- Ekstensi PHP: PDO, fileinfo, gd

### Browser Support
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Instalasi

### 1. Setup Database
```sql
-- Import file database.sql ke MySQL
mysql -u username -p database_name < database.sql
```

### 2. Konfigurasi Database
Edit file `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'visdatrekrut');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 3. Setup Folder Upload
```bash
# Buat folder uploads dan set permission
mkdir uploads
chmod 755 uploads
```

### 4. Konfigurasi Web Server
Pastikan folder `uploads/` dapat diakses untuk upload file.

## Penggunaan

### Untuk Pelamar
1. Buka `index.html` di browser
2. Isi form pendaftaran dengan lengkap
3. Upload dokumen sesuai petunjuk:
   - **CV/Resume**: PDF, DOC, DOCX (max 5MB)
   - **Foto 3x4**: JPG, JPEG, PNG (max 2MB)
   - **Sertifikat K3**: PDF, JPG, JPEG, PNG (max 3MB)
   - **SIM A/C**: PDF, JPG, JPEG, PNG (max 3MB)
4. Submit form
5. Tunggu konfirmasi berhasil dengan nomor referensi

### Untuk Admin
1. Akses `/admin/login.php`
2. Login dengan kredensial admin
3. Lihat semua lamaran di dashboard
4. Update status lamaran sesuai kebutuhan
5. Export data jika diperlukan

## Posisi yang Tersedia

### 1. Teknisi FOT
- **Tugas**: Menangani gangguan layanan FOT/FOC/PS
- **Kualifikasi**: Sertifikat K3, SMK/D3 Telekomunikasi, pengalaman 2 tahun

### 2. Teknisi FOC
- **Tugas**: Menangani gangguan layanan FOC
- **Kualifikasi**: Sertifikat K3, SMK/D3 Telekomunikasi, pengalaman 2 tahun

### 3. Teknisi Jointer
- **Tugas**: Penarikan kabel FO dan pemasangan aksesoris
- **Kualifikasi**: Pengalaman APD, bisa memanjat tower, pengalaman 2 tahun

### 4. Driver
- **Tugas**: Mengemudikan kendaraan untuk tim
- **Kualifikasi**: SIM A & C, pendidikan minimal SMK, tidak sedang proses hukum

### 5. Admin Zona
- **Tugas**: Administrasi Serpo
- **Kualifikasi**: Pendidikan minimal SMA, pengalaman admin

## Keamanan

### Validasi File
- Validasi ekstensi file
- Validasi ukuran file (maksimal 5MB)
- Validasi tipe MIME
- Kompresi gambar untuk optimasi

### Database Security
- Prepared statements untuk mencegah SQL injection
- Validasi input di sisi server
- Sanitasi data sebelum disimpan

### Upload Security
- Validasi file di sisi server
- Pembatasan tipe file yang diizinkan
- Pembatasan ukuran file
- Nama file yang unik untuk mencegah konflik

## Troubleshooting

### Masalah Upload File
1. **File terlalu besar**: Pastikan file tidak melebihi batas maksimal
2. **Format tidak didukung**: Gunakan format file yang diizinkan
3. **Upload gagal**: Periksa permission folder uploads

### Masalah Database
1. **Koneksi gagal**: Periksa konfigurasi database di config.php
2. **Tabel tidak ada**: Import ulang database.sql
3. **Error query**: Periksa log error PHP

### Masalah Tampilan
1. **CSS tidak load**: Periksa path file CSS
2. **JavaScript error**: Periksa console browser
3. **Modal tidak muncul**: Pastikan Bootstrap JS terload

## Kontribusi

Untuk berkontribusi pada project ini:
1. Fork repository
2. Buat branch fitur baru
3. Commit perubahan
4. Push ke branch
5. Buat Pull Request

## Lisensi

Project ini dibuat untuk PT. Visdat Teknik Utama. Semua hak cipta dilindungi.

## Kontak

Untuk pertanyaan atau dukungan teknis:
- Email: hrd@visualdata.co.id
- Telepon: +62 xxx xxxx xxxx

---

**PT. Visdat Teknik Utama** - Sistem Pendaftaran Kerja Online
