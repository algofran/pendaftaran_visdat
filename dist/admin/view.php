<?php
session_start();
require_once '../config.php';

// Simple authentication
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

// Get application details
$stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->execute([$id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    header('Location: index.php');
    exit;
}

// Decode work experiences
$workExperiences = json_decode($app['work_experiences'] ?? '[]', true);

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Pending': return 'bg-warning text-dark';
        case 'Review': return 'bg-info text-white';
        case 'Interview': return 'bg-primary text-white';
        case 'Accepted': return 'bg-success text-white';
        case 'Rejected': return 'bg-danger text-white';
        default: return 'bg-secondary text-white';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Lamaran - <?= htmlspecialchars($app['full_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="admin-style.css?v=1.1.6" rel="stylesheet">
    <style>
        .detail-label { font-weight: 600; color: #4b5563; }
        .detail-value { color: #1f2937; }
        .section-title { 
            border-bottom: 2px solid #e5e7eb; 
            padding-bottom: 0.5rem; 
            margin-bottom: 1rem; 
            color: #1e3a8a;
            font-weight: 700;
        }
        .profile-photo {
            width: 150px;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            border: 3px solid #fff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .experience-item {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-arrow-left me-2"></i>
                Kembali ke Dashboard
            </a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row">
            <div class="col-lg-4">
                <!-- Profile Summary Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <?php if (!empty($app['photo_file'])): ?>
                            <img src="../uploads/<?= $app['photo_file'] ?>" alt="Foto" class="profile-photo mb-3">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 150px; height: 200px; border-radius: 8px;">
                                <i class="fas fa-user fa-4x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h4 class="mb-1"><?= htmlspecialchars($app['full_name']) ?></h4>
                        <p class="text-muted mb-3"><?= htmlspecialchars($app['position']) ?></p>
                        
                        <div class="d-grid gap-2">
                             <span class="badge <?= getStatusBadgeClass($app['application_status']) ?> py-2 fs-6">
                                <?= $app['application_status'] ?>
                            </span>
                            <button class="btn btn-outline-primary" onclick="updateStatus(<?= $app['id'] ?>)">
                                <i class="fas fa-edit me-1"></i> Update Status
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Contact Info Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="section-title">Informasi Kontak</h5>
                        <div class="mb-3">
                            <div class="detail-label"><i class="fas fa-envelope me-2"></i>Email</div>
                            <div class="detail-value text-break"><?= htmlspecialchars($app['email']) ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="detail-label"><i class="fas fa-phone me-2"></i>Telepon</div>
                            <div class="detail-value"><?= htmlspecialchars($app['phone']) ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="detail-label"><i class="fas fa-map-marker-alt me-2"></i>Alamat</div>
                            <div class="detail-value small"><?= nl2br(htmlspecialchars($app['address'])) ?></div>
                        </div>
                    </div>
                </div>

                <!-- Files Card -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="section-title">Dokumen Terlampir</h5>
                        <div class="list-group list-group-flush">
                            <?php if (!empty($app['cv_file'])): ?>
                                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="openFileModal('../uploads/<?= $app['cv_file'] ?>', 'CV - <?= addslashes($app['full_name']) ?>', 'pdf')">
                                    <span><i class="fas fa-file-pdf text-danger me-2"></i>CV / Resume</span>
                                    <i class="fas fa-eye text-muted"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if (!empty($app['ktp_file'])): ?>
                                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="openFileModal('../uploads/<?= $app['ktp_file'] ?>', 'KTP - <?= addslashes($app['full_name']) ?>', 'image')">
                                    <span><i class="fas fa-id-card text-primary me-2"></i>KTP</span>
                                    <i class="fas fa-eye text-muted"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if (!empty($app['ijazah_file'])): ?>
                                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="openFileModal('../uploads/<?= $app['ijazah_file'] ?>', 'Ijazah - <?= addslashes($app['full_name']) ?>', 'pdf')">
                                    <span><i class="fas fa-graduation-cap text-dark me-2"></i>Ijazah</span>
                                    <i class="fas fa-eye text-muted"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if (!empty($app['certificate_file'])): ?>
                                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="openFileModal('../uploads/<?= $app['certificate_file'] ?>', 'Sertifikat K3 - <?= addslashes($app['full_name']) ?>', 'pdf')">
                                    <span><i class="fas fa-certificate text-warning me-2"></i>Sertifikat K3</span>
                                    <i class="fas fa-eye text-muted"></i>
                                </button>
                            <?php endif; ?>
                            
                            <?php if (!empty($app['sim_file'])): ?>
                                <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="openFileModal('../uploads/<?= $app['sim_file'] ?>', 'SIM - <?= addslashes($app['full_name']) ?>', 'image')">
                                    <span><i class="fas fa-id-card text-info me-2"></i>SIM A/C</span>
                                    <i class="fas fa-eye text-muted"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Data Pribadi Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="section-title">Data Pribadi & Pendidikan</h5>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="detail-label">Tanggal Lahir</div>
                                <div class="detail-value"><?= date('d F Y', strtotime($app['birth_date'])) ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-label">Jenis Kelamin</div>
                                <div class="detail-value"><?= htmlspecialchars($app['gender']) ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-label">Lokasi Penempatan</div>
                                <div class="detail-value"><?= htmlspecialchars($app['location']) ?></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="detail-label">Pendidikan Terakhir</div>
                                <div class="detail-value"><?= htmlspecialchars($app['education']) ?></div>
                            </div>
                            <div class="col-md-4">
                                <div class="detail-label">Total Pengalaman</div>
                                <div class="detail-value"><?= $app['experience_years'] ?> Tahun</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pengalaman Kerja Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="section-title">Detail Pengalaman Kerja</h5>
                        <?php if (empty($workExperiences)): ?>
                            <p class="text-muted italic">Tidak ada detail pengalaman kerja yang dicantumkan.</p>
                        <?php else: ?>
                            <?php foreach ($workExperiences as $exp): ?>
                                <div class="experience-item">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($exp['company']) ?></h6>
                                        <span class="badge bg-light text-dark"><?= htmlspecialchars($exp['start_year']) ?> - <?= htmlspecialchars($exp['end_year']) ?></span>
                                    </div>
                                    <div class="fw-semibold mb-1"><?= htmlspecialchars($exp['position']) ?></div>
                                    <p class="small text-muted mb-0"><?= nl2br(htmlspecialchars($exp['description'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Visi Misi Section -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="section-title">Visi & Misi Kerja</h5>
                        <div class="mb-3">
                            <h6 class="fw-bold">Visi Anda dalam Bekerja:</h6>
                            <p class="border rounded p-3 bg-light"><?= nl2br(htmlspecialchars($app['work_vision'])) ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">Misi Anda dalam Bekerja:</h6>
                            <p class="border rounded p-3 bg-light"><?= nl2br(htmlspecialchars($app['work_mission'])) ?></p>
                        </div>
                        <div>
                            <h6 class="fw-bold">Apa yang Membangkitkan Motivasi Anda?</h6>
                            <p class="border rounded p-3 bg-light"><?= nl2br(htmlspecialchars($app['motivation'])) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Position-specific Questions -->
                <?php if ($app['position'] === 'Driver' || !empty($app['driver_pre_trip'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="section-title">Evaluasi Kompetensi Driver</h5>
                        <div class="mb-3">
                            <h6 class="fw-bold">1. Apa yang Anda lakukan dalam pre-trip inspection?</h6>
                            <p class="small bg-light p-2 rounded"><?= nl2br(htmlspecialchars($app['driver_pre_trip'] ?? 'N/A')) ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">2. Penanganan dalam situasi darurat (kecelakaan/kerusakan)?</h6>
                            <p class="small bg-light p-2 rounded"><?= nl2br(htmlspecialchars($app['driver_emergency'] ?? 'N/A')) ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">3. Integritas - Bagaimana jika atasan meminta melanggar aturan lalu lintas?</h6>
                            <p class="small bg-light p-2 rounded"><?= nl2br(htmlspecialchars($app['driver_integrity'] ?? 'N/A')) ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">4. Bagaimana Anda mengenali gejala kelelahan (fatigue)?</h6>
                            <p class="small bg-light p-2 rounded"><?= nl2br(htmlspecialchars($app['driver_fatigue'] ?? 'N/A')) ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">5. Kesanggupan & Komitmen</h6>
                            <p class="small bg-light p-2 rounded"><?= nl2br(htmlspecialchars($app['driver_commitment'] ?? 'N/A')) ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (strpos($app['position'], 'Teknisi') !== false || !empty($app['tech_troubleshooting'])): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="section-title">Evaluasi Kompetensi Teknisi</h5>
                        <div class="mb-3">
                            <h6 class="fw-bold">1. Urutan troubleshooting jika ada gangguan pada jaringan FO?</h6>
                            <p class="small bg-light p-2 rounded"><?= nl2br(htmlspecialchars($app['tech_troubleshooting'] ?? 'N/A')) ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">2. Bagaimana Anda mengelola inventory (alat kerja/material)?</h6>
                            <p class="small bg-light p-2 rounded"><?= nl2br(htmlspecialchars($app['tech_inventory'] ?? 'N/A')) ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">3. Bagaimana Anda memastikan keselamatan kerja di lapangan?</h6>
                            <p class="small bg-light p-2 rounded"><?= nl2br(htmlspecialchars($app['tech_safety'] ?? 'N/A')) ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">4. Cara berkomunikasi dengan pelanggan saat instalasi?</h6>
                            <p class="small bg-light p-2 rounded"><?= nl2br(htmlspecialchars($app['tech_communication'] ?? 'N/A')) ?></p>
                        </div>
                        <div class="mb-3">
                            <h6 class="fw-bold">5. Kesanggupan & Komitmen</h6>
                            <p class="small bg-light p-2 rounded"><?= nl2br(htmlspecialchars($app['tech_commitment'] ?? 'N/A')) ?></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Status Update Modal (Reuse from index) -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status Lamaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm">
                    <div class="modal-body">
                        <input type="hidden" id="applicationId" name="application_id" value="<?= $app['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select id="newStatus" name="new_status" class="form-select" required>
                                <option value="Pending" <?= $app['application_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Review" <?= $app['application_status'] === 'Review' ? 'selected' : '' ?>>Review</option>
                                <option value="Interview" <?= $app['application_status'] === 'Interview' ? 'selected' : '' ?>>Interview</option>
                                <option value="Accepted" <?= $app['application_status'] === 'Accepted' ? 'selected' : '' ?>>Diterima</option>
                                <option value="Rejected" <?= $app['application_status'] === 'Rejected' ? 'selected' : '' ?>>Ditolak</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catatan (opsional)</label>
                            <textarea name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="admin-script.js?v=1.1.5"></script>
</body>
</html>
