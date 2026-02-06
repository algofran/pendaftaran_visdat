<?php
session_start();
require_once '../config.php';

// Simple authentication (you should implement proper authentication)
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

// Get filter parameters
$statusFilter = $_GET['status'] ?? 'all';
$positionFilter = $_GET['position'] ?? 'all';
$locationFilter = $_GET['location'] ?? 'all';
$yearFilter = $_GET['year'] ?? date('Y');
$searchTerm = $_GET['search'] ?? '';

// Build query
$whereConditions = [];
$params = [];

if ($statusFilter !== 'all') {
    $whereConditions[] = "application_status = ?";
    $params[] = $statusFilter;
}

if ($positionFilter !== 'all') {
    $whereConditions[] = "position = ?";
    $params[] = $positionFilter;
}

if ($locationFilter !== 'all') {
    $whereConditions[] = "location = ?";
    $params[] = $locationFilter;
}

if (!empty($searchTerm)) {
    $whereConditions[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchParam = "%$searchTerm%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Get applications with registration number based on creation date order
// First, get all applications to calculate registration numbers based on creation order
$baseQuery = "SELECT * FROM applications ORDER BY created_at ASC";
$baseStmt = $pdo->query($baseQuery);
$allApplications = $baseStmt->fetchAll(PDO::FETCH_ASSOC);

// Add registration numbers
foreach ($allApplications as $index => $app) {
    $allApplications[$index]['registration_number'] = $index + 1;
}

// Now filter the applications if needed
$applications = array_filter($allApplications, function($app) use ($statusFilter, $positionFilter, $locationFilter, $yearFilter, $searchTerm) {
    // Apply year filter
    if ($yearFilter !== 'all') {
        $appYear = date('Y', strtotime($app['created_at']));
        if ($appYear !== $yearFilter) {
            return false;
        }
    }

    // Apply status filter
    if ($statusFilter !== 'all' && $app['application_status'] !== $statusFilter) {
        return false;
    }
    
    // Apply position filter
    if ($positionFilter !== 'all' && $app['position'] !== $positionFilter) {
        return false;
    }

    // Apply location filter
    if ($locationFilter !== 'all' && ($app['location'] ?? '') !== $locationFilter) {
        return false;
    }
    
    // Apply search filter
    if (!empty($searchTerm)) {
        $searchLower = strtolower($searchTerm);
        $fullNameMatch = strpos(strtolower($app['full_name']), $searchLower) !== false;
        $emailMatch = strpos(strtolower($app['email']), $searchLower) !== false;
        $phoneMatch = strpos(strtolower($app['phone']), $searchLower) !== false;
        
        if (!($fullNameMatch || $emailMatch || $phoneMatch)) {
            return false;
        }
    }
    
    return true;
});

// Sort by created_at DESC for display
usort($applications, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Get statistics
$statsQuery = "SELECT 
    application_status,
    COUNT(*) as count
    FROM applications 
    GROUP BY application_status";
$statsStmt = $pdo->query($statsQuery);
$stats = $statsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get positions - using fixed list to ensure all are visible
$availablePositions = [
    "Teknisi Fiber Optic Bersertifikat K3",
    "Teknisi Fiber Optic",
    "Admin"
];

// Get locations - using fixed list to ensure all are visible
$availableLocations = [
    "Makassar", "Maros", "Gowa", "Takalar", "Pangkep - Barru", "Pare Pare - Sidrap",
    "Pinrang - Enrekang", "Palopo - Luwu", "Luwu Utara - Luwu Timur", "Bulukumba",
    "Bone", "Kendari - Konawe - Konsel", "Kolaka", "Bau Bau - Buton", "Morowali",
    "Palu - Donggala - Sigi", "Toli Toli - Parigi Moutong", "Mamuju - Mamasa",
    "Polman - Majene", "Sinjai", "Manado", "Minahasa - Tomohon",
    "Bitung - Minahasa Utara", "Bolsel - Kotamobagu", "Gorontalo - Bone Bolango"
];

// Get available years for filter
$yearsQuery = "SELECT DISTINCT YEAR(created_at) as year FROM applications ORDER BY year DESC";
try {
    $yearsStmt = $pdo->query($yearsQuery);
    $availableYears = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    // Fallback for empty table or other issues
    $availableYears = [date('Y')];
}
// Ensure current year is always in the list
if (!in_array(date('Y'), $availableYears)) {
    array_unshift($availableYears, date('Y'));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - PT. Visdat Teknik Utama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="admin-style.css?v=1.1.1" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-users-cog me-2"></i>
                Admin Panel - PT. Visdat
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="test-delete.php" title="Test Delete Function">
                    <i class="fas fa-bug me-1"></i>
                    Test Delete
                </a>
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= array_sum($stats) ?></h4>
                                <p class="mb-0">Total Lamaran</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-file-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= $stats['Pending'] ?? 0 ?></h4>
                                <p class="mb-0">Pending</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= $stats['Review'] ?? 0 ?></h4>
                                <p class="mb-0">Review</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-search fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4><?= $stats['Accepted'] ?? 0 ?></h4>
                                <p class="mb-0">Diterima</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>Semua Status</option>
                            <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="Review" <?= $statusFilter === 'Review' ? 'selected' : '' ?>>Review</option>
                            <option value="Interview" <?= $statusFilter === 'Interview' ? 'selected' : '' ?>>Interview</option>
                            <option value="Accepted" <?= $statusFilter === 'Accepted' ? 'selected' : '' ?>>Diterima</option>
                            <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Ditolak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Posisi</label>
                        <select name="position" class="form-select">
                            <option value="all" <?= $positionFilter === 'all' ? 'selected' : '' ?>>Semua Posisi</option>
                            <?php foreach ($availablePositions as $position): ?>
                                <option value="<?= htmlspecialchars($position) ?>" <?= $positionFilter === $position ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($position) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Lokasi</label>
                        <select name="location" class="form-select">
                            <option value="all" <?= $locationFilter === 'all' ? 'selected' : '' ?>>Semua Lokasi</option>
                            <?php foreach ($availableLocations as $loc): ?>
                                <option value="<?= htmlspecialchars($loc) ?>" <?= $locationFilter === $loc ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loc) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Tahun</label>
                        <select name="year" class="form-select">
                            <option value="all" <?= $yearFilter === 'all' ? 'selected' : '' ?>>Semua</option>
                            <?php foreach ($availableYears as $year): ?>
                                <option value="<?= $year ?>" <?= (string)$yearFilter === (string)$year ? 'selected' : '' ?>>
                                    <?= $year ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Pencarian</label>
                        <input type="text" name="search" class="form-control" placeholder="Nama, email, atau telp" value="<?= htmlspecialchars($searchTerm) ?>">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-success" onclick="exportToExcel()">
                            <i class="fas fa-file-excel me-1"></i>
                            Export to Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar Lamaran</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Foto</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Posisi</th>
                                <th>Pendidikan</th>
                                <th>Pengalaman</th>
                                <th>File</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($applications)): ?>
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data lamaran</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($applications as $app): ?>
                                    <tr>
                                        <td><?= $app['registration_number'] ?></td>
                                        <td class="text-center">
                                            <?php if (!empty($app['photo_file'])): ?>
                                                <img src="../uploads/<?= $app['photo_file'] ?>" 
                                                     alt="Foto <?= htmlspecialchars($app['full_name']) ?>" 
                                                     class="admin-photo-thumbnail"
                                                     data-bs-toggle="tooltip" 
                                                     data-bs-placement="top" 
                                                     title="<?= htmlspecialchars($app['full_name']) ?>">
                                            <?php else: ?>
                                                <div class="admin-photo-placeholder">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($app['full_name']) ?></td>
                                        <td><?= htmlspecialchars($app['email']) ?></td>
                                        <td><?= htmlspecialchars($app['position']) ?></td>
                                        <td><?= htmlspecialchars($app['education']) ?></td>
                                        <td><?= $app['experience_years'] ?> tahun</td>
                                        <td>
                                            <div class="file-links">
                                                <?php if (!empty($app['cv_file'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-primary mb-1" title="CV/Resume" onclick="openFileModal('../uploads/<?= $app['cv_file'] ?>', 'CV - <?= htmlspecialchars($app['full_name']) ?>', 'pdf')">
                                                        <i class="fas fa-file-pdf"></i> CV
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (!empty($app['photo_file'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-success mb-1" title="Foto" onclick="openFileModal('../uploads/<?= $app['photo_file'] ?>', 'Foto - <?= htmlspecialchars($app['full_name']) ?>', 'image')">
                                                        <i class="fas fa-image"></i> Foto
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (!empty($app['ktp_file'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary mb-1" title="KTP" onclick="openFileModal('../uploads/<?= $app['ktp_file'] ?>', 'KTP - <?= htmlspecialchars($app['full_name']) ?>', 'image')">
                                                        <i class="fas fa-id-card-alt"></i> KTP
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (!empty($app['ijazah_file'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-dark mb-1" title="Ijazah" onclick="openFileModal('../uploads/<?= $app['ijazah_file'] ?>', 'Ijazah - <?= htmlspecialchars($app['full_name']) ?>', 'pdf')">
                                                        <i class="fas fa-graduation-cap"></i> Ijazah
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (!empty($app['certificate_file'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-warning mb-1" title="Sertifikat K3" onclick="openFileModal('../uploads/<?= $app['certificate_file'] ?>', 'Sertifikat K3 - <?= htmlspecialchars($app['full_name']) ?>', 'pdf')">
                                                        <i class="fas fa-certificate"></i> K3
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (!empty($app['sim_file'])): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-info mb-1" title="SIM" onclick="openFileModal('../uploads/<?= $app['sim_file'] ?>', 'SIM - <?= htmlspecialchars($app['full_name']) ?>', 'image')">
                                                        <i class="fas fa-id-card"></i> SIM
                                                    </button>
                                                <?php endif; ?>
                                                <?php if (empty($app['cv_file']) && empty($app['photo_file']) && empty($app['ktp_file']) && empty($app['ijazah_file']) && empty($app['certificate_file']) && empty($app['sim_file'])): ?>
                                                    <small class="text-muted">Tidak ada file</small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= getStatusColor($app['application_status']) ?>">
                                                <?= $app['application_status'] ?>
                                            </span>
                                        </td>
                                        <td><?= date('d/m/Y H:i', strtotime($app['created_at'])) ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="view.php?id=<?= $app['id'] ?>" class="btn btn-info" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button type="button" class="btn btn-warning" onclick="updateStatus(<?= $app['id'] ?>)" title="Update Status">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger" onclick="deleteApplication(<?= $app['id'] ?>)" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
                        <input type="hidden" id="applicationId" name="application_id">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select id="newStatus" name="new_status" class="form-select" required>
                                <option value="Pending">Pending</option>
                                <option value="Review">Review</option>
                                <option value="Interview">Interview</option>
                                <option value="Accepted">Diterima</option>
                                <option value="Rejected">Ditolak</option>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="admin-script.js?v=1.1.1"></script>
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