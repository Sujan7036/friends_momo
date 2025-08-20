// Staff Dashboard JavaScript Functions

// Refresh pending orders
function refreshPendingOrders() {
    location.reload();
}

// Refresh active orders  
function refreshActiveOrders() {
    location.reload();
}

// View order items modal
function viewOrderItems(orderId) {
    const modal = document.getElementById('orderItemsModal');
    const content = document.getElementById('orderItemsContent');
    
    // Show loading
    content.innerHTML = '<div class="loading">Loading order items...</div>';
    modal.style.display = 'block';
    
    // Fetch order items via AJAX
    fetch(`/api/order-items.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrderItems(data.items, data.order);
            } else {
                content.innerHTML = '<div class="error">Failed to load order items</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="error">Error loading order items</div>';
        });
}

// Display order items in modal
function displayOrderItems(items, order) {
    const content = document.getElementById('orderItemsContent');
    
    let html = `
        <div class="order-info">
            <h4>Order #${String(order.id).padStart(4, '0')}</h4>
            <p><strong>Customer:</strong> ${order.customer_name}</p>
            <p><strong>Type:</strong> ${order.order_type.replace('_', ' ')}</p>
            <p><strong>Status:</strong> ${order.status}</p>
        </div>
        <div class="order-items-list">
            <h5>Items:</h5>
    `;
    
    items.forEach(item => {
        html += `
            <div class="order-item">
                <div class="item-details">
                    <span class="item-name">${item.menu_item_name}</span>
                    <span class="item-quantity">x${item.quantity}</span>
                </div>
                <div class="item-price">₹${parseFloat(item.price).toFixed(2)}</div>
            </div>
        `;
    });
    
    html += `
        </div>
        <div class="order-total">
            <strong>Total: ₹${parseFloat(order.total_amount).toFixed(2)}</strong>
        </div>
    `;
    
    content.innerHTML = html;
}

// Modal functionality
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('orderItemsModal');
    const closeBtn = modal.querySelector('.close');
    
    // Close modal when clicking X
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
});

// Timer functionality for active orders
function updateTimers() {
    const timers = document.querySelectorAll('.timer');
    
    timers.forEach(timer => {
        const startTime = parseInt(timer.dataset.start) * 1000; // Convert to milliseconds
        const now = Date.now();
        const elapsed = Math.floor((now - startTime) / 1000); // Convert to seconds
        
        const minutes = Math.floor(elapsed / 60);
        const seconds = elapsed % 60;
        
        timer.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
    });
}

// Update timers every second
setInterval(updateTimers, 1000);

// Update current time in header
function updateCurrentTime() {
    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        timeElement.textContent = timeString;
    }
}

// Update time every second
setInterval(updateCurrentTime, 1000);
updateCurrentTime(); // Initial call

// Auto-refresh dashboard every 30 seconds
setInterval(() => {
    // Only refresh if no modal is open
    const modal = document.getElementById('orderItemsModal');
    if (modal.style.display !== 'block') {
        location.reload();
    }
}, 30000);
