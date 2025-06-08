/**
 * AdminCRUD - Shared JavaScript for Admin Panel CRUD Operations
 * Eliminates duplicate code across admin views (products, slider, etc.)
 */
class AdminCRUD {
    constructor(entityName, endpoint) {
        this.entityName = entityName;
        this.endpoint = endpoint;
    }
    
    /**
     * Open create modal - unified for all admin entities
     */
    openCreateModal(modalId, formId, titleId) {
        document.getElementById(titleId).textContent = `Add New ${this.entityName}`;
        const form = document.getElementById(formId);
        form.action = `${this.endpoint}/store`;
        
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.textContent = `Save ${this.entityName}`;
        }
        
        form.reset();
        
        // Reset hidden ID field if exists
        const idField = form.querySelector('input[name$="ID"]');
        if (idField) {
            idField.value = '';
        }
        
        this.resetImagePreview();
    }
    
    /**
     * Open edit modal - unified for all admin entities
     */
    openEditModal(id, modalId, formId, titleId) {
        document.getElementById(titleId).textContent = `Edit ${this.entityName}`;
        const form = document.getElementById(formId);
        form.action = `${this.endpoint}/update`;
        
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.textContent = `Update ${this.entityName}`;
        }
        
        // Fetch entity data
        fetch(`${this.endpoint}/get?id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    alert(`Error loading ${this.entityName.toLowerCase()} data`);
                    return;
                }
                
                this.populateForm(data, formId);
                new bootstrap.Modal(document.getElementById(modalId)).show();
            })
            .catch(error => {
                console.error('Error:', error);
                alert(`Error loading ${this.entityName.toLowerCase()} data`);
            });
    }
    
    /**
     * Populate form fields with data
     */
    populateForm(data, formId) {
        const form = document.getElementById(formId);
        
        // Auto-populate all matching form fields
        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'checkbox') {
                    field.checked = data[key] == 1;
                } else {
                    field.value = data[key] || '';
                }
            }
        });
        
        // Handle image preview for different entity types
        const imagePath = data.image_path || data.image;
        if (imagePath) {
            this.showExistingImage('/uploads/' + imagePath);
        } else {
            this.resetImagePreview();
        }
    }
    
    /**
     * Confirm delete modal - unified for all admin entities
     */
    confirmDelete(id, name, deleteModalId) {
        const nameElement = document.getElementById(`delete${this.entityName}Name`);
        if (nameElement) {
            nameElement.textContent = name;
        }
        
        const deleteBtn = document.getElementById('deleteBtn');
        if (deleteBtn) {
            deleteBtn.href = `${this.endpoint}/delete?id=${id}`;
        }
        
        new bootstrap.Modal(document.getElementById(deleteModalId)).show();
    }
    
    /**
     * Preview uploaded image - IDENTICAL across all admin views
     */
    previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                this.showExistingImage(e.target.result);
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    /**
     * Show existing image - IDENTICAL across all admin views
     */
    showExistingImage(imageSrc) {
        const previewImg = document.getElementById('previewImg');
        const currentImage = document.getElementById('currentImage');
        const noImage = document.getElementById('noImage');
        
        if (previewImg) previewImg.src = imageSrc;
        if (currentImage) currentImage.style.display = 'block';
        if (noImage) noImage.style.display = 'none';
    }
    
    /**
     * Reset image preview - IDENTICAL across all admin views
     */
    resetImagePreview() {
        const currentImage = document.getElementById('currentImage');
        const noImage = document.getElementById('noImage');
        
        if (currentImage) currentImage.style.display = 'none';
        if (noImage) noImage.style.display = 'flex';
        
        // Reset file input (find dynamically by type)
        const fileInput = document.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.value = '';
        }
    }
}

// Initialize global CRUD instances for common entities
const productCRUD = new AdminCRUD('Product', '/admin/products');
const sliderCRUD = new AdminCRUD('Slider', '/admin/slider');

// Global wrapper functions for backward compatibility
function openCreateModal() {
    // Detect current page and use appropriate CRUD instance
    if (window.location.pathname.includes('/products')) {
        productCRUD.openCreateModal('productModal', 'productForm', 'productModalTitle');
    } else if (window.location.pathname.includes('/slider')) {
        sliderCRUD.openCreateModal('sliderModal', 'sliderForm', 'sliderModalTitle');
    }
}

function openEditModal(id) {
    if (window.location.pathname.includes('/products')) {
        productCRUD.openEditModal(id, 'productModal', 'productForm', 'productModalTitle');
    } else if (window.location.pathname.includes('/slider')) {
        sliderCRUD.openEditModal(id, 'sliderModal', 'sliderForm', 'sliderModalTitle');
    }
}

function confirmDelete(id, name) {
    if (window.location.pathname.includes('/products')) {
        productCRUD.confirmDelete(id, name, 'deleteModal');
    } else if (window.location.pathname.includes('/slider')) {
        sliderCRUD.confirmDelete(id, name, 'deleteModal');
    }
}

function previewImage(input) {
    if (window.location.pathname.includes('/products')) {
        productCRUD.previewImage(input);
    } else if (window.location.pathname.includes('/slider')) {
        sliderCRUD.previewImage(input);
    }
}

function showExistingImage(imageSrc) {
    if (window.location.pathname.includes('/products')) {
        productCRUD.showExistingImage(imageSrc);
    } else if (window.location.pathname.includes('/slider')) {
        sliderCRUD.showExistingImage(imageSrc);
    }
}

function resetImagePreview() {
    if (window.location.pathname.includes('/products')) {
        productCRUD.resetImagePreview();
    } else if (window.location.pathname.includes('/slider')) {
        sliderCRUD.resetImagePreview();
    }
} 