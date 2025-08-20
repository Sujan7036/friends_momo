<?php
/**
 * Admin Menu Management
 * Friends and Momos Restaurant Management System
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/models/MenuItem.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../../public/login.php');
    exit();
}

$pageTitle = "Menu Management - Admin Panel";
$currentPage = "menu";

$menuItemModel = new MenuItem();
$message = '';
$messageType = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'add_item':
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => (float)$_POST['price'],
                'category_id' => (int)$_POST['category_id'],
                'is_available' => isset($_POST['is_available']) ? 1 : 0,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'preparation_time' => (int)($_POST['preparation_time'] ?? 15)
            ];
            
            if ($menuItemModel->create($data)) {
                $message = "Menu item added successfully!";
                $messageType = 'success';
            } else {
                $message = "Failed to add menu item.";
                $messageType = 'error';
            }
            break;
            
        case 'update_item':
            $id = (int)$_POST['id'];
            $data = [
                'name' => $_POST['name'],
                'description' => $_POST['description'],
                'price' => (float)$_POST['price'],
                'category_id' => (int)$_POST['category_id'],
                'is_available' => isset($_POST['is_available']) ? 1 : 0,
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'preparation_time' => (int)($_POST['preparation_time'] ?? 15)
            ];
            
            if ($menuItemModel->update($id, $data)) {
                $message = "Menu item updated successfully!";
                $messageType = 'success';
            } else {
                $message = "Failed to update menu item.";
                $messageType = 'error';
            }
            break;
            
        case 'delete_item':
            $id = (int)$_POST['id'];
            
            if ($menuItemModel->delete($id)) {
                $message = "Menu item deleted successfully!";
                $messageType = 'success';
            } else {
                $message = "Failed to delete menu item.";
                $messageType = 'error';
            }
            break;
            
        case 'toggle_availability':
            $id = (int)$_POST['id'];
            $currentStatus = (int)$_POST['current_status'];
            $newStatus = $currentStatus ? 0 : 1;
            
            if ($menuItemModel->update($id, ['is_available' => $newStatus])) {
                $message = "Menu item availability updated!";
                $messageType = 'success';
            } else {
                $message = "Failed to update availability.";
                $messageType = 'error';
            }
            break;
    }
}

// Get filter parameters
$category = $_GET['category'] ?? '';
$availability = $_GET['availability'] ?? '';

// Get menu items with filters
$menuItems = $menuItemModel->getAllWithCategories();

// Filter items based on parameters
if (!empty($category) || !empty($availability)) {
    $menuItems = array_filter($menuItems, function($item) use ($category, $availability) {
        if (!empty($category) && $item['category_id'] != $category) {
            return false;
        }
        if ($availability !== '' && $item['is_available'] != $availability) {
            return false;
        }
        return true;
    });
}

// Get available categories for filter dropdown
$categories = [
    ['id' => 1, 'name' => 'Momos'],
    ['id' => 2, 'name' => 'Chowmein'],
    ['id' => 3, 'name' => 'Snacks'],
    ['id' => 4, 'name' => 'Beverages']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include_once dirname(__DIR__, 2) . '/includes/admin_header.php'; ?>
    
    <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-utensils"></i> Menu Management</h1>
            <button class="btn btn-primary" onclick="toggleModal('addItemModal')">
                <i class="fas fa-plus"></i> Add Menu Item
            </button>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-utensils"></i>
                </div>
                <div class="stat-content">
                    <h3><?= count($menuItems) ?></h3>
                    <p>Total Items</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= count(array_filter($menuItems, function($item) { return $item['is_available']; })) ?></h3>
                    <p>Available Items</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <h3><?= count(array_filter($menuItems, function($item) { return $item['is_featured']; })) ?></h3>
                    <p>Featured Items</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-content">
                    <h3><?= count($categories) ?></h3>
                    <p>Categories</p>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <div class="filter-group">
                    <label for="category">Category:</label>
                    <select name="category" id="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="availability">Availability:</label>
                    <select name="availability" id="availability">
                        <option value="">All Items</option>
                        <option value="1" <?= $availability === '1' ? 'selected' : '' ?>>Available</option>
                        <option value="0" <?= $availability === '0' ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Filter
                </button>
                
                <a href="menu.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>
        
        <!-- Menu Items Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Featured</th>
                        <th>Prep Time</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($menuItems)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No menu items found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($menuItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="item-info">
                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars(substr($item['description'] ?? '', 0, 50)) ?>...</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="category-badge">
                                        <?php 
                                        $catName = 'No Category';
                                        foreach($categories as $cat) {
                                            if($cat['id'] == $item['category_id']) {
                                                $catName = $cat['name'];
                                                break;
                                            }
                                        }
                                        echo htmlspecialchars($catName);
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <strong>$<?= number_format($item['price'], 2) ?></strong>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $item['is_available'] ? 'available' : 'unavailable' ?>">
                                        <?= $item['is_available'] ? 'Available' : 'Unavailable' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($item['is_featured']): ?>
                                        <span class="featured-badge">
                                            <i class="fas fa-star"></i> Featured
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $item['preparation_time'] ?? 15 ?> min
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <!-- Edit Button -->
                                        <button class="btn btn-sm btn-primary" onclick="editItem(<?= htmlspecialchars(json_encode($item)) ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <!-- Toggle Availability -->
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_availability">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <input type="hidden" name="current_status" value="<?= $item['is_available'] ?>">
                                            <button type="submit" class="btn btn-sm <?= $item['is_available'] ? 'btn-warning' : 'btn-success' ?>" 
                                                    title="<?= $item['is_available'] ? 'Make Unavailable' : 'Make Available' ?>">
                                                <i class="fas fa-<?= $item['is_available'] ? 'eye-slash' : 'eye' ?>"></i>
                                            </button>
                                        </form>
                                        
                                        <!-- Delete Button -->
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this item?')">
                                            <input type="hidden" name="action" value="delete_item">
                                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <!-- Add Item Modal -->
    <div id="addItemModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Add Menu Item</h2>
                <button type="button" class="close" onclick="toggleModal('addItemModal')">&times;</button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="add_item">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Item Name *</label>
                        <input type="text" name="name" id="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price *</label>
                        <input type="number" name="price" id="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select name="category_id" id="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="preparation_time">Prep Time (minutes)</label>
                        <input type="number" name="preparation_time" id="preparation_time" value="15" min="1">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="description">Description *</label>
                        <textarea name="description" id="description" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_available" checked> Available
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_featured"> Featured Item
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="toggleModal('addItemModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Item Modal -->
    <div id="editItemModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Menu Item</h2>
                <button type="button" class="close" onclick="toggleModal('editItemModal')">&times;</button>
            </div>
            
            <form method="POST">
                <input type="hidden" name="action" value="update_item">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_name">Item Name *</label>
                        <input type="text" name="name" id="edit_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_price">Price *</label>
                        <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_category_id">Category *</label>
                        <select name="category_id" id="edit_category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_preparation_time">Prep Time (minutes)</label>
                        <input type="number" name="preparation_time" id="edit_preparation_time" min="1">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="edit_description">Description *</label>
                        <textarea name="description" id="edit_description" rows="3" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_available" id="edit_is_available"> Available
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_featured" id="edit_is_featured"> Featured Item
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="toggleModal('editItemModal')">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Modal management
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            if (modal.style.display === 'none' || modal.style.display === '') {
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            } else {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        }
    }

    // Edit item function
    function editItem(item) {
        // Populate edit form with item data
        document.getElementById('edit_id').value = item.id;
        document.getElementById('edit_name').value = item.name;
        document.getElementById('edit_description').value = item.description || '';
        document.getElementById('edit_price').value = item.price;
        document.getElementById('edit_category_id').value = item.category_id;
        document.getElementById('edit_preparation_time').value = item.preparation_time || 15;
        document.getElementById('edit_is_available').checked = item.is_available == 1;
        document.getElementById('edit_is_featured').checked = item.is_featured == 1;
        
        // Show edit modal
        toggleModal('editItemModal');
    }

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });

    // Close modal with escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                }
            });
            document.body.style.overflow = 'auto';
        }
    });

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            }, 5000);
        });
    });
    </script>
</body>
</html>
