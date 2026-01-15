/**
 * Visual Search JavaScript for OpenCart
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize visual search functionality
    initVisualSearch();
});

/**
 * Initialize the visual search functionality
 */
function initVisualSearch() {
    // Set up event listeners for the UI components
    setupEventListeners();
    
    // Check if this is a results page with a session ID
    // checkAndDisplayVisualSearchResults();
}

/**
 * Set up event listeners for all visual search UI components
 */
function setupEventListeners() {
    setupVisualSearchModal();
    setupPhotoCapturing();
    setupDragAndDrop();
    setupRegionSelection();
}

/**
 * Set up the visual search modal dialog
 */
function setupVisualSearchModal() {
    // Event listener for opening the modal
    const visualSearchTrigger = document.getElementById('visual-search-trigger');
    if (visualSearchTrigger) {
        visualSearchTrigger.addEventListener('click', function() {
            const visualSearchModal = new bootstrap.Modal(document.getElementById('visualSearchModal'));
            visualSearchModal.show();
        });
    }
    
    // Event listener for file input changes
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Check file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPEG, PNG, GIF, or WEBP).');
                    return;
                }
                
                // Submit the form with the selected file
                submitForm();
            }
        });
    }
    
    // Event listener for URL search
    const urlSearchForm = document.getElementById('url-search-form');
    if (urlSearchForm) {
        urlSearchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const imageUrl = document.getElementById('imageUrl').value.trim();
            if (imageUrl) {
                submitFormWithUrl(imageUrl);
            } else {
                alert('Please enter a valid URL.');
            }
        });
    }
    
    // Event listener for sample images
    const sampleImages = document.querySelectorAll('.visual-sample-img');
    sampleImages.forEach(img => {
        img.addEventListener('click', function() {
            const imgSrc = this.getAttribute('data-url');
            if(imgSrc){
                submitFormWithUrl(imgSrc);
            }
        });
    });
}

/**
 * Set up photo capturing functionality
 */
function setupPhotoCapturing() {
    // const takePhotoBtn = document.getElementByClassName('bing-capture-button');
    const takePhotoBtn = document.getElementsByClassName('bing-capture-button')[0];

    if (takePhotoBtn) {
        takePhotoBtn.addEventListener('click', async () => {
            try {
                // Hide the modal first
                const modalElement = document.getElementById('visualSearchModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();
                
                // Create camera overlay
                const cameraOverlay = document.createElement('div');
                cameraOverlay.className = 'bing-camera-overlay';
                cameraOverlay.innerHTML = `
                    <div class="camera-header">
                        <div class="camera-title">Take a Photo</div>
                        <button class="camera-close">&times;</button>
                    </div>
                    <div class="camera-container">
                        <video class="camera-video" autoplay></video>
                    </div>
                    <div class="camera-controls">
                        <div class="capture-btn"></div>
                    </div>
                `;
                document.body.appendChild(cameraOverlay);
                
                // Get video stream
                const videoElement = cameraOverlay.querySelector('.camera-video');
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } 
                });
                videoElement.srcObject = stream;
                
                // Set up close button
                const closeBtn = cameraOverlay.querySelector('.camera-close');
                closeBtn.addEventListener('click', () => {
                    stream.getTracks().forEach(track => track.stop());
                    cameraOverlay.remove();
                    modal.show();
                });
                
                // Set up capture button
                const captureBtn = cameraOverlay.querySelector('.capture-btn');
                captureBtn.addEventListener('click', async () => {
                    // Disable capture button
                    captureBtn.style.pointerEvents = 'none';
                    captureBtn.style.opacity = '0.6';
                    
                    // Create canvas and capture frame
                    const canvas = document.createElement('canvas');
                    canvas.width = videoElement.videoWidth;
                    canvas.height = videoElement.videoHeight;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(videoElement, 0, 0, canvas.width, canvas.height);
                    
                    // Convert to blob
                    canvas.toBlob(async (blob) => {
                        // Clean up camera
                        stream.getTracks().forEach(track => track.stop());
                        cameraOverlay.remove();
                        
                        // Create a file from the blob
                        const file = new File([blob], "camera-capture.jpg", { type: 'image/jpeg' });
                        
                        // Create and submit a form with the file
                        const formData = new FormData();
                        formData.append('file', file);
                        
                        // Get the current base URL for OpenCart
                        const currentUrl = window.location.href;
                        const opencartBaseUrl = 'https://www.ipshopy.com/';
                        
                        // Add the base URLs to the form data
                        formData.append('base_url', 'http://181.224.131.247:5004/');
                        formData.append('opencart_base_url', opencartBaseUrl);
                        
                        // Show loading overlay
                        showFullPageLoading('Processing your image... Please wait');
                        console.log(' camera : ',formData);
                        // Send to server
                        fetch(opencartBaseUrl + 'index.php?route=product/visual_search/upload', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.session_id) {
                                redirectToSearchResults(data);
                            } else {
                                hideFullPageLoading();
                                alert(data.error || 'An error occurred while processing the image.');
                            }
                        })
                        .catch(error => {
                            hideFullPageLoading();
                            alert('Error: ' + error.message);
                        });
                    }, 'image/jpeg', 0.9);
                });
            } catch (err) {
                console.error('Error accessing camera:', err);
                alert('Unable to access camera. Please allow camera access.');
            }
        });
    }
}

