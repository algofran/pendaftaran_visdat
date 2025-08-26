// JavaScript for PT. Visdat Teknik Utama Registration Website

// Global variables to store compressed files
let compressedFiles = new Map();
let tempFileStorage = new Map(); // Temporary storage for file validation

// Auto-fill functionality with Faker.js
function autoFillForm() {
    // Check if faker is available
    if (typeof faker === 'undefined') {
        console.error('Faker.js is not loaded');
        alert('Faker.js library is not available. Please check your internet connection.');
        return;
    }

    // Define position options
    const positions = ["Teknisi FOT", "Teknisi FOC", "Teknisi Jointer", "Driver", "Admin Zona"];
    const educations = ["SMA/SMK", "D3", "S1", "S2"];
    const genders = ["Laki-laki", "Perempuan"];
    const yesNoOptions = ["Ya", "Tidak"];
    const experienceOptions = ["Tidak", "Sedikit", "Ya"];

    // Generate a birth date between 18-60 years old
    const birthDate = faker.date.birthdate({ min: 18, max: 60, mode: 'age' });
    const formattedBirthDate = birthDate.toISOString().split('T')[0];

    // Fill text inputs
    document.getElementById('full_name').value = faker.person.fullName();
    document.getElementById('email').value = faker.internet.email();
    document.getElementById('phone').value = faker.phone.number('08#########');
    document.getElementById('birth_date').value = formattedBirthDate;
    document.getElementById('experience_years').value = faker.number.int({ min: 0, max: 20 });

    // Fill textareas
    document.getElementById('address').value = faker.location.streetAddress() + ', ' + faker.location.city() + ', ' + faker.location.state();
    document.getElementById('fiber_optic_knowledge').value = faker.lorem.paragraphs(2, '\n\n');
    document.getElementById('work_vision').value = faker.lorem.paragraph();
    document.getElementById('work_mission').value = faker.lorem.paragraph();
    document.getElementById('motivation').value = faker.lorem.paragraphs(2, '\n\n');

    // Fill select dropdowns
    document.getElementById('gender').value = faker.helpers.arrayElement(genders);
    document.getElementById('position').value = faker.helpers.arrayElement(positions);
    document.getElementById('education').value = faker.helpers.arrayElement(educations);
    document.getElementById('otdr_experience').value = faker.helpers.arrayElement(experienceOptions);
    document.getElementById('jointing_experience').value = faker.helpers.arrayElement(experienceOptions);
    document.getElementById('tower_climbing_experience').value = faker.helpers.arrayElement(yesNoOptions);
    document.getElementById('k3_certificate').value = faker.helpers.arrayElement(yesNoOptions);

    // Show success message
    console.log('Form auto-filled with fake data');
    
    // Optional: Show a brief notification
    showAutoFillNotification();
}

