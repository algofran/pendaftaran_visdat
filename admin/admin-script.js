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

// Export all data to Excel function
function exportToExcel() {
    // Show loading state
    const button = event.target;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exporting...';
    button.disabled = true;

    // Fetch data from PHP endpoint
    fetch('export-data.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.error || 'Export failed');
            }

            // Create Excel workbook
            const workbook = XLSX.utils.book_new();
            
            // Convert data to worksheet
            const worksheet = XLSX.utils.json_to_sheet(data.data);
            
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
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Lamaran Kerja');

            // Generate filename with current date
            const now = new Date();
            const dateStr = now.getFullYear() + 
                           ('0' + (now.getMonth() + 1)).slice(-2) + 
                           ('0' + now.getDate()).slice(-2) + '_' +
                           ('0' + now.getHours()).slice(-2) + 
                           ('0' + now.getMinutes()).slice(-2);
            const filename = `Lamaran_Kerja_${dateStr}.xlsx`;

            // Save file
            XLSX.writeFile(workbook, filename);

            // Show success message
            if (data.isEmpty) {
                showAlert(`File template Excel telah diunduh (${filename}). Belum ada data lamaran untuk diekspor.`, 'info');
            } else {
                showAlert(`Data berhasil diekspor ke file ${filename}. Total: ${data.count} lamaran`, 'success');
            }
        })
        .catch(error => {
            console.error('Export error:', error);
            showAlert('Gagal mengekspor data: ' + error.message, 'danger');
        })
        .finally(() => {
            // Restore button state
            button.innerHTML = originalHTML;
            button.disabled = false;
        });
}