/**
 * Set up drag and drop functionality
 */
function setupDragAndDrop() {
    const interactionBox = document.querySelector('.visual-interaction-box');
    const fileInput = document.getElementById('fileInput');
    
    if (!interactionBox || !fileInput) return;
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        interactionBox.addEventListener(eventName, preventDefaults, false);
        document.body.addEventListener(eventName, preventDefaults, false);
    });
    
    // Highlight drop area when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
        interactionBox.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        interactionBox.addEventListener(eventName, unhighlight, false);
    });
    
    // Handle dropped files
    interactionBox.addEventListener('drop', handleDrop, false);
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    function highlight() {
        interactionBox.classList.add('drag-over');
    }
    
    function unhighlight() {
        interactionBox.classList.remove('drag-over');
    }
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length) {
            fileInput.files = files;
            submitForm();
        }
    }
}

/**
 * Submit the form with file upload
 */
function submitForm() {
    const form = document.getElementById('visual-search-form');
    if (!form) return;
    
    const formData = new FormData(form);
    
    // Get the current base URL for OpenCart
    const currentUrl = window.location.href;
    const opencartBaseUrl = 'https://www.ipshopy.com/';
    
    // Add the base URLs to the form data
    formData.append('base_url', 'http://181.224.131.247:5004/');
    formData.append('opencart_base_url', opencartBaseUrl);
    
    // Show a full page loading overlay
    showFullPageLoading('Processing your image... Please wait');
    console.log('normal submit: ',formData);
    // Submit the form using fetch API
    fetch(opencartBaseUrl + 'index.php?route=product/visual_search/upload', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.session_id) {
            redirectToSearchResults(data);
        } else {
            hideFullPageLoading();
            alert(data.error || 'An error occurred while processing the image.');
        }
    })
    .catch(error => {
        hideFullPageLoading();
        alert('Error: ' + error.message);
    });
}

/**
 * Submit form with an image URL
 */function submitFormWithUrl(imageFile) {
     document.getElementById('visualSearchModal').style.display='none';
    const form = document.getElementById('visual-search-form');
    if (!form || !imageFile) return;

    console.log('from submit form with url function external js : ', imageFile);

    const isImageUrl = /\.(jpeg|jpg|gif|png|webp|bmp|svg)$/i.test(imageFile);
    if (!isImageUrl) {
        alert('Provided URL does not look like a valid image.');
        return;
    }

    // Timeout: reject after 10 seconds
    const fetchTimeout = new Promise((_, reject) =>
        setTimeout(() => reject(new Error('Image fetch timed out')), 10000)
    );

    // Race between image fetch and timeout
    Promise.race([
        fetch(imageFile),
        fetchTimeout
    ])
    .then(response => {
        if (!response.ok) {
            throw new Error(`Failed to fetch image (HTTP ${response.status})`);
        }
        return response.blob();
    })
    .then(blob => {
        const formData = new FormData();
        formData.append('file', blob, 'image_from_url.jpg');

        // Add required extra form data
        formData.append('base_url', 'http://181.224.131.247:5004/');
        formData.append('opencart_base_url', 'https://www.ipshopy.com/');

        // Show loading
        showFullPageLoading('Processing your image... Please wait');

        // Submit to OpenCart backend
        return fetch('https://www.ipshopy.com/index.php?route=product/visual_search/upload', {
            method: 'POST',
            body: formData
        });
    })
    .then(response => response.json())
    .then(data => {
        hideFullPageLoading();
        if (data.success && data.session_id) {
            redirectToSearchResults(data);
        } else {
            alert(data.error || 'An error occurred while processing the image.');
        }
    })
    .catch(error => {
        hideFullPageLoading();
        alert('Error: ' + error.message);
    });
}


