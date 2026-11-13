{{-- Image upload with drag & drop and URL download (inside the form's script block). $noImagesMessage: the empty-preview text. Shared by the create form and the edit page (re-audit 2026-11-13, DRY). --}}
// ==================== IMAGE UPLOAD WITH DRAG & DROP AND URL DOWNLOAD ====================
const imageInput = document.getElementById('consultation_images');
const previewContainer = document.getElementById('image_preview_container');
const dropZone = document.getElementById('drop_zone');
const imageUrlInput = document.getElementById('image_url_input');
const downloadUrlBtn = document.getElementById('download_from_url_btn');
const maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
let selectedFiles = [];

// Function to validate and add files
function processFiles(files) {
    const filesArray = Array.from(files);
    const dataTransfer = new DataTransfer();

    // Keep existing files
    selectedFiles.forEach(f => dataTransfer.items.add(f));

    filesArray.forEach((file) => {
        // Validate file type
        if (!file.type.match('image.*')) {
            showError(`${file.name} is not a valid image file.`);
            return;
        }

        // Validate file size
        if (file.size > maxFileSize) {
            showError(`${file.name} exceeds 5MB limit (${(file.size / 1024 / 1024).toFixed(2)}MB)`);
            return;
        }

        // Add valid file to the list
        selectedFiles.push(file);
        dataTransfer.items.add(file);

        // Create preview
        createImagePreview(file);
    });

    // Update the file input with all files
    imageInput.files = dataTransfer.files;
}

// Function to create image preview
function createImagePreview(file) {
    const reader = new FileReader();
    reader.onload = function(event) {
        const wrapper = document.createElement('div');
        wrapper.className = 'image-preview-wrapper';
        wrapper.dataset.filename = file.name;

        const img = document.createElement('img');
        img.src = event.target.result;
        img.className = 'image-preview';
        img.alt = file.name;

        const removeBtn = document.createElement('button');
        removeBtn.className = 'remove-image-btn';
        removeBtn.innerHTML = '×';
        removeBtn.type = 'button';
        removeBtn.onclick = function() {
            removeImage(file, wrapper);
        };

        const fileInfo = document.createElement('small');
        fileInfo.className = 'text-gray-600 block mt-1';
        fileInfo.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB)`;

        wrapper.appendChild(img);
        wrapper.appendChild(removeBtn);
        wrapper.appendChild(fileInfo);
        previewContainer.appendChild(wrapper);
    };

    reader.readAsDataURL(file);
}

// Function to remove image
function removeImage(file, wrapper) {
    // Remove from selectedFiles array
    const fileIndex = selectedFiles.indexOf(file);
    if (fileIndex > -1) {
        selectedFiles.splice(fileIndex, 1);
    }

    // Update the file input
    const newDataTransfer = new DataTransfer();
    selectedFiles.forEach(f => newDataTransfer.items.add(f));
    imageInput.files = newDataTransfer.files;

    // Remove preview
    wrapper.remove();

    // Show message if no images
    if (selectedFiles.length === 0) {
        previewContainer.innerHTML = '<p class="text-gray-500 col-span-3">{{ $noImagesMessage }}</p>';
    }
}

// Function to show error
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'col-span-3 image-size-error';
    errorDiv.textContent = `⚠️ ${message}`;
    previewContainer.appendChild(errorDiv);

    // Auto-remove error after 5 seconds
    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}

// File input change event
if (imageInput) {
    imageInput.addEventListener('change', function(e) {
        processFiles(e.target.files);
    });
}

// ==================== DRAG & DROP FUNCTIONALITY ====================
if (dropZone) {
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('drag-over');
    }

    function unhighlight(e) {
        dropZone.classList.remove('drag-over');
    }

    // Handle dropped files
    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;

        processFiles(files);
    }

    // Click to browse
    dropZone.addEventListener('click', function(e) {
        if (e.target.id !== 'consultation_images' && !e.target.closest('label')) {
            imageInput.click();
        }
    });
}

// ==================== IMAGE URL DOWNLOAD FUNCTIONALITY ====================
if (downloadUrlBtn && imageUrlInput) {
    downloadUrlBtn.addEventListener('click', async function() {
        const imageUrl = imageUrlInput.value.trim();

        if (!imageUrl) {
            alert('Please enter an image URL');
            return;
        }

        // Validate URL format
        try {
            new URL(imageUrl);
        } catch (e) {
            alert('Please enter a valid URL');
            return;
        }

        // Show loading state
        downloadUrlBtn.disabled = true;
        downloadUrlBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Downloading...';
        downloadUrlBtn.classList.add('url-downloading');

        try {
            // Fetch the image
            const response = await fetch(imageUrl);

            if (!response.ok) {
                throw new Error(`Failed to download image: ${response.statusText}`);
            }

            const blob = await response.blob();

            // Validate if it's an image
            if (!blob.type.match('image.*')) {
                throw new Error('The URL does not point to a valid image file');
            }

            // Validate file size
            if (blob.size > maxFileSize) {
                throw new Error(`Image exceeds 5MB limit (${(blob.size / 1024 / 1024).toFixed(2)}MB)`);
            }

            // Extract filename from URL or generate one
            let filename = imageUrl.split('/').pop().split('?')[0];
            if (!filename || !filename.match(/\\.(jpg|jpeg|png|gif)$/i)) {
                const ext = blob.type.split('/')[1];
                filename = `downloaded_image_${Date.now()}.${ext}`;
            }

            // Create File object from blob
            const file = new File([blob], filename, {
                type: blob.type
            });

            // Add to selected files
            selectedFiles.push(file);

            // Update file input
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(f => dataTransfer.items.add(f));
            imageInput.files = dataTransfer.files;

            // Create preview
            createImagePreview(file);

            // Clear input
            imageUrlInput.value = '';

            // Show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'col-span-3 text-green-600 font-semibold';
            successDiv.textContent = `✓ Image downloaded successfully: ${filename}`;
            previewContainer.appendChild(successDiv);

            setTimeout(() => {
                successDiv.remove();
            }, 3000);

        } catch (error) {
            console.error('Error downloading image:', error);
            alert(`Error downloading image: ${error.message}`);
        } finally {
            // Reset button state
            downloadUrlBtn.disabled = false;
            downloadUrlBtn.innerHTML = '<i class="fas fa-download"></i> Download';
            downloadUrlBtn.classList.remove('url-downloading');
        }
    });

    // Allow Enter key to trigger download
    imageUrlInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            downloadUrlBtn.click();
        }
    });
}
