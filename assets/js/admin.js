/**
 * Admin Panel JavaScript
 * Friends and Momos Restaurant Management System
 */

// Admin-specific functionality
class AdminPanel {
    constructor() {
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.initializeComponents();
    }
    
    bindEvents() {
        // Confirm dialogs for delete actions
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
                e.preventDefault();
                const btn = e.target.classList.contains('delete-btn') ? e.target : e.target.closest('.delete-btn');
                this.confirmDelete(btn);
            }
        });
        
        // Status update buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('status-btn') || e.target.closest('.status-btn')) {
                e.preventDefault();
                const btn = e.target.classList.contains('status-btn') ? e.target : e.target.closest('.status-btn');
                this.updateStatus(btn);
            }
        });
        
        // Bulk actions
        const bulkActionSelect = document.getElementById('bulk-action');
        const applyBulkBtn = document.getElementById('apply-bulk');
        
        if (bulkActionSelect && applyBulkBtn) {
            applyBulkBtn.addEventListener('click', () => {
                this.handleBulkAction();
            });
        }
        
        // Select all checkbox
        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                this.toggleSelectAll(e.target.checked);
            });
        }
        
        // Individual checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('item-checkbox')) {
                this.updateSelectAllState();
            }
        });
        
        // Auto-refresh toggle
        const autoRefreshToggle = document.getElementById('auto-refresh');
        if (autoRefreshToggle) {
            autoRefreshToggle.addEventListener('change', (e) => {
                this.toggleAutoRefresh(e.target.checked);
            });
        }
        
        // Search functionality
        const searchInput = document.getElementById('search-input');
        if (searchInput) {
            let searchTimeout;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.performSearch(e.target.value);
                }, 500);
            });
        }
        
        // Filter functionality
        const filterInputs = document.querySelectorAll('.filter-input');
        filterInputs.forEach(input => {
            input.addEventListener('change', () => {
                this.applyFilters();
            });
        });
    }
    
    initializeComponents() {
        // Initialize date pickers
        this.initializeDatePickers();
        
        // Initialize tooltips
        this.initializeTooltips();
        
        // Initialize modals
        this.initializeModals();
        
        // Initialize charts if Chart.js is available
        if (typeof Chart !== 'undefined') {
            this.initializeCharts();
        }
        
        // Initialize real-time updates
        this.initializeRealTimeUpdates();
    }
    
    initializeDatePickers() {
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            // Set max date to today for past dates
            if (input.dataset.maxToday) {
                input.max = new Date().toISOString().split('T')[0];
            }
            
            // Set min date to today for future dates
            if (input.dataset.minToday) {
                input.min = new Date().toISOString().split('T')[0];
            }
        });
    }
    
    initializeTooltips() {
        const tooltipElements = document.querySelectorAll('[data-tooltip]');
        tooltipElements.forEach(element => {
            element.addEventListener('mouseenter', (e) => {
                this.showTooltip(e.target);
            });
            
            element.addEventListener('mouseleave', (e) => {
                this.hideTooltip();
            });
        });
    }
    
    initializeModals() {
        // Close modals when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.closeModal(e.target.closest('.modal'));
            }
        });
        
        // Close modals with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const activeModal = document.querySelector('.modal.active');
                if (activeModal) {
                    this.closeModal(activeModal);
                }
            }
        });
    }
    
    initializeCharts() {
        // Chart configurations will be implemented by specific pages
        this.chartInstances = {};
    }
    
    initializeRealTimeUpdates() {
        // Check for real-time updates every 30 seconds
        this.updateInterval = setInterval(() => {
            this.checkForUpdates();
        }, 30000);
    }
    
    confirmDelete(button) {
        const itemName = button.dataset.itemName || 'this item';
        const confirmMessage = `Are you sure you want to delete ${itemName}? This action cannot be undone.`;
        
        if (confirm(confirmMessage)) {
            const url = button.dataset.deleteUrl;
            const itemId = button.dataset.itemId;
            
            if (url) {
                this.performDelete(url, itemId, button);
            }
        }
    }
    
    performDelete(url, itemId, button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
        button.disabled = true;
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: itemId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row/item from the DOM
                const row = button.closest('tr') || button.closest('.item-card');
                if (row) {
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        this.updateSelectAllState();
                    }, 300);
                }
                
                showToast('Item deleted successfully', 'success');
            } else {
                showToast(data.message || 'Failed to delete item', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            showToast('An error occurred while deleting', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
    
    updateStatus(button) {
        const status = button.dataset.status;
        const itemId = button.dataset.itemId;
        const itemType = button.dataset.itemType;
        const url = button.dataset.updateUrl;
        
        if (!status || !itemId || !url) {
            showToast('Missing required data for status update', 'error');
            return;
        }
        
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'update_status',
                id: itemId,
                status: status,
                type: itemType
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update status badge
                const statusBadge = button.closest('tr')?.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = `status-badge status-${status}`;
                    statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                }
                
                showToast(data.message || 'Status updated successfully', 'success');
                
                // Remove button if it's a one-time action
                if (button.dataset.removeAfter) {
                    button.remove();
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            } else {
                showToast(data.message || 'Failed to update status', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            }
        })
        .catch(error => {
            console.error('Status update error:', error);
            showToast('An error occurred while updating status', 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }
    
    handleBulkAction() {
        const bulkActionSelect = document.getElementById('bulk-action');
        const selectedItems = document.querySelectorAll('.item-checkbox:checked');
        
        if (!bulkActionSelect.value) {
            showToast('Please select an action', 'warning');
            return;
        }
        
        if (selectedItems.length === 0) {
            showToast('Please select at least one item', 'warning');
            return;
        }
        
        const action = bulkActionSelect.value;
        const itemIds = Array.from(selectedItems).map(checkbox => checkbox.value);
        
        if (action === 'delete') {
            const confirmMessage = `Are you sure you want to delete ${itemIds.length} selected item(s)? This action cannot be undone.`;
            if (!confirm(confirmMessage)) {
                return;
            }
        }
        
        this.performBulkAction(action, itemIds);
    }
    
    performBulkAction(action, itemIds) {
        const applyBtn = document.getElementById('apply-bulk');
        const originalText = applyBtn.textContent;
        
        applyBtn.textContent = 'Processing...';
        applyBtn.disabled = true;
        
        const url = applyBtn.dataset.bulkUrl;
        
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: action,
                item_ids: itemIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Bulk action completed successfully', 'success');
                
                // Refresh the page or update the UI
                if (action === 'delete') {
                    itemIds.forEach(id => {
                        const checkbox = document.querySelector(`.item-checkbox[value="${id}"]`);
                        const row = checkbox?.closest('tr') || checkbox?.closest('.item-card');
                        if (row) {
                            row.remove();
                        }
                    });
                    this.updateSelectAllState();
                } else {
                    // For other actions, you might want to refresh the page
                    location.reload();
                }
            } else {
                showToast(data.message || 'Bulk action failed', 'error');
            }
        })
        .catch(error => {
            console.error('Bulk action error:', error);
            showToast('An error occurred during bulk action', 'error');
        })
        .finally(() => {
            applyBtn.textContent = originalText;
            applyBtn.disabled = false;
            
            // Reset form
            document.getElementById('bulk-action').value = '';
            this.toggleSelectAll(false);
        });
    }
    
    toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        
        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = checked;
        }
    }
    
    updateSelectAllState() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        const selectAllCheckbox = document.getElementById('select-all');
        
        if (selectAllCheckbox) {
            if (checkedBoxes.length === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (checkedBoxes.length === checkboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }
    }
    
    toggleAutoRefresh(enabled) {
        if (enabled) {
            this.autoRefreshInterval = setInterval(() => {
                this.refreshCurrentView();
            }, 10000); // Refresh every 10 seconds
            
            showToast('Auto-refresh enabled', 'info');
        } else {
            if (this.autoRefreshInterval) {
                clearInterval(this.autoRefreshInterval);
                this.autoRefreshInterval = null;
            }
            showToast('Auto-refresh disabled', 'info');
        }
    }
    
    performSearch(query) {
        const searchUrl = document.getElementById('search-input')?.dataset.searchUrl;
        
        if (!searchUrl) {
            // Client-side search fallback
            this.performClientSideSearch(query);
            return;
        }
        
        // Server-side search
        fetch(`${searchUrl}?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.updateSearchResults(data.results);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            this.performClientSideSearch(query);
        });
    }
    
    performClientSideSearch(query) {
        const searchableElements = document.querySelectorAll('[data-searchable]');
        const lowerQuery = query.toLowerCase();
        
        searchableElements.forEach(element => {
            const text = element.textContent.toLowerCase();
            const row = element.closest('tr') || element.closest('.item-card');
            
            if (row) {
                if (text.includes(lowerQuery) || query === '') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }
    
    applyFilters() {
        const filterInputs = document.querySelectorAll('.filter-input');
        const filters = {};
        
        filterInputs.forEach(input => {
            if (input.value) {
                filters[input.name] = input.value;
            }
        });
        
        const filterUrl = document.querySelector('.filter-form')?.action;
        
        if (filterUrl) {
            // Server-side filtering
            const params = new URLSearchParams(filters);
            window.location.href = `${filterUrl}?${params.toString()}`;
        } else {
            // Client-side filtering
            this.performClientSideFiltering(filters);
        }
    }
    
    performClientSideFiltering(filters) {
        const filterableElements = document.querySelectorAll('[data-filterable]');
        
        filterableElements.forEach(element => {
            const row = element.closest('tr') || element.closest('.item-card');
            let shouldShow = true;
            
            Object.entries(filters).forEach(([key, value]) => {
                const elementValue = element.dataset[key];
                if (elementValue && elementValue !== value) {
                    shouldShow = false;
                }
            });
            
            if (row) {
                row.style.display = shouldShow ? '' : 'none';
            }
        });
    }
    
    showTooltip(element) {
        const tooltip = document.createElement('div');
        tooltip.className = 'admin-tooltip';
        tooltip.textContent = element.dataset.tooltip;
        
        document.body.appendChild(tooltip);
        
        const rect = element.getBoundingClientRect();
        tooltip.style.position = 'fixed';
        tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
        tooltip.style.left = (rect.left + rect.width / 2 - tooltip.offsetWidth / 2) + 'px';
        tooltip.style.zIndex = '9999';
        tooltip.style.backgroundColor = '#333';
        tooltip.style.color = '#fff';
        tooltip.style.padding = '8px 12px';
        tooltip.style.borderRadius = '4px';
        tooltip.style.fontSize = '12px';
        tooltip.style.opacity = '0';
        tooltip.style.transition = 'opacity 0.2s ease';
        
        setTimeout(() => {
            tooltip.style.opacity = '1';
        }, 10);
        
        this.currentTooltip = tooltip;
    }
    
    hideTooltip() {
        if (this.currentTooltip) {
            this.currentTooltip.remove();
            this.currentTooltip = null;
        }
    }
    
    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    closeModal(modal) {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    checkForUpdates() {
        // Check for new orders, notifications, etc.
        const updateUrl = '/api/check-updates.php';
        
        fetch(updateUrl)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.updates) {
                this.handleUpdates(data.updates);
            }
        })
        .catch(error => {
            console.error('Update check error:', error);
        });
    }
    
    handleUpdates(updates) {
        // Handle different types of updates
        if (updates.new_orders) {
            this.updateOrderNotifications(updates.new_orders);
        }
        
        if (updates.new_reservations) {
            this.updateReservationNotifications(updates.new_reservations);
        }
        
        if (updates.status_changes) {
            this.updateStatusChanges(updates.status_changes);
        }
    }
    
    updateOrderNotifications(newOrders) {
        const notificationCount = document.querySelector('.notification-count');
        if (notificationCount && newOrders > 0) {
            const currentCount = parseInt(notificationCount.textContent) || 0;
            notificationCount.textContent = currentCount + newOrders;
            notificationCount.style.display = 'block';
        }
    }
    
    refreshCurrentView() {
        // Refresh current page data without full reload
        const refreshBtn = document.querySelector('[data-refresh]');
        if (refreshBtn) {
            refreshBtn.click();
        } else {
            location.reload();
        }
    }
    
    exportData(format = 'csv') {
        const exportUrl = document.querySelector('[data-export-url]')?.dataset.exportUrl;
        
        if (!exportUrl) {
            showToast('Export functionality not available', 'warning');
            return;
        }
        
        const params = new URLSearchParams({
            format: format,
            ...this.getCurrentFilters()
        });
        
        window.open(`${exportUrl}?${params.toString()}`, '_blank');
    }
    
    getCurrentFilters() {
        const filters = {};
        const filterInputs = document.querySelectorAll('.filter-input');
        
        filterInputs.forEach(input => {
            if (input.value) {
                filters[input.name] = input.value;
            }
        });
        
        return filters;
    }
    
    // Cleanup method
    destroy() {
        if (this.updateInterval) {
            clearInterval(this.updateInterval);
        }
        
        if (this.autoRefreshInterval) {
            clearInterval(this.autoRefreshInterval);
        }
        
        if (this.currentTooltip) {
            this.currentTooltip.remove();
        }
    }
}

// Initialize admin panel when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.adminPanel = new AdminPanel();
});

// Global admin utility functions
window.adminUtils = {
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    },
    
    formatDate: function(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        };
        
        return new Intl.DateTimeFormat('en-US', { ...defaultOptions, ...options }).format(new Date(date));
    },
    
    formatTime: function(date) {
        return new Intl.DateTimeFormat('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        }).format(new Date(date));
    },
    
    openModal: function(modalId) {
        window.adminPanel.openModal(modalId);
    },
    
    closeModal: function(modal) {
        window.adminPanel.closeModal(modal);
    },
    
    exportData: function(format) {
        window.adminPanel.exportData(format);
    }
};