/**
 * Redirect to search results page
 */
function redirectToSearchResults(response) {
    try {
        if (response.success && response.session_id) {
            // Create the search parameters
            const searchParams = new URLSearchParams();
            searchParams.set('route', 'product/visual_search');
            searchParams.set('session_id', response.session_id);
            searchParams.set('result_count', response.result_count || 0);
            
            // Use the opencart_base_url from the response if available
            let baseUrl = '';
            if (response.opencart_base_url) {
                // Remove trailing slash if present
                baseUrl = response.opencart_base_url.endsWith('/') ? 
                         response.opencart_base_url : 
                         response.opencart_base_url + '/';
            }
            
            const redirectUrl = baseUrl + 'index.php?' + searchParams.toString();
            console.log('Redirecting to:', redirectUrl);
            
            // Redirect to the search results page
            window.location.href = redirectUrl;
        } else {
            hideFullPageLoading();
            alert('Error: Invalid response from server.');
        }
    } catch (err) {
        hideFullPageLoading();
        alert('Error: ' + err.message);
    }
}

/**
 * Check if this is a results page and display visual search results
 */
function checkAndDisplayVisualSearchResults() {
    // Get URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const route = urlParams.get('route');
    const sessionId = urlParams.get('session_id');
    
    // If this is a visual search results page with a session ID
    if (route === 'product/visual_search' && sessionId) {
        try {
            // Get the content area
            let contentArea = document.getElementById('content');
            if (!contentArea) {
                contentArea = document.querySelector('.container');
            }
            
            if (!contentArea) return;
            
            // Show loading
            showFullPageLoading('Loading visual search results...');
            
            // Get the current base URL for OpenCart
            const currentUrl = window.location.href;
            const opencartBaseUrl = 'https://www.ipshopy.com/';
            
            // Fetch the results from the backend
            fetch(opencartBaseUrl + 'index.php?route=product/visual_search/results&session_id=' + sessionId)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error('Server error: ' + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Hide loading
                    hideFullPageLoading();
                    
                    const results = data.results || [];
                    const uploadedImageUrl = data.uploaded_image_url || '';
                    const message = data.message || (results.length === 0 ? 'No similar products found.' : '');
                    
                    // Create results container
                    const resultsContainer = document.createElement('div');
                    resultsContainer.id = 'results-container';
                    
                    // Add uploaded image if available
                    if (uploadedImageUrl) {
                        const imageContainer = document.createElement('div');
                        imageContainer.className = 'uploaded-image-container';
                        imageContainer.innerHTML = `
                            <div id="image-wrapper" class="image-wrapper">
                                <img id="uploaded-search-image" class="uploaded-search-image" src="${uploadedImageUrl}" alt="Uploaded Image">
                                <div id="region-points" class="region-points"></div>
                            </div>
                            <div class="action-buttons-group" id="region-buttons"></div>
                        `;
                        resultsContainer.appendChild(imageContainer);
                    }
                    
                    // Add message if available
                    if (message) {
                        const messageElement = document.createElement('div');
                        messageElement.className = 'alert alert-info';
                        messageElement.textContent = message;
                        resultsContainer.appendChild(messageElement);
                    }
                    
                    if (results && results.length > 0) {
                        // Store all results for pagination
                        window.allSearchResults = results;
                        
                        // Set items per page to 20
                        const itemsPerPage = 20;
                        
                        // Calculate total pages
                        const totalPages = Math.ceil(results.length / itemsPerPage);
                        
                        // Initialize with page 1
                        const currentPage = 1;
                        
                        // Create products grid
                        const productsGrid = document.createElement('div');
                        productsGrid.className = 'product-grid';
                        productsGrid.id = 'products-grid';
                        resultsContainer.appendChild(productsGrid);
                        
                        // Add pagination if needed
                        if (totalPages > 1) {
                            const paginationHTML = generatePaginationHTML(currentPage, totalPages);
                            resultsContainer.insertAdjacentHTML('beforeend', paginationHTML);
                            
                            // Add event listeners for pagination
                            document.querySelectorAll('.page-item').forEach(item => {
                                item.addEventListener('click', function() {
                                    if (this.classList.contains('disabled')) return;
                                    
                                    const page = parseInt(this.getAttribute('data-page'));
                                    if (isNaN(page)) return;
                                    
                                    updatePageContent(window.allSearchResults, page, itemsPerPage);
                                    
                                    // Update active page
                                    document.querySelectorAll('.page-item').forEach(p => {
                                        p.classList.remove('active');
                                    });
                                    this.classList.add('active');
                                });
                            });
                        }
                        
                        // Load first page
                        updatePageContent(results, currentPage, itemsPerPage);
                        
                        // Setup region selection if uploaded image is available
                        if (uploadedImageUrl) {
                            setupCustomCropSelection();
                        }
                    }
                    
                    // Replace content with results
                    contentArea.innerHTML = '';
                    contentArea.appendChild(resultsContainer);
                })
                .catch(error => {
                    console.error('Error fetching search results:', error);
                    hideFullPageLoading();
                    alert('Failed to load visual search results: ' + error.message);
                });
        } catch (err) {
            hideFullPageLoading();
            alert('Failed to display visual search results: ' + err.message);
        }
    }
}

