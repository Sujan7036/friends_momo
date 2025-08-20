/**
 * Main JavaScript File
 * Friends and Momos Restaurant Management System
 */

// Global configuration
const CONFIG = {
    baseUrl: window.location.origin + '/friends_momo',
    apiUrl: window.location.origin + '/friends_momo/api',
    csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
};

// Utility functions
const Utils = {
    // Format currency
    formatCurrency: (amount) => {
        return new Intl.NumberFormat('en-AU', {
            style: 'currency',
            currency: 'AUD'
        }).format(amount);
    },
    
    // Format date
    formatDate: (date, options = {}) => {
        const defaultOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        return new Intl.DateTimeFormat('en-AU', { ...defaultOptions, ...options }).format(new Date(date));
    },
    
    // Debounce function
    debounce: (func, wait) => {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Show loading state
    showLoading: (element, text = 'Loading...') => {
        const originalContent = element.innerHTML;
        element.dataset.originalContent = originalContent;
        element.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${text}`;
        element.disabled = true;
        return originalContent;
    },
    
    // Hide loading state
    hideLoading: (element) => {
        if (element.dataset.originalContent) {
            element.innerHTML = element.dataset.originalContent;
            delete element.dataset.originalContent;
        }
        element.disabled = false;
    },
    
    // Show toast notification
    showToast: (message, type = 'info', duration = 5000) => {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Create toast container if it doesn't exist
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        
        container.appendChild(toast);
        
        // Auto remove after duration
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideOut 0.3s ease-in forwards';
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
    },
    
    // Validate email
    isValidEmail: (email) => {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },
    
    // Validate phone (Australian format)
    isValidPhone: (phone) => {
        const phoneRegex = /^(\+61|0)[2-9]\d{8}$/;
        return phoneRegex.test(phone.replace(/\s/g, ''));
    },
    
    // Sanitize HTML
    sanitizeHtml: (str) => {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
};

// API helper functions
const API = {
    // Make API request
    request: async (endpoint, options = {}) => {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CONFIG.csrfToken
            }
        };
        
        const mergedOptions = { ...defaultOptions, ...options };
        
        if (mergedOptions.body && typeof mergedOptions.body === 'object') {
            mergedOptions.body = JSON.stringify(mergedOptions.body);
        }
        
        try {
            const response = await fetch(`${CONFIG.apiUrl}/${endpoint}`, mergedOptions);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return await response.json();
            } else {
                return await response.text();
            }
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    },
    
    // GET request
    get: (endpoint) => API.request(endpoint),
    
    // POST request
    post: (endpoint, data) => API.request(endpoint, {
        method: 'POST',
        body: data
    }),
    
    // PUT request
    put: (endpoint, data) => API.request(endpoint, {
        method: 'PUT',
        body: data
    }),
    
    // DELETE request
    delete: (endpoint) => API.request(endpoint, {
        method: 'DELETE'
    })
};

// Cart management
const Cart = {
    // Add item to cart
    addItem: async (itemId, quantity = 1, options = {}) => {
        try {
            const data = await API.post('cart.php', {
                action: 'add',
                item_id: itemId,
                quantity: quantity,
                options: options
            });
            
            if (data.success) {
                Utils.showToast(`Item added to cart!`, 'success');
                Cart.updateCartCount();
                // Trigger cart button animation
                if (window.animateCartButton) {
                    window.animateCartButton();
                }
                return true;
            } else {
                Utils.showToast(data.message || 'Failed to add item to cart', 'error');
                return false;
            }
        } catch (error) {
            console.error('Add to cart error:', error);
            Utils.showToast('Failed to add item to cart', 'error');
            return false;
        }
    },
    
    // Remove item from cart
    removeItem: async (cartKey) => {
        try {
            const data = await API.post('cart.php', {
                action: 'remove',
                cart_key: cartKey
            });
            
            if (data.success) {
                Utils.showToast('Item removed from cart', 'success');
                Cart.updateCartCount();
                return true;
            } else {
                Utils.showToast(data.message || 'Failed to remove item', 'error');
                return false;
            }
        } catch (error) {
            console.error('Remove from cart error:', error);
            Utils.showToast('Failed to remove item', 'error');
            return false;
        }
    },
    
    // Update cart item quantity
    updateQuantity: async (cartKey, quantity) => {
        try {
            const data = await API.post('cart.php', {
                action: 'update',
                cart_key: cartKey,
                quantity: quantity
            });
            
            if (data.success) {
                Cart.updateCartCount();
                return true;
            } else {
                Utils.showToast(data.message || 'Failed to update quantity', 'error');
                return false;
            }
        } catch (error) {
            console.error('Update cart error:', error);
            Utils.showToast('Failed to update quantity', 'error');
            return false;
        }
    },
    
    // Get cart contents
    getCart: async () => {
        try {
            return await API.get('cart.php?action=get');
        } catch (error) {
            console.error('Get cart error:', error);
            return { items: [], total: 0 };
        }
    },
    
    // Update cart count in header
    updateCartCount: async () => {
        try {
            const data = await API.get('cart.php?action=count');
            const cartCountElement = document.getElementById('cart-count');
            
            if (cartCountElement) {
                if (data.count > 0) {
                    cartCountElement.textContent = data.count;
                    cartCountElement.style.display = 'flex';
                } else {
                    cartCountElement.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Cart count update failed:', error);
        }
    },
    
    // Clear cart
    clearCart: async () => {
        try {
            const data = await API.post('cart.php', { action: 'clear' });
            
            if (data.success) {
                Utils.showToast('Cart cleared', 'success');
                Cart.updateCartCount();
                return true;
            } else {
                Utils.showToast('Failed to clear cart', 'error');
                return false;
            }
        } catch (error) {
            console.error('Clear cart error:', error);
            Utils.showToast('Failed to clear cart', 'error');
            return false;
        }
    }
};

// Form handling
const Forms = {
    // Initialize form validation
    initValidation: (form) => {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => Forms.validateField(input));
            input.addEventListener('input', Utils.debounce(() => Forms.validateField(input), 300));
        });
        
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (Forms.validateForm(form)) {
                Forms.submitForm(form);
            }
        });
    },
    
    // Validate single field
    validateField: (field) => {
        const value = field.value.trim();
        const fieldName = field.name;
        const fieldType = field.type;
        let isValid = true;
        let errorMessage = '';
        
        // Remove existing error
        Forms.clearFieldError(field);
        
        // Required field validation
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = `${fieldName} is required`;
        }
        
        // Email validation
        else if (fieldType === 'email' && value && !Utils.isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid email address';
        }
        
        // Phone validation
        else if (fieldName === 'phone' && value && !Utils.isValidPhone(value)) {
            isValid = false;
            errorMessage = 'Please enter a valid Australian phone number';
        }
        
        // Password validation
        else if (fieldType === 'password' && value && value.length < 8) {
            isValid = false;
            errorMessage = 'Password must be at least 8 characters long';
        }
        
        // Confirm password validation
        else if (fieldName === 'confirm_password') {
            const passwordField = field.form.querySelector('input[name="password"]');
            if (passwordField && value !== passwordField.value) {
                isValid = false;
                errorMessage = 'Passwords do not match';
            }
        }
        
        if (!isValid) {
            Forms.showFieldError(field, errorMessage);
        }
        
        return isValid;
    },
    
    // Validate entire form
    validateForm: (form) => {
        const inputs = form.querySelectorAll('input, textarea, select');
        let isValid = true;
        
        inputs.forEach(input => {
            if (!Forms.validateField(input)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    // Show field error
    showFieldError: (field, message) => {
        field.classList.add('error');
        
        let errorElement = field.parentElement.querySelector('.form-error');
        if (!errorElement) {
            errorElement = document.createElement('div');
            errorElement.className = 'form-error';
            field.parentElement.appendChild(errorElement);
        }
        
        errorElement.textContent = message;
    },
    
    // Clear field error
    clearFieldError: (field) => {
        field.classList.remove('error');
        const errorElement = field.parentElement.querySelector('.form-error');
        if (errorElement) {
            errorElement.remove();
        }
    },
    
    // Submit form
    submitForm: async (form) => {
        const submitButton = form.querySelector('button[type="submit"]');
        const originalContent = Utils.showLoading(submitButton, 'Processing...');
        
        try {
            const formData = new FormData(form);
            const action = form.getAttribute('action') || '';
            
            const response = await fetch(action, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                Utils.showToast(result.message || 'Form submitted successfully!', 'success');
                
                // Redirect if specified
                if (result.redirect) {
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else {
                    form.reset();
                }
            } else {
                Utils.showToast(result.message || 'Form submission failed', 'error');
                
                // Show field-specific errors
                if (result.errors) {
                    Object.entries(result.errors).forEach(([fieldName, errorMessage]) => {
                        const field = form.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            Forms.showFieldError(field, errorMessage);
                        }
                    });
                }
            }
        } catch (error) {
            console.error('Form submission error:', error);
            Utils.showToast('Form submission failed. Please try again.', 'error');
        } finally {
            Utils.hideLoading(submitButton);
        }
    }
};

// Menu interactions
const Menu = {
    // Initialize menu page
    init: () => {
        Menu.initQuantityControls();
        Menu.initAddToCartButtons();
        Menu.initFilters();
        Menu.initSearch();
    },
    
    // Initialize quantity controls
    initQuantityControls: () => {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.quantity-btn[data-action="increase"]')) {
                e.preventDefault();
                Menu.updateQuantity(e.target.closest('.quantity-controls'), 1);
            } else if (e.target.matches('.quantity-btn[data-action="decrease"]')) {
                e.preventDefault();
                Menu.updateQuantity(e.target.closest('.quantity-controls'), -1);
            }
        });
    },
    
    // Update quantity display
    updateQuantity: (container, change) => {
        const display = container.querySelector('.quantity-display');
        let currentQuantity = parseInt(display.textContent) || 1;
        const newQuantity = Math.max(1, currentQuantity + change);
        display.textContent = newQuantity;
        
        // Update data attribute for add to cart
        container.closest('.menu-item').dataset.quantity = newQuantity;
    },
    
    // Initialize add to cart buttons
    initAddToCartButtons: () => {
        document.addEventListener('click', async (e) => {
            if (e.target.matches('.add-to-cart-btn') || e.target.closest('.add-to-cart-btn')) {
                e.preventDefault();
                
                const button = e.target.matches('.add-to-cart-btn') ? e.target : e.target.closest('.add-to-cart-btn');
                const menuItem = button.closest('.menu-item');
                const itemId = menuItem.dataset.itemId;
                const quantity = parseInt(menuItem.dataset.quantity) || 1;
                
                if (itemId) {
                    const originalContent = Utils.showLoading(button, 'Adding...');
                    const success = await Cart.addItem(itemId, quantity);
                    Utils.hideLoading(button);
                    
                    if (success) {
                        // Reset quantity to 1
                        const quantityDisplay = menuItem.querySelector('.quantity-display');
                        if (quantityDisplay) {
                            quantityDisplay.textContent = '1';
                            menuItem.dataset.quantity = '1';
                        }
                    }
                }
            }
        });
    },
    
    // Initialize menu filters
    initFilters: () => {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const menuItems = document.querySelectorAll('.menu-item');
        
        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Update active filter
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');
                
                const filter = button.dataset.filter;
                
                menuItems.forEach(item => {
                    if (filter === 'all' || item.classList.contains(filter)) {
                        item.style.display = 'block';
                        item.style.animation = 'fadeIn 0.3s ease-in';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    },
    
    // Initialize search functionality
    initSearch: () => {
        const searchInput = document.querySelector('.menu-search');
        if (searchInput) {
            searchInput.addEventListener('input', Utils.debounce((e) => {
                Menu.filterBySearch(e.target.value);
            }, 300));
        }
    },
    
    // Filter menu items by search term
    filterBySearch: (searchTerm) => {
        const menuItems = document.querySelectorAll('.menu-item');
        const term = searchTerm.toLowerCase();
        
        menuItems.forEach(item => {
            const title = item.querySelector('.menu-item-title').textContent.toLowerCase();
            const description = item.querySelector('.menu-item-description')?.textContent.toLowerCase() || '';
            
            if (title.includes(term) || description.includes(term)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
};

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize cart count
    Cart.updateCartCount();
    
    // Initialize forms
    const forms = document.querySelectorAll('form[data-validate="true"]');
    forms.forEach(form => Forms.initValidation(form));
    
    // Initialize menu if on menu page
    if (document.querySelector('.menu-grid')) {
        Menu.init();
    }
    
    // Add smooth scrolling to all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Initialize lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
});

// Add CSS for toast notifications
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    .toast-container {
        position: fixed;
        top: 100px;
        right: 20px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .toast {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        padding: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
        border-left: 4px solid var(--info-color);
    }
    
    .toast-success {
        border-left-color: var(--success-color);
    }
    
    .toast-error {
        border-left-color: var(--error-color);
    }
    
    .toast-warning {
        border-left-color: var(--warning-color);
    }
    
    .toast-content {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }
    
    .toast-close {
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
        color: var(--gray-500);
        border-radius: 4px;
    }
    
    .toast-close:hover {
        background-color: var(--gray-100);
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    .form-control.error {
        border-color: var(--error-color);
        background-color: #fef2f2;
    }
    
    .lazy {
        opacity: 0;
        transition: opacity 0.3s;
    }
    
    .lazy.loaded {
        opacity: 1;
    }
`;

document.head.appendChild(toastStyles);

// Cart Button Animation Function
window.animateCartButton = function() {
    const cartButton = document.getElementById('cart-button');
    const cartIcon = cartButton?.querySelector('.cart-icon');
    const cartCount = document.getElementById('cart-count');
    
    if (cartButton && cartIcon) {
        // Add bounce animation to cart button
        cartButton.classList.add('cart-bounce');
        cartIcon.classList.add('cart-icon-bounce');
        
        // Add flash effect to cart count
        if (cartCount && cartCount.style.display !== 'none') {
            cartCount.classList.add('cart-count-flash');
        }
        
        // Show temporary success indicator
        const tempIndicator = document.createElement('div');
        tempIndicator.className = 'cart-add-indicator';
        tempIndicator.innerHTML = '<i class="fas fa-check"></i>';
        cartButton.appendChild(tempIndicator);
        
        // Remove animations after they complete
        setTimeout(() => {
            cartButton.classList.remove('cart-bounce');
            cartIcon.classList.remove('cart-icon-bounce');
            cartCount?.classList.remove('cart-count-flash');
            tempIndicator.remove();
        }, 600);
    }
};

// Initialize cart functionality on page load
document.addEventListener('DOMContentLoaded', function() {
    // Update cart count on page load
    Cart.updateCartCount();
    
    // Add event listeners for add to cart buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.add-to-cart-btn')) {
            e.preventDefault();
            const button = e.target.closest('.add-to-cart-btn');
            const itemId = button.dataset.itemId;
            const quantity = parseInt(button.dataset.quantity || '1');
            
            if (itemId) {
                // Add visual feedback to the clicked button
                button.classList.add('adding-to-cart');
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                
                Cart.addItem(itemId, quantity).then(success => {
                    // Reset button state
                    button.classList.remove('adding-to-cart');
                    button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                    
                    if (success) {
                        // Brief success state
                        button.classList.add('added-to-cart');
                        button.innerHTML = '<i class="fas fa-check"></i> Added!';
                        
                        setTimeout(() => {
                            button.classList.remove('added-to-cart');
                            button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
                        }, 1500);
                    }
                });
            }
        }
    });
});

// Export for use in other scripts
window.FriendsAndMomos = {
    Utils,
    API,
    Cart,
    Forms,
    Menu,
    CONFIG
};
