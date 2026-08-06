from pathlib import Path

root = Path(r'c:\Users\lewis\Desktop\busia-chicken-website')

pages = {
    'Frontend/admin/animals.php': r'''<?php
/**
 * Admin - Animals Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Animals - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_animal'])) {
    $animalId = (int)($_POST['animal_id'] ?? 0);
    $tag = trim($_POST['tag'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $birthDate = trim($_POST['birth_date'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $herdId = (int)($_POST['herd_id'] ?? 0) ?: null;
    $notes = trim($_POST['notes'] ?? '');

    if ($tag === '' || $name === '') {
        $error_message = 'Animal tag and name are required.';
    } else {
        try {
            if ($animalId > 0) {
                $stmt = $pdo->prepare('UPDATE animals SET tag = ?, name = ?, type = ?, breed = ?, gender = ?, birth_date = ?, status = ?, herd_id = ?, notes = ? WHERE id = ?');
                $stmt->execute([$tag, $name, $type, $breed, $gender, $birthDate ?: null, $status, $herdId, $notes, $animalId]);
                $message = 'Animal record updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO animals (tag, name, type, breed, gender, birth_date, status, herd_id, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$tag, $name, $type, $breed, $gender, $birthDate ?: null, $status, $herdId, $notes]);
                $message = 'Animal record saved successfully.';
            }
        } catch (Exception $e) {
            $error_message = 'Unable to save animal record: ' . $e->getMessage();
        }
    }
}

$herds = [];
if ($pdo) {
    $herds = $pdo->query('SELECT id, name FROM herds ORDER BY name ASC')->fetchAll(PDO::FETCH_ASSOC);
}

$animals = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT a.*, h.name AS herd_name FROM animals a LEFT JOIN herds h ON a.herd_id = h.id ORDER BY a.created_at DESC');
    $animals = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$selectedAnimal = null;
if (in_array($action, ['view', 'edit'], true) && $pdo) {
    $animalId = (int)($_GET['id'] ?? 0);
    if ($animalId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM animals WHERE id = ?');
        $stmt->execute([$animalId]);
        $selectedAnimal = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

function renderInput(string $label, string $name, string $value = '', string $type = 'text'): string {
    return '<div class="admin-form-group"><label class="admin-form-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label><input class="admin-form-control" type="' . $type . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"></div>';
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Animals</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Track every animal with actual farm data.</p>
    </div>
    <a href="?action=add" class="btn btn-primary" style="border-radius:4px;display:inline-flex;align-items:center;gap:8px;">
        <i data-lucide="plus-circle" style="width:18px;height:18px;"></i>
        <span>Add Animal</span>
    </a>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Animal Records</h3>
        <span style="font-size:0.85rem;color:#64748b;">Live database-backed records</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Tag</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Breed</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($animals)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 20px; color: #64748b;">No animal records found.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($animals as $animal): ?>
                <tr>
                    <td><?php echo htmlspecialchars($animal['tag'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($animal['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($animal['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($animal['breed'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($animal['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align:right;">
                        <a class="btn btn-outline btn-sm" href="?action=view&id=<?php echo (int)$animal['id']; ?>">View</a>
                        <a class="btn btn-outline btn-sm" href="?action=edit&id=<?php echo (int)$animal['id']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<?php
    $formValues = [
        'tag' => '',
        'name' => '',
        'type' => '',
        'breed' => '',
        'gender' => '',
        'birth_date' => '',
        'status' => 'Active',
        'herd_id' => '',
        'notes' => '',
    ];
    if ($action === 'edit' && $selectedAnimal) {
        $formValues = [
            'tag' => $selectedAnimal['tag'],
            'name' => $selectedAnimal['name'],
            'type' => $selectedAnimal['type'],
            'breed' => $selectedAnimal['breed'],
            'gender' => $selectedAnimal['gender'],
            'birth_date' => $selectedAnimal['birth_date'],
            'status' => $selectedAnimal['status'],
            'herd_id' => $selectedAnimal['herd_id'],
            'notes' => $selectedAnimal['notes'],
        ];
    }
?>
<div class="admin-card">
    <h3 style="margin:0 0 16px 0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">
        <?php echo $action === 'add' ? 'Add Animal' : 'Edit Animal'; ?>
    </h3>
    <form method="POST" action="">
        <input type="hidden" name="save_animal" value="1">
        <?php if ($action === 'edit' && $selectedAnimal): ?>
            <input type="hidden" name="animal_id" value="<?php echo (int)$selectedAnimal['id']; ?>">
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
            <?php echo renderInput('Animal Tag', 'tag', $formValues['tag']); ?>
            <?php echo renderInput('Animal Name', 'name', $formValues['name']); ?>
            <?php echo renderInput('Type', 'type', $formValues['type']); ?>
            <?php echo renderInput('Breed', 'breed', $formValues['breed']); ?>
            <?php echo renderInput('Gender', 'gender', $formValues['gender']); ?>
            <?php echo renderInput('Status', 'status', $formValues['status']); ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Birth Date</label>
                <input class="admin-form-control" type="date" name="birth_date" value="<?php echo htmlspecialchars($formValues['birth_date'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Herd</label>
                <select class="admin-form-control" name="herd_id">
                    <option value="">-- Select Herd --</option>
                    <?php foreach ($herds as $herd): ?>
                        <option value="<?php echo (int)$herd['id']; ?>" <?php echo $formValues['herd_id'] === $herd['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($herd['name'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group" style="grid-column: span 2;">
                <label class="admin-form-label">Notes</label>
                <textarea class="admin-form-control" name="notes" rows="4"><?php echo htmlspecialchars($formValues['notes'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:4px;">Save</button>
            <a href="/Frontend/admin/animals.php" class="btn btn-outline" style="border-radius:4px;">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Animal Details</h3>
            <p style="margin:6px 0 0 0;color:#64748b;">Review the selected animal record.</p>
        </div>
        <a href="/Frontend/admin/animals.php" class="btn btn-outline" style="border-radius:4px;">Back</a>
    </div>
    <?php if ($selectedAnimal): ?>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
        <div><strong>Tag:</strong> <?php echo htmlspecialchars($selectedAnimal['tag'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Name:</strong> <?php echo htmlspecialchars($selectedAnimal['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Type:</strong> <?php echo htmlspecialchars($selectedAnimal['type'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Breed:</strong> <?php echo htmlspecialchars($selectedAnimal['breed'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Gender:</strong> <?php echo htmlspecialchars($selectedAnimal['gender'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Status:</strong> <?php echo htmlspecialchars($selectedAnimal['status'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Herd:</strong> <?php echo htmlspecialchars($selectedAnimal['herd_name'] ?? 'None', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Birth Date:</strong> <?php echo htmlspecialchars($selectedAnimal['birth_date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="grid-column: span 2;"><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($selectedAnimal['notes'] ?? '', ENT_QUOTES, 'UTF-8')); ?></div>
    </div>
    <?php else: ?>
    <p style="color:#64748b;">Animal record not found.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/herds.php': r'''<?php
/**
 * Admin - Herds Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Herds - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_herd'])) {
    $herdId = (int)($_POST['herd_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $size = (int)($_POST['size'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $status = trim($_POST['status'] ?? 'Active');
    $notes = trim($_POST['notes'] ?? '');

    if ($name === '') {
        $error_message = 'Herd name is required.';
    } else {
        try {
            if ($herdId > 0) {
                $stmt = $pdo->prepare('UPDATE herds SET name = ?, species = ?, size = ?, location = ?, status = ?, notes = ? WHERE id = ?');
                $stmt->execute([$name, $species, $size, $location, $status, $notes, $herdId]);
                $message = 'Herd updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO herds (name, species, size, location, status, notes) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->execute([$name, $species, $size, $location, $status, $notes]);
                $message = 'Herd added successfully.';
            }
        } catch (Exception $e) {
            $error_message = 'Unable to save herd: ' . $e->getMessage();
        }
    }
}

$herds = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT * FROM herds ORDER BY created_at DESC');
    $herds = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$selectedHerd = null;
if (in_array($action, ['view', 'edit'], true) && $pdo) {
    $herdId = (int)($_GET['id'] ?? 0);
    if ($herdId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM herds WHERE id = ?');
        $stmt->execute([$herdId]);
        $selectedHerd = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

function renderInput(string $label, string $name, string $value = '', string $type = 'text'): string {
    return '<div class="admin-form-group"><label class="admin-form-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label><input class="admin-form-control" type="' . $type . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"></div>';
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Herds</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Group your animals into herds or pens.</p>
    </div>
    <a href="?action=add" class="btn btn-primary" style="border-radius:4px;display:inline-flex;align-items:center;gap:8px;">
        <i data-lucide="plus-circle" style="width:18px;height:18px;"></i>
        <span>Add Herd</span>
    </a>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Herds</h3>
        <span style="font-size:0.85rem;color:#64748b;">Live herd management data</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Species</th>
                    <th>Size</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($herds)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 20px; color: #64748b;">No herds found.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($herds as $herd): ?>
                <tr>
                    <td><?php echo htmlspecialchars($herd['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($herd['species'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars((string)$herd['size'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($herd['location'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($herd['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align:right;">
                        <a class="btn btn-outline btn-sm" href="?action=view&id=<?php echo (int)$herd['id']; ?>">View</a>
                        <a class="btn btn-outline btn-sm" href="?action=edit&id=<?php echo (int)$herd['id']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<?php
    $formValues = [
        'name' => '',
        'species' => '',
        'size' => '',
        'location' => '',
        'status' => 'Active',
        'notes' => '',
    ];
    if ($action === 'edit' && $selectedHerd) {
        $formValues = [
            'name' => $selectedHerd['name'],
            'species' => $selectedHerd['species'],
            'size' => $selectedHerd['size'],
            'location' => $selectedHerd['location'],
            'status' => $selectedHerd['status'],
            'notes' => $selectedHerd['notes'],
        ];
    }
?>
<div class="admin-card">
    <h3 style="margin:0 0 16px 0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">
        <?php echo $action === 'add' ? 'Add Herd' : 'Edit Herd'; ?>
    </h3>
    <form method="POST" action="">
        <input type="hidden" name="save_herd" value="1">
        <?php if ($action === 'edit' && $selectedHerd): ?>
            <input type="hidden" name="herd_id" value="<?php echo (int)$selectedHerd['id']; ?>">
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
            <?php echo renderInput('Herd Name', 'name', $formValues['name']); ?>
            <?php echo renderInput('Species', 'species', $formValues['species']); ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Location</label>
                <input class="admin-form-control" type="text" name="location" value="<?php echo htmlspecialchars($formValues['location'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Size</label>
                <input class="admin-form-control" type="number" name="size" value="<?php echo htmlspecialchars($formValues['size'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Status</label>
                <select class="admin-form-control" name="status">
                    <?php foreach (['Active', 'Sold', 'Archived'] as $statusOption): ?>
                        <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $formValues['status'] === $statusOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group" style="grid-column: span 2;">
                <label class="admin-form-label">Notes</label>
                <textarea class="admin-form-control" name="notes" rows="4"><?php echo htmlspecialchars($formValues['notes'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:4px;">Save</button>
            <a href="/Frontend/admin/herds.php" class="btn btn-outline" style="border-radius:4px;">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Herd Details</h3>
            <p style="margin:6px 0 0 0;color:#64748b;">View herd summary and status.</p>
        </div>
        <a href="/Frontend/admin/herds.php" class="btn btn-outline" style="border-radius:4px;">Back</a>
    </div>
    <?php if ($selectedHerd): ?>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
        <div><strong>Name:</strong> <?php echo htmlspecialchars($selectedHerd['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Species:</strong> <?php echo htmlspecialchars($selectedHerd['species'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Size:</strong> <?php echo htmlspecialchars((string)$selectedHerd['size'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Location:</strong> <?php echo htmlspecialchars($selectedHerd['location'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Status:</strong> <?php echo htmlspecialchars($selectedHerd['status'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="grid-column: span 2;"><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($selectedHerd['notes'] ?? '', ENT_QUOTES, 'UTF-8')); ?></div>
    </div>
    <?php else: ?>
    <p style="color:#64748b;">Herd not found.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/breeding.php': r'''<?php
/**
 * Admin - Breeding Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Breeding - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_breeding'])) {
    $recordId = (int)($_POST['record_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $maleParent = trim($_POST['male_parent'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $dueDate = trim($_POST['due_date'] ?? '');
    $status = trim($_POST['status'] ?? 'Pending');
    $notes = trim($_POST['notes'] ?? '');

    if ($subject === '' || $type === '') {
        $error_message = 'Subject and type are required.';
    } else {
        try {
            if ($recordId > 0) {
                $stmt = $pdo->prepare('UPDATE breeding_records SET subject = ?, type = ?, male_parent = ?, date = ?, due_date = ?, status = ?, notes = ? WHERE id = ?');
                $stmt->execute([$subject, $type, $maleParent, $date ?: null, $dueDate ?: null, $status, $notes, $recordId]);
                $message = 'Breeding record updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO breeding_records (subject, type, male_parent, date, due_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$subject, $type, $maleParent, $date ?: null, $dueDate ?: null, $status, $notes]);
                $message = 'Breeding record saved successfully.';
            }
        } catch (Exception $e) {
            $error_message = 'Unable to save breeding record: ' . $e->getMessage();
        }
    }
}

$statuses = ['Pending', 'Insemination', 'Pregnant', 'Confirmed', 'Completed', 'Cancelled'];
$records = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT * FROM breeding_records ORDER BY date DESC, created_at DESC');
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$selectedRecord = null;
if (in_array($action, ['view', 'edit'], true) && $pdo) {
    $recordId = (int)($_GET['id'] ?? 0);
    if ($recordId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM breeding_records WHERE id = ?');
        $stmt->execute([$recordId]);
        $selectedRecord = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

function renderInput(string $label, string $name, string $value = '', string $type = 'text'): string {
    return '<div class="admin-form-group"><label class="admin-form-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label><input class="admin-form-control" type="' . $type . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"></div>';
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Breeding</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Track mating, pregnancy and birth plans.</p>
    </div>
    <a href="?action=add" class="btn btn-primary" style="border-radius:4px;display:inline-flex;align-items:center;gap:8px;">
        <i data-lucide="plus-circle" style="width:18px;height:18px;"></i>
        <span>Add Record</span>
    </a>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Breeding Records</h3>
        <span style="font-size:0.85rem;color:#64748b;">Actual breeding records from the database</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding: 20px; color: #64748b;">No breeding records available.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($records as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['due_date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align:right;">
                        <a class="btn btn-outline btn-sm" href="?action=view&id=<?php echo (int)$item['id']; ?>">View</a>
                        <a class="btn btn-outline btn-sm" href="?action=edit&id=<?php echo (int)$item['id']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<?php
    $formValues = [
        'subject' => '',
        'type' => '',
        'male_parent' => '',
        'date' => '',
        'due_date' => '',
        'status' => 'Pending',
        'notes' => '',
    ];
    if ($action === 'edit' && $selectedRecord) {
        $formValues = [
            'subject' => $selectedRecord['subject'],
            'type' => $selectedRecord['type'],
            'male_parent' => $selectedRecord['male_parent'],
            'date' => $selectedRecord['date'],
            'due_date' => $selectedRecord['due_date'],
            'status' => $selectedRecord['status'],
            'notes' => $selectedRecord['notes'],
        ];
    }
?>
<div class="admin-card">
    <h3 style="margin:0 0 16px 0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">
        <?php echo $action === 'add' ? 'Add Breeding' : 'Edit Breeding'; ?>
    </h3>
    <form method="POST" action="">
        <input type="hidden" name="save_breeding" value="1">
        <?php if ($action === 'edit' && $selectedRecord): ?>
            <input type="hidden" name="record_id" value="<?php echo (int)$selectedRecord['id']; ?>">
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
            <?php echo renderInput('Animal / Herd', 'subject', $formValues['subject']); ?>
            <?php echo renderInput('Type', 'type', $formValues['type']); ?>
            <?php echo renderInput('Male Parent', 'male_parent', $formValues['male_parent']); ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Date</label>
                <input class="admin-form-control" type="date" name="date" value="<?php echo htmlspecialchars($formValues['date'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Due Date</label>
                <input class="admin-form-control" type="date" name="due_date" value="<?php echo htmlspecialchars($formValues['due_date'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Status</label>
                <select class="admin-form-control" name="status">
                    <?php foreach ($statuses as $statusOption): ?>
                        <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $formValues['status'] === $statusOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group" style="grid-column: span 2;">
                <label class="admin-form-label">Notes</label>
                <textarea class="admin-form-control" name="notes" rows="4"><?php echo htmlspecialchars($formValues['notes'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:4px;">Save</button>
            <a href="/Frontend/admin/breeding.php" class="btn btn-outline" style="border-radius:4px;">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Breeding Record</h3>
            <p style="margin:6px 0 0 0;color:#64748b;">View breeding details and due date.</p>
        </div>
        <a href="/Frontend/admin/breeding.php" class="btn btn-outline" style="border-radius:4px;">Back</a>
    </div>
    <?php if ($selectedRecord): ?>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
        <div><strong>Animal / Herd:</strong> <?php echo htmlspecialchars($selectedRecord['subject'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Type:</strong> <?php echo htmlspecialchars($selectedRecord['type'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Date:</strong> <?php echo htmlspecialchars($selectedRecord['date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Due:</strong> <?php echo htmlspecialchars($selectedRecord['due_date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Status:</strong> <?php echo htmlspecialchars($selectedRecord['status'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Male Parent:</strong> <?php echo htmlspecialchars($selectedRecord['male_parent'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="grid-column: span 2;"><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($selectedRecord['notes'] ?? '', ENT_QUOTES, 'UTF-8')); ?></div>
    </div>
    <?php else: ?>
    <p style="color:#64748b;">Record not found.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/health.php': r'''<?php
/**
 * Admin - Health Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Health - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_health'])) {
    $recordId = (int)($_POST['record_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $product = trim($_POST['product'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $nextDate = trim($_POST['next_date'] ?? '');
    $status = trim($_POST['status'] ?? 'Scheduled');
    $notes = trim($_POST['notes'] ?? '');

    if ($subject === '' || $type === '') {
        $error_message = 'Subject and type are required.';
    } else {
        try {
            if ($recordId > 0) {
                $stmt = $pdo->prepare('UPDATE health_records SET subject = ?, type = ?, product = ?, date = ?, next_date = ?, status = ?, notes = ? WHERE id = ?');
                $stmt->execute([$subject, $type, $product, $date ?: null, $nextDate ?: null, $status, $notes, $recordId]);
                $message = 'Health record updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO health_records (subject, type, product, date, next_date, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$subject, $type, $product, $date ?: null, $nextDate ?: null, $status, $notes]);
                $message = 'Health record saved successfully.';
            }
        } catch (Exception $e) {
            $error_message = 'Unable to save health record: ' . $e->getMessage();
        }
    }
}

$statuses = ['Scheduled', 'Completed', 'Pending', 'Missed'];
$records = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT * FROM health_records ORDER BY date DESC, created_at DESC');
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$selectedRecord = null;
if (in_array($action, ['view', 'edit'], true) && $pdo) {
    $recordId = (int)($_GET['id'] ?? 0);
    if ($recordId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM health_records WHERE id = ?');
        $stmt->execute([$recordId]);
        $selectedRecord = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

function renderInput(string $label, string $name, string $value = '', string $type = 'text'): string {
    return '<div class="admin-form-group"><label class="admin-form-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label><input class="admin-form-control" type="' . $type . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"></div>';
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Health</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Track vaccines and treatments with live records.</p>
    </div>
    <a href="?action=add" class="btn btn-primary" style="border-radius:4px;display:inline-flex;align-items:center;gap:8px;">
        <i data-lucide="plus-circle" style="width:18px;height:18px;"></i>
        <span>Add Health Record</span>
    </a>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Health Records</h3>
        <span style="font-size:0.85rem;color:#64748b;">Live health log</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Animal / Herd</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($records)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 20px; color: #64748b;">No health records found.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($records as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align:right;">
                        <a class="btn btn-outline btn-sm" href="?action=view&id=<?php echo (int)$item['id']; ?>">View</a>
                        <a class="btn btn-outline btn-sm" href="?action=edit&id=<?php echo (int)$item['id']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<?php
    $formValues = [
        'subject' => '',
        'type' => '',
        'product' => '',
        'date' => '',
        'next_date' => '',
        'status' => 'Scheduled',
        'notes' => '',
    ];
    if ($action === 'edit' && $selectedRecord) {
        $formValues = [
            'subject' => $selectedRecord['subject'],
            'type' => $selectedRecord['type'],
            'product' => $selectedRecord['product'],
            'date' => $selectedRecord['date'],
            'next_date' => $selectedRecord['next_date'],
            'status' => $selectedRecord['status'],
            'notes' => $selectedRecord['notes'],
        ];
    }
?>
<div class="admin-card">
    <h3 style="margin:0 0 16px 0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">
        <?php echo $action === 'add' ? 'Add Health Record' : 'Edit Health Record'; ?>
    </h3>
    <form method="POST" action="">
        <input type="hidden" name="save_health" value="1">
        <?php if ($action === 'edit' && $selectedRecord): ?>
            <input type="hidden" name="record_id" value="<?php echo (int)$selectedRecord['id']; ?>">
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
            <?php echo renderInput('Animal / Herd', 'subject', $formValues['subject']); ?>
            <?php echo renderInput('Health Type', 'type', $formValues['type']); ?>
            <?php echo renderInput('Product / Vaccine', 'product', $formValues['product']); ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Date</label>
                <input class="admin-form-control" type="date" name="date" value="<?php echo htmlspecialchars($formValues['date'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Next Date</label>
                <input class="admin-form-control" type="date" name="next_date" value="<?php echo htmlspecialchars($formValues['next_date'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Status</label>
                <select class="admin-form-control" name="status">
                    <?php foreach ($statuses as $statusOption): ?>
                        <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $formValues['status'] === $statusOption ? 'selected' : ''; ?>><?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group" style="grid-column: span 2;">
                <label class="admin-form-label">Notes</label>
                <textarea class="admin-form-control" name="notes" rows="4"><?php echo htmlspecialchars($formValues['notes'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:4px;">Save</button>
            <a href="/Frontend/admin/health.php" class="btn btn-outline" style="border-radius:4px;">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Health Record</h3>
            <p style="margin:6px 0 0 0;color:#64748b;">View health record details and next steps.</p>
        </div>
        <a href="/Frontend/admin/health.php" class="btn btn-outline" style="border-radius:4px;">Back</a>
    </div>
    <?php if ($selectedRecord): ?>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
        <div><strong>Animal / Herd:</strong> <?php echo htmlspecialchars($selectedRecord['subject'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Type:</strong> <?php echo htmlspecialchars($selectedRecord['type'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Product / Vaccine:</strong> <?php echo htmlspecialchars($selectedRecord['product'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Date:</strong> <?php echo htmlspecialchars($selectedRecord['date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Status:</strong> <?php echo htmlspecialchars($selectedRecord['status'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Next Date:</strong> <?php echo htmlspecialchars($selectedRecord['next_date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="grid-column: span 2;"><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($selectedRecord['notes'] ?? '', ENT_QUOTES, 'UTF-8')); ?></div>
    </div>
    <?php else: ?>
    <p style="color:#64748b;">Record not found.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/feed_stock.php': r'''<?php
/**
 * Admin - Feed Stock Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Feed Stock - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager', 'stock_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_feed'])) {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $feedType = trim($_POST['feed_type'] ?? 'Feed');
    $stockTons = (float)($_POST['stock_tons'] ?? 0);
    $pricePerTon = (float)($_POST['price_per_ton'] ?? 0);
    $minStockLevel = (float)($_POST['min_stock_level'] ?? 0);

    if ($name === '') {
        $error_message = 'Feed item name is required.';
    } else {
        try {
            if ($itemId > 0) {
                $stmt = $pdo->prepare('UPDATE raw_materials SET name = ?, feed_type = ?, stock_tons = ?, current_price_per_ton = ?, min_stock_level = ? WHERE id = ?');
                $stmt->execute([$name, $feedType, $stockTons, $pricePerTon, $minStockLevel, $itemId]);
                $message = 'Feed item updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO raw_materials (name, feed_type, stock_tons, current_price_per_ton, min_stock_level) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$name, $feedType, $stockTons, $pricePerTon, $minStockLevel]);
                $message = 'Feed item saved successfully.';
            }
        } catch (Exception $e) {
            $error_message = 'Unable to save feed item: ' . $e->getMessage();
        }
    }
}

$feedItems = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT * FROM raw_materials ORDER BY name ASC');
    $feedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$selectedItem = null;
if (in_array($action, ['view', 'edit'], true) && $pdo) {
    $itemId = (int)($_GET['id'] ?? 0);
    if ($itemId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM raw_materials WHERE id = ?');
        $stmt->execute([$itemId]);
        $selectedItem = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

function renderInput(string $label, string $name, string $value = '', string $type = 'text'): string {
    return '<div class="admin-form-group"><label class="admin-form-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label><input class="admin-form-control" type="' . $type . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"></div>';
}

function calculateStatus(array $item): string {
    return ((float)$item['stock_tons'] <= (float)$item['min_stock_level']) ? 'Low' : 'Good';
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Feed Stock</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Control feed items and raw materials.</p>
    </div>
    <a href="?action=add" class="btn btn-primary" style="border-radius:4px;display:inline-flex;align-items:center;gap:8px;">
        <i data-lucide="plus-circle" style="width:18px;height:18px;"></i>
        <span>Add Feed Item</span>
    </a>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Feed Items</h3>
        <span style="font-size:0.85rem;color:#64748b;">Inventory of raw materials and feed ingredients</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Type</th>
                    <th>Stock</th>
                    <th>Unit</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($feedItems)): ?>
                <tr><td colspan="6" style="text-align:center; padding: 20px; color: #64748b;">No feed stock items found.</td></tr>
                <?php else: ?>
                <?php foreach ($feedItems as $feed): ?>
                <tr>
                    <td><?php echo htmlspecialchars($feed['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($feed['feed_type'] ?? 'Feed', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo number_format((float)$feed['stock_tons'], 3); ?></td>
                    <td>tons</td>
                    <td><?php echo calculateStatus($feed); ?></td>
                    <td style="text-align:right;">
                        <a class="btn btn-outline btn-sm" href="?action=view&id=<?php echo (int)$feed['id']; ?>">View</a>
                        <a class="btn btn-outline btn-sm" href="?action=edit&id=<?php echo (int)$feed['id']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<?php
    $formValues = [
        'name' => '',
        'feed_type' => 'Feed',
        'stock_tons' => '',
        'price_per_ton' => '',
        'min_stock_level' => '',
    ];
    if ($action === 'edit' && $selectedItem) {
        $formValues = [
            'name' => $selectedItem['name'],
            'feed_type' => $selectedItem['feed_type'] ?? 'Feed',
            'stock_tons' => $selectedItem['stock_tons'],
            'price_per_ton' => $selectedItem['current_price_per_ton'],
            'min_stock_level' => $selectedItem['min_stock_level'],
        ];
    }
?>
<div class="admin-card">
    <h3 style="margin:0 0 16px 0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">
        <?php echo $action === 'add' ? 'Add Feed Item' : 'Edit Feed Item'; ?>
    </h3>
    <form method="POST" action="">
        <input type="hidden" name="save_feed" value="1">
        <?php if ($action === 'edit' && $selectedItem): ?>
            <input type="hidden" name="item_id" value="<?php echo (int)$selectedItem['id']; ?>">
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
            <?php echo renderInput('Item Name', 'name', $formValues['name']); ?>
            <?php echo renderInput('Feed Type', 'feed_type', $formValues['feed_type']); ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Stock (tons)</label>
                <input class="admin-form-control" type="number" step="0.001" name="stock_tons" value="<?php echo htmlspecialchars($formValues['stock_tons'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Price per Ton</label>
                <input class="admin-form-control" type="number" step="0.01" name="price_per_ton" value="<?php echo htmlspecialchars($formValues['price_per_ton'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Minimum Stock Level</label>
                <input class="admin-form-control" type="number" step="0.001" name="min_stock_level" value="<?php echo htmlspecialchars($formValues['min_stock_level'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:4px;">Save</button>
            <a href="/Frontend/admin/feed_stock.php" class="btn btn-outline" style="border-radius:4px;">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Feed Item</h3>
            <p style="margin:6px 0 0 0;color:#64748b;">Detail view for this feed record.</p>
        </div>
        <a href="/Frontend/admin/feed_stock.php" class="btn btn-outline" style="border-radius:4px;">Back</a>
    </div>
    <?php if ($selectedItem): ?>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
        <div><strong>Name:</strong> <?php echo htmlspecialchars($selectedItem['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Type:</strong> <?php echo htmlspecialchars($selectedItem['feed_type'] ?? 'Feed', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Stock:</strong> <?php echo number_format((float)$selectedItem['stock_tons'], 3); ?> tons</div>
        <div><strong>Price / ton:</strong> KES <?php echo number_format((float)$selectedItem['current_price_per_ton'], 2); ?></div>
        <div><strong>Status:</strong> <?php echo calculateStatus($selectedItem); ?></div>
        <div><strong>Min Stock:</strong> <?php echo number_format((float)$selectedItem['min_stock_level'], 3); ?> tons</div>
    </div>
    <?php else: ?>
    <p style="color:#64748b;">Feed item not found.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/farm_items.php': r'''<?php
/**
 * Admin - Farm Items Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Farm Items - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_item'])) {
    $itemId = (int)($_POST['item_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $itemType = trim($_POST['item_type'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $status = trim($_POST['status'] ?? 'active');
    $description = trim($_POST['description'] ?? '');

    if ($name === '') {
        $error_message = 'Item name is required.';
    } else {
        try {
            if ($itemId > 0) {
                $stmt = $pdo->prepare('UPDATE farm_items SET name = ?, item_type = ?, species = ?, price = ?, stock_quantity = ?, status = ?, description = ? WHERE id = ?');
                $stmt->execute([$name, $itemType, $species, $price, $stock, $status, $description, $itemId]);
                $message = 'Farm item updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO farm_items (name, item_type, species, price, stock_quantity, status, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$name, $itemType, $species, $price, $stock, $status, $description]);
                $message = 'Farm item saved successfully.';
            }
        } catch (Exception $e) {
            $error_message = 'Unable to save farm item: ' . $e->getMessage();
        }
    }
}

$items = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT * FROM farm_items ORDER BY created_at DESC');
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$selectedItem = null;
if (in_array($action, ['view', 'edit'], true) && $pdo) {
    $itemId = (int)($_GET['id'] ?? 0);
    if ($itemId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM farm_items WHERE id = ?');
        $stmt->execute([$itemId]);
        $selectedItem = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

function renderInput(string $label, string $name, string $value = '', string $type = 'text'): string {
    return '<div class="admin-form-group"><label class="admin-form-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label><input class="admin-form-control" type="' . $type . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"></div>';
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Farm Items</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Manage products and live animal listings.</p>
    </div>
    <a href="?action=add" class="btn btn-primary" style="border-radius:4px;display:inline-flex;align-items:center;gap:8px;">
        <i data-lucide="plus-circle" style="width:18px;height:18px;"></i>
        <span>Add Item</span>
    </a>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Item List</h3>
        <span style="font-size:0.85rem;color:#64748b;">Products and animals for sale</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Species</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr><td colspan="6" style="text-align:center; padding: 20px; color: #64748b;">No farm items found.</td></tr>
                <?php else: ?>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['item_type'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($item['species'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>KES <?php echo number_format((float)$item['price'], 2); ?></td>
                    <td><?php echo (int)$item['stock_quantity']; ?></td>
                    <td style="text-align:right;">
                        <a class="btn btn-outline btn-sm" href="?action=view&id=<?php echo (int)$item['id']; ?>">View</a>
                        <a class="btn btn-outline btn-sm" href="?action=edit&id=<?php echo (int)$item['id']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<?php
    $formValues = [
        'name' => '',
        'item_type' => '',
        'species' => '',
        'price' => '',
        'stock' => '',
        'status' => 'active',
        'description' => '',
    ];
    if ($action === 'edit' && $selectedItem) {
        $formValues = [
            'name' => $selectedItem['name'],
            'item_type' => $selectedItem['item_type'],
            'species' => $selectedItem['species'],
            'price' => $selectedItem['price'],
            'stock' => $selectedItem['stock_quantity'],
            'status' => $selectedItem['status'],
            'description' => $selectedItem['description'],
        ];
    }
?>
<div class="admin-card">
    <h3 style="margin:0 0 16px 0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">
        <?php echo $action === 'add' ? 'Add Item' : 'Edit Item'; ?>
    </h3>
    <form method="POST" action="">
        <input type="hidden" name="save_item" value="1">
        <?php if ($action === 'edit' && $selectedItem): ?>
            <input type="hidden" name="item_id" value="<?php echo (int)$selectedItem['id']; ?>">
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
            <?php echo renderInput('Name', 'name', $formValues['name']); ?>
            <?php echo renderInput('Type', 'item_type', $formValues['item_type']); ?>
            <?php echo renderInput('Species', 'species', $formValues['species']); ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Price</label>
                <input class="admin-form-control" type="number" step="0.01" name="price" value="<?php echo htmlspecialchars($formValues['price'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Stock</label>
                <input class="admin-form-control" type="number" name="stock" value="<?php echo htmlspecialchars($formValues['stock'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Status</label>
                <select class="admin-form-control" name="status">
                    <?php foreach (['active','out_of_stock','inactive'] as $statusOption): ?>
                        <option value="<?php echo htmlspecialchars($statusOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $formValues['status'] === $statusOption ? 'selected' : ''; ?>><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $statusOption)), ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group" style="grid-column: span 2;">
                <label class="admin-form-label">Notes</label>
                <textarea class="admin-form-control" name="description" rows="4"><?php echo htmlspecialchars($formValues['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:4px;">Save</button>
            <a href="/Frontend/admin/farm_items.php" class="btn btn-outline" style="border-radius:4px;">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<?php if ($selectedItem): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Item Details</h3>
            <p style="margin:6px 0 0 0;color:#64748b;">Details for this farm item.</p>
        </div>
        <a href="/Frontend/admin/farm_items.php" class="btn btn-outline" style="border-radius:4px;">Back</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
        <div><strong>Name:</strong> <?php echo htmlspecialchars($selectedItem['name'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Type:</strong> <?php echo htmlspecialchars($selectedItem['item_type'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Species:</strong> <?php echo htmlspecialchars($selectedItem['species'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Price:</strong> KES <?php echo number_format((float)$selectedItem['price'], 2); ?></div>
        <div><strong>Stock:</strong> <?php echo (int)$selectedItem['stock_quantity']; ?></div>
        <div><strong>Status:</strong> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $selectedItem['status'])), ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="grid-column: span 2;"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($selectedItem['description'] ?? '', ENT_QUOTES, 'UTF-8')); ?></div>
    </div>
</div>
<?php else: ?>
<div class="admin-card">
    <p style="color:#64748b;">Item not found.</p>
</div>
<?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/payments.php': r'''<?php
/**
 * Admin - Payments Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Payments - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_payment'])) {
    $paymentId = (int)($_POST['payment_id'] ?? 0);
    $category = trim($_POST['category'] ?? 'Supplier');
    $amount = (float)($_POST['amount'] ?? 0);
    $method = trim($_POST['method'] ?? 'Cash');
    $status = trim($_POST['status'] ?? 'Pending');
    $date = trim($_POST['date'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($category === '' || $amount <= 0) {
        $error_message = 'Category and positive amount are required.';
    } else {
        try {
            if ($paymentId > 0) {
                $stmt = $pdo->prepare('UPDATE financial_records SET category = ?, amount = ?, payment_method = ?, payment_status = ?, transaction_date = ?, description = ? WHERE id = ?');
                $stmt->execute([$category, $amount, $method, $status, $date ?: null, $description, $paymentId]);
                $message = 'Payment record updated successfully.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO financial_records (type, category, amount, transaction_date, description, payment_method, payment_status) VALUES ("expense", ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$category, $amount, $date ?: null, $description, $method, $status]);
                $message = 'Payment record saved successfully.';
            }
        } catch (Exception $e) {
            $error_message = 'Unable to save payment record: ' . $e->getMessage();
        }
    }
}

$payments = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT * FROM financial_records WHERE type = "expense" ORDER BY transaction_date DESC, created_at DESC');
    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$selectedPayment = null;
if (in_array($action, ['view', 'edit'], true) && $pdo) {
    $paymentId = (int)($_GET['id'] ?? 0);
    if ($paymentId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM financial_records WHERE id = ?');
        $stmt->execute([$paymentId]);
        $selectedPayment = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

function renderInput(string $label, string $name, string $value = '', string $type = 'text'): string {
    return '<div class="admin-form-group"><label class="admin-form-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label><input class="admin-form-control" type="' . $type . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"></div>';
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Payments</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Track payments and approvals.</p>
    </div>
    <a href="?action=add" class="btn btn-primary" style="border-radius:4px;display:inline-flex;align-items:center;gap:8px;">
        <i data-lucide="plus-circle" style="width:18px;height:18px;"></i>
        <span>Add Payment</span>
    </a>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Payment List</h3>
        <span style="font-size:0.85rem;color:#64748b;">See pending and approved payments</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                <tr><td colspan="6" style="text-align:center; padding: 20px; color: #64748b;">No payments found.</td></tr>
                <?php else: ?>
                <?php foreach ($payments as $payment): ?>
                <tr>
                    <td><?php echo htmlspecialchars($payment['category'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>KES <?php echo number_format((float)$payment['amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars($payment['payment_method'] ?? 'Cash', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($payment['payment_status'] ?? 'Pending', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($payment['transaction_date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align:right;">
                        <a class="btn btn-outline btn-sm" href="?action=view&id=<?php echo (int)$payment['id']; ?>">View</a>
                        <a class="btn btn-outline btn-sm" href="?action=edit&id=<?php echo (int)$payment['id']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<?php
    $formValues = [
        'category' => 'Supplier',
        'amount' => '',
        'method' => 'Cash',
        'status' => 'Pending',
        'date' => '',
        'description' => '',
    ];
    if ($action === 'edit' && $selectedPayment) {
        $formValues = [
            'category' => $selectedPayment['category'],
            'amount' => $selectedPayment['amount'],
            'method' => $selectedPayment['payment_method'] ?? 'Cash',
            'status' => $selectedPayment['payment_status'] ?? 'Pending',
            'date' => $selectedPayment['transaction_date'],
            'description' => $selectedPayment['description'],
        ];
    }
?>
<div class="admin-card">
    <h3 style="margin:0 0 16px 0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">
        <?php echo $action === 'add' ? 'Add Payment' : 'Edit Payment'; ?>
    </h3>
    <form method="POST" action="">
        <input type="hidden" name="save_payment" value="1">
        <?php if ($action === 'edit' && $selectedPayment): ?>
            <input type="hidden" name="payment_id" value="<?php echo (int)$selectedPayment['id']; ?>">
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
            <?php echo renderInput('Payment Category', 'category', $formValues['category']); ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Amount</label>
                <input class="admin-form-control" type="number" step="0.01" name="amount" value="<?php echo htmlspecialchars($formValues['amount'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <?php echo renderInput('Payment Method', 'method', $formValues['method']); ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Status</label>
                <select class="admin-form-control" name="status">
                    <?php foreach (['Pending', 'Approved', 'Failed', 'Completed'] as $paymentStatus): ?>
                        <option value="<?php echo htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $formValues['status'] === $paymentStatus ? 'selected' : ''; ?>><?php echo htmlspecialchars($paymentStatus, ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Date</label>
                <input class="admin-form-control" type="date" name="date" value="<?php echo htmlspecialchars($formValues['date'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="admin-form-group" style="grid-column: span 2;">
                <label class="admin-form-label">Notes</label>
                <textarea class="admin-form-control" name="description" rows="4"><?php echo htmlspecialchars($formValues['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:4px;">Save</button>
            <a href="/Frontend/admin/payments.php" class="btn btn-outline" style="border-radius:4px;">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Payment Details</h3>
            <p style="margin:6px 0 0 0;color:#64748b;">View payment status and notes.</p>
        </div>
        <a href="/Frontend/admin/payments.php" class="btn btn-outline" style="border-radius:4px;">Back</a>
    </div>
    <?php if ($selectedPayment): ?>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
        <div><strong>Category:</strong> <?php echo htmlspecialchars($selectedPayment['category'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Amount:</strong> KES <?php echo number_format((float)$selectedPayment['amount'], 2); ?></div>
        <div><strong>Method:</strong> <?php echo htmlspecialchars($selectedPayment['payment_method'] ?? 'Cash', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Status:</strong> <?php echo htmlspecialchars($selectedPayment['payment_status'] ?? 'Pending', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Date:</strong> <?php echo htmlspecialchars($selectedPayment['transaction_date'] ?: 'N/A', ENT_QUOTES, 'UTF-8'); ?></div>
        <div style="grid-column: span 2;"><strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($selectedPayment['description'] ?? '', ENT_QUOTES, 'UTF-8')); ?></div>
    </div>
    <?php else: ?>
    <p style="color:#64748b;">Payment record not found.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/sales.php': r'''<?php
/**
 * Admin - Sales Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Sales - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';

if ($pdo && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    $validStatuses = ['pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled'];

    if (in_array($newStatus, $validStatuses, true)) {
        try {
            $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
            $stmt->execute([$newStatus, $orderId]);
            $message = 'Order status updated successfully.';
        } catch (Exception $e) {
            $error_message = 'Unable to update order status: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Invalid order status selected.';
    }
}

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$orders = [];
if ($pdo) {
    $query = 'SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE 1=1';
    $params = [];
    if ($search !== '') {
        $query .= ' AND (o.order_number LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    if ($statusFilter !== '') {
        $query .= ' AND o.status = ?';
        $params[] = $statusFilter;
    }
    $query .= ' ORDER BY o.created_at DESC';
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$selectedOrder = null;
if ($action === 'view' && $pdo) {
    $orderId = (int)($_GET['id'] ?? 0);
    if ($orderId > 0) {
        $stmt = $pdo->prepare('SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?');
        $stmt->execute([$orderId]);
        $selectedOrder = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if ($selectedOrder) {
            $stmt = $pdo->prepare('SELECT oi.*, p.name FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?');
            $stmt->execute([$orderId]);
            $selectedOrder['items'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}

function renderInput(string $label, string $name, string $value = '', string $type = 'text'): string {
    return '<div class="admin-form-group"><label class="admin-form-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label><input class="admin-form-control" type="' . $type . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"></div>';
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Sales</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Manage customer sales and invoices.</p>
    </div>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Sales List</h3>
        <span style="font-size:0.85rem;color:#64748b;">Recent customer sales</span>
    </div>
    <div style="margin-bottom:16px;">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <input type="text" name="search" placeholder="Search orders..." value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" style="padding:10px;border:1px solid #cbd5e1;border-radius:6px;min-width:220px;">
            <select name="status" style="padding:10px;border:1px solid #cbd5e1;border-radius:6px;">
                <option value="">All Statuses</option>
                <?php foreach (['pending','paid','processing','shipped','completed','cancelled'] as $statusOption): ?>
                <option value="<?php echo $statusOption; ?>" <?php echo $statusFilter === $statusOption ? 'selected' : ''; ?>><?php echo ucfirst($statusOption); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-outline" style="border-radius:4px;">Filter</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr><td colspan="6" style="text-align:center; padding: 20px; color: #64748b;">No sales orders found.</td></tr>
                <?php else: ?>
                <?php foreach ($orders as $order): ?>
                <tr>
                    <td>#<?php echo htmlspecialchars($order['order_number'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($order['username'] ?: $order['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>KES <?php echo number_format((float)$order['total_amount'], 2); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($order['status']), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($order['created_at'] ?? 'now')), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align:right;">
                        <a class="btn btn-outline btn-sm" href="?action=view&id=<?php echo (int)$order['id']; ?>">View</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'view'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Order Details</h3>
            <p style="margin:6px 0 0 0;color:#64748b;">Review customer sales order information.</p>
        </div>
        <a href="/Frontend/admin/sales.php" class="btn btn-outline" style="border-radius:4px;">Back</a>
    </div>
    <?php if ($selectedOrder): ?>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;margin-bottom:20px;">
        <div><strong>Order #:</strong> <?php echo htmlspecialchars($selectedOrder['order_number'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($selectedOrder['status']), ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Customer:</strong> <?php echo htmlspecialchars($selectedOrder['username'] ?: 'Guest', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Email:</strong> <?php echo htmlspecialchars($selectedOrder['email'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Total:</strong> KES <?php echo number_format((float)$selectedOrder['total_amount'], 2); ?></div>
        <div><strong>Payment:</strong> <?php echo htmlspecialchars($selectedOrder['payment_method'], ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($selectedOrder['items'])): ?>
                <tr><td colspan="4" style="text-align:center; padding: 20px; color: #64748b;">No products found for this order.</td></tr>
                <?php else: ?>
                <?php foreach ($selectedOrder['items'] as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int)$item['quantity']; ?></td>
                    <td>KES <?php echo number_format((float)$item['price_at_purchase'], 2); ?></td>
                    <td>KES <?php echo number_format((float)$item['quantity'] * (float)$item['price_at_purchase'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p style="color:#64748b;">Order not found.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/staff.php': r'''<?php
/**
 * Admin - Staff Management Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Staff - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';
$currentAdminId = (int)($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_staff'])) {
    $staffId = (int)($_POST['staff_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $role = trim($_POST['role'] ?? 'farm_manager');
    $status = trim($_POST['status'] ?? 'active');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $email === '') {
        $error_message = 'Username and email are required.';
    } else {
        try {
            if ($staffId > 0) {
                $stmt = $pdo->prepare('UPDATE users SET username = ?, email = ?, first_name = ?, last_name = ?, phone_number = ?, role = ? WHERE id = ?');
                $stmt->execute([$username, $email, $firstName, $lastName, $phone, $role, $staffId]);
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
                    $stmt->execute([$hash, $staffId]);
                }
                $message = 'Staff record updated successfully.';
            } else {
                $hash = password_hash($password !== '' ? $password : 'Staff@123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, role, first_name, last_name, phone_number) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$username, $email, $hash, $role, $firstName, $lastName, $phone]);
                $message = 'Staff member added successfully.';
            }
        } catch (Exception $e) {
            $error_message = 'Unable to save staff member: ' . $e->getMessage();
        }
    }
}

$users = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT * FROM users WHERE role IN ("super_admin", "farm_manager", "stock_manager") ORDER BY created_at DESC');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$selectedUser = null;
if (in_array($action, ['view', 'edit'], true) && $pdo) {
    $userId = (int)($_GET['id'] ?? 0);
    if ($userId > 0) {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $selectedUser = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}

function renderInput(string $label, string $name, string $value = '', string $type = 'text'): string {
    return '<div class="admin-form-group"><label class="admin-form-label">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</label><input class="admin-form-control" type="' . $type . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"></div>';
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Staff</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Manage farm staff and team roles.</p>
    </div>
    <a href="?action=add" class="btn btn-primary" style="border-radius:4px;display:inline-flex;align-items:center;gap:8px;">
        <i data-lucide="user-plus" style="width:18px;height:18px;"></i>
        <span>Add Staff</span>
    </a>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Staff List</h3>
        <span style="font-size:0.85rem;color:#64748b;">Live staff accounts</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr><td colspan="5" style="text-align:center; padding: 20px; color: #64748b;">No staff members found.</td></tr>
                <?php else: ?>
                <?php foreach ($users as $staff): ?>
                <tr>
                    <td><?php echo htmlspecialchars(trim(($staff['first_name'] ?? '') . ' ' . ($staff['last_name'] ?? '')) ?: $staff['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($staff['role'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($staff['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($staff['phone_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align:right;">
                        <a class="btn btn-outline btn-sm" href="?action=view&id=<?php echo (int)$staff['id']; ?>">View</a>
                        <a class="btn btn-outline btn-sm" href="?action=edit&id=<?php echo (int)$staff['id']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>
<?php
    $formValues = [
        'username' => '',
        'email' => '',
        'first_name' => '',
        'last_name' => '',
        'phone' => '',
        'role' => 'farm_manager',
        'status' => 'active',
        'password' => '',
    ];
    if ($action === 'edit' && $selectedUser) {
        $formValues = [
            'username' => $selectedUser['username'],
            'email' => $selectedUser['email'],
            'first_name' => $selectedUser['first_name'],
            'last_name' => $selectedUser['last_name'],
            'phone' => $selectedUser['phone_number'] ?? '',
            'role' => $selectedUser['role'],
            'status' => 'active',
            'password' => '',
        ];
    }
?>
<div class="admin-card">
    <h3 style="margin:0 0 16px 0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">
        <?php echo $action === 'add' ? 'Add Staff' : 'Edit Staff'; ?>
    </h3>
    <form method="POST" action="">
        <input type="hidden" name="save_staff" value="1">
        <?php if ($action === 'edit' && $selectedUser): ?>
            <input type="hidden" name="staff_id" value="<?php echo (int)$selectedUser['id']; ?>">
        <?php endif; ?>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
            <?php echo renderInput('Username', 'username', $formValues['username']); ?>
            <?php echo renderInput('Email', 'email', $formValues['email'], 'email'); ?>
            <?php echo renderInput('First Name', 'first_name', $formValues['first_name']); ?>
            <?php echo renderInput('Last Name', 'last_name', $formValues['last_name']); ?>
            <?php echo renderInput('Phone', 'phone', $formValues['phone'], 'tel'); ?>
            <div class="admin-form-group">
                <label class="admin-form-label">Role</label>
                <select class="admin-form-control" name="role">
                    <?php foreach (['super_admin','farm_manager','stock_manager'] as $roleOption): ?>
                        <option value="<?php echo htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $formValues['role'] === $roleOption ? 'selected' : ''; ?>><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $roleOption)), ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php echo renderInput('Password', 'password', '', 'password'); ?>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:4px;">Save</button>
            <a href="/Frontend/admin/staff.php" class="btn btn-outline" style="border-radius:4px;">Cancel</a>
        </div>
    </form>
</div>

<?php else: ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Staff Details</h3>
            <p style="margin:6px 0 0 0;color:#64748b;">View staff member information.</p>
        </div>
        <a href="/Frontend/admin/staff.php" class="btn btn-outline" style="border-radius:4px;">Back</a>
    </div>
    <?php if ($selectedUser): ?>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
        <div><strong>Name:</strong> <?php echo htmlspecialchars(trim(($selectedUser['first_name'] ?? '') . ' ' . ($selectedUser['last_name'] ?? '')) ?: $selectedUser['username'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Role:</strong> <?php echo htmlspecialchars($selectedUser['role'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Email:</strong> <?php echo htmlspecialchars($selectedUser['email'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Phone:</strong> <?php echo htmlspecialchars($selectedUser['phone_number'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Created At:</strong> <?php echo htmlspecialchars($selectedUser['created_at'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <?php else: ?>
    <p style="color:#64748b;">Staff member not found.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/logs.php': r'''<?php
/**
 * Admin - System Logs Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Logs - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$logs = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT * FROM system_logs ORDER BY log_time DESC LIMIT 100');
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Logs</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Review recent system events and errors.</p>
    </div>
    <a href="logs.php" class="btn btn-outline" style="border-radius:4px;">Refresh</a>
</div>

<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Recent Logs</h3>
        <span style="font-size:0.85rem;color:#64748b;">Events from the system log table</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Level</th>
                    <th>Message</th>
                    <th>Context</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr><td colspan="4" style="text-align:center; padding:20px; color:#64748b;">No logs available.</td></tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['log_time'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($log['level'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($log['message'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($log['context'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/messages.php': r'''<?php
/**
 * Admin - Messages Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Messages - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager', 'stock_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$action = $_GET['action'] ?? 'list';
$message = '';
$error_message = '';
$currentAdminId = (int)($_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    $recipientId = (int)($_POST['recipient_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $body = trim($_POST['body'] ?? '');

    if ($recipientId <= 0 || $subject === '' || $body === '') {
        $error_message = 'Recipient, subject, and message body are required.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO messages (sender_id, recipient_id, subject, body, status) VALUES (?, ?, ?, ?, "pending")');
            $stmt->execute([$currentAdminId, $recipientId, $subject, $body]);
            $message = 'Message sent successfully.';
        } catch (Exception $e) {
            $error_message = 'Unable to send message: ' . $e->getMessage();
        }
    }
}

$users = [];
if ($pdo) {
    $stmt = $pdo->query('SELECT id, username, first_name, last_name FROM users WHERE id != ' . $currentAdminId . ' ORDER BY first_name ASC, last_name ASC');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$messages = [];
if ($pdo) {
    $stmt = $pdo->prepare('SELECT m.*, su.username AS sender_username, ru.username AS recipient_username FROM messages m LEFT JOIN users su ON m.sender_id = su.id LEFT JOIN users ru ON m.recipient_id = ru.id WHERE m.sender_id = ? OR m.recipient_id = ? ORDER BY m.created_at DESC');
    $stmt->execute([$currentAdminId, $currentAdminId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$selectedMessage = null;
if ($action === 'view' && $pdo) {
    $messageId = (int)($_GET['id'] ?? 0);
    if ($messageId > 0) {
        $stmt = $pdo->prepare('SELECT m.*, su.username AS sender_username, ru.username AS recipient_username FROM messages m LEFT JOIN users su ON m.sender_id = su.id LEFT JOIN users ru ON m.recipient_id = ru.id WHERE m.id = ?');
        $stmt->execute([$messageId]);
        $selectedMessage = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Messages</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Send quick notes to staff and check message status.</p>
    </div>
    <a href="?action=compose" class="btn btn-primary" style="border-radius:4px;display:inline-flex;align-items:center;gap:8px;">
        <i data-lucide="message-circle" style="width:18px;height:18px;"></i>
        <span>Compose</span>
    </a>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);">Message Inbox</h3>
        <span style="font-size:0.85rem;color:#64748b;">See messages you sent or received</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>To</th>
                    <th>Subject</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr><td colspan="5" style="text-align:center; padding:20px; color:#64748b;">No messages found.</td></tr>
                <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                <tr>
                    <td><?php echo htmlspecialchars($msg['recipient_username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($msg['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($msg['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($msg['status']), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align:right;"><a class="btn btn-outline btn-sm" href="?action=view&id=<?php echo (int)$msg['id']; ?>">View</a></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($action === 'compose'): ?>
<div class="admin-card">
    <h3 style="margin:0 0 16px 0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Compose Message</h3>
    <form method="POST" action="">
        <input type="hidden" name="send_message" value="1">
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;">
            <div class="admin-form-group">
                <label class="admin-form-label">Send to</label>
                <select class="admin-form-control" name="recipient_id" required>
                    <option value="">Select staff member...</option>
                    <?php foreach ($users as $user): ?>
                    <option value="<?php echo (int)$user['id']; ?>"><?php echo htmlspecialchars(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')) ?: $user['username'], ENT_QUOTES, 'UTF-8'); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php echo renderInput('Subject', 'subject'); ?>
            <div class="admin-form-group" style="grid-column: span 2;">
                <label class="admin-form-label">Message</label>
                <textarea class="admin-form-control" name="body" rows="6"></textarea>
            </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:4px;">Send</button>
            <a href="/Frontend/admin/messages.php" class="btn btn-outline" style="border-radius:4px;">Cancel</a>
        </div>
    </form>
</div>

<?php elseif ($action === 'view'): ?>
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.2rem;color:var(--admin-text-heading);">Message Details</h3>
            <p style="margin:6px 0 0 0;color:#64748b;">Review message content and status.</p>
        </div>
        <a href="/Frontend/admin/messages.php" class="btn btn-outline" style="border-radius:4px;">Back</a>
    </div>
    <?php if ($selectedMessage): ?>
    <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px; margin-bottom:18px;">
        <div><strong>From:</strong> <?php echo htmlspecialchars($selectedMessage['sender_username'] ?? 'System', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>To:</strong> <?php echo htmlspecialchars($selectedMessage['recipient_username'] ?? 'Unknown', ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Subject:</strong> <?php echo htmlspecialchars($selectedMessage['subject'], ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($selectedMessage['status']), ENT_QUOTES, 'UTF-8'); ?></div>
        <div><strong>Date:</strong> <?php echo htmlspecialchars($selectedMessage['created_at'], ENT_QUOTES, 'UTF-8'); ?></div>
    </div>
    <div style="padding:18px;background:#f8fafc;border:1px solid var(--admin-border);border-radius:8px;color:#334155;">
        <?php echo nl2br(htmlspecialchars($selectedMessage['body'], ENT_QUOTES, 'UTF-8')); ?>
    </div>
    <?php else: ?>
    <p style="color:#64748b;">Message not found.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/setup.php': r'''<?php
/**
 * Admin - Setup Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Setup - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$section = $_GET['section'] ?? 'species';
$message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_setup'])) {
    $groupKey = trim($_POST['group_key'] ?? $section);
    $optionLabel = trim($_POST['option_label'] ?? '');
    $optionValue = trim($_POST['option_value'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 10);

    if ($groupKey === '' || $optionLabel === '') {
        $error_message = 'Group key and option label are required.';
    } else {
        if ($optionValue === '') {
            $optionValue = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $optionLabel));
        }
        try {
            $stmt = $pdo->prepare('INSERT INTO system_dropdowns (group_key, group_label, option_value, option_label, sort_order, is_active, is_system) VALUES (?, ?, ?, ?, ?, 1, 0)');
            $label = ucwords(str_replace('_', ' ', $groupKey));
            $stmt->execute([$groupKey, $label, $optionValue, $optionLabel, $sortOrder]);
            $message = 'Setup option added successfully.';
        } catch (Exception $e) {
            $error_message = 'Unable to save setup option: ' . $e->getMessage();
        }
    }
}

$groupMap = [
    'species' => 'Species',
    'breeds' => 'Breeds',
    'vaccines' => 'Vaccines',
];
$selectedGroupLabel = $groupMap[$section] ?? 'Setup Group';

$options = [];
if ($pdo) {
    $stmt = $pdo->prepare('SELECT id, option_label, option_value, sort_order, is_active FROM system_dropdowns WHERE group_key = ? ORDER BY sort_order ASC, option_label ASC');
    $stmt->execute([$section]);
    $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Setup</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">Configure species, breeds, and health setup options.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="?section=species" class="btn <?php echo $section === 'species' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:4px;">Species</a>
        <a href="?section=breeds" class="btn <?php echo $section === 'breeds' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:4px;">Breeds</a>
        <a href="?section=vaccines" class="btn <?php echo $section === 'vaccines' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:4px;">Vaccines</a>
    </div>
</div>

<?php if ($message): ?>
<div style="padding:14px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:6px;color:#166534;margin-bottom:20px;">
    <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:14px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:6px;color:#b91c1c;margin-bottom:20px;">
    <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);"><?php echo htmlspecialchars($selectedGroupLabel, ENT_QUOTES, 'UTF-8'); ?></h3>
        <span style="font-size:0.85rem;color:#64748b;">Manage dropdown values stored in system dropdowns</span>
    </div>
    <?php if (empty($options)): ?>
        <p style="color:#64748b;">No setup values found for this section yet.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Value</th>
                    <th>Order</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($options as $opt): ?>
                <tr>
                    <td><?php echo htmlspecialchars($opt['option_label'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($opt['option_value'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int)$opt['sort_order']; ?></td>
                    <td><?php echo (int)$opt['is_active'] === 1 ? 'Active' : 'Inactive'; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <form method="POST" style="margin-top:24px;">
        <input type="hidden" name="save_setup" value="1">
        <input type="hidden" name="group_key" value="<?php echo htmlspecialchars($section, ENT_QUOTES, 'UTF-8'); ?>">
        <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:20px;">
            <div class="admin-form-group">
                <label class="admin-form-label">Option Label</label>
                <input class="admin-form-control" type="text" name="option_label" required>
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Option Value</label>
                <input class="admin-form-control" type="text" name="option_value" placeholder="Auto-generated if blank">
            </div>
            <div class="admin-form-group">
                <label class="admin-form-label">Sort Order</label>
                <input class="admin-form-control" type="number" name="sort_order" value="10">
            </div>
        </div>
        <div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="btn btn-primary" style="border-radius:4px;">Add Option</button>
            <a href="/Frontend/admin/setup.php?section=<?php echo htmlspecialchars($section, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-outline" style="border-radius:4px;">Refresh</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
    'Frontend/admin/calendar.php': r'''<?php
/**
 * Admin - Calendar Module
 */
declare(strict_types=1);

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

$page_title = 'Calendar - Admin';
include __DIR__ . '/includes/admin_header.php';

if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager', 'stock_manager'], true)) {
    header('Location: /busiaadmin');
    exit;
}

