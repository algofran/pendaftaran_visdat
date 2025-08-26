// JavaScript for PT. Visdat Teknik Utama Registration Website

import { faker } from 'https://esm.sh/@faker-js/faker';

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

// Hotkey handler for Ctrl+I
document.addEventListener('keydown', function(event) {
    // Check for Ctrl+I combination
    if (event.ctrlKey && !event.altKey && event.key.toLowerCase() === 'i') {
        event.preventDefault(); // Prevent any default behavior
        console.log('Auto-fill hotkey triggered: Ctrl+I');
        autoFillForm();
    }
});

// Log when the hotkey handler is loaded
console.log('Auto-fill hotkey handler loaded. Press Ctrl+I to auto-fill the form.');

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
        showUploadFieldError(input, validation.message);
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
        compressImageSafely(file, input.name, previewContent, preview, input);
    } else {
        // For non-image files, validate and store directly
        processNonImageFile(file, input.name, previewContent, preview, input);
    }
    
    // Add remove button functionality
    const removeBtn = container.querySelector('.remove-file');
    removeBtn.onclick = function() {
        removeFile(input, container);
    };
}

// Safely compress image with better error handling
function compressImageSafely(file, inputName, previewContent, preview, input) {
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
        showUploadFieldError(input, 'File bukan gambar yang valid');
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
                    showUploadFieldError(input, `File terkompresi masih terlalu besar: ${formatFileSize(result.size)}`);
                    cleanupTempFile(inputName);
                    return;
                }
                
                // Remove progress bar after animation
                setTimeout(() => {
                    progressDiv.remove();
                }, 500);
                
                // Store compressed file safely
                compressedFiles.set(inputName, result);
                
                // Clear any upload field errors for this input
                clearUploadFieldError(input);
                
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
                    clearUploadFieldError(input);
                    updateFilePreview(previewContent, file, 'File asli (kompresi gagal)', false);
                    preview.classList.add('success');
                    tempFileStorage.delete(inputName);
                } else {
                    showUploadFieldError(input, `Gagal mengompres gambar: ${err.message}. File terlalu besar.`);
                    cleanupTempFile(inputName);
                }
            }
        });
    } catch (error) {
        clearInterval(progressInterval);
        progressDiv.remove();
        console.error('Compressor initialization failed:', error);
        showUploadFieldError(input, 'Gagal memulai kompresi gambar');
        cleanupTempFile(inputName);
    }
}

// Process non-image files
function processNonImageFile(file, inputName, previewContent, preview, input) {
    // Validate file size for non-images
    if (file.size > 5 * 1024 * 1024) { // 5MB limit for documents
        showUploadFieldError(input, `File terlalu besar: ${formatFileSize(file.size)}. Maksimal 5MB untuk dokumen.`);
        cleanupTempFile(inputName);
        return;
    }
    
    // Store non-image file directly
    compressedFiles.set(inputName, file);
    clearUploadFieldError(input);
    updateFilePreview(previewContent, file, 'Siap diupload', false);
    preview.classList.add('success');
    
    // Clear temp storage
    tempFileStorage.delete(inputName);
    
    console.log('Non-image file processed:', inputName, formatFileSize(file.size));
}

// Show file preview for non-image files
function showFilePreview(file, previewContent, preview, inputName, input) {
    compressedFiles.set(inputName, file);
    if (input) {
        clearUploadFieldError(input);
    }
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
    
    // Show validation error if this is a required field
    showUploadValidationAfterRemoval(input);
    
    console.log('File removed:', input.name);
}

