-- SQLite Schema for Visdat Recruitment

CREATE TABLE IF NOT EXISTS applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT NOT NULL,
    position TEXT NOT NULL,
    location TEXT,
    education TEXT NOT NULL,
    experience_years INTEGER NOT NULL,
    address TEXT NOT NULL,
    birth_date TEXT NOT NULL,
    gender TEXT CHECK(gender IN ('Laki-laki', 'Perempuan')) NOT NULL,
    
    -- Dokumen yang diupload
    cv_file TEXT,
    photo_file TEXT,
    ktp_file TEXT,
    ijazah_file TEXT,
    certificate_file TEXT,
    sim_file TEXT,
    
    -- Pertanyaan khusus fiber optik
    fiber_optic_knowledge TEXT,
    otdr_experience TEXT DEFAULT 'Tidak',
    jointing_experience TEXT DEFAULT 'Tidak',
    tower_climbing_experience TEXT DEFAULT 'Tidak',
    k3_certificate TEXT DEFAULT 'Tidak',
    
    -- Visi misi dalam bekerja
    work_vision TEXT,
    work_mission TEXT,
    motivation TEXT,
    
    -- Status aplikasi
    application_status TEXT DEFAULT 'Pending' CHECK(application_status IN ('Pending', 'Review', 'Interview', 'Accepted', 'Rejected')),
    
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TRIGGER IF NOT EXISTS update_applications_timestamp 
AFTER UPDATE ON applications
BEGIN
    UPDATE applications SET updated_at = CURRENT_TIMESTAMP WHERE id = OLD.id;
END;

CREATE TABLE IF NOT EXISTS job_positions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    position_name TEXT NOT NULL,
    description TEXT,
    requirements TEXT,
    is_active INTEGER DEFAULT 1
);

INSERT INTO job_positions (position_name, description, requirements) VALUES
('Teknisi FOT', 'Menangani gangguan layanan FOT/FOC/PS', 'Sertifikat K3, Pendidikan SMK/D3 Telekomunikasi, Pengalaman minimal 2 tahun, Bisa pemeliharaan AC dan menggunakan smartphone untuk navigasi, Menguasai Penanganan Gangguan FOT'),
('Teknisi FOC', 'Menangani gangguan layanan FOC', 'Sertifikat K3, Pendidikan SMK/D3 Telekomunikasi, Pengalaman minimal 2 tahun, Bisa menggunakan OTDR dan jointing core, Menguasai Penanganan Gangguan FOC'),
('Teknisi Fiber Optic Bersertifikat K3', 'Penarikan kabel FO dan pemasangan aksesoris di tiang/tower', 'Pengalaman menggunakan peralatan keselamatan (APD, harness), Bisa memanjat tower, Pengalaman minimal 2 tahun'),
('Teknisi Fiber Optic', 'Menangani gangguan layanan FOC', 'Sertifikat K3, Pendidikan SMK/D3 Telekomunikasi, Pengalaman minimal 2 tahun, Bisa menggunakan OTDR dan jointing core, Menguasai Penanganan Gangguan FOC'),
('Admin', 'Administrasi Serpo', 'Keterampilan MS Office, Pengetahuan jaringan fiber optik, Pendidikan minimal SMK/Sederajat');

CREATE TABLE IF NOT EXISTS admin_users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    full_name TEXT NOT NULL,
    email TEXT NOT NULL,
    role TEXT DEFAULT 'hr' CHECK(role IN ('admin', 'hr')),
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

INSERT OR IGNORE INTO admin_users (username, password, full_name, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@visdat.com', 'admin');