$pdo = getDB();
$view = $_GET['view'] ?? 'month';
$tasks = [];
$events = [];

if ($pdo) {
    $stmt = $pdo->query('SELECT id, title, due_date, status FROM tasks ORDER BY due_date ASC, created_at DESC LIMIT 20');
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->query('SELECT id, vaccine_name AS title, scheduled_date AS date, status FROM vaccinations ORDER BY scheduled_date ASC LIMIT 20');
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:16px;">
    <div>
        <h2 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.5rem;color:var(--admin-text-heading);">Calendar</h2>
        <p style="margin:4px 0 0 0;font-size:0.9rem;color:#64748b;">View farm tasks and health schedules.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="?view=month" class="btn <?php echo $view === 'month' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:4px;">Month</a>
        <a href="?view=week" class="btn <?php echo $view === 'week' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:4px;">Week</a>
        <a href="?view=day" class="btn <?php echo $view === 'day' ? 'btn-primary' : 'btn-outline'; ?>" style="border-radius:4px;">Day</a>
    </div>
</div>

<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;color:var(--admin-text-heading);"><?php echo ucfirst($view); ?> View</h3>
        <span style="font-size:0.85rem;color:#64748b;">Schedule overview from tasks and vaccinations</span>
    </div>
    <?php if (empty($tasks) && empty($events)): ?>
        <p style="color:#64748b;">No scheduled items or events found.</p>
    <?php else: ?>
        <div style="display:grid;gap:14px;">
            <?php foreach ($tasks as $task): ?>
                <div style="border:1px solid var(--admin-border);border-radius:8px;padding:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div>
                        <div style="font-weight:600;color:var(--admin-text-heading);"><?php echo htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div style="color:#64748b;font-size:0.9rem;">Due: <?php echo htmlspecialchars($task['due_date'] ?: 'TBD', ENT_QUOTES, 'UTF-8'); ?> &middot; <?php echo htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <span class="badge-pill badge-pill-primary">Task</span>
                </div>
            <?php endforeach; ?>
            <?php foreach ($events as $event): ?>
                <div style="border:1px solid var(--admin-border);border-radius:8px;padding:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                    <div>
                        <div style="font-weight:600;color:var(--admin-text-heading);"><?php echo htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8'); ?></div>
                        <div style="color:#64748b;font-size:0.9rem;">Date: <?php echo htmlspecialchars($event['date'] ?: 'TBD', ENT_QUOTES, 'UTF-8'); ?> &middot; <?php echo htmlspecialchars($event['status'], ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                    <span class="badge-pill badge-pill-success">Vaccination</span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
''',
}

for rel_path, content in pages.items():
    path = root / rel_path
    path.write_text(content, encoding='utf-8')

# Update migration file
migration_path = root / 'Backend' / 'config' / 'migration_v5_admin_extensions.sql'
if migration_path.exists():
    migration = migration_path.read_text(encoding='utf-8')
    replacement = r"""-- Migration V5: Admin Modules and Activity Tables

CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `assigned_to` INT NULL,
    `due_date` DATE NULL,
    `status` ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `created_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `sender_id` INT NULL,
    `recipient_id` INT NULL,
    `subject` VARCHAR(150) NOT NULL,
    `body` TEXT NOT NULL,
    `status` ENUM('pending', 'sent', 'read') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`recipient_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `log_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `level` ENUM('INFO', 'WARNING', 'ERROR', 'DEBUG') NOT NULL DEFAULT 'INFO',
    `message` TEXT NOT NULL,
    `context` VARCHAR(255) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `herds` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `species` VARCHAR(100) DEFAULT NULL,
    `size` INT DEFAULT 0,
    `location` VARCHAR(200) DEFAULT NULL,
    `status` ENUM('Active', 'Sold', 'Archived') DEFAULT 'Active',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `animals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tag` VARCHAR(100) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `type` VARCHAR(100) DEFAULT NULL,
    `breed` VARCHAR(100) DEFAULT NULL,
    `gender` VARCHAR(50) DEFAULT NULL,
    `birth_date` DATE DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Active',
    `herd_id` INT DEFAULT NULL,
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`herd_id`) REFERENCES `herds`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `breeding_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100) DEFAULT NULL,
    `male_parent` VARCHAR(150) DEFAULT NULL,
    `date` DATE DEFAULT NULL,
    `due_date` DATE DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Pending',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `health_records` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject` VARCHAR(255) NOT NULL,
    `type` VARCHAR(100) DEFAULT NULL,
    `product` VARCHAR(150) DEFAULT NULL,
    `date` DATE DEFAULT NULL,
    `next_date` DATE DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'Scheduled',
    `notes` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `farm_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `item_type` VARCHAR(100) NOT NULL,
    `species` VARCHAR(100) DEFAULT NULL,
    `price` DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    `stock_quantity` INT DEFAULT 0,
    `status` ENUM('active', 'out_of_stock', 'inactive') DEFAULT 'active',
    `description` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE `financial_records`
    ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(50) DEFAULT 'Cash',
    ADD COLUMN IF NOT EXISTS `payment_status` ENUM('Pending', 'Approved', 'Failed', 'Completed') DEFAULT 'Pending';

ALTER TABLE `raw_materials`
    ADD COLUMN IF NOT EXISTS `feed_type` VARCHAR(100) DEFAULT 'Feed';

ALTER TABLE `users`
    MODIFY COLUMN `role` ENUM('super_admin', 'farm_manager', 'stock_manager', 'customer') DEFAULT 'customer';
"""
    migration_path.write_text(replacement, encoding='utf-8')
else:
    raise FileNotFoundError(str(migration_path))