// Clear upload field errors
function clearUploadFieldError(fileInput) {
    // Remove invalid styling from the file input
    fileInput.classList.remove('is-invalid');
    
    // Find and remove error message
    const uploadContainer = fileInput.closest('.upload-container');
    if (uploadContainer) {
        const errorMessage = uploadContainer.querySelector('.upload-error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
        uploadContainer.classList.remove('upload-error');
    }
}

// Show validation feedback after file removal
function showUploadValidationAfterRemoval(fileInput) {
    const inputName = fileInput.name;
    const position = document.getElementById('position')?.value || '';
    
    // Check if this is a required field and show error
    if (inputName === 'cv_file') {
        showUploadFieldError(fileInput, 'CV/Resume wajib diupload');
    } else if (inputName === 'photo_file') {
        showUploadFieldError(fileInput, 'Foto 3x4 wajib diupload');
    } else if (inputName === 'sim_file' && position === 'Driver') {
        showUploadFieldError(fileInput, 'SIM A/C wajib untuk posisi Driver');
    } else if (inputName === 'certificate_file') {
        const technicalPositions = ['Teknisi FOT', 'Teknisi FOC', 'Teknisi Jointer'];
        if (technicalPositions.includes(position)) {
            showUploadFieldError(fileInput, 'Sertifikat K3 wajib untuk posisi teknis');
        }
    }
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
            // Show error message at bottom of form
            showMessage((data && data.message) || 'Terjadi kesalahan saat mengirim lamaran. Silakan coba lagi.', 'error');
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
        
        showMessage(errorMessage, 'error');
    });
}

