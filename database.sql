-- Database untuk Website Pendaftaran PT. Visdat Teknik Utama
CREATE DATABASE IF NOT EXISTS visdat_recruitment;
USE visdat_recruitment;

-- Tabel untuk menyimpan data pelamar
CREATE TABLE applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    position VARCHAR(50) NOT NULL,
    education VARCHAR(50) NOT NULL,
    experience_years INT NOT NULL,
    address TEXT NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('Laki-laki', 'Perempuan') NOT NULL,
    
    -- Dokumen yang diupload
    cv_file VARCHAR(255),
    photo_file VARCHAR(255),
    certificate_file VARCHAR(255),
    sim_file VARCHAR(255),
    
    -- Pertanyaan khusus fiber optik
    fiber_optic_knowledge TEXT,
    otdr_experience ENUM('Ya', 'Tidak', 'Sedikit') DEFAULT 'Tidak',
    jointing_experience ENUM('Ya', 'Tidak', 'Sedikit') DEFAULT 'Tidak',
    tower_climbing_experience ENUM('Ya', 'Tidak') DEFAULT 'Tidak',
    k3_certificate ENUM('Ya', 'Tidak') DEFAULT 'Tidak',
    
    -- Visi misi dalam bekerja
    work_vision TEXT,
    work_mission TEXT,
    motivation TEXT,
    
    -- Status aplikasi
    application_status ENUM('Pending', 'Review', 'Interview', 'Accepted', 'Rejected') DEFAULT 'Pending',
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk menyimpan posisi pekerjaan
CREATE TABLE job_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_name VARCHAR(100) NOT NULL,
    description TEXT,
    requirements TEXT,
    is_active BOOLEAN DEFAULT TRUE
);

-- Insert data posisi pekerjaan
INSERT INTO job_positions (position_name, description, requirements) VALUES
('Teknisi FOT', 'Menangani gangguan layanan FOT/FOC/PS', 'Sertifikat K3, Pendidikan SMK/D3 Telekomunikasi, Pengalaman minimal 2 tahun, Bisa pemeliharaan AC dan menggunakan smartphone untuk navigasi, Menguasai Penanganan Gangguan FOT'),
('Teknisi FOC', 'Menangani gangguan layanan FOC', 'Sertifikat K3, Pendidikan SMK/D3 Telekomunikasi, Pengalaman minimal 2 tahun, Bisa menggunakan OTDR dan jointing core, Menguasai Penanganan Gangguan FOC'),
('Teknisi Jointer', 'Penarikan kabel FO dan pemasangan aksesoris di tiang/tower', 'Pengalaman menggunakan peralatan keselamatan (APD, harness), Bisa memanjat tower, Pengalaman minimal 2 tahun'),
('Driver', 'Mengemudikan kendaraan untuk tim', 'SIM A & C yang berlaku, Pendidikan minimal SMK/Sederajat, Tidak sedang menjalani proses hukum'),
('Admin Zona', 'Administrasi Serpo', 'Keterampilan MS Office, Pengetahuan jaringan fiber optik, Pendidikan minimal SMK/Sederajat');

-- Tabel untuk menyimpan admin users
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'hr') DEFAULT 'hr',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO admin_users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@visdat.com', 'admin');