/**
 * Set up region selection on the uploaded image
 */
function setupRegionSelection() {
    const uploadedImage = document.getElementById('uploaded-search-image');
    if (!uploadedImage) return;
    
    let isSelecting = false;
    let startX, startY;
    let selectionElement = null;
    
    const imageWrapper = document.getElementById('image-wrapper');
    
    // Mouse down event to start selection
    imageWrapper.addEventListener('mousedown', function(e) {
        // Only allow selection if no selection exists
        if (selectionElement) return;
        
        isSelecting = true;
        
        const rect = imageWrapper.getBoundingClientRect();
        startX = e.clientX - rect.left;
        startY = e.clientY - rect.top;
        
        // Create selection element
        selectionElement = document.createElement('div');
        selectionElement.className = 'image-selection-outline';
        selectionElement.style.left = startX + 'px';
        selectionElement.style.top = startY + 'px';
        selectionElement.style.width = '0';
        selectionElement.style.height = '0';
        
        imageWrapper.appendChild(selectionElement);
    });
    
    // Mouse move event to update selection
    imageWrapper.addEventListener('mousemove', function(e) {
        if (!isSelecting) return;
        
        const rect = imageWrapper.getBoundingClientRect();
        const currentX = e.clientX - rect.left;
        const currentY = e.clientY - rect.top;
        
        const width = Math.abs(currentX - startX);
        const height = Math.abs(currentY - startY);
        
        const left = Math.min(startX, currentX);
        const top = Math.min(startY, currentY);
        
        selectionElement.style.left = left + 'px';
        selectionElement.style.top = top + 'px';
        selectionElement.style.width = width + 'px';
        selectionElement.style.height = height + 'px';
    });
    
    // Mouse up event to complete selection
    document.addEventListener('mouseup', function(e) {
        if (isSelecting && selectionElement) {
            isSelecting = false;
            
            const width = parseInt(selectionElement.style.width);
            const height = parseInt(selectionElement.style.height);
            
            // If selection is too small, remove it
            if (width < 20 || height < 20) {
                selectionElement.remove();
                selectionElement = null;
                return;
            }
            
            // Get selection coordinates relative to image
            const imageRect = uploadedImage.getBoundingClientRect();
            const wrapperRect = imageWrapper.getBoundingClientRect();
            
            const left = parseInt(selectionElement.style.left);
            const top = parseInt(selectionElement.style.top);
            
            // Calculate normalized coordinates (0-1)
            const normX = left / wrapperRect.width;
            const normY = top / wrapperRect.height;
            const normWidth = width / wrapperRect.width;
            const normHeight = height / wrapperRect.height;
            
            // Add region button
            addRegionButton({
                x: normX,
                y: normY,
                width: normWidth,
                height: normHeight
            });
            
            // Remove selection outline
            selectionElement.remove();
            selectionElement = null;
        }
    });
}