// Form validation
function validateForm() {
    let isValid = true;
    let firstInvalidField = null;
    const requiredFields = [
        'full_name', 'email', 'phone', 'birth_date', 'gender', 'position', 
        'education', 'experience_years', 'address', 'work_vision', 'work_mission', 'motivation'
    ];
    
    // Clear previous error messages
    document.querySelectorAll('.error-message').forEach(el => el.remove());
    document.querySelectorAll('.upload-error-message').forEach(el => el.remove());
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    document.querySelectorAll('.upload-error').forEach(el => el.classList.remove('upload-error'));
    
    // Validate required fields
    requiredFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field && !field.value.trim()) {
            showFieldError(field, 'Field ini wajib diisi');
            if (!firstInvalidField) {
                firstInvalidField = field;
            }
            isValid = false;
        }
    });
    
    // Validate email
    const email = document.getElementById('email');
    if (email && email.value && !isValidEmail(email.value)) {
        showFieldError(email, 'Format email tidak valid');
        if (!firstInvalidField) {
            firstInvalidField = email;
        }
        isValid = false;
    }
    
    // Validate phone
    const phone = document.getElementById('phone');
    if (phone && phone.value && !isValidPhone(phone.value)) {
        showFieldError(phone, 'Format nomor telepon tidak valid');
        if (!firstInvalidField) {
            firstInvalidField = phone;
        }
        isValid = false;
    }
    
    // Validate required files
    if (!compressedFiles.has('cv_file')) {
        const cvFileInput = document.querySelector('[name="cv_file"]');
        if (cvFileInput) {
            showUploadFieldError(cvFileInput, 'CV/Resume wajib diupload');
            if (!firstInvalidField) {
                firstInvalidField = cvFileInput;
            }
        }
        isValid = false;
    }
    
    if (!compressedFiles.has('photo_file')) {
        const photoFileInput = document.querySelector('[name="photo_file"]');
        if (photoFileInput) {
            showUploadFieldError(photoFileInput, 'Foto 3x4 wajib diupload');
            if (!firstInvalidField) {
                firstInvalidField = photoFileInput;
            }
        }
        isValid = false;
    }
    
    // Validate position-specific required files
    const position = document.getElementById('position').value;
    
    if (position === 'Driver' && !compressedFiles.has('sim_file')) {
        const simFileInput = document.querySelector('[name="sim_file"]');
        if (simFileInput) {
            showUploadFieldError(simFileInput, 'SIM A/C wajib untuk posisi Driver');
            if (!firstInvalidField) {
                firstInvalidField = simFileInput;
            }
        }
        isValid = false;
    }
    
    const technicalPositions = ['Teknisi FOT', 'Teknisi FOC', 'Teknisi Jointer'];
    if (technicalPositions.includes(position) && !compressedFiles.has('certificate_file')) {
        const certFileInput = document.querySelector('[name="certificate_file"]');
        if (certFileInput) {
            showUploadFieldError(certFileInput, 'Sertifikat K3 wajib untuk posisi teknis');
            if (!firstInvalidField) {
                firstInvalidField = certFileInput;
            }
        }
        isValid = false;
    }
    
    // Scroll to first invalid field if validation failed
    if (!isValid && firstInvalidField) {
        scrollToField(firstInvalidField);
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

// Show upload field error
function showUploadFieldError(fileInput, message) {
    // Add invalid styling to the file input
    fileInput.classList.add('is-invalid');
    
    // Find the upload container
    const uploadContainer = fileInput.closest('.upload-container');
    if (!uploadContainer) {
        console.warn('Upload container not found for file input');
        return;
    }
    
    // Remove any existing error messages for this field
    const existingError = uploadContainer.querySelector('.upload-error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Create error message element
    const errorDiv = document.createElement('div');
    errorDiv.className = 'upload-error-message text-danger small mt-2';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle me-1"></i>
        <strong>${message}</strong>
    `;
    
    // Insert error message after the file input but before guidelines
    const guidelines = uploadContainer.querySelector('.file-guidelines');
    if (guidelines) {
        uploadContainer.insertBefore(errorDiv, guidelines);
    } else {
        uploadContainer.appendChild(errorDiv);
    }
    
    // Add visual highlighting to the upload container
    uploadContainer.classList.add('upload-error');
}

// Scroll to field with smooth animation and visual focus
function scrollToField(field) {
    if (!field) return;
    
    // Calculate position with some offset for better visibility
    const fieldRect = field.getBoundingClientRect();
    const absoluteTop = window.pageYOffset + fieldRect.top;
    const offset = 100; // Offset from top to ensure field is well visible
    
    // Smooth scroll to the field
    window.scrollTo({
        top: absoluteTop - offset,
        behavior: 'smooth'
    });
    
    // Add visual focus to the field after a short delay to allow scroll to complete
    setTimeout(() => {
        // Temporarily add a highlighting class
        field.classList.add('field-highlight');
        
        // Focus the field if it's focusable
        if (field.focus && (field.type !== 'file')) {
            field.focus();
        }
        
        // Remove highlight after animation
        setTimeout(() => {
            field.classList.remove('field-highlight');
        }, 2000);
    }, 500);
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
    // Get the error container at the bottom of the form
    const errorContainer = document.getElementById('formErrorContainer');
    
    if (!errorContainer) {
        console.warn('Form error container not found');
        return;
    }
    
    // Remove existing messages from the container
    errorContainer.innerHTML = '';
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show mb-3`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add the alert to the error container and show it
    errorContainer.appendChild(alertDiv);
    errorContainer.style.display = 'block';
    
    // Scroll to the error container to make it visible
    errorContainer.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
    });
    
    // Auto remove after 8 seconds for better visibility at bottom
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
            // Hide container if no more alerts
            if (errorContainer.children.length === 0) {
                errorContainer.style.display = 'none';
            }
        }
    }, 8000);
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
            const simFileInput = simFileContainer.querySelector('[name="sim_file"]');
            
            if (this.value === 'Driver') {
                simFileContainer.style.display = 'block';
                if (label) label.innerHTML = 'SIM A/C (wajib untuk Driver) *';
                // Show validation error if no file uploaded for Driver
                if (simFileInput && !compressedFiles.has('sim_file')) {
                    showUploadFieldError(simFileInput, 'SIM A/C wajib untuk posisi Driver');
                }
            } else {
                simFileContainer.style.display = 'block';
                if (label) label.innerHTML = 'SIM A/C (jika ada)';
                // Clear validation error if not Driver
                if (simFileInput) {
                    clearUploadFieldError(simFileInput);
                }
            }
        }
        
        // Show certificate field for technical positions
        if (certificateFileContainer) {
            const label = certificateFileContainer.querySelector('label');
            const certFileInput = certificateFileContainer.querySelector('[name="certificate_file"]');
            const technicalPositions = ['Teknisi FOT', 'Teknisi FOC', 'Teknisi Jointer'];
            
            if (technicalPositions.includes(this.value)) {
                if (label) label.innerHTML = 'Sertifikat K3 (wajib untuk posisi teknis) *';
                // Show validation error if no file uploaded for technical position
                if (certFileInput && !compressedFiles.has('certificate_file')) {
                    showUploadFieldError(certFileInput, 'Sertifikat K3 wajib untuk posisi teknis');
                }
            } else {
                if (label) label.innerHTML = 'Sertifikat K3 (jika ada)';
                // Clear validation error if not technical position
                if (certFileInput) {
                    clearUploadFieldError(certFileInput);
                }
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
    const uploadFieldsIdentity = document.getElementById('upload-fields-identity');
    const uploadFieldsOptional = document.getElementById('upload-fields-optional');
    
    if (!uploadWarning || !uploadFieldsRequired || !uploadFieldsIdentity || !uploadFieldsOptional) {
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
            uploadFieldsIdentity.style.display = 'block';
            uploadFieldsOptional.style.display = 'block';
        } else {
            // Hide upload fields, show warning
            uploadWarning.style.display = 'block';
            uploadFieldsRequired.style.display = 'none';
            uploadFieldsIdentity.style.display = 'none';
            uploadFieldsOptional.style.display = 'none';
            
            // Clear any uploaded files when hiding fields
            clearUploadedFiles();
        }
    }
    
    // Function to clear uploaded files
    function clearUploadedFiles() {
        const fileInputs = document.querySelectorAll('#upload-fields-required .file-input, #upload-fields-identity .file-input, #upload-fields-optional .file-input');
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

// Faker.js Demo Functions for Test Data Generation
// These functions demonstrate proper usage of faker.js in the browser
// Access faker via the global 'faker' object after CDN is loaded

function generateTestData() {
    // Check if faker is available
    if (typeof faker === 'undefined') {
        console.error('Faker.js is not loaded. Make sure the CDN script is included.');
        return null;
    }
    
    // Generate sample person data using faker.js v8+ syntax
    const testPerson = {
        id: faker.string.uuid(),
        name: faker.person.fullName(),
        email: faker.internet.email(),
        phone: faker.phone.number(),
        address: faker.location.streetAddress(),
        city: faker.location.city(),
        company: faker.company.name(),
        jobTitle: faker.person.jobTitle(),
        birthDate: faker.date.birthdate({ min: 18, max: 65, mode: 'age' }),
        avatar: faker.image.avatar()
    };
    
    console.log('Generated test data:', testPerson);
    return testPerson;
}

function fillFormWithTestData() {
    // Check if faker is available
    if (typeof faker === 'undefined') {
        console.error('Faker.js is not loaded. Make sure the CDN script is included.');
        return;
    }
    
    // Fill form fields with faker data
    const form = document.getElementById('registrationForm');
    if (!form) {
        console.log('Registration form not found');
        return;
    }
    
    // Fill basic fields
    const nameField = form.querySelector('input[name="nama"]');
    if (nameField) nameField.value = faker.person.fullName();
    
    const emailField = form.querySelector('input[name="email"]');
    if (emailField) emailField.value = faker.internet.email();
    
    const phoneField = form.querySelector('input[name="telepon"]');
    if (phoneField) phoneField.value = faker.phone.number('08##########');
    
    const addressField = form.querySelector('textarea[name="alamat"]');
    if (addressField) addressField.value = faker.location.streetAddress() + ', ' + faker.location.city();
    
    console.log('Form filled with test data using faker.js');
}

// Global function to test faker availability
function testFaker() {
    if (typeof faker === 'undefined') {
        console.error('❌ Faker.js is not available. Check if CDN is loaded.');
        return false;
    }
    
    console.log('✅ Faker.js is loaded and working!');
    console.log('Faker version:', faker.version || 'Version info not available');
    
    // Test basic faker functions
    console.log('Sample data:');
    console.log('- Name:', faker.person.fullName());
    console.log('- Email:', faker.internet.email());
    console.log('- UUID:', faker.string.uuid());
    console.log('- Company:', faker.company.name());
    
    return true;
}

// Auto-test faker when page loads (after a delay to ensure CDN is loaded)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        testFaker();
    }, 1000); // Wait 1 second for CDN to load
    
    // Initialize modal functionality
    initializeModalSystem();
});

