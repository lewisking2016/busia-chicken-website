<?php
/**
 * Dropdowns & Master Data Management Center
 * Manage all system dropdowns across the Website and Admin Dashboard in one place.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Backend/config/database.php';
require_once __DIR__ . '/../../Backend/api/dropdowns.php';

// Auth Guard
if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager', 'stock_manager'], true)) {
    header("Location: /Frontend/admin/login.php");
    exit;
}

$pageTitle = "Dropdowns & Master Data Manager";
$groups = getAllDropdownGroups();
$selectedGroup = $_GET['group'] ?? ($groups[0]['group_key'] ?? 'product_categories');

// Fetch options for active group
$options = getSystemDropdownOptions($selectedGroup, false);

// Find active group metadata
$currentGroupLabel = "Dropdown Group";
foreach ($groups as $g) {
    if ($g['group_key'] === $selectedGroup) {
        $currentGroupLabel = $g['group_label'];
        break;
    }
}

// Calculate stats
$pdo = getDatabaseConnection();
$totalGroups = count($groups);
$totalOptions = 0;
$totalCustom = 0;
if ($pdo) {
    $totalOptions = (int)($pdo->query("SELECT COUNT(*) FROM system_dropdowns")->fetchColumn() ?: 0);
    $totalCustom = (int)($pdo->query("SELECT COUNT(*) FROM system_dropdowns WHERE is_system = 0")->fetchColumn() ?: 0);
}

require_once __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-main-content" style="padding: 24px;">
    <!-- Page Title & Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; color: var(--dark, #0f172a); margin: 0 0 4px 0;">
                <i data-lucide="list-filter" style="width: 28px; height: 28px; vertical-align: middle; color: var(--primary, #d97706); margin-right: 8px;"></i>
                Dropdowns & Master Data Manager
            </h1>
            <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
                Manage and customize every selectable dropdown option across the website and admin dashboard in one place.
            </p>
        </div>
        <div style="display: flex; gap: 12px;">
            <button onclick="openAddGroupModal()" class="btn btn-outline" style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="folder-plus" style="width: 18px; height: 18px;"></i>
                <span>New Category Group</span>
            </button>
            <button onclick="openAddOptionModal()" class="btn btn-primary" style="display: flex; align-items: center; gap: 8px; background: var(--primary, #d97706); color: white; padding: 10px 18px; border-radius: 6px; border: none; font-weight: 600; cursor: pointer;">
                <i data-lucide="plus-circle" style="width: 18px; height: 18px;"></i>
                <span>Add New Option</span>
            </button>
        </div>
    </div>

    <!-- Alert Message Banner -->
    <div id="apiAlert" style="display: none; padding: 14px 18px; border-radius: 8px; margin-bottom: 20px; font-size: 0.95rem; font-weight: 500;"></div>

    <!-- KPI Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 28px;">
        <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;"><?php echo $totalGroups; ?></div>
                <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Dropdown Groups</div>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="check-circle-2" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;"><?php echo $totalOptions; ?></div>
                <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Total Options</div>
            </div>
        </div>

        <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 16px;">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                <i data-lucide="sparkles" style="width: 24px; height: 24px;"></i>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #0f172a;"><?php echo $totalCustom; ?></div>
                <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Custom Admin Options</div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid Layout (Sidebar Groups Navigation + Options Table) -->
    <div style="display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start;">
        
        <!-- Left Group Navigation Tabs -->
        <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="font-size: 0.85rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; margin-bottom: 12px;">
                Dropdown Categories
            </div>
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <?php foreach ($groups as $g): 
                    $isActive = ($g['group_key'] === $selectedGroup);
                ?>
                    <a href="?group=<?php echo urlencode($g['group_key']); ?>" 
                       style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-radius: 8px; text-decoration: none; font-weight: 500; font-size: 0.92rem; transition: all 0.2s; <?php echo $isActive ? 'background: #fef3c7; color: #92400e; font-weight: 600;' : 'color: #475569; hover:background: #f8fafc;'; ?>">
                        <span><?php echo htmlspecialchars($g['group_label']); ?></span>
                        <span style="background: <?php echo $isActive ? '#f59e0b' : '#e2e8f0'; ?>; color: <?php echo $isActive ? '#ffffff' : '#475569'; ?>; padding: 2px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                            <?php echo $g['active_options']; ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Options Table View -->
        <div style="background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden;">
            
            <!-- Table Action Header -->
            <div style="padding: 16px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; background: #f8fafc;">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin: 0;">
                        <?php echo htmlspecialchars($currentGroupLabel); ?>
                    </h3>
                    <span style="font-size: 0.82rem; color: #64748b;">
                        Group Key: <code style="background: #e2e8f0; padding: 2px 6px; border-radius: 4px;"><?php echo htmlspecialchars($selectedGroup); ?></code>
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="position: relative;">
                        <i data-lucide="search" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: #94a3b8;"></i>
                        <input type="text" id="searchInput" onkeyup="filterOptionsTable()" placeholder="Search options..." 
                               style="padding: 8px 12px 8px 34px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.88rem; outline: none; width: 200px;">
                    </div>
                </div>
            </div>

            <!-- Options Table -->
            <div style="overflow-x: auto;">
                <table id="optionsTable" style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                    <thead>
                        <tr style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 600; font-size: 0.82rem; text-transform: uppercase;">
                            <th style="padding: 12px 18px; width: 60px;">Order</th>
                            <th style="padding: 12px 18px;">Option Label</th>
                            <th style="padding: 12px 18px;">Stored Value</th>
                            <th style="padding: 12px 18px;">Type</th>
                            <th style="padding: 12px 18px;">Status</th>
                            <th style="padding: 12px 18px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($options)): ?>
                            <tr>
                                <td colspan="6" style="padding: 32px; text-align: center; color: #94a3b8;">
                                    No options found for this group. Click <strong>Add New Option</strong> above to create one!
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($options as $opt): ?>
                                <tr class="option-row" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;">
                                    <td style="padding: 14px 18px; color: #64748b; font-weight: 600;">
                                        #<?php echo (int)$opt['sort_order']; ?>
                                    </td>
                                    <td style="padding: 14px 18px; font-weight: 600; color: #0f172a;" class="opt-label">
                                        <?php echo htmlspecialchars($opt['option_label']); ?>
                                    </td>
                                    <td style="padding: 14px 18px; color: #475569;" class="opt-value">
                                        <code style="background: #f1f5f9; padding: 3px 8px; border-radius: 4px; font-size: 0.85rem; color: #0369a1;">
                                            <?php echo htmlspecialchars($opt['option_value']); ?>
                                        </code>
                                    </td>
                                    <td style="padding: 14px 18px;">
                                        <?php if ($opt['is_system']): ?>
                                            <span style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                                <i data-lucide="shield-check" style="width: 12px; height: 12px; color: #0284c7;"></i> Core System
                                            </span>
                                        <?php else: ?>
                                            <span style="background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                                Custom
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 14px 18px;">
                                        <button onclick="toggleOptionStatus(<?php echo $opt['id']; ?>)" 
                                                style="border: none; background: transparent; cursor: pointer; padding: 0;">
                                            <?php if ($opt['is_active']): ?>
                                                <span style="background: #dcfce7; color: #15803d; padding: 4px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #16a34a;"></span> Active
                                                </span>
                                            <?php else: ?>
                                                <span style="background: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 12px; font-size: 0.78rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #dc2626;"></span> Inactive
                                                </span>
                                            <?php endif; ?>
                                        </button>
                                    </td>
                                    <td style="padding: 14px 18px; text-align: right;">
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($opt)); ?>)" 
                                                    class="btn btn-sm btn-outline" title="Edit Option"
                                                    style="padding: 5px 10px; border: 1px solid #cbd5e1; border-radius: 4px; background: white; cursor: pointer;">
                                                <i data-lucide="pencil" style="width: 14px; height: 14px; color: #0284c7;"></i>
                                            </button>
                                            
                                            <?php if (!$opt['is_system']): ?>
                                                <button onclick="deleteOption(<?php echo $opt['id']; ?>, '<?php echo htmlspecialchars(addslashes($opt['option_label'])); ?>')" 
                                                        class="btn btn-sm" title="Delete Option"
                                                        style="padding: 5px 10px; border: 1px solid #fca5a5; border-radius: 4px; background: #fff5f5; cursor: pointer;">
                                                    <i data-lucide="trash-2" style="width: 14px; height: 14px; color: #dc2626;"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add New Option -->
<div id="addOptionModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a;">Add Dropdown Option</h3>
            <button onclick="closeModal('addOptionModal')" style="border: none; background: transparent; cursor: pointer; color: #64748b;">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        <form id="addOptionForm" onsubmit="submitAddOption(event)">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Target Dropdown Group</label>
                <select name="group_key" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
                    <?php foreach ($groups as $g): ?>
                        <option value="<?php echo htmlspecialchars($g['group_key']); ?>" <?php echo $g['group_key'] === $selectedGroup ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($g['group_label']); ?> (<?php echo htmlspecialchars($g['group_key']); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Option Display Label</label>
                <input type="text" name="option_label" placeholder="e.g. Free Range Broilers" required 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Stored System Value <small style="color: #94a3b8; font-weight: normal;">(Optional - auto-generated if left blank)</small>
                </label>
                <input type="text" name="option_value" placeholder="e.g. free_range_broilers" 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Sort Order Number</label>
                <input type="number" name="sort_order" value="10" 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeModal('addOptionModal')" style="padding: 10px 18px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 18px; border: none; border-radius: 6px; background: var(--primary, #d97706); color: white; font-weight: 600; cursor: pointer;">Save Option</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Option -->
<div id="editOptionModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a;">Edit Dropdown Option</h3>
            <button onclick="closeModal('editOptionModal')" style="border: none; background: transparent; cursor: pointer; color: #64748b;">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        <form id="editOptionForm" onsubmit="submitEditOption(event)">
            <input type="hidden" name="id" id="edit_id">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Option Display Label</label>
                <input type="text" name="option_label" id="edit_option_label" required 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Stored System Value</label>
                <input type="text" name="option_value" id="edit_option_value" required 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Sort Order Number</label>
                <input type="number" name="sort_order" id="edit_sort_order" required 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 600; color: #334155; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="edit_is_active" value="1" style="width: 18px; height: 18px;">
                    <span>Active and visible in forms</span>
                </label>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeModal('editOptionModal')" style="padding: 10px 18px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 18px; border: none; border-radius: 6px; background: #0284c7; color: white; font-weight: 600; cursor: pointer;">Update Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add New Group -->
<div id="addGroupModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 1.15rem; font-weight: 700; color: #0f172a;">Create New Dropdown Group</h3>
            <button onclick="closeModal('addGroupModal')" style="border: none; background: transparent; cursor: pointer; color: #64748b;">
                <i data-lucide="x" style="width: 20px; height: 20px;"></i>
            </button>
        </div>
        <form id="addGroupForm" onsubmit="submitAddGroup(event)">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Group Display Title</label>
                <input type="text" name="group_label" placeholder="e.g. Customer Feedback Categories" required 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Group Identifier Key <small style="color: #94a3b8; font-weight: normal;">(e.g. feedback_types)</small>
                </label>
                <input type="text" name="group_key" placeholder="e.g. feedback_types" required 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">First Initial Option Label</label>
                <input type="text" name="option_label" placeholder="e.g. General Suggestion" required 
                       style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem;">
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="closeModal('addGroupModal')" style="padding: 10px 18px; border: 1px solid #cbd5e1; border-radius: 6px; background: white; font-weight: 600; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 18px; border: none; border-radius: 6px; background: var(--primary, #d97706); color: white; font-weight: 600; cursor: pointer;">Create Category Group</button>
            </div>
        </form>
    </div>
</div>

<script>
function showAlert(message, isSuccess = true) {
    const banner = document.getElementById('apiAlert');
    banner.style.display = 'block';
    banner.style.background = isSuccess ? '#dcfce7' : '#fee2e2';
    banner.style.color = isSuccess ? '#15803d' : '#b91c1c';
    banner.style.border = isSuccess ? '1px solid #bbf7d0' : '1px solid #fecaca';
    banner.textContent = message;
    setTimeout(() => { banner.style.display = 'none'; }, 4000);
}

function openAddOptionModal() {
    document.getElementById('addOptionModal').style.display = 'flex';
}

function openAddGroupModal() {
    document.getElementById('addGroupModal').style.display = 'flex';
}

function openEditModal(opt) {
    document.getElementById('edit_id').value = opt.id;
    document.getElementById('edit_option_label').value = opt.option_label;
    document.getElementById('edit_option_value').value = opt.option_value;
    document.getElementById('edit_sort_order').value = opt.sort_order;
    document.getElementById('edit_is_active').checked = (parseInt(opt.is_active) === 1);
    document.getElementById('editOptionModal').style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function filterOptionsTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('.option-row');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? '' : 'none';
    });
}

async function submitAddOption(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'add_option');

    try {
        const res = await fetch('/Backend/api/dropdowns.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            closeModal('addOptionModal');
            showAlert(data.message, true);
            setTimeout(() => window.location.reload(), 600);
        } else {
            alert(data.message || 'Failed to add option.');
        }
    } catch (err) {
        alert('Network error submitting option.');
    }
}

async function submitEditOption(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'update_option');

    try {
        const res = await fetch('/Backend/api/dropdowns.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            closeModal('editOptionModal');
            showAlert(data.message, true);
            setTimeout(() => window.location.reload(), 600);
        } else {
            alert(data.message || 'Failed to update option.');
        }
    } catch (err) {
        alert('Network error updating option.');
    }
}

async function submitAddGroup(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    formData.append('action', 'add_option');

    try {
        const res = await fetch('/Backend/api/dropdowns.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            closeModal('addGroupModal');
            showAlert(data.message, true);
            setTimeout(() => {
                window.location.href = '?group=' + encodeURIComponent(formData.get('group_key'));
            }, 600);
        } else {
            alert(data.message || 'Failed to create group.');
        }
    } catch (err) {
        alert('Network error creating group.');
    }
}

async function toggleOptionStatus(id) {
    const formData = new FormData();
    formData.append('action', 'toggle_status');
    formData.append('id', id);

    try {
        const res = await fetch('/Backend/api/dropdowns.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showAlert(data.message, true);
            setTimeout(() => window.location.reload(), 500);
        } else {
            alert(data.message || 'Could not toggle status.');
        }
    } catch (err) {
        alert('Network error.');
    }
}

async function deleteOption(id, label) {
    if (!confirm(`Are you sure you want to delete "${label}"?`)) return;

    const formData = new FormData();
    formData.append('action', 'delete_option');
    formData.append('id', id);

    try {
        const res = await fetch('/Backend/api/dropdowns.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showAlert(data.message, true);
            setTimeout(() => window.location.reload(), 500);
        } else {
            alert(data.message || 'Could not delete option.');
        }
    } catch (err) {
        alert('Network error.');
    }
}
</script>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
