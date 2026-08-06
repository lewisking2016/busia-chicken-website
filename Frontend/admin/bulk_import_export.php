<?php
/**
 * Admin - Bulk Import/Export Module
 * Handles CSV/Excel import and export for all major data entities
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) {
    session_save_path($temp_dir);
}
session_start();

$path_prefix = '../../';
$page_title = 'Bulk Import/Export - Admin';

include __DIR__ . '/includes/admin_header.php';
require_once __DIR__ . '/../../Backend/api/dropdowns.php';

// Check admin access
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin','farm_manager'], true)) {
    echo "<script>window.location.href = '/busiaadmin';</script>";
    exit;
}

$pdo = getDB();
$success_message = '';
$error_message = '';
$csrf_token = function_exists('generateCSRFToken') ? generateCSRFToken() : ($_SESSION['csrf_token'] ?? '');

// Handle Export Actions
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['export'])) {
    $export_type = $_GET['export'];
    
    try {
        switch ($export_type) {
            case 'products':
                exportProducts($pdo);
                break;
            case 'orders':
                exportOrders($pdo);
                break;
            case 'customers':
                exportCustomers($pdo);
                break;
            case 'raw_materials':
                exportRawMaterials($pdo);
                break;
            case 'flocks':
                exportFlocks($pdo);
                break;
            case 'expenses':
                exportExpenses($pdo);
                break;
            default:
                die('Invalid export type');
        }
    } catch (Exception $e) {
        die('Export failed: ' . $e->getMessage());
    }
    exit;
}

// Handle Import Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'import') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security token expired. Please refresh and try again.';
    } else {
        $import_type = $_POST['import_type'] ?? '';
        
        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $error_message = 'Please select a valid CSV file to upload.';
        } else {
            try {
                $file_path = $_FILES['import_file']['tmp_name'];
                
                switch ($import_type) {
                    case 'products':
                        $result = importProducts($pdo, $file_path);
                        break;
                    case 'customers':
                        $result = importCustomers($pdo, $file_path);
                        break;
                    case 'raw_materials':
                        $result = importRawMaterials($pdo, $file_path);
                        break;
                    case 'flocks':
                        $result = importFlocks($pdo, $file_path);
                        break;
                    case 'expenses':
                        $result = importExpenses($pdo, $file_path);
                        break;
                    default:
                        throw new Exception('Invalid import type');
                }
                
                $success_message = $result['success'] . ' records imported successfully. ' . ($result['errors'] > 0 ? $result['errors'] . ' errors.' : '');
            } catch (Exception $e) {
                $error_message = 'Import failed: ' . $e->getMessage();
            }
        }
    }
}

// ==================== EXPORT FUNCTIONS ====================

function exportProducts($pdo) {
    $stmt = $pdo->query("
        SELECT p.id, p.name, c.name as category, p.product_type, p.price, p.stock_quantity, 
               p.description, p.is_active, p.created_at
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.id
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    outputCSV('products_export', [
        'ID', 'Name', 'Category', 'Type', 'Price', 'Stock', 'Description', 'Active', 'Created'
    ], $rows);
}

function exportOrders($pdo) {
    $stmt = $pdo->query("
        SELECT o.id, o.order_number, u.username, u.email, o.total_amount, o.payment_method, 
               o.status, o.shipping_address, o.phone_contact, o.created_at
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    outputCSV('orders_export', [
        'ID', 'Order#', 'Customer', 'Email', 'Amount', 'Payment', 'Status', 'Address', 'Phone', 'Date'
    ], $rows);
}

function exportCustomers($pdo) {
    $stmt = $pdo->query("
        SELECT id, username, email, first_name, last_name, phone_number, role, 
               is_active, created_at
        FROM users
        WHERE role IN ('customer', 'demo')
        ORDER BY created_at DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    outputCSV('customers_export', [
        'ID', 'Username', 'Email', 'First Name', 'Last Name', 'Phone', 'Role', 'Active', 'Registered'
    ], $rows);
}

function exportRawMaterials($pdo) {
    $stmt = $pdo->query("
        SELECT id, name, stock_tons, current_price_per_ton, min_stock_level, 
               created_at, updated_at
        FROM raw_materials
        ORDER BY name
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    outputCSV('raw_materials_export', [
        'ID', 'Name', 'Stock (tons)', 'Price/ton', 'Min Stock Level', 'Created', 'Updated'
    ], $rows);
}

function exportFlocks($pdo) {
    $stmt = $pdo->query("
        SELECT id, flock_name, breed, initial_count, current_count, 
               hatch_date, status
        FROM flocks
        ORDER BY hatch_date DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    outputCSV('flocks_export', [
        'ID', 'Flock Name', 'Breed', 'Initial Count', 'Current Count', 'Hatch Date', 'Status'
    ], $rows);
}

function exportExpenses($pdo) {
    $stmt = $pdo->query("
        SELECT id, category, description, amount, transaction_date, 
               payment_method, created_at
        FROM financial_records
        WHERE type = 'expense'
        ORDER BY transaction_date DESC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    outputCSV('expenses_export', [
        'ID', 'Category', 'Description', 'Amount', 'Date', 'Payment Method', 'Created'
    ], $rows);
}

function outputCSV($filename, $headers, $rows) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, $headers);
    
    foreach ($rows as $row) {
        fputcsv($output, array_values($row));
    }
    
    fclose($output);
}

// ==================== IMPORT FUNCTIONS ====================

function importProducts($pdo, $file_path) {
    $success = 0;
    $errors = 0;
    
    if (($handle = fopen($file_path, 'r')) !== FALSE) {
        $headers = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            try {
                // Expected: Name, Category, Type, Price, Stock, Description
                $name = trim($data[0] ?? '');
                $category_name = trim($data[1] ?? '');
                $type = trim($data[2] ?? 'general');
                $price = (float)($data[3] ?? 0);
                $stock = (int)($data[4] ?? 0);
                $description = trim($data[5] ?? '');
                
                if (empty($name)) {
                    $errors++;
                    continue;
                }
                
                // Get category ID
                $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
                $stmt->execute([$category_name]);
                $category = $stmt->fetch();
                $category_id = $category ? $category['id'] : 1;
                
                $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
                
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, slug, category_id, product_type, price, stock_quantity, description, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                    ON DUPLICATE KEY UPDATE 
                    price = VALUES(price), stock_quantity = VALUES(stock_quantity), description = VALUES(description)
                ");
                $stmt->execute([$name, $slug, $category_id, $type, $price, $stock, $description]);
                $success++;
            } catch (Exception $e) {
                $errors++;
            }
        }
        fclose($handle);
    }
    
    return ['success' => $success, 'errors' => $errors];
}

function importCustomers($pdo, $file_path) {
    $success = 0;
    $errors = 0;
    
    if (($handle = fopen($file_path, 'r')) !== FALSE) {
        $headers = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            try {
                // Expected: Username, Email, First Name, Last Name, Phone
                $username = trim($data[0] ?? '');
                $email = trim($data[1] ?? '');
                $first_name = trim($data[2] ?? '');
                $last_name = trim($data[3] ?? '');
                $phone = trim($data[4] ?? '');
                
                if (empty($username) || empty($email)) {
                    $errors++;
                    continue;
                }
                
                $password_hash = password_hash('password123', PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password_hash, first_name, last_name, phone_number, role, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, 'customer', 1)
                    ON DUPLICATE KEY UPDATE 
                    first_name = VALUES(first_name), last_name = VALUES(last_name), phone_number = VALUES(phone_number)
                ");
                $stmt->execute([$username, $email, $password_hash, $first_name, $last_name, $phone]);
                $success++;
            } catch (Exception $e) {
                $errors++;
            }
        }
        fclose($handle);
    }
    
    return ['success' => $success, 'errors' => $errors];
}

function importRawMaterials($pdo, $file_path) {
    $success = 0;
    $errors = 0;
    
    if (($handle = fopen($file_path, 'r')) !== FALSE) {
        $headers = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            try {
                // Expected: Name, Stock (tons), Price/ton, Min Stock Level
                $name = trim($data[0] ?? '');
                $stock_tons = (float)($data[1] ?? 0);
                $price_per_ton = (float)($data[2] ?? 0);
                $min_stock_level = (float)($data[3] ?? 1.0);
                
                if (empty($name)) {
                    $errors++;
                    continue;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO raw_materials (name, stock_tons, current_price_per_ton, min_stock_level)
                    VALUES (?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    stock_tons = VALUES(stock_tons), current_price_per_ton = VALUES(current_price_per_ton), 
                    min_stock_level = VALUES(min_stock_level)
                ");
                $stmt->execute([$name, $stock_tons, $price_per_ton, $min_stock_level]);
                $success++;
            } catch (Exception $e) {
                $errors++;
            }
        }
        fclose($handle);
    }
    
    return ['success' => $success, 'errors' => $errors];
}

function importFlocks($pdo, $file_path) {
    $success = 0;
    $errors = 0;
    
    if (($handle = fopen($file_path, 'r')) !== FALSE) {
        $headers = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            try {
                // Expected: Flock Name, Breed, Initial Count, Current Count, Hatch Date (YYYY-MM-DD), Status
                $flock_name = trim($data[0] ?? '');
                $breed = trim($data[1] ?? '');
                $initial_count = (int)($data[2] ?? 0);
                $current_count = (int)($data[3] ?? 0);
                $hatch_date = trim($data[4] ?? date('Y-m-d'));
                $status = trim($data[5] ?? 'active');
                
                if (empty($flock_name)) {
                    $errors++;
                    continue;
                }
                
                // If current_count not provided, use initial_count
                if ($current_count === 0 && $initial_count > 0) {
                    $current_count = $initial_count;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO flocks (flock_name, breed, initial_count, current_count, hatch_date, status)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$flock_name, $breed, $initial_count, $current_count, $hatch_date, $status]);
                $success++;
            } catch (Exception $e) {
                $errors++;
            }
        }
        fclose($handle);
    }
    
    return ['success' => $success, 'errors' => $errors];
}

function importExpenses($pdo, $file_path) {
    $success = 0;
    $errors = 0;
    
    if (($handle = fopen($file_path, 'r')) !== FALSE) {
        $headers = fgetcsv($handle);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            try {
                // Expected: Category, Description, Amount, Date (YYYY-MM-DD), Payment Method
                $category = trim($data[0] ?? '');
                $description = trim($data[1] ?? '');
                $amount = (float)($data[2] ?? 0);
                $transaction_date = trim($data[3] ?? date('Y-m-d'));
                $payment_method = trim($data[4] ?? 'cash');
                
                if (empty($description) || $amount <= 0) {
                    $errors++;
                    continue;
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO financial_records (type, category, description, amount, transaction_date, payment_method)
                    VALUES ('expense', ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$category, $description, $amount, $transaction_date, $payment_method]);
                $success++;
            } catch (Exception $e) {
                $errors++;
            }
        }
        fclose($handle);
    }
    
    return ['success' => $success, 'errors' => $errors];
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
<div style="margin-bottom: 32px;">
    <h2 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.5rem; color: var(--admin-text-heading);">Bulk Import & Export</h2>
    <p style="margin: 4px 0 0 0; font-size: 0.875rem; color: #475569;">Mass data operations for products, customers, orders, raw materials, flocks, and expenses.</p>
</div>

<!-- Export Section -->
<div class="admin-card" style="margin-bottom: 32px;">
    <div style="border-bottom: 1px solid var(--admin-border); padding-bottom: 16px; margin-bottom: 24px;">
        <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.15rem; color: var(--admin-text-heading); display: flex; align-items: center; gap: 8px;">
            <i data-lucide="download" style="width: 20px; height: 20px; color: var(--admin-primary);"></i>
            Export Data to CSV
        </h3>
        <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: #64748b;">Download current data as CSV files for backup or analysis in Excel.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 4px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i data-lucide="package" style="width: 18px; height: 18px; color: var(--admin-primary);"></i>
                    <h4 style="margin: 0; font-weight: 700; font-size: 0.95rem; color: var(--admin-text-heading);">Products</h4>
                </div>
                <p style="margin: 0; font-size: 0.8rem; color: #64748b;">All products with categories, pricing, and stock levels.</p>
            </div>
            <a href="?export=products" class="btn btn-primary btn-sm" style="margin-top: 16px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                Export Products
            </a>
        </div>
        
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 4px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i data-lucide="shopping-cart" style="width: 18px; height: 18px; color: var(--admin-primary);"></i>
                    <h4 style="margin: 0; font-weight: 700; font-size: 0.95rem; color: var(--admin-text-heading);">Orders</h4>
                </div>
                <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Complete order history with customer details and payment info.</p>
            </div>
            <a href="?export=orders" class="btn btn-primary btn-sm" style="margin-top: 16px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                Export Orders
            </a>
        </div>
        
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 4px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i data-lucide="users" style="width: 18px; height: 18px; color: var(--admin-primary);"></i>
                    <h4 style="margin: 0; font-weight: 700; font-size: 0.95rem; color: var(--admin-text-heading);">Customers</h4>
                </div>
                <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Customer database with contact information and accounts.</p>
            </div>
            <a href="?export=customers" class="btn btn-primary btn-sm" style="margin-top: 16px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                Export Customers
            </a>
        </div>
        
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 4px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i data-lucide="layers" style="width: 18px; height: 18px; color: var(--admin-primary);"></i>
                    <h4 style="margin: 0; font-weight: 700; font-size: 0.95rem; color: var(--admin-text-heading);">Raw Materials</h4>
                </div>
                <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Ingredient stock levels and pricing data.</p>
            </div>
            <a href="?export=raw_materials" class="btn btn-primary btn-sm" style="margin-top: 16px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                Export Raw Materials
            </a>
        </div>
        
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 4px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i data-lucide="bird" style="width: 18px; height: 18px; color: var(--admin-primary);"></i>
                    <h4 style="margin: 0; font-weight: 700; font-size: 0.95rem; color: var(--admin-text-heading);">Flocks</h4>
                </div>
                <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Poultry flock records with breeds, counts, and locations.</p>
            </div>
            <a href="?export=flocks" class="btn btn-primary btn-sm" style="margin-top: 16px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                Export Flocks
            </a>
        </div>
        
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 4px; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                    <i data-lucide="dollar-sign" style="width: 18px; height: 18px; color: var(--admin-primary);"></i>
                    <h4 style="margin: 0; font-weight: 700; font-size: 0.95rem; color: var(--admin-text-heading);">Expenses</h4>
                </div>
                <p style="margin: 0; font-size: 0.8rem; color: #64748b;">Farm expenses with categories, vendors, and dates.</p>
            </div>
            <a href="?export=expenses" class="btn btn-primary btn-sm" style="margin-top: 16px; text-decoration: none; text-align: center; display: flex; align-items: center; justify-content: center; gap: 6px;">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                Export Expenses
            </a>
        </div>
    </div>
</div>

<!-- Import Section -->
<div class="admin-card">
    <div style="border-bottom: 1px solid var(--admin-border); padding-bottom: 16px; margin-bottom: 24px;">
        <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.15rem; color: var(--admin-text-heading); display: flex; align-items: center; gap: 8px;">
            <i data-lucide="upload" style="width: 20px; height: 20px; color: var(--admin-primary);"></i>
            Import Data from CSV
        </h3>
        <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: #64748b;">Upload CSV files to bulk import data. Download export templates to see required format.</p>
    </div>
    
    <form method="POST" enctype="multipart/form-data" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 28px; border-radius: 4px;">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="action" value="import">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 24px;">
            <div class="admin-form-group">
                <label class="admin-form-label" style="margin-bottom: 8px; display: block; font-weight: 600;">Import Type</label>
                <select name="import_type" required class="admin-form-control" style="width: 100%;">
                    <option value="">Select Data Type...</option>
                    <option value="products">Products</option>
                    <option value="customers">Customers</option>
                    <option value="raw_materials">Raw Materials</option>
                    <option value="flocks">Flocks</option>
                    <option value="expenses">Expenses</option>
                </select>
            </div>
            
            <div class="admin-form-group">
                <label class="admin-form-label" style="margin-bottom: 8px; display: block; font-weight: 600;">CSV File</label>
                <input type="file" name="import_file" accept=".csv" required class="admin-form-control" style="width: 100%;">
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px;">
            <i data-lucide="upload" style="width: 18px; height: 18px;"></i>
            Upload & Import Data
        </button>
    </form>
    
    <!-- Import Instructions -->
    <div style="margin-top: 32px; padding: 20px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 4px;">
        <div style="display: flex; align-items: start; gap: 12px;">
            <i data-lucide="info" style="width: 20px; height: 20px; color: #d97706; flex-shrink: 0; margin-top: 2px;"></i>
            <div>
                <h4 style="margin: 0 0 12px 0; font-size: 0.9rem; font-weight: 700; color: #92400e;">CSV Format Requirements</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 0.85rem; color: #b45309; line-height: 1.8;">
                    <li><strong>Products:</strong> Name, Category, Type, Price, Stock, Description</li>
                    <li><strong>Customers:</strong> Username, Email, First Name, Last Name, Phone</li>
                    <li><strong>Raw Materials:</strong> Name, Stock (tons), Price/ton, Min Stock Level</li>
                    <li><strong>Flocks:</strong> Flock Name, Breed, Initial Count, Current Count, Hatch Date (YYYY-MM-DD), Status</li>
                    <li><strong>Expenses:</strong> Category, Description, Amount, Date (YYYY-MM-DD), Payment Method</li>
                </ul>
                <p style="margin: 12px 0 0 0; font-size: 0.8rem; color: #92400e; font-weight: 600;">
                    <i data-lucide="lightbulb" style="width: 14px; height: 14px; display: inline-block; margin-right: 4px;"></i>
                    TIP: Export existing data first to get the exact CSV format template.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Import Options -->
<div class="admin-card" style="margin-top: 32px;">
    <div style="border-bottom: 1px solid var(--admin-border); padding-bottom: 16px; margin-bottom: 24px;">
        <h3 style="margin: 0; font-family: 'Outfit', sans-serif; font-size: 1.15rem; color: var(--admin-text-heading); display: flex; align-items: center; gap: 8px;">
            <i data-lucide="settings" style="width: 20px; height: 20px; color: var(--admin-primary);"></i>
            Import Behavior
        </h3>
        <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: #64748b;">How the system handles duplicate records during import.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div style="background: #f0fdf4; border: 1px solid #dcfce7; padding: 20px; border-radius: 4px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <i data-lucide="check-circle" style="width: 18px; height: 18px; color: #16a34a;"></i>
                <h4 style="margin: 0; font-weight: 700; font-size: 0.95rem; color: #166534;">Products, Raw Materials, Customers</h4>
            </div>
            <p style="margin: 0; font-size: 0.85rem; color: #15803d; line-height: 1.6;">
                <strong>UPDATE ON DUPLICATE:</strong> If a product/customer with the same name/email exists, it will be updated with new data.
            </p>
        </div>
        
        <div style="background: #fef3c7; border: 1px solid #fde68a; padding: 20px; border-radius: 4px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px;">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px; color: #d97706;"></i>
                <h4 style="margin: 0; font-weight: 700; font-size: 0.95rem; color: #92400e;">Flocks, Expenses</h4>
            </div>
            <p style="margin: 0; font-size: 0.85rem; color: #b45309; line-height: 1.6;">
                <strong>ALWAYS INSERT:</strong> Every row creates a new record. Good for historical data that doesn't need deduplication.
            </p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