// Modal system for registration type selection
function initializeModalSystem() {
    // Show registration type modal on page load
    const registrationTypeModal = new bootstrap.Modal(document.getElementById('registrationTypeModal'));
    const emailCheckModal = new bootstrap.Modal(document.getElementById('emailCheckModal'));
    
    // Show the initial modal after a short delay
    setTimeout(() => {
        registrationTypeModal.show();
    }, 500);
    
    // Handle new registrant button
    document.getElementById('newRegistrantBtn').addEventListener('click', function() {
        registrationTypeModal.hide();
        // Clear form and show empty form
        clearForm();
        showFormForNewUser();
    });
    
    // Handle existing registrant button
    document.getElementById('existingRegistrantBtn').addEventListener('click', function() {
        registrationTypeModal.hide();
        emailCheckModal.show();
    });
    
    // Handle email check modal close buttons
    document.getElementById('emailCheckCloseBtn').addEventListener('click', function() {
        emailCheckModal.hide();
        registrationTypeModal.show();
    });
    
    document.getElementById('emailCheckCancelBtn').addEventListener('click', function() {
        emailCheckModal.hide();
        registrationTypeModal.show();
    });
    
    // Handle email check
    document.getElementById('checkEmailBtn').addEventListener('click', function() {
        checkExistingEmail();
    });
    
    // Handle email check form submission
    document.getElementById('emailCheckForm').addEventListener('submit', function(e) {
        e.preventDefault();
        checkExistingEmail();
    });
}

