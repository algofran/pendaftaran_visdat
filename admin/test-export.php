<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Export Functionality</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Test Export to Excel</h5>
                    </div>
                    <div class="card-body">
                        <p>Click the button below to test the Excel export functionality:</p>
                        <button type="button" class="btn btn-success" onclick="testExport()">
                            <i class="fas fa-file-excel me-1"></i>
                            Test Export to Excel
                        </button>
                        
                        <div id="results" class="mt-4"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        function showResult(message, type) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }

        function testExport() {
            const button = event.target;
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Testing...';
            button.disabled = true;

            // Test data
            const testData = [
                {
                    'No': 1,
                    'Nama Lengkap': 'John Doe',
                    'Email': 'john@example.com',
                    'Telepon': '081234567890',
                    'Posisi': 'Teknisi FOT',
                    'Pendidikan': 'SMK Teknik',
                    'Pengalaman (Tahun)': 3,
                    'Alamat': 'Jl. Test No. 123, Jakarta',
                    'Tanggal Lahir': '1990-01-01',
                    'Jenis Kelamin': 'Laki-laki',
                    'File CV': 'http://localhost/uploads/cv_test.pdf',
                    'File Foto': 'http://localhost/uploads/photo_test.jpg',
                    'File Sertifikat K3': 'http://localhost/uploads/k3_test.pdf',
                    'File SIM': 'http://localhost/uploads/sim_test.jpg',
                    'Pengetahuan Fiber Optik': 'Sangat baik dalam instalasi dan maintenance fiber optik',
                    'Pengalaman OTDR': 'Ya',
                    'Pengalaman Jointing': 'Ya',
                    'Pengalaman Panjat Tower': 'Ya',
                    'Sertifikat K3': 'Ya',
                    'Visi Kerja': 'Menjadi teknisi fiber optik yang profesional',
                    'Misi Kerja': 'Memberikan layanan terbaik untuk pelanggan',
                    'Motivasi': 'Terus belajar dan berkembang dalam teknologi telekomunikasi',
                    'Status Lamaran': 'Review',
                    'Tanggal Daftar': '01/01/2024 10:30:00',
                    'Terakhir Update': '02/01/2024 14:20:00'
                },
                {
                    'No': 2,
                    'Nama Lengkap': 'Jane Smith',
                    'Email': 'jane@example.com',
                    'Telepon': '081234567891',
                    'Posisi': 'Admin Zona',
                    'Pendidikan': 'S1 Administrasi',
                    'Pengalaman (Tahun)': 2,
                    'Alamat': 'Jl. Sample No. 456, Bandung',
                    'Tanggal Lahir': '1992-05-15',
                    'Jenis Kelamin': 'Perempuan',
                    'File CV': 'http://localhost/uploads/cv_jane.pdf',
                    'File Foto': 'http://localhost/uploads/photo_jane.jpg',
                    'File Sertifikat K3': '',
                    'File SIM': 'http://localhost/uploads/sim_jane.jpg',
                    'Pengetahuan Fiber Optik': 'Memahami dasar-dasar teknologi fiber optik',
                    'Pengalaman OTDR': 'Tidak',
                    'Pengalaman Jointing': 'Tidak',
                    'Pengalaman Panjat Tower': 'Tidak',
                    'Sertifikat K3': 'Tidak',
                    'Visi Kerja': 'Mengorganisir administrasi dengan efisien',
                    'Misi Kerja': 'Mendukung tim lapangan dengan data yang akurat',
                    'Motivasi': 'Membantu perusahaan mencapai target operasional',
                    'Status Lamaran': 'Accepted',
                    'Tanggal Daftar': '03/01/2024 09:15:00',
                    'Terakhir Update': '05/01/2024 16:45:00'
                }
            ];

            try {
                // Create Excel workbook
                const workbook = XLSX.utils.book_new();
                
                // Convert data to worksheet
                const worksheet = XLSX.utils.json_to_sheet(testData);
                
                // Set column widths for better formatting
                const columnWidths = [
                    { wch: 5 },   // No
                    { wch: 20 },  // Nama Lengkap
                    { wch: 25 },  // Email
                    { wch: 15 },  // Telepon
                    { wch: 15 },  // Posisi
                    { wch: 15 },  // Pendidikan
                    { wch: 12 },  // Pengalaman
                    { wch: 30 },  // Alamat
                    { wch: 12 },  // Tanggal Lahir
                    { wch: 12 },  // Jenis Kelamin
                    { wch: 40 },  // File CV
                    { wch: 40 },  // File Foto
                    { wch: 40 },  // File Sertifikat K3
                    { wch: 40 },  // File SIM
                    { wch: 30 },  // Pengetahuan Fiber Optik
                    { wch: 15 },  // Pengalaman OTDR
                    { wch: 15 },  // Pengalaman Jointing
                    { wch: 20 },  // Pengalaman Panjat Tower
                    { wch: 12 },  // Sertifikat K3
                    { wch: 30 },  // Visi Kerja
                    { wch: 30 },  // Misi Kerja
                    { wch: 30 },  // Motivasi
                    { wch: 15 },  // Status Lamaran
                    { wch: 18 },  // Tanggal Daftar
                    { wch: 18 }   // Terakhir Update
                ];
                worksheet['!cols'] = columnWidths;

                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(workbook, worksheet, 'Test Data');

                // Generate filename with current date
                const now = new Date();
                const dateStr = now.getFullYear() + 
                               ('0' + (now.getMonth() + 1)).slice(-2) + 
                               ('0' + now.getDate()).slice(-2) + '_' +
                               ('0' + now.getHours()).slice(-2) + 
                               ('0' + now.getMinutes()).slice(-2);
                const filename = `Test_Export_${dateStr}.xlsx`;

                // Save file
                XLSX.writeFile(workbook, filename);

                // Show success message
                showResult(`✅ Test berhasil! File ${filename} telah diunduh. Total: ${testData.length} records.`, 'success');
                
            } catch (error) {
                console.error('Export error:', error);
                showResult('❌ Test gagal: ' + error.message, 'danger');
            } finally {
                // Restore button state
                button.innerHTML = originalHTML;
                button.disabled = false;
            }
        }
    </script>
</body>
</html>