// Photo thumbnail functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips for photo thumbnails
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add click handler for photo thumbnails to open larger view
    document.querySelectorAll('.admin-photo-thumbnail').forEach(function(thumbnail) {
        thumbnail.addEventListener('click', function() {
            const imageSrc = this.src;
            const altText = this.alt;
            
            // Create modal for larger image view
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.id = 'photoModal';
            modal.innerHTML = `
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-image me-2"></i>
                                ${altText}
                            </h5>
                            <div class="d-flex gap-2">
                                <a href="${imageSrc}" target="_blank" class="btn btn-light btn-sm" title="Open in new tab">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="${imageSrc}" download class="btn btn-light btn-sm" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                        </div>
                        <div class="modal-body text-center p-4">
                            <div class="image-controls mb-3">
                                <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="rotateImage(-90)" title="Rotate Left">
                                    <i class="fas fa-undo"></i> Rotate Left
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="rotateImage(90)" title="Rotate Right">
                                    <i class="fas fa-redo"></i> Rotate Right
                                </button>
                                <button type="button" class="btn btn-success btn-sm" onclick="saveRotatedImage('${imageSrc}')" title="Save Rotation" id="saveRotationBtn" style="display: none;">
                                    <i class="fas fa-save"></i> Save Rotation
                                </button>
                            </div>
                            <div class="image-container">
                                <img id="previewImage" src="${imageSrc}" alt="${altText}" class="img-fluid rounded shadow" style="max-height: 60vh; max-width: 100%; transition: transform 0.3s ease;">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            
            // Reset rotation state when opening modal
            currentRotation = 0;
            
            bsModal.show();
            
            // Remove modal from DOM when hidden
            modal.addEventListener('hidden.bs.modal', function() {
                // Reset rotation state when closing
                currentRotation = 0;
                document.body.removeChild(modal);
            });
        });
    });
});

// File Modal functionality
function openFileModal(fileUrl, fileName, fileType) {
    // Remove existing file modal if any
    const existingModal = document.getElementById('fileModal');
    if (existingModal) {
        document.body.removeChild(existingModal);
    }

    // Determine file extension
    const extension = fileUrl.split('.').pop().toLowerCase();
    const isImage = ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension);
    const isPdf = extension === 'pdf';
    
    let modalContent = '';
    
    if (isImage) {
        modalContent = `
            <div class="modal-body text-center p-4">
                <div class="image-controls mb-3">
                    <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="rotateImage(-90)" title="Rotate Left">
                        <i class="fas fa-undo"></i> Rotate Left
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm me-2" onclick="rotateImage(90)" title="Rotate Right">
                        <i class="fas fa-redo"></i> Rotate Right
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="saveRotatedImage('${fileUrl}')" title="Save Rotation" id="saveRotationBtn" style="display: none;">
                        <i class="fas fa-save"></i> Save Rotation
                    </button>
                </div>
                <div class="image-container">
                    <img id="previewImage" src="${fileUrl}" alt="${fileName}" class="img-fluid rounded shadow" style="max-height: 60vh; max-width: 100%; transition: transform 0.3s ease;">
                </div>
            </div>`;
    } else if (isPdf) {
        modalContent = `
            <div class="modal-body p-0" style="height: 80vh;">
                <iframe src="${fileUrl}" width="100%" height="100%" style="border: none;"></iframe>
            </div>`;
    } else {
        modalContent = `
            <div class="modal-body">
                <div class="file-preview-container">
                    <i class="fas fa-file-alt file-preview-icon"></i>
                    <h5 class="file-preview-title">File Preview Not Available</h5>
                    <p class="file-preview-subtitle">This file type cannot be previewed in the browser.</p>
                    <a href="${fileUrl}" target="_blank" class="file-preview-button">
                        <i class="fas fa-external-link-alt me-2"></i>Open in New Tab
                    </a>
                </div>
            </div>`;
    }

    // Create modal
    const modal = document.createElement('div');
    modal.className = 'modal fade';
    modal.id = 'fileModal';
    modal.innerHTML = `
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-${isImage ? 'image' : isPdf ? 'file-pdf' : 'file-alt'} me-2"></i>
                        ${fileName}
                    </h5>
                    <div class="d-flex gap-2">
                        <a href="${fileUrl}" target="_blank" class="btn btn-light btn-sm" title="Open in new tab">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="${fileUrl}" download class="btn btn-light btn-sm" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                ${modalContent}
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    const bsModal = new bootstrap.Modal(modal);
    
    // Reset rotation state when opening modal
    currentRotation = 0;
    
    bsModal.show();
    
    // Remove modal from DOM when hidden
    modal.addEventListener('hidden.bs.modal', function() {
        // Reset rotation state when closing
        currentRotation = 0;
        if (document.body.contains(modal)) {
            document.body.removeChild(modal);
        }
    });
}

// Image rotation functionality
let currentRotation = 0;
let originalImageUrl = '';

function getFileNameFromUrl(url) {
    // Extract filename from URL, removing any query parameters
    const urlParts = url.split('/');
    const fileName = urlParts[urlParts.length - 1];
    return fileName.split('?')[0]; // Remove query parameters if any
}

function rotateImage(degrees) {
    const previewImage = document.getElementById('previewImage');
    const saveBtn = document.getElementById('saveRotationBtn');
    
    if (!previewImage) return;
    
    currentRotation += degrees;
    // Normalize rotation to 0-360 range
    currentRotation = ((currentRotation % 360) + 360) % 360;
    
    previewImage.style.transform = `rotate(${currentRotation}deg)`;
    
    // Show save button if rotation is not 0
    if (saveBtn) {
        saveBtn.style.display = currentRotation !== 0 ? 'inline-block' : 'none';
    }
}

async function saveRotatedImage(fileUrl) {
    // Extract filename from URL
    const fileName = getFileNameFromUrl(fileUrl);
    const saveBtn = document.getElementById('saveRotationBtn');
    const previewImage = document.getElementById('previewImage');
    
    if (!previewImage || currentRotation === 0) return;
    
    // Disable save button and show loading
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    }
    
    try {
        // Create canvas to apply rotation
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();
        
        img.crossOrigin = 'anonymous';
        
        await new Promise((resolve, reject) => {
            img.onload = resolve;
            img.onerror = reject;
            img.src = fileUrl;
        });
        
        // Calculate new dimensions based on rotation
        const angle = (currentRotation * Math.PI) / 180;
        const cos = Math.abs(Math.cos(angle));
        const sin = Math.abs(Math.sin(angle));
        
        canvas.width = img.width * cos + img.height * sin;
        canvas.height = img.width * sin + img.height * cos;
        
        // Apply rotation
        ctx.translate(canvas.width / 2, canvas.height / 2);
        ctx.rotate(angle);
        ctx.drawImage(img, -img.width / 2, -img.height / 2);
        
        // Convert to blob
        const blob = await new Promise(resolve => {
            canvas.toBlob(resolve, 'image/jpeg', 0.9);
        });
        
        // Send to server
        const formData = new FormData();
        formData.append('image', blob);
        formData.append('fileUrl', fileUrl);
        formData.append('rotation', currentRotation);
        
        const response = await fetch('rotate_image.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Reset rotation state
            currentRotation = 0;
            previewImage.style.transform = 'rotate(0deg)';
            
            // Update image source with new filename
            const newUrl = fileUrl.replace(result.oldFileName, result.newFileName);
            previewImage.src = newUrl;
            
            // Update any thumbnails on the page with the new filename
            updatePageThumbnails(result.oldFileName, result.newFileName);
            
            // Hide save button
            if (saveBtn) {
                saveBtn.style.display = 'none';
            }
            
            // Show success message with filename info
            showSuccessMessage(`✅ Image rotation saved successfully! All references updated automatically.`);
        } else {
            throw new Error(result.message || 'Failed to save rotation');
        }
        
    } catch (error) {
        console.error('Error saving rotated image:', error);
        showErrorMessage('Failed to save image rotation: ' + error.message);
    } finally {
        // Re-enable save button
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Rotation';
        }
    }
}

function showSuccessMessage(message) {
    // Create and show success toast/alert
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 3000);
}