// Check if email exists in database
async function checkExistingEmail() {
    const emailInput = document.getElementById('checkEmail');
    const email = emailInput.value.trim();
    const errorDiv = document.getElementById('emailCheckError');
    const loadingDiv = document.getElementById('emailCheckLoading');
    const checkBtn = document.getElementById('checkEmailBtn');
    
    // Validate email
    if (!email) {
        showEmailCheckError('Email tidak boleh kosong');
        return;
    }
    
    if (!isValidEmail(email)) {
        showEmailCheckError('Format email tidak valid');
        return;
    }
    
    // Show loading state
    loadingDiv.style.display = 'block';
    errorDiv.style.display = 'none';
    checkBtn.disabled = true;
    
    try {
        const response = await fetch('check_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (result.found) {
                // Email found, fill form with existing data
                const emailCheckModal = bootstrap.Modal.getInstance(document.getElementById('emailCheckModal'));
                emailCheckModal.hide();
                
                // Show success notification
                showNotification('Email ditemukan! Mengisi form dengan data yang tersimpan...', 'success');
                
                // Fill form with existing data
                fillFormWithExistingData(result.data);
                showFormForExistingUser();
            } else {
                // Email not found
                showEmailCheckError('Email tidak ditemukan dalam database. Silakan daftar sebagai pendaftar baru.');
            }
        } else {
            showEmailCheckError(result.message || 'Terjadi kesalahan saat memeriksa email');
        }
    } catch (error) {
        console.error('Error checking email:', error);
        showEmailCheckError('Terjadi kesalahan koneksi. Silakan coba lagi.');
    } finally {
        loadingDiv.style.display = 'none';
        checkBtn.disabled = false;
    }
}

