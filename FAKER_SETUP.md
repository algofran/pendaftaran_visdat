# Setup Faker.js untuk Form Auto-fill

## Masalah yang Diatasi
Error "Faker.js is not loaded" terjadi karena library Faker.js tidak di-include di file HTML.

## Solusi yang Diterapkan

### 1. Menambahkan CDN Faker.js
File `index.html` sudah diupdate dengan menambahkan:
```html
<!-- Faker.js for form auto-fill -->
<script src="https://cdn.jsdelivr.net/npm/@faker-js/faker@8.0.0/dist/faker.min.js"></script>
```

### 2. Posisi Script
Script Faker.js ditempatkan di bagian `<head>` sebelum Bootstrap CSS untuk memastikan library tersedia sebelum script.js dijalankan.

## Fitur Auto-fill yang Tersedia

### Hotkey
- **Ctrl + Alt + I**: Auto-fill seluruh form dengan data dummy

### Fungsi yang Tersedia
- `autoFillForm()`: Mengisi semua field form dengan data dummy
- `generateTestData()`: Generate data test untuk development
- `fillFormWithTestData()`: Isi form dengan data test
- `testFaker()`: Test ketersediaan Faker.js

## Cara Penggunaan

### 1. Auto-fill Form
```javascript
// Panggil fungsi
autoFillForm();

// Atau gunakan hotkey: Ctrl + Alt + I
```

### 2. Test Faker.js
```javascript
// Test apakah Faker.js tersedia
testFaker();

// Generate data test
const testData = generateTestData();
```

## Data yang Dihasilkan

### Personal Information
- Nama lengkap (Indonesia)
- Email valid
- Nomor telepon (format 08##########)
- Tanggal lahir (18-60 tahun)
- Alamat lengkap

### Professional Information
- Posisi kerja (random dari daftar tersedia)
- Pendidikan (SMA/SMK, D3, S1, S2)
- Pengalaman kerja (0-20 tahun)
- Pengetahuan fiber optic
- Visi dan misi kerja
- Motivasi

### Technical Skills
- Pengalaman OTDR
- Pengalaman jointing
- Pengalaman memanjat tower
- Sertifikat K3

## Troubleshooting

### 1. Faker.js masih tidak tersedia
- Pastikan koneksi internet stabil
- Refresh halaman dan tunggu beberapa detik
- Periksa console browser untuk error

### 2. Fungsi auto-fill tidak berfungsi
- Pastikan semua field form memiliki ID yang sesuai
- Periksa console untuk error JavaScript
- Pastikan script.js dimuat setelah Faker.js

### 3. Data yang dihasilkan tidak sesuai
- Faker.js menggunakan locale default (en)
- Untuk data Indonesia, bisa di-customize di fungsi

## File Test
File `test-faker.html` tersedia untuk memverifikasi Faker.js berfungsi dengan baik.

## Versi yang Digunakan
- Faker.js v8.0.0 (latest stable)
- CDN: jsdelivr.net
- Compatible dengan semua browser modern

## Catatan Penting
- Faker.js hanya untuk development/testing
- Jangan gunakan untuk production data
- Data yang dihasilkan adalah dummy dan tidak nyata
- Fungsi auto-fill memudahkan testing form tanpa input manual
