// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize status form
    const statusForm = document.getElementById('statusForm');
    if (statusForm) {
        statusForm.addEventListener('submit', handleStatusUpdate);
    }
});

// Update application status
function updateStatus(applicationId) {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    document.getElementById('applicationId').value = applicationId;
    modal.show();
}

// Handle status update form submission
function handleStatusUpdate(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    fetch('update-status.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Status berhasil diupdate!', 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Gagal mengupdate status', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan saat mengupdate status', 'danger');
    });
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('statusModal'));
    modal.hide();
}

// Delete application
function deleteApplication(applicationId) {
    // Show confirmation dialog with more details
    const confirmed = confirm('⚠️ PERHATIAN!\n\nApakah Anda yakin ingin menghapus lamaran ini?\n\nTindakan ini akan:\n• Menghapus data lamaran dari database\n• Menghapus semua file yang diupload (CV, Foto, Sertifikat, SIM)\n• TIDAK DAPAT DIBATALKAN\n\nKlik OK untuk melanjutkan atau Cancel untuk membatalkan.');
    
    if (confirmed) {
        // Show loading state
        showAlert('Menghapus lamaran...', 'info');
        
        fetch('delete-application.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ application_id: applicationId })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showAlert('✅ Lamaran berhasil dihapus!', 'success');
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showAlert(`❌ Gagal menghapus lamaran: ${data.message}`, 'danger');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showAlert(`❌ Terjadi kesalahan: ${error.message}`, 'danger');
        });
    }
}

// Print application
function printApplication(applicationId) {
    window.open(`print.php?id=${applicationId}`, '_blank', 'width=800,height=600');
}

// Export to PDF
function exportToPDF(applicationId) {
    window.location.href = `export.php?id=${applicationId}&format=pdf`;
}

// Show alert message
function showAlert(message, type) {
    // Remove existing alerts
    document.querySelectorAll('.alert').forEach(el => el.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 3000);
}

// Enhanced table functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add table sorting (simple implementation)
    const tableHeaders = document.querySelectorAll('th[data-sort]');
    tableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            sortTable(this.dataset.sort);
        });
    });
    
    // Add row hover effects
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});

// Simple table sorting function
function sortTable(column) {
    const table = document.querySelector('table tbody');
    const rows = Array.from(table.querySelectorAll('tr'));
    const columnIndex = getColumnIndex(column);
    
    rows.sort((a, b) => {
        const aValue = a.cells[columnIndex].textContent.trim();
        const bValue = b.cells[columnIndex].textContent.trim();
        
        // Check if values are numbers
        if (!isNaN(aValue) && !isNaN(bValue)) {
            return parseFloat(aValue) - parseFloat(bValue);
        }
        
        // Check if values are dates
        if (isDate(aValue) && isDate(bValue)) {
            return new Date(aValue) - new Date(bValue);
        }
        
        // String comparison
        return aValue.localeCompare(bValue);
    });
    
    // Clear table and re-append sorted rows
    table.innerHTML = '';
    rows.forEach(row => table.appendChild(row));
}

// Get column index by name
function getColumnIndex(columnName) {
    const headers = document.querySelectorAll('th');
    for (let i = 0; i < headers.length; i++) {
        if (headers[i].dataset.sort === columnName) {
            return i;
        }
    }
    return 0;
}

// Check if string is a date
function isDate(str) {
    return !isNaN(Date.parse(str));
}

// Bulk actions functionality
function selectAll() {
    const checkboxes = document.querySelectorAll('input[name="selected_applications[]"]');
    const selectAllCheckbox = document.getElementById('selectAll');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkActions();
}

function updateBulkActions() {
    const selectedCheckboxes = document.querySelectorAll('input[name="selected_applications[]"]:checked');
    const bulkActionsDiv = document.getElementById('bulkActions');
    
    if (selectedCheckboxes.length > 0) {
        bulkActionsDiv.style.display = 'block';
        document.getElementById('selectedCount').textContent = selectedCheckboxes.length;
    } else {
        bulkActionsDiv.style.display = 'none';
    }
}

// Bulk status update
function bulkUpdateStatus() {
    const selectedCheckboxes = document.querySelectorAll('input[name="selected_applications[]"]:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        showAlert('Pilih minimal satu lamaran', 'warning');
        return;
    }
    
    const newStatus = prompt('Masukkan status baru (Pending/Review/Interview/Accepted/Rejected):');
    if (!newStatus) return;
    
    const validStatuses = ['Pending', 'Review', 'Interview', 'Accepted', 'Rejected'];
    if (!validStatuses.includes(newStatus)) {
        showAlert('Status tidak valid', 'danger');
        return;
    }
    
    fetch('bulk-update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            application_ids: selectedIds,
            new_status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(`${data.updated_count} lamaran berhasil diupdate!`, 'success');
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Gagal mengupdate lamaran', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Terjadi kesalahan saat mengupdate lamaran', 'danger');
    });
}

// Export selected applications
function exportSelected() {
    const selectedCheckboxes = document.querySelectorAll('input[name="selected_applications[]"]:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        showAlert('Pilih minimal satu lamaran', 'warning');
        return;
    }
    
    const format = prompt('Format export (excel/pdf):');
    if (!format || !['excel', 'pdf'].includes(format.toLowerCase())) {
        showAlert('Format tidak valid', 'danger');
        return;
    }
    
    window.location.href = `export.php?ids=${selectedIds.join(',')}&format=${format.toLowerCase()}`;
}