// Show email check error
function showEmailCheckError(message) {
    const errorDiv = document.getElementById('emailCheckError');
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

// Clear the registration form
function clearForm() {
    const form = document.getElementById('registrationForm');
    if (form) {
        form.reset();
        
        // Clear any file uploads
        compressedFiles.clear();
        tempFileStorage.clear();
        
        // Hide file previews
        const filePreviews = form.querySelectorAll('.file-preview');
        filePreviews.forEach(preview => {
            preview.style.display = 'none';
        });
        
        // Clear any error messages
        const errorContainers = form.querySelectorAll('.form-error-container, .alert-danger');
        errorContainers.forEach(container => {
            container.style.display = 'none';
        });
    }
}

// Show form for new user
function showFormForNewUser() {
    // Enable all form fields
    enableFormFields();
    showNotification('Silakan isi formulir pendaftaran baru', 'info');
}

// Show form for existing user
function showFormForExistingUser() {
    // Enable form fields but make email readonly
    enableFormFields();
    const emailField = document.getElementById('email');
    if (emailField) {
        emailField.readOnly = true;
        emailField.style.backgroundColor = '#f8f9fa';
    }
    showNotification('Form telah diisi dengan data yang tersimpan. Anda dapat mengubah data sesuai kebutuhan.', 'success');
}

// Enable all form fields
function enableFormFields() {
    const form = document.getElementById('registrationForm');
    if (form) {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.disabled = false;
        });
    }
    
    // Show upload fields
    showUploadFields();
}

// Show upload fields
function showUploadFields() {
    const uploadWarning = document.getElementById('upload-warning');
    const uploadFieldsRequired = document.getElementById('upload-fields-required');
    const uploadFieldsIdentity = document.getElementById('upload-fields-identity');
    const uploadFieldsOptional = document.getElementById('upload-fields-optional');
    
    if (uploadWarning) uploadWarning.style.display = 'none';
    if (uploadFieldsRequired) uploadFieldsRequired.style.display = 'block';
    if (uploadFieldsIdentity) uploadFieldsIdentity.style.display = 'block';
    if (uploadFieldsOptional) uploadFieldsOptional.style.display = 'block';
}

// Fill form with existing data
function fillFormWithExistingData(data) {
    // Fill text inputs
    if (data.full_name) document.getElementById('full_name').value = data.full_name;
    if (data.email) document.getElementById('email').value = data.email;
    if (data.phone) document.getElementById('phone').value = data.phone;
    if (data.birth_date) document.getElementById('birth_date').value = data.birth_date;
    if (data.experience_years) document.getElementById('experience_years').value = data.experience_years;
    
    // Fill textareas
    if (data.address) document.getElementById('address').value = data.address;
    if (data.fiber_optic_knowledge) document.getElementById('fiber_optic_knowledge').value = data.fiber_optic_knowledge;
    if (data.work_vision) document.getElementById('work_vision').value = data.work_vision;
    if (data.work_mission) document.getElementById('work_mission').value = data.work_mission;
    if (data.motivation) document.getElementById('motivation').value = data.motivation;
    
    // Fill select fields
    if (data.gender) document.getElementById('gender').value = data.gender;
    if (data.position) document.getElementById('position').value = data.position;
    if (data.education) document.getElementById('education').value = data.education;
    if (data.otdr_experience) document.getElementById('otdr_experience').value = data.otdr_experience;
    if (data.jointing_experience) document.getElementById('jointing_experience').value = data.jointing_experience;
    if (data.tower_climbing_experience) document.getElementById('tower_climbing_experience').value = data.tower_climbing_experience;
    if (data.k3_certificate) document.getElementById('k3_certificate').value = data.k3_certificate;
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification-toast`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border: none;
        animation: slideInRight 0.3s ease;
    `;
    
    const icon = type === 'success' ? 'fa-check-circle' : 
                 type === 'error' || type === 'danger' ? 'fa-exclamation-triangle' : 
                 'fa-info-circle';
    
    notification.innerHTML = `
        <i class="fas ${icon} me-2"></i>
        ${message}
        <button type="button" class="btn-close" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Handle close button
    const closeBtn = notification.querySelector('.btn-close');
    closeBtn.addEventListener('click', () => {
        removeNotification(notification);
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        removeNotification(notification);
    }, 5000);
}

// Remove notification with animation
function removeNotification(notification) {
    if (notification.parentNode) {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
}

// Add CSS animations for notifications
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-toast {
        transition: all 0.3s ease;
    }
`;
document.head.appendChild(notificationStyles);
