<?php
session_start();
require_once '../config.php';

// Simple test page for delete functionality
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Delete Function - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4>Test Delete Function</h4>
                    </div>
                    <div class="card-body">
                        <p>Masukkan ID aplikasi yang ingin dihapus:</p>
                        <div class="input-group mb-3">
                            <input type="number" id="applicationId" class="form-control" placeholder="Masukkan ID aplikasi">
                            <button class="btn btn-danger" onclick="testDelete()">Test Delete</button>
                        </div>
                        <div id="result" class="mt-3"></div>
                        
                        <hr>
                        <h5>Daftar Aplikasi Terbaru:</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Posisi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $stmt = $pdo->query("SELECT id, full_name, email, position, application_status FROM applications ORDER BY created_at DESC LIMIT 10");
                                        while ($row = $stmt->fetch()) {
                                            echo "<tr>";
                                            echo "<td>{$row['id']}</td>";
                                            echo "<td>{$row['full_name']}</td>";
                                            echo "<td>{$row['email']}</td>";
                                            echo "<td>{$row['position']}</td>";
                                            echo "<td><span class='badge bg-secondary'>{$row['application_status']}</span></td>";
                                            echo "</tr>";
                                        }
                                    } catch (Exception $e) {
                                        echo "<tr><td colspan='5' class='text-danger'>Error: " . $e->getMessage() . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function testDelete() {
            const applicationId = document.getElementById('applicationId').value;
            const resultDiv = document.getElementById('result');
            
            if (!applicationId) {
                resultDiv.innerHTML = '<div class="alert alert-warning">Masukkan ID aplikasi terlebih dahulu!</div>';
                return;
            }
            
            const confirmed = confirm(`Apakah Anda yakin ingin menghapus aplikasi dengan ID ${applicationId}?`);
            if (!confirmed) return;
            
            resultDiv.innerHTML = '<div class="alert alert-info">Menghapus aplikasi...</div>';
            
            fetch('delete-application.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ application_id: applicationId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h5>✅ Berhasil!</h5>
                            <p>${data.message}</p>
                            <small>ID: ${data.details.application_id}<br>
                            Files deleted: ${data.details.files_deleted.join(', ') || 'None'}<br>
                            Deleted at: ${data.details.deleted_at}</small>
                        </div>
                    `;
                    // Reload page after 2 seconds
                    setTimeout(() => location.reload(), 2000);
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h5>❌ Gagal!</h5>
                            <p>${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5>❌ Error!</h5>
                        <p>${error.message}</p>
                    </div>
                `;
            });
        }
    </script>
</body>
</html>
