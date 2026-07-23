<?php
/**
 * Admin - Product Management (Full CRUD)
 * Clean SaaS Minimalist Design
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

$path_prefix = '../../';
$page_title = 'Manage Products - Admin';

include __DIR__ . '/includes/admin_header.php';

// Check admin access
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager'], true)) {
    echo "<script>window.location.href = '/busiaadmin';</script>";
    exit;
}

$pdo = getDB();
$success_message = '';
$error_message = '';
$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] ?? '');

// --- Handle POST actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security token expired. Please refresh and try again.';
    } else {
    $action = $_POST['action'] ?? '';

    // Image Upload Helper
    $handleImageUpload = function($file) {
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) return null;
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Image upload failed.');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException('Image must be 5MB or smaller.');
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']) ?: '';
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new RuntimeException('Only JPG, PNG, WEBP, and GIF images are allowed.');
        }

        $target_dir = __DIR__ . '/../images/products/';
        if (!is_dir($target_dir) && !mkdir($target_dir, 0755, true) && !is_dir($target_dir)) {
            throw new RuntimeException('Unable to create upload directory.');
        }

        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file_ext === '') {
            throw new RuntimeException('Uploaded image must have a file extension.');
        }

        $new_filename = uniqid('prod_', true) . '.' . $file_ext;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return '/Frontend/images/products/' . $new_filename;
        }
        throw new RuntimeException('Unable to save uploaded image.');
    };

    if ($action === 'add_product') {
        try {
            $image_url = $handleImageUpload($_FILES['product_image'] ?? null);
            $stmt = $pdo->prepare("INSERT INTO products (category_id, name, slug, product_type, price, stock_quantity, description, image_url, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($_POST['name'])));
            $stmt->execute([
                (int)$_POST['category_id'],
                trim($_POST['name']),
                $slug,
                $_POST['product_type'],
                (float)$_POST['price'],
                (int)$_POST['stock_quantity'],
                trim($_POST['description'] ?? ''),
                $image_url
            ]);
            $success_message = 'Product added successfully.';
        } catch (Exception $e) {
            $error_message = 'Failed to add product: ' . $e->getMessage();
        }
    }

    if ($action === 'edit_product') {
        try {
            $image_url = $handleImageUpload($_FILES['product_image'] ?? null);
            $sql = "UPDATE products SET name = ?, product_type = ?, price = ?, stock_quantity = ?, description = ?, category_id = ?";
            $params = [
                trim($_POST['name']),
                $_POST['product_type'],
                (float)$_POST['price'],
                (int)$_POST['stock_quantity'],
                trim($_POST['description'] ?? ''),
                (int)$_POST['category_id']
            ];
            
            if ($image_url) {
                $sql .= ", image_url = ?";
                $params[] = $image_url;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = (int)$_POST['product_id'];
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $success_message = 'Product updated successfully.';
        } catch (Exception $e) {
            $error_message = 'Failed to update product: ' . $e->getMessage();
        }
    }

    if ($action === 'delete_product') {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([(int)$_POST['product_id']]);
            $success_message = 'Product deleted successfully.';
        } catch (Exception $e) {
            $error_message = 'Failed to delete product: ' . $e->getMessage();
        }
    }

    if ($action === 'toggle_status') {
        try {
            $stmt = $pdo->prepare("UPDATE products SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([(int)$_POST['product_id']]);
            $success_message = 'Product status toggled.';
        } catch (Exception $e) {
            $error_message = 'Failed to toggle status.';
        }
    }
    }
}

// --- Fetch products with search/filter ---
$products = [];
$categories = [];
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';

if ($pdo) {
    try {
        // Fetch categories for dropdowns
        $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

        // Build product query
        $query = "SELECT p.*, c.name as category_name, fr.id as recipe_id 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  LEFT JOIN feed_recipes fr ON fr.product_id = p.id
                  WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $query .= " AND p.name LIKE ?";
            $params[] = "%$search%";
        }
        if (!empty($type_filter)) {
            $query .= " AND p.product_type = ?";
            $params[] = $type_filter;
        }

        $query .= " ORDER BY p.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Admin products error: " . $e->getMessage());
    }
}
?>

<!-- Alerts -->
<?php if ($success_message): ?>
<div style="padding: 12px 20px; background: #dcfce7; border: 1px solid #bbf7d0; border-radius: 4px; color: #15803d; font-size: 0.9rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
    <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
    <?php echo htmlspecialchars($success_message); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding: 12px 20px; background: #fee2e2; border: 1px solid #fecaca; border-radius: 4px; color: #b91c1c; font-size: 0.9rem; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
    <i data-lucide="alert-circle" style="width: 16px; height: 16px;"></i>
    <?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.5rem; color: var(--admin-text-heading);">Product Catalog</h2>
        <p style="margin: 4px 0 0 0; font-size: 0.875rem; color: #475569;">Add, edit, and monitor your farm inventory levels.</p>
    </div>
    <button onclick="document.getElementById('add-modal').style.display='flex'" class="btn btn-primary" style="border-radius: 4px; display: flex; align-items: center; gap: 8px;">
        <i data-lucide="plus" style="width: 18px; height: 18px;"></i>
        <span>Add Product</span>
    </button>
</div>

<div class="admin-card" style="padding: 0; overflow: hidden; border-radius: 4px;">
    <!-- Search & Filter Bar -->
    <form method="GET" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; background: #fafafa; border-bottom: 1px solid var(--admin-border); flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px; flex: 1; min-width: 200px;">
            <i data-lucide="search" style="width: 18px; height: 18px; color: #94a3b8;"></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products..." style="background: transparent; border: none; outline: none; font-size: 0.9rem; width: 100%;">
        </div>
        <div style="display: flex; gap: 8px;">
            <select name="type" style="padding: 6px 12px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.85rem; outline: none; background: #ffffff;">
                <option value="">All Types</option>
                <option value="live_chicken" <?php echo $type_filter === 'live_chicken' ? 'selected' : ''; ?>>Live Chicken</option>
                <option value="feed" <?php echo $type_filter === 'feed' ? 'selected' : ''; ?>>Feeds</option>
                <option value="chicks" <?php echo $type_filter === 'chicks' ? 'selected' : ''; ?>>Chicks</option>
                <option value="eggs" <?php echo $type_filter === 'eggs' ? 'selected' : ''; ?>>Eggs</option>
                <option value="meat" <?php echo $type_filter === 'meat' ? 'selected' : ''; ?>>Meat</option>
            </select>
            <button type="submit" class="btn btn-outline" style="border-radius: 4px; padding: 6px 16px; font-size: 0.85rem;">Filter</button>
            <?php if ($search || $type_filter): ?>
                <a href="products.php" style="padding: 6px 12px; font-size: 0.85rem; color: #64748b; text-decoration: none;">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Product Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Stock Level</th>
                    <th>Stock Brain</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 48px; height: 48px; background: #f1f5f9; border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center; border: 1px solid var(--admin-border);">
                                <?php if ($product['image_url']): ?>
                                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i data-lucide="package" style="width: 20px; height: 20px; color: #94a3b8;"></i>
                                <?php endif; ?>
                            </div>
                            <div style="font-weight: 600; color: var(--admin-text-heading);"><?php echo htmlspecialchars($product['name']); ?></div>
                        </div>
                    </td>
                    <td style="color: #475569; font-size: 0.9rem;">
                        <?php echo ucfirst(str_replace('_', ' ', $product['product_type'] ?? 'General')); ?>
                    </td>
                    <td style="font-weight: 600; color: var(--admin-text-heading);">
                        KES <?php echo number_format((float)$product['price']); ?>
                    </td>
                    <td>
                        <span style="font-weight: 500; <?php echo ($product['stock_quantity'] < 10) ? 'color: #dc2626; font-weight: 600;' : 'color: #475569;'; ?>">
                            <?php echo $product['stock_quantity']; ?> units
                        </span>
                    </td>
                    <td>
                        <?php if ($product['product_type'] === 'feed'): ?>
                            <?php if ($product['recipe_id']): ?>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <a href="stock_formula_center.php" class="badge-pill badge-pill-success" style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; justify-content: center;">
                                        <i data-lucide="brain-circuit" style="width: 12px; height: 12px;"></i> Linked
                                    </a>
                                    <a href="stock_formula_center.php" class="btn btn-trans btn-sm" style="font-size: 0.7rem; padding: 2px 4px; color: var(--admin-primary); border: 1px solid var(--admin-primary);">
                                        <i data-lucide="package-plus" style="width: 12px; height: 12px;"></i> Produce
                                    </a>
                                </div>
                            <?php else: ?>
                                <a href="stock_formula_center.php" class="badge-pill badge-pill-warning" style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                    <i data-lucide="alert-circle" style="width: 12px; height: 12px;"></i> No Recipe
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: #94a3b8; font-size: 0.8rem;">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" style="background: none; border: none; cursor: pointer; padding: 0;">
                                <?php if ($product['is_active']): ?>
                                    <span class="badge-pill badge-pill-success">Active</span>
                                <?php else: ?>
                                    <span class="badge-pill badge-pill-danger">Inactive</span>
                                <?php endif; ?>
                            </button>
                        </form>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button title="Edit" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($product)); ?>)" style="background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; transition: color 0.2s;" onmouseover="this.style.color='var(--admin-primary)'" onmouseout="this.style.color='#94a3b8'">
                                <i data-lucide="edit-3" style="width: 16px; height: 16px;"></i>
                            </button>
                            <form method="POST" onsubmit="return confirm('Delete this product permanently?');" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="action" value="delete_product">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <button type="submit" title="Delete" style="background: none; border: none; cursor: pointer; color: #94a3b8; padding: 4px; transition: color 0.2s;" onmouseover="this.style.color='#dc2626'" onmouseout="this.style.color='#94a3b8'">
                                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">No products found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ========== ADD PRODUCT MODAL ========== -->
<div id="add-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 4px; width: 100%; max-width: 560px; padding: 32px; box-shadow: 0 24px 48px rgba(0,0,0,0.15); position: relative;">
        <button onclick="document.getElementById('add-modal').style.display='none'" style="position: absolute; top: 16px; right: 16px; background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 1.2rem;">✕</button>
        <h3 style="margin: 0 0 24px 0; font-family: 'Outfit', sans-serif; font-size: 1.25rem; color: var(--admin-text-heading);">Add New Product</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="add_product">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="admin-form-group" style="grid-column: span 2;">
                    <label class="admin-form-label">Product Image</label>
                    <div style="display: flex; align-items: center; gap: 16px; border: 1px dashed #cbd5e1; padding: 16px; border-radius: 4px;">
                        <div id="add-preview" style="width: 60px; height: 60px; background: #f8fafc; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #e2e8f0;">
                            <i data-lucide="image" style="width: 24px; height: 24px; color: #94a3b8;"></i>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="product_image" accept="image/*" onchange="previewImage(this, 'add-preview')" style="font-size: 0.8rem;">
                            <p style="margin: 4px 0 0 0; font-size: 0.7rem; color: #64748b;">Recommended: Square image, max 2MB.</p>
                        </div>
                    </div>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Product Name *</label>
                    <input type="text" name="name" required class="admin-form-control">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Category *</label>
                    <select name="category_id" required class="admin-form-control">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Product Type *</label>
                    <select name="product_type" required class="admin-form-control">
                        <option value="live_chicken">Live Chicken</option>
                        <option value="feed">Feed</option>
                        <option value="chicks">Chicks</option>
                        <option value="eggs">Eggs</option>
                        <option value="meat">Meat</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Price (KES) *</label>
                    <input type="number" name="price" required min="0" step="0.01" class="admin-form-control">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" required min="0" class="admin-form-control">
                </div>
            </div>
            <div class="admin-form-group" style="margin-top: 8px;">
                <label class="admin-form-label">Description</label>
                <textarea name="description" rows="3" class="admin-form-control" style="resize: vertical;"></textarea>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="document.getElementById('add-modal').style.display='none'" class="btn btn-outline" style="border-radius: 4px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 4px;">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- ========== EDIT PRODUCT MODAL ========== -->
<div id="edit-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 4px; width: 100%; max-width: 560px; padding: 32px; box-shadow: 0 24px 48px rgba(0,0,0,0.15); position: relative;">
        <button onclick="document.getElementById('edit-modal').style.display='none'" style="position: absolute; top: 16px; right: 16px; background: none; border: none; cursor: pointer; color: #94a3b8; font-size: 1.2rem;">✕</button>
        <h3 style="margin: 0 0 24px 0; font-family: 'Outfit', sans-serif; font-size: 1.25rem; color: var(--admin-text-heading);">Edit Product</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="action" value="edit_product">
            <input type="hidden" name="product_id" id="edit-id">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="admin-form-group" style="grid-column: span 2;">
                    <label class="admin-form-label">Update Product Image</label>
                    <div style="display: flex; align-items: center; gap: 16px; border: 1px dashed #cbd5e1; padding: 16px; border-radius: 4px;">
                        <div id="edit-preview" style="width: 60px; height: 60px; background: #f8fafc; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #e2e8f0;">
                            <i data-lucide="image" style="width: 24px; height: 24px; color: #94a3b8;"></i>
                        </div>
                        <div style="flex: 1;">
                            <input type="file" name="product_image" accept="image/*" onchange="previewImage(this, 'edit-preview')" style="font-size: 0.8rem;">
                            <p style="margin: 4px 0 0 0; font-size: 0.7rem; color: #64748b;">Leave empty to keep current image.</p>
                        </div>
                    </div>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Product Name *</label>
                    <input type="text" name="name" id="edit-name" required class="admin-form-control">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Category *</label>
                    <select name="category_id" id="edit-category" required class="admin-form-control">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Product Type *</label>
                    <select name="product_type" id="edit-type" required class="admin-form-control">
                        <option value="live_chicken">Live Chicken</option>
                        <option value="feed">Feed</option>
                        <option value="chicks">Chicks</option>
                        <option value="eggs">Eggs</option>
                        <option value="meat">Meat</option>
                    </select>
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Price (KES) *</label>
                    <input type="number" name="price" id="edit-price" required min="0" step="0.01" class="admin-form-control">
                </div>
                <div class="admin-form-group">
                    <label class="admin-form-label">Stock Quantity *</label>
                    <input type="number" name="stock_quantity" id="edit-stock" required min="0" class="admin-form-control">
                </div>
            </div>
            <div class="admin-form-group" style="margin-top: 8px;">
                <label class="admin-form-label">Description</label>
                <textarea name="description" id="edit-desc" rows="3" class="admin-form-control" style="resize: vertical;"></textarea>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="document.getElementById('edit-modal').style.display='none'" class="btn btn-outline" style="border-radius: 4px;">Cancel</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 4px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function openEditModal(product) {
    document.getElementById('edit-id').value = product.id;
    document.getElementById('edit-name').value = product.name;
    document.getElementById('edit-category').value = product.category_id;
    document.getElementById('edit-type').value = product.product_type;
    document.getElementById('edit-price').value = product.price;
    document.getElementById('edit-stock').value = product.stock_quantity;
    document.getElementById('edit-desc').value = product.description || '';
    
    // Set preview
    const preview = document.getElementById('edit-preview');
    if (product.image_url) {
        preview.innerHTML = `<img src="${product.image_url}" style="width: 100%; height: 100%; object-fit: cover;">`;
    } else {
        preview.innerHTML = `<i data-lucide="image" style="width: 24px; height: 24px; color: #94a3b8;"></i>`;
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }
    
    document.getElementById('edit-modal').style.display = 'flex';
}
</script>

<?php
include __DIR__ . '/includes/admin_footer.php';
?>