/**
 * Add a region button for a selected area
 */
function addRegionButton(region) {
    const regionsContainer = document.getElementById('region-buttons');
    if (!regionsContainer) return;
    
    // Get next region index
    const regionIndex = regionsContainer.children.length + 1;
    
    // Create region button
    const button = document.createElement('button');
    button.className = 'region-btn';
    button.setAttribute('data-region-index', regionIndex);
    button.innerHTML = `
        <span class="region-color"></span>
        Region ${regionIndex}
    `;
    
    // Store region data
    button.dataset.region = JSON.stringify(region);
    
    // Add click event
    button.addEventListener('click', function() {
        searchImageRegion(regionIndex);
    });
    
    regionsContainer.appendChild(button);
    
    // Add point marker on the image
    addRegionPoint(region, regionIndex);
}

/**
 * Add a point marker on the image for a region
 */
function addRegionPoint(region, index) {
    const pointsContainer = document.getElementById('region-points');
    const imageWrapper = document.getElementById('image-wrapper');
    
    if (!pointsContainer || !imageWrapper) return;
    
    const wrapperWidth = imageWrapper.offsetWidth;
    const wrapperHeight = imageWrapper.offsetHeight;
    
    // Calculate center point of region
    const centerX = (region.x + region.width / 2) * wrapperWidth;
    const centerY = (region.y + region.height / 2) * wrapperHeight;
    
    // Create point element
    const point = document.createElement('div');
    point.className = 'region-point';
    point.textContent = index;
    point.style.left = (centerX - 12) + 'px'; // 12 is half the width of point
    point.style.top = (centerY - 12) + 'px'; // 12 is half the height of point
    
    // Add click event
    point.addEventListener('click', function() {
        searchImageRegion(index);
    });
    
    pointsContainer.appendChild(point);
}

/**
 * Search a specific region of the image
 */