function showErrorMessage(message) {
    // Create and show error toast/alert
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    alert.style.top = '20px';
    alert.style.right = '20px';
    alert.style.zIndex = '9999';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
}

function updatePageThumbnails(oldFileName, newFileName) {
    console.log(`Updating page references: ${oldFileName} -> ${newFileName}`);
    
    // Update all images on the page that reference the old filename
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        if (img.src.includes(oldFileName)) {
            img.src = img.src.replace(oldFileName, newFileName);
            console.log(`Updated image src: ${img.src}`);
        }
    });
    
    // Update any onclick handlers that reference the old filename
    updateOnClickHandlers(oldFileName, newFileName);
    
    // Update any links that reference the old filename
    const links = document.querySelectorAll('a[href*="' + oldFileName + '"]');
    links.forEach(link => {
        link.href = link.href.replace(oldFileName, newFileName);
        console.log(`Updated link href: ${link.href}`);
    });
    
    // Update modal content if currently open
    updateModalContent(oldFileName, newFileName);
    
    console.log('All page references updated successfully - no refresh needed!');
}

function updateOnClickHandlers(oldFileName, newFileName) {
    // Find all elements with onclick attributes that reference the old filename
    const elementsWithOnClick = document.querySelectorAll('*[onclick]');
    
    elementsWithOnClick.forEach(element => {
        const onclickStr = element.getAttribute('onclick');
        if (onclickStr && onclickStr.includes(oldFileName)) {
            const newOnClickStr = onclickStr.replace(new RegExp(escapeRegExp(oldFileName), 'g'), newFileName);
            element.setAttribute('onclick', newOnClickStr);
            console.log(`Updated onclick handler: ${newOnClickStr}`);
        }
    });
    
    // Also update any event listeners that might have been attached
    const buttons = document.querySelectorAll('button, a');
    buttons.forEach(button => {
        // Check if the button has a data attribute or other reference to the old filename
        const dataAttributes = button.attributes;
        for (let attr of dataAttributes) {
            if (attr.value && attr.value.includes(oldFileName)) {
                attr.value = attr.value.replace(oldFileName, newFileName);
                console.log(`Updated ${attr.name} attribute: ${attr.value}`);
            }
        }
    });
}

function updateModalContent(oldFileName, newFileName) {
    // Update any open modal content
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (modal.style.display !== 'none' && modal.classList.contains('show')) {
            // Update images in modal
            const modalImages = modal.querySelectorAll('img');
            modalImages.forEach(img => {
                if (img.src.includes(oldFileName)) {
                    img.src = img.src.replace(oldFileName, newFileName);
                    console.log(`Updated modal image: ${img.src}`);
                }
            });
            
            // Update modal title if it contains the filename
            const modalTitle = modal.querySelector('.modal-title');
            if (modalTitle && modalTitle.textContent.includes(oldFileName)) {
                modalTitle.textContent = modalTitle.textContent.replace(oldFileName, newFileName);
                console.log(`Updated modal title: ${modalTitle.textContent}`);
            }
            
            // Update any buttons or links in the modal
            const modalButtons = modal.querySelectorAll('button[onclick], a[href]');
            modalButtons.forEach(button => {
                if (button.getAttribute('onclick') && button.getAttribute('onclick').includes(oldFileName)) {
                    const newOnClick = button.getAttribute('onclick').replace(oldFileName, newFileName);
                    button.setAttribute('onclick', newOnClick);
                }
                if (button.href && button.href.includes(oldFileName)) {
                    button.href = button.href.replace(oldFileName, newFileName);
                }
            });
        }
    });
}

function escapeRegExp(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Function to get file type icon
function getFileTypeIcon(fileName) {
    const extension = fileName.split('.').pop().toLowerCase();
    const icons = {
        'pdf': 'fa-file-pdf',
        'jpg': 'fa-image',
        'jpeg': 'fa-image',
        'png': 'fa-image',
        'gif': 'fa-image',
        'webp': 'fa-image',
        'doc': 'fa-file-word',
        'docx': 'fa-file-word',
        'xls': 'fa-file-excel',
        'xlsx': 'fa-file-excel',
        'txt': 'fa-file-alt'
    };
    return icons[extension] || 'fa-file';
}