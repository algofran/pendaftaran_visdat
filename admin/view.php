<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? 0;

// Get application details
$stmt = $pdo->prepare("SELECT * FROM applications WHERE id = ?");
$stmt->execute([$id]);
$application = $stmt->fetch();

if (!$application) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Lamaran - <?= htmlspecialchars($application['full_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="admin-style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-arrow-left me-2"></i>
                Kembali ke Daftar
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8">
                <!-- Personal Information -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            Informasi Pribadi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Nama Lengkap:</strong></td>
                                        <td><?= htmlspecialchars($application['full_name']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><?= htmlspecialchars($application['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Telepon:</strong></td>
                                        <td><?= htmlspecialchars($application['phone']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tanggal Lahir:</strong></td>
                                        <td><?= date('d/m/Y', strtotime($application['birth_date'])) ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Jenis Kelamin:</strong></td>
                                        <td><?= htmlspecialchars($application['gender']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Posisi:</strong></td>
                                        <td><span class="badge bg-info"><?= htmlspecialchars($application['position']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pendidikan:</strong></td>
                                        <td><?= htmlspecialchars($application['education']) ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pengalaman:</strong></td>
                                        <td><?= $application['experience_years'] ?> tahun</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <strong>Alamat:</strong>
                                <p class="mt-2"><?= nl2br(htmlspecialchars($application['address'])) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical Knowledge -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            Pengetahuan Teknis
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Pengalaman OTDR:</strong></td>
                                        <td><span class="badge bg-<?= $application['otdr_experience'] === 'Ya' ? 'success' : ($application['otdr_experience'] === 'Sedikit' ? 'warning' : 'secondary') ?>"><?= $application['otdr_experience'] ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Pengalaman Jointing:</strong></td>
                                        <td><span class="badge bg-<?= $application['jointing_experience'] === 'Ya' ? 'success' : ($application['jointing_experience'] === 'Sedikit' ? 'warning' : 'secondary') ?>"><?= $application['jointing_experience'] ?></span></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Pengalaman Tower:</strong></td>
                                        <td><span class="badge bg-<?= $application['tower_climbing_experience'] === 'Ya' ? 'success' : 'secondary' ?>"><?= $application['tower_climbing_experience'] ?></span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Sertifikat K3:</strong></td>
                                        <td><span class="badge bg-<?= $application['k3_certificate'] === 'Ya' ? 'success' : 'secondary' ?>"><?= $application['k3_certificate'] ?></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <?php if (!empty($application['fiber_optic_knowledge'])): ?>
                            <div class="row">
                                <div class="col-12">
                                    <strong>Pengetahuan Fiber Optik:</strong>
                                    <p class="mt-2"><?= nl2br(htmlspecialchars($application['fiber_optic_knowledge'])) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Vision & Mission -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-bullseye me-2"></i>
                            Visi & Misi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Visi dalam Bekerja:</strong>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($application['work_vision'])) ?></p>
                        </div>
                        <div class="mb-3">
                            <strong>Misi dalam Bekerja:</strong>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($application['work_mission'])) ?></p>
                        </div>
                        <div class="mb-3">
                            <strong>Motivasi:</strong>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($application['motivation'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Status & Actions -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-tasks me-2"></i>
                            Status & Aksi
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Status Saat Ini:</strong>
                            <br>
                            <span class="badge bg-<?= getStatusColor($application['application_status']) ?> fs-6 mt-2">
                                <?= $application['application_status'] ?>
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>Tanggal Daftar:</strong>
                            <br>
                            <?= date('d/m/Y H:i', strtotime($application['created_at'])) ?>
                        </div>
                        <div class="mb-3">
                            <strong>Terakhir Update:</strong>
                            <br>
                            <?= date('d/m/Y H:i', strtotime($application['updated_at'])) ?>
                        </div>
                        <div class="d-grid gap-2">
                            <button class="btn btn-primary" onclick="updateStatus(<?= $application['id'] ?>)">
                                <i class="fas fa-edit me-1"></i>
                                Update Status
                            </button>
                            <button class="btn btn-info" onclick="printApplication(<?= $application['id'] ?>)">
                                <i class="fas fa-print me-1"></i>
                                Print
                            </button>
                            <button class="btn btn-success" onclick="exportToPDF(<?= $application['id'] ?>)">
                                <i class="fas fa-file-pdf me-1"></i>
                                Export PDF
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Files -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-paperclip me-2"></i>
                            Dokumen
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($application['cv_file']): ?>
                            <div class="mb-2">
                                <a href="../uploads/<?= $application['cv_file'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-file-alt me-1"></i>
                                    CV/Resume
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($application['photo_file']): ?>
                            <div class="mb-2">
                                <a href="../uploads/<?= $application['photo_file'] ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-image me-1"></i>
                                    Foto 3x4
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($application['ktp_file']): ?>
                            <div class="mb-2">
                                <a href="../uploads/<?= $application['ktp_file'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-id-card-alt me-1"></i>
                                    KTP
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($application['ijazah_file']): ?>
                            <div class="mb-2">
                                <a href="../uploads/<?= $application['ijazah_file'] ?>" target="_blank" class="btn btn-sm btn-outline-dark">
                                    <i class="fas fa-graduation-cap me-1"></i>
                                    Ijazah
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($application['certificate_file']): ?>
                            <div class="mb-2">
                                <a href="../uploads/<?= $application['certificate_file'] ?>" target="_blank" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-certificate me-1"></i>
                                    Sertifikat K3
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($application['sim_file']): ?>
                            <div class="mb-2">
                                <a href="../uploads/<?= $application['sim_file'] ?>" target="_blank" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-id-card me-1"></i>
                                    SIM A/C
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Photo Preview -->
                <?php if ($application['photo_file']): ?>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Foto Pelamar</h6>
                        </div>
                        <div class="card-body text-center">
                            <img src="../uploads/<?= $application['photo_file'] ?>" alt="Foto Pelamar" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status Lamaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="statusForm">
                    <div class="modal-body">
                        <input type="hidden" id="applicationId" name="application_id" value="<?= $application['id'] ?>">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select id="newStatus" name="new_status" class="form-select" required>
                                <option value="Pending" <?= $application['application_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="Review" <?= $application['application_status'] === 'Review' ? 'selected' : '' ?>>Review</option>
                                <option value="Interview" <?= $application['application_status'] === 'Interview' ? 'selected' : '' ?>>Interview</option>
                                <option value="Accepted" <?= $application['application_status'] === 'Accepted' ? 'selected' : '' ?>>Diterima</option>
                                <option value="Rejected" <?= $application['application_status'] === 'Rejected' ? 'selected' : '' ?>>Ditolak</option>
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
    <script src="admin-script.js"></script>
</body>
</html>

<?php
function getStatusColor($status) {
    switch ($status) {
        case 'Pending': return 'warning';
        case 'Review': return 'info';
        case 'Interview': return 'primary';
        case 'Accepted': return 'success';
        case 'Rejected': return 'danger';
        default: return 'secondary';
    }
}
?>