function searchImageRegion(regionIndex) {
    const button = document.querySelector(`.region-btn[data-region-index="${regionIndex}"]`);
    if (!button) return;
    
    // Get region data
    const region = JSON.parse(button.dataset.region);
    
    // Highlight active button
    document.querySelectorAll('.region-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    button.classList.add('active');
    
    // TODO: Add backend call to search by region
    // For now, just show a loading message
    showFullPageLoading('Searching region ' + regionIndex + '...');
    
    // Simulate a delay
    setTimeout(() => {
        hideFullPageLoading();
        alert('Region search functionality is not implemented in this demo.');
    }, 1000);
}

/**
 * Generate pagination HTML
 */
function generatePaginationHTML(currentPage, totalPages) {
    let paginationHTML = `
        <div class="pagination-container">
            <div class="pagination">
    `;
    
    // Previous button
    const prevDisabled = currentPage === 1 ? 'disabled' : '';
    paginationHTML += `<div class="page-item ${prevDisabled}" data-page="${currentPage - 1}">«</div>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        const active = i === currentPage ? 'active' : '';
        paginationHTML += `<div class="page-item ${active}" data-page="${i}">${i}</div>`;
    }
    
    // Next button
    const nextDisabled = currentPage === totalPages ? 'disabled' : '';
    paginationHTML += `<div class="page-item ${nextDisabled}" data-page="${currentPage + 1}">»</div>`;
    
    paginationHTML += `
            </div>
        </div>
    `;
    
    return paginationHTML;
}

/**
 * Update page content with products
 */
function updatePageContent(allResults, page, itemsPerPage) {
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) return;
    
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, allResults.length);
    
    const pageResults = allResults.slice(startIndex, endIndex);
    
    // Clear existing content
    productsGrid.innerHTML = '';
    
    // Add products
    pageResults.forEach(product => {
        const discountPercentage = product.discount_percentage ? 
            `<div class="discount-badge">-${Math.round(product.discount_percentage)}%</div>` : '';
        
        const availabilityClass = product.in_stock ? 'in-stock' : 'out-of-stock';
        const availabilityText = product.in_stock ? 'In Stock' : 'Out of Stock';
        
        // Create product card
        const productCard = `
            <div class="product-card">
                ${discountPercentage}
                <div class="product-image">
                    <img src="${product.image_url}" alt="${product.title}">
                </div>
                <div class="product-info">
                    <h5 class="product-title">${product.title}</h5>
                    <div class="product-price">
                        ${product.original_price ? `<span class="original-price">${product.original_price}</span>` : ''}
                        <span class="regular-price">${product.sale_price || product.price || ''}</span>
                    </div>
                    <div class="similarity">Similarity: ${Math.round(product.similarity_score * 100)}%</div>
                    <div class="action-buttons mt-2">
                        <button class="btn btn-sm btn-primary add-to-cart-btn">Add to Cart</button>
                        <button class="btn btn-sm btn-outline-secondary view-product-btn">View</button>
                    </div>
                    <div class="availability ${availabilityClass}">${availabilityText}</div>
                </div>
            </div>
        `;
        
        productsGrid.innerHTML += productCard;
    });
    
    // Add event listeners for buttons
    setupProductButtons();
}

/**
 * Set up event listeners for product buttons
 */
function setupProductButtons() {
    // Add to cart buttons
    document.querySelectorAll('.add-to-cart-btn').forEach((button, index) => {
        button.addEventListener('click', function() {
            alert('Add to cart functionality is not implemented in this demo.');
        });
    });
    
    // View product buttons
    document.querySelectorAll('.view-product-btn').forEach((button, index) => {
        button.addEventListener('click', function() {
            const productCard = this.closest('.product-card');
            const productTitle = productCard.querySelector('.product-title').textContent;
            alert(`View product: ${productTitle}`);
        });
    });
}

/**
 * Show full page loading overlay
 */
// function showFullPageLoading(message) {
//     // Remove any existing loading overlay
//     const existingOverlay = document.querySelector('.full-page-loading');
//     if (existingOverlay) {
//         existingOverlay.remove();
//     }
    
//     // Create loading overlay
//     const loadingOverlay = document.createElement('div');
//     loadingOverlay.className = 'full-page-loading';
//     loadingOverlay.innerHTML = `
//         <div class="loading-container">
//             <div class="bubble-loader">
//                 <div class="bubble"></div>
//                 <div class="bubble"></div>
//                 <div class="bubble"></div>
//                 <div class="bubble"></div>
//                 <div class="bubble"></div>
//             </div>
//             <div class="loading-text">${message || 'Loading...'}</div>
//         </div>
//     `;
    
//     document.body.appendChild(loadingOverlay);
// }

function showFullPageLoading(initialMessage) {
    console.log('loder call')
      // Remove any existing loading overlay
      const existingOverlay = document.querySelector('.visual-full-page-loading');
      if (existingOverlay) {
        existingOverlay.remove();
      }
      
      // Create the full page loading overlay
      const loadingOverlay = document.createElement('div');
      loadingOverlay.className = 'visual-full-page-loading';
      loadingOverlay.style.opacity = '0';
      loadingOverlay.style.backgroundColor = '#fff'; // Add white background
      loadingOverlay.innerHTML = `
        <div class="visual-loading-container">
          <div class="visual-bubble-loader">
            <div class="visual-bubble"></div>
            <div class="visual-bubble"></div>
            <div class="visual-bubble"></div>
            <div class="visual-bubble"></div>
            <div class="visual-bubble"></div>
          </div>
          <div class="visual-loading-text" id="full-page-loading-message">${initialMessage || 'Analyzing your image...'}</div>
          <div class="visual-progress-bar-container">
            <div class="visual-progress-bar" id="full-page-progress-bar"></div>
          </div>
        </div>
      `;
      
      document.body.appendChild(loadingOverlay);
      
      // Force a reflow to enable the transition
      loadingOverlay.offsetHeight;
      
      // Fade in the overlay
      loadingOverlay.style.opacity = '1';
      
      // Set up sequence of messages
      const loadingMessages = [
        initialMessage || "Analyzing your image...",
        "Looking for similar products...",
        "Matching colors and patterns...",
        "Finding best matches...",
        "Almost done! Preparing results...",
        "Sorting by relevance..."
      ];
      
      let messageIndex = 0;
      let progress = 0;
      
      // Update loading message and progress bar
      const messageElement = document.getElementById('full-page-loading-message');
      const progressBar = document.getElementById('full-page-progress-bar');
      
      const updateMessage = setInterval(() => {
        messageIndex = (messageIndex + 1) % loadingMessages.length;
        if (messageElement) {
          messageElement.textContent = loadingMessages[messageIndex];
          
          // Update progress
          progress += (100 - progress) * 0.2;
          if (progressBar) {
            progressBar.style.width = Math.min(progress, 95) + '%';
          }
        }
      }, 2000);
      
      // Store the interval ID
      loadingOverlay.dataset.intervalId = updateMessage;
      
      // Set a timeout to hide the loading after a maximum wait time
      window.fullPageLoadingTimeout = setTimeout(() => {
        hideFullPageLoading();
      }, 30000); // 30 seconds max loading time
      
      return loadingOverlay;
    }
/**
 * Hide full page loading overlay
 */
function hideFullPageLoading() {
    const loadingOverlay = document.querySelector('.visual-full-page-loading');
    if (loadingOverlay) {
        loadingOverlay.remove();
    }
}


document.addEventListener('DOMContentLoaded', function () {
    // Bing-style camera implementation
    $("#take-photo-btn").on("click", async () => {
      try {
        // Hide the modal first using Bootstrap 3.3.5 method
        $("#visualSearchModal").modal('hide');

        // Create Bing-style camera overlay
        const cameraOverlay = document.createElement('div');
        cameraOverlay.className = 'bing-camera-overlay';
        cameraOverlay.innerHTML = `
          <div class="bing-camera-container">
            <div class="bing-camera-header">
              <div class="bing-camera-title">Take a photo</div>
              <button class="bing-camera-close">&times;</button>
            </div>
            <div class="bing-camera-video-container">
              <video class="bing-camera-video" id="bing-camera-video" autoplay playsinline></video>
              <div class="bing-camera-instruction">Drag one</div>
              <div class="bing-camera-controls">
                <div class="bing-capture-button"></div>
              </div>
              <div class="bing-side-controls">
                <button class="bing-control-btn" id="bing-flash-btn">
                  <i class="fa fa-bolt"></i>
                </button>
                <button class="bing-control-btn" id="bing-switch-btn">
                  <i class="fa fa-refresh" aria-hidden="true"></i>
                </button>
              </div>
            </div>
          </div>
        `;
        document.body.appendChild(cameraOverlay);

        // Get camera elements
        const cameraVideo = document.getElementById('bing-camera-video');
        const captureBtn = document.querySelector('.bing-capture-button');
        const closeBtn = document.querySelector('.bing-camera-close');
        const switchBtn = document.getElementById('bing-switch-btn');
        const flashBtn = document.getElementById('bing-flash-btn');
        const canvas = document.getElementById('canvas');

        // Camera setup
        let currentFacingMode = 'environment';
        let stream = null;
        let flashEnabled = false;

        // Function to start camera
        async function startCamera(facingMode) {
          try {
            if (stream) {
              stream.getTracks().forEach(track => track.stop());
            }
            
            // First check if we have camera permissions
            stream = await navigator.mediaDevices.getUserMedia({ 
              video: { 
                facingMode: facingMode,
                width: { ideal: 640 },
                height: { ideal: 480 }
              } 
            });
            
            cameraVideo.srcObject = stream;
            
            // Wait for video to be ready
            await new Promise(resolve => {
              cameraVideo.onloadedmetadata = () => {
                resolve();
              };
            });
            
            // Show the camera is active visually
            cameraVideo.style.transform = facingMode === 'user' ? 'scaleX(-1)' : 'none';
            
            // Make sure both canvas elements have correct dimensions
            const captureCanvas = document.getElementById('capture-canvas');
            captureCanvas.width = cameraVideo.videoWidth;
            captureCanvas.height = cameraVideo.videoHeight;
            
            // Also set the other canvas for compatibility
            if (canvas) {
              canvas.width = cameraVideo.videoWidth;
              canvas.height = cameraVideo.videoHeight;
            }
            

            return true;
          } catch (err) {
            console.error('Error starting camera:', err);
            alert('Unable to access camera. Please allow camera access and try again.');
            return false;
          }
        }

        // Start camera
        await startCamera(currentFacingMode);

        // Switch camera
        switchBtn.addEventListener('click', async () => {
          currentFacingMode = currentFacingMode === 'environment' ? 'user' : 'environment';
          await startCamera(currentFacingMode);
        });

        // Flash toggle
        flashBtn.addEventListener('click', () => {
          flashEnabled = !flashEnabled;
          flashBtn.style.color = flashEnabled ? '#ffd700' : 'white';
        });

        // Capture photo
        captureBtn.addEventListener('click', async () => {
          // Disable capture button
          captureBtn.style.pointerEvents = 'none';
          captureBtn.style.opacity = '0.6';
          
          // Flash effect if enabled
          if (flashEnabled) {
            cameraOverlay.style.background = 'rgba(255, 255, 255, 0.8)';
            setTimeout(() => {
              cameraOverlay.style.background = 'rgba(0, 0, 0, 0.7)';
            }, 150);
          }
          
          try {
            // Get capture canvas and draw the image
            const captureCanvas = document.getElementById('capture-canvas');
            captureCanvas.width = cameraVideo.videoWidth;
            captureCanvas.height = cameraVideo.videoHeight;
            const ctx = captureCanvas.getContext('2d');
            ctx.drawImage(cameraVideo, 0, 0, captureCanvas.width, captureCanvas.height);
            
            // Convert to base64 image data
            const imageData = captureCanvas.toDataURL('image/jpeg', 0.9);
            document.getElementById('image-base64').value = imageData;
            
            // Create a file from the base64 data
            const dataURLtoBlob = (dataurl) => {
              const arr = dataurl.split(',');
              const mime = arr[0].match(/:(.*?);/)[1];
              const bstr = atob(arr[1]);
              let n = bstr.length;
              const u8arr = new Uint8Array(n);
              while (n--) {
                u8arr[n] = bstr.charCodeAt(n);
              }
              return new Blob([u8arr], { type: mime });
            };
            
            // Create a File object from the blob
            const blob = dataURLtoBlob(imageData);
            const file = new File([blob], 'camera_capture.jpg', { type: 'image/jpeg' });
            
            // Create a new FileList-like object and assign it to the file input
            try {
              // Modern browsers support DataTransfer API
              const dataTransfer = new DataTransfer();
              dataTransfer.items.add(file);
              document.getElementById('fileInput').files = dataTransfer.files;
            } catch (err) {
              // Fallback for browsers that don't support DataTransfer
              // Store the base64 data and directly submit the form without updating the file input
            }
            
            // Show the unified loading animation
            showFullPageLoading('Processing your camera image... Please wait');
          } catch (err) {
            console.error('Error capturing image:', err);
          }
          
          // Stop camera
          if (stream) {
            stream.getTracks().forEach(track => track.stop());
          }
          
          // Remove camera overlay
          setTimeout(() => {
            cameraOverlay.remove();
            submitForm();
          }, 500);
        });

        // Close camera
        closeBtn.addEventListener('click', () => {
          if (stream) {
            stream.getTracks().forEach(track => track.stop());
          }
          cameraOverlay.remove();
          
          // Reopen modal using Bootstrap 3.3.5 method
          $("#visualSearchModal").modal('show');
        });

        // Close on overlay click
        cameraOverlay.addEventListener('click', (e) => {
          if (e.target === cameraOverlay) {
            if (stream) {
              stream.getTracks().forEach(track => track.stop());
            }
            cameraOverlay.remove();
          }
        });

      } catch (err) {
        console.error('Error accessing camera:', err);
        alert('Unable to access camera. Please allow camera access.');
      }
    });
});