// Show notification when auto-fill is triggered
function showAutoFillNotification() {
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        z-index: 9999;
        font-weight: bold;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: opacity 0.3s ease;
    `;
    notification.textContent = 'Form auto-filled with fake data! 🎉';
    
    document.body.appendChild(notification);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Hotkey handler for Ctrl+Alt+I
document.addEventListener('keydown', function(event) {
    // Check for Ctrl+Alt+I combination
    if (event.ctrlKey && event.key.toLowerCase() === 'i') {
        event.preventDefault(); // Prevent any default behavior
        console.log('Auto-fill hotkey triggered: Ctrl+Alt+I');
        autoFillForm();
    }
});

// Log when the hotkey handler is loaded
console.log('Auto-fill hotkey handler loaded. Press Ctrl+Alt+I to auto-fill the form.');

// Logo handling
document.addEventListener('DOMContentLoaded', function() {
    // Handle logo loading
    const logoImg = document.querySelector('.logo-img');
    const logoFallback = document.querySelector('.logo-fallback');
    
    if (logoImg) {
        logoImg.addEventListener('load', function() {
            this.style.opacity = '1';
            if (logoFallback) {
                logoFallback.style.display = 'none';
            }
        });
        
        logoImg.addEventListener('error', function() {
            this.style.display = 'none';
            if (logoFallback) {
                logoFallback.style.display = 'flex';
                logoFallback.style.animation = 'fadeInLogo 0.5s ease forwards';
            }
        });
    }
});

// File Upload with Compression Configuration
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing file upload with Compressor.js');
    

    
    // Initialize file upload handlers
    initializeFileUploads();
    
    // Form validation and submission
    const form = document.getElementById('registrationForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }

    // Dynamic form behavior
    setTimeout(() => {
        setupDynamicForm();
        setupUploadFieldValidation();
    }, 100);
});



// Initialize file upload functionality with compression
function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('.file-input');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            handleFileSelection(e.target);
        });
        
        // Add drag and drop functionality
        input.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.borderColor = 'var(--primary-color)';
            this.style.backgroundColor = 'rgba(13, 110, 253, 0.05)';
        });
        
        input.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.borderColor = '#dee2e6';
            this.style.backgroundColor = '#f8f9fa';
        });
        
        input.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            this.style.borderColor = '#dee2e6';
            this.style.backgroundColor = '#f8f9fa';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                // Create a mock change event
                this.files = files;
                handleFileSelection(this);
            }
        });
    });
}

// Handle file selection and processing
function handleFileSelection(input) {
    const file = input.files[0];
    if (!file) return;
    
    const container = input.closest('.upload-container');
    const preview = container.querySelector('.file-preview');
    const previewContent = container.querySelector('.preview-content');
    
    // Validate file first
    const validation = validateFile(file, input.name);
    if (!validation.valid) {
        showMessage(validation.message, 'error');
        input.value = '';
        cleanupTempFile(input.name);
        return;
    }
    
    // Store original file temporarily
    tempFileStorage.set(input.name, file);
    
    // Show preview container
    preview.style.display = 'block';
    preview.classList.remove('success');
    
    // Check if it's an image that needs compression
    const isImage = input.classList.contains('image-input') && file.type.startsWith('image/');
    
    if (isImage) {
        compressImageSafely(file, input.name, previewContent, preview);
    } else {
        // For non-image files, validate and store directly
        processNonImageFile(file, input.name, previewContent, preview);
    }
    
    // Add remove button functionality
    const removeBtn = container.querySelector('.remove-file');
    removeBtn.onclick = function() {
        removeFile(input, container);
    };
}

// Safely compress image with better error handling
function compressImageSafely(file, inputName, previewContent, preview) {
    // Show compression status
    updateFilePreview(previewContent, file, 'Mengompres gambar...', true);
    
    // Add progress bar
    const progressDiv = document.createElement('div');
    progressDiv.className = 'compression-progress';
    progressDiv.innerHTML = '<div class="compression-progress-bar"></div>';
    previewContent.appendChild(progressDiv);
    
    // Animate progress bar
    const progressBar = progressDiv.querySelector('.compression-progress-bar');
    let progress = 0;
    const progressInterval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 85) progress = 85;
        progressBar.style.width = progress + '%';
    }, 150);
    
    // Validate image before compression
    if (!file.type.startsWith('image/')) {
        clearInterval(progressInterval);
        progressDiv.remove();
        showMessage('File bukan gambar yang valid', 'error');
        cleanupTempFile(inputName);
        return;
    }
    
    try {
        new Compressor(file, {
            quality: 0.7, // More aggressive compression
            maxWidth: 1600, // Reduced max size
            maxHeight: 1600,
            convertSize: 500000, // Convert to JPEG if larger than 500KB
            mimeType: 'image/jpeg', // Force JPEG for better compression
            checkOrientation: false, // Disable orientation fix for performance
            success(result) {
                clearInterval(progressInterval);
                progressBar.style.width = '100%';
                
                // Final validation of compressed file
                if (result.size > 3 * 1024 * 1024) { // 3MB limit
                    progressDiv.remove();
                    showMessage(`File terkompresi masih terlalu besar: ${formatFileSize(result.size)}`, 'error');
                    cleanupTempFile(inputName);
                    return;
                }
                
                // Remove progress bar after animation
                setTimeout(() => {
                    progressDiv.remove();
                }, 500);
                
                // Store compressed file safely
                compressedFiles.set(inputName, result);
                
                // Show success preview
                const compressionRatio = ((file.size - result.size) / file.size * 100).toFixed(1);
                const statusText = `Dikompres ${compressionRatio}% (${formatFileSize(result.size)})`;
                updateFilePreview(previewContent, result, statusText, false);
                preview.classList.add('success');
                
                console.log('Compression successful:', {
                    original: formatFileSize(file.size),
                    compressed: formatFileSize(result.size),
                    ratio: compressionRatio + '%'
                });
                
                // Clear temp storage
                tempFileStorage.delete(inputName);
            },
            error(err) {
                clearInterval(progressInterval);
                progressDiv.remove();
                
                console.error('Compression failed:', err);
                
                // Try fallback with original file if it's small enough
                if (file.size <= 2 * 1024 * 1024) { // 2MB
                    console.log('Using original file as fallback');
                    compressedFiles.set(inputName, file);
                    updateFilePreview(previewContent, file, 'File asli (kompresi gagal)', false);
                    preview.classList.add('success');
                    tempFileStorage.delete(inputName);
                } else {
                    showMessage(`Gagal mengompres gambar: ${err.message}. File terlalu besar.`, 'error');
                    cleanupTempFile(inputName);
                }
            }
        });
    } catch (error) {
        clearInterval(progressInterval);
        progressDiv.remove();
        console.error('Compressor initialization failed:', error);
        showMessage('Gagal memulai kompresi gambar', 'error');
        cleanupTempFile(inputName);
    }
}

// Process non-image files
function processNonImageFile(file, inputName, previewContent, preview) {
    // Validate file size for non-images
    if (file.size > 5 * 1024 * 1024) { // 5MB limit for documents
        showMessage(`File terlalu besar: ${formatFileSize(file.size)}. Maksimal 5MB untuk dokumen.`, 'error');
        cleanupTempFile(inputName);
        return;
    }
    
    // Store non-image file directly
    compressedFiles.set(inputName, file);
    updateFilePreview(previewContent, file, 'Siap diupload', false);
    preview.classList.add('success');
    
    // Clear temp storage
    tempFileStorage.delete(inputName);
    
    console.log('Non-image file processed:', inputName, formatFileSize(file.size));
}

// Show file preview for non-image files
function showFilePreview(file, previewContent, preview, inputName) {
    compressedFiles.set(inputName, file);
    updateFilePreview(previewContent, file, 'Siap diupload', false);
    preview.classList.add('success');
}

// Update file preview content
function updateFilePreview(previewContent, file, status, isCompressing) {
    const extension = file.name.split('.').pop().toLowerCase();
    const iconClass = getFileIconClass(extension);
    
    previewContent.innerHTML = `
        <div class="file-icon ${iconClass}">
            ${getFileIcon(extension)}
        </div>
        <div class="file-info">
            <div class="file-name">${file.name}</div>
            <div class="file-size">${formatFileSize(file.size)}</div>
            <div class="file-status ${isCompressing ? 'compressing' : ''}">${status}</div>
        </div>
    `;
    
    // Add image preview for image files
    if (file.type.startsWith('image/')) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.onload = () => URL.revokeObjectURL(img.src);
        previewContent.insertBefore(img, previewContent.firstChild);
    }
}

// Clean up temporary file storage
function cleanupTempFile(inputName) {
    tempFileStorage.delete(inputName);
    compressedFiles.delete(inputName);
    console.log('Cleaned up temp files for:', inputName);
}

// Remove file from input and preview
function removeFile(input, container) {
    input.value = '';
    const preview = container.querySelector('.file-preview');
    preview.style.display = 'none';
    preview.classList.remove('success');
    
    // Remove from both storage maps
    compressedFiles.delete(input.name);
    tempFileStorage.delete(input.name);
    
    console.log('File removed:', input.name);
}

// Validate file before processing
function validateFile(file, inputName) {
    // Check file size
    if (file.size > 5 * 1024 * 1024) { // 5MB
        return { valid: false, message: 'File terlalu besar (maksimal 5MB)' };
    }
    
    // Check file type based on input name
    const allowedTypes = getAcceptedExtensions(inputName);
    const extension = file.name.split('.').pop().toLowerCase();
    
    if (!allowedTypes.includes(extension)) {
        return { 
            valid: false, 
            message: `Format file tidak didukung. Gunakan: ${allowedTypes.join(', ').toUpperCase()}` 
        };
    }
    
    return { valid: true };
}

// Get accepted file extensions for specific input
function getAcceptedExtensions(inputName) {
    switch(inputName) {
        case 'cv_file':
            return ['pdf', 'doc', 'docx'];
        case 'photo_file':
            return ['jpg', 'jpeg', 'png'];
        case 'certificate_file':
        case 'sim_file':
            return ['pdf', 'jpg', 'jpeg', 'png'];
        default:
            return ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    }
}

// Get file icon based on extension
function getFileIcon(extension) {
    switch(extension) {
        case 'pdf':
            return '<i class="fas fa-file-pdf"></i>';
        case 'doc':
        case 'docx':
            return '<i class="fas fa-file-word"></i>';
        case 'jpg':
        case 'jpeg':
        case 'png':
            return '<i class="fas fa-file-image"></i>';
        default:
            return '<i class="fas fa-file"></i>';
    }
}

// Get file icon CSS class
function getFileIconClass(extension) {
    switch(extension) {
        case 'pdf':
            return 'pdf';
        case 'doc':
        case 'docx':
            return 'doc';
        case 'jpg':
        case 'jpeg':
        case 'png':
            return 'image';
        default:
            return '';
    }
}

// Format file size for display
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Handle form submission
function handleFormSubmit(e) {
    e.preventDefault();
    
    // Show loading
    showLoading();
    
    // Validate form
    if (!validateForm()) {
        hideLoading();
        return false;
    }
    
    // Create FormData with explicit handling
    const formData = new FormData();
    
    // Add all form fields manually to avoid issues
    const formElements = e.target.elements;
    for (let element of formElements) {
        if (element.name && element.type !== 'file' && element.type !== 'submit') {
            formData.append(element.name, element.value);
            console.log('Adding field:', element.name, element.value);
        }
    }
    
    // Add compressed files separately
    compressedFiles.forEach((file, inputName) => {
        // Check file size before adding
        if (file.size > 5 * 1024 * 1024) { // 5MB
            hideLoading();
            showMessage(`File ${inputName} terlalu besar: ${formatFileSize(file.size)}. Maksimal 5MB.`, 'error');
            return false;
        }
        formData.append(inputName, file, file.name);
        console.log('Adding compressed file:', inputName, file.name, formatFileSize(file.size));
    });
    
    // Final cleanup of temp storage before submit
    console.log('Cleaning up temporary storage before submit...');
    tempFileStorage.clear();
    
    // Submit form
    console.log('Submitting form to process.php...');
    console.log('Total files to upload:', compressedFiles.size);
    console.log('FormData contents:');
    for (let [key, value] of formData.entries()) {
        if (value instanceof File) {
            console.log(key + ':', value.name, formatFileSize(value.size));
        } else {
            console.log(key + ':', value);
        }
    }
    
    // Add timeout and better error handling
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000); // 30 second timeout
    
    fetch('./process.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        signal: controller.signal,
        redirect: 'follow',
        cache: 'no-cache'
    })
    .then(response => {
        clearTimeout(timeoutId); // Clear timeout on successful response
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                console.error('Non-JSON response:', text);
                throw new Error(`Server returned non-JSON response: ${text.substring(0, 200)}...`);
            });
        }
        
        return response.json();
    })
    .then(data => {
        hideLoading();
        console.log('Response data:', data);
        
        if (data && data.success) {
            // Show success modal
            showSuccessModal(data);
            
            // Reset form
            e.target.reset();
            
            // Reset file previews
            document.querySelectorAll('.file-preview').forEach(preview => {
                preview.style.display = 'none';
                preview.classList.remove('success');
            });
            
            // Clear all file storage
            compressedFiles.clear();
            tempFileStorage.clear();
            
            console.log('All file storage cleared after successful submission');
        } else {
            // Show error modal
            showErrorModal((data && data.message) || 'Terjadi kesalahan saat mengirim lamaran. Silakan coba lagi.');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId); // Clear timeout on error
        hideLoading();
        console.error('Fetch error details:', error);
        console.error('Error name:', error.name);
        console.error('Error message:', error.message);
        
        // Clean up all file storage on error
        console.log('Cleaning up file storage due to error...');
        compressedFiles.clear();
        tempFileStorage.clear();
        
        // More specific error messages
        let errorMessage = 'Terjadi kesalahan saat mengirim lamaran. ';
        
        if (error.name === 'AbortError') {
            errorMessage += 'Request timeout - server terlalu lama merespons. Coba kurangi ukuran file.';
        } else if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
            errorMessage += 'Tidak dapat terhubung ke server. Pastikan server PHP berjalan.';
        } else if (error.message.includes('HTTP error')) {
            errorMessage += `Server error (${error.message}). Periksa log server.`;
        } else if (error.message.includes('non-JSON')) {
            errorMessage += 'Server mengembalikan response yang tidak valid.';
        } else {
            errorMessage += `Detail: ${error.message}`;
        }
        
        showErrorModal(errorMessage);
    });
}

// Form validation
function validateForm() {
    let isValid = true;
    const requiredFields = [
        'full_name', 'email', 'phone', 'birth_date', 'gender', 'position', 
        'education', 'experience_years', 'address', 'work_vision', 'work_mission', 'motivation'
    ];
    
    // Clear previous error messages
    document.querySelectorAll('.error-message').forEach(el => el.remove());
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Validate required fields
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field && !field.value.trim()) {
            showFieldError(field, 'Field ini wajib diisi');
            isValid = false;
        }
    });
    
    // Validate email
    const email = document.getElementById('email');
    if (email && email.value && !isValidEmail(email.value)) {
        showFieldError(email, 'Format email tidak valid');
        isValid = false;
    }
    
    // Validate phone
    const phone = document.getElementById('phone');
    if (phone && phone.value && !isValidPhone(phone.value)) {
        showFieldError(phone, 'Format nomor telepon tidak valid');
        isValid = false;
    }
    
    // Validate required files
    if (!compressedFiles.has('cv_file')) {
        showMessage('CV/Resume wajib diupload', 'error');
        isValid = false;
    }
    
    if (!compressedFiles.has('photo_file')) {
        showMessage('Foto 3x4 wajib diupload', 'error');
        isValid = false;
    }
    
    // Validate position-specific required files
    const position = document.getElementById('position').value;
    
    if (position === 'Driver' && !compressedFiles.has('sim_file')) {
        showMessage('SIM A/C wajib untuk posisi Driver', 'error');
        isValid = false;
    }
    
    const technicalPositions = ['Teknisi FOT', 'Teknisi FOC', 'Teknisi Jointer'];
    if (technicalPositions.includes(position) && !compressedFiles.has('certificate_file')) {
        showMessage('Sertifikat K3 wajib untuk posisi teknis', 'error');
        isValid = false;
    }
    
    return isValid;
}

// Show field error
function showFieldError(field, message) {
    field.classList.add('is-invalid');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message text-danger small mt-1';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

// Email validation
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Phone validation
function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[0-9\-\(\)\s]{8,20}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

// Show loading
function showLoading() {
    let loading = document.querySelector('.loading');
    if (!loading) {
        loading = document.createElement('div');
        loading.className = 'loading';
        loading.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(loading);
    }
    loading.style.display = 'flex';
}

// Hide loading
function hideLoading() {
    const loading = document.querySelector('.loading');
    if (loading) {
        loading.style.display = 'none';
    }
}

// Show message
function showMessage(message, type) {
    // Remove existing messages
    document.querySelectorAll('.alert').forEach(el => el.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the form
    const form = document.getElementById('registrationForm');
    form.parentNode.insertBefore(alertDiv, form);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Setup dynamic form behavior
function setupDynamicForm() {
    const positionSelect = document.getElementById('position');
    
    if (!positionSelect) {
        console.warn('Position select element not found');
        return;
    }
    
    // Find file input containers
    const simFileContainer = document.querySelector('[name="sim_file"]')?.closest('.col-md-6');
    const certificateFileContainer = document.querySelector('[name="certificate_file"]')?.closest('.col-md-6');
    
    // Show/hide SIM upload based on position
    positionSelect.addEventListener('change', function() {
        if (simFileContainer) {
            const label = simFileContainer.querySelector('label');
            if (this.value === 'Driver') {
                simFileContainer.style.display = 'block';
                if (label) label.innerHTML = 'SIM A/C (wajib untuk Driver) *';
            } else {
                simFileContainer.style.display = 'block';
                if (label) label.innerHTML = 'SIM A/C (jika ada)';
            }
        }
        
        // Show certificate field for technical positions
        if (certificateFileContainer) {
            const label = certificateFileContainer.querySelector('label');
            const technicalPositions = ['Teknisi FOT', 'Teknisi FOC', 'Teknisi Jointer'];
            if (technicalPositions.includes(this.value)) {
                if (label) label.innerHTML = 'Sertifikat K3 (wajib untuk posisi teknis) *';
            } else {
                if (label) label.innerHTML = 'Sertifikat K3 (jika ada)';
            }
        }
    });
    
    // Auto-format phone number
    const phoneInput = document.getElementById('phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.startsWith('0')) {
                value = '62' + value.substring(1);
            }
            if (value.startsWith('8')) {
                value = '62' + value;
            }
            
            // Format: +62 xxx xxxx xxxx
            if (value.startsWith('62')) {
                value = value.replace(/^62/, '+62 ');
                value = value.replace(/(\+62 \d{3})(\d{4})(\d{4})/, '$1 $2 $3');
            }
            
            this.value = value;
        });
    }
    
    // Character counter for textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        const maxLength = textarea.getAttribute('maxlength') || 1000;
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        counter.textContent = `0/${maxLength}`;
        textarea.parentNode.appendChild(counter);
        
        textarea.addEventListener('input', function() {
            const length = this.value.length;
            counter.textContent = `${length}/${maxLength}`;
            
            if (length > maxLength * 0.9) {
                counter.className = 'text-warning float-end';
            } else {
                counter.className = 'text-muted float-end';
            }
        });
    });
}

// Smooth scrolling for form sections
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({ behavior: 'smooth' });
    }
}

// Print function for admin
function printApplication(applicationId) {
    window.open(`print.php?id=${applicationId}`, '_blank');
}

// Export to PDF function for admin
function exportToPDF(applicationId) {
    window.location.href = `export.php?id=${applicationId}&format=pdf`;
}

// Show success modal
function showSuccessModal(data) {
    // Generate reference number if not provided
    const referenceNumber = data.reference_number || generateReferenceNumber();
    
    // Set reference number in modal
    document.getElementById('referenceNumber').textContent = referenceNumber;
    
    // Show modal
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    successModal.show();
}

// Show error modal
function showErrorModal(message) {
    // Set error message in modal
    document.getElementById('errorMessage').textContent = message;
    
    // Show modal
    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
    errorModal.show();
}

// Generate reference number
function generateReferenceNumber() {
    const timestamp = Date.now();
    const random = Math.floor(Math.random() * 1000);
    return `VIS-${timestamp}-${random.toString().padStart(3, '0')}`;
}

// Setup upload field validation - hide upload fields if required inputs not filled
function setupUploadFieldValidation() {
    const requiredFields = [
        'full_name', 'email', 'phone', 'birth_date', 'gender', 'position', 
        'education', 'experience_years', 'address', 'work_vision', 'work_mission', 'motivation'
    ];
    
    const uploadWarning = document.getElementById('upload-warning');
    const uploadFieldsRequired = document.getElementById('upload-fields-required');
    const uploadFieldsOptional = document.getElementById('upload-fields-optional');
    
    if (!uploadWarning || !uploadFieldsRequired || !uploadFieldsOptional) {
        console.warn('Upload validation elements not found');
        return;
    }
    
    // Function to check if all required fields are filled
    function checkRequiredFields() {
        let allFilled = true;
        
        for (const fieldName of requiredFields) {
            const field = document.getElementById(fieldName);
            if (field && !field.value.trim()) {
                allFilled = false;
                break;
            }
        }
        
        return allFilled;
    }
    
    // Function to update upload fields visibility
    function updateUploadFieldsVisibility() {
        const allRequiredFilled = checkRequiredFields();
        
        if (allRequiredFilled) {
            // Show upload fields, hide warning
            uploadWarning.style.display = 'none';
            uploadFieldsRequired.style.display = 'block';
            uploadFieldsOptional.style.display = 'block';
        } else {
            // Hide upload fields, show warning
            uploadWarning.style.display = 'block';
            uploadFieldsRequired.style.display = 'none';
            uploadFieldsOptional.style.display = 'none';
            
            // Clear any uploaded files when hiding fields
            clearUploadedFiles();
        }
    }
    
    // Function to clear uploaded files
    function clearUploadedFiles() {
        const fileInputs = document.querySelectorAll('#upload-fields-required .file-input, #upload-fields-optional .file-input');
        fileInputs.forEach(input => {
            const container = input.closest('.upload-container');
            if (container) {
                removeFile(input, container);
            }
        });
    }
    
    // Add event listeners to all required fields
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
            // Add input event listener for real-time validation
            field.addEventListener('input', updateUploadFieldsVisibility);
            field.addEventListener('change', updateUploadFieldsVisibility);
        }
    });
    
    // Initial check on page load
    updateUploadFieldsVisibility();
}