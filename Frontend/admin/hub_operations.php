<?php
/**
 * Hub: Farm Operations — Flocks, Animals, Health, Breeding, Herds, Vaccinations, Production
 */
declare(strict_types=1);
$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();
$page_title = 'Farm Operations - Admin';
include __DIR__ . '/includes/admin_header.php';

$tab = $_GET['tab'] ?? 'flocks';
$validTabs = ['flocks','animals','health','breeding','herds','vaccinations','production'];
if (!in_array($tab, $validTabs, true)) $tab = 'flocks';

$pdo = getDB();
$message = ''; $error_message = '';

/* ── handle POST actions ─────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pdo) {
    $postAction = $_POST['_action'] ?? '';

    /* ─ Animals ─ */
    if ($postAction === 'save_animal') {
        $id = (int)($_POST['id'] ?? 0);
        $tag   = trim($_POST['tag_id'] ?? '');
        $name  = trim($_POST['name'] ?? '');
        $species = trim($_POST['species'] ?? '');
        $breed  = trim($_POST['breed'] ?? '');
        $gender = trim($_POST['gender'] ?? '');
        $dob    = trim($_POST['dob'] ?? '');
        $status = trim($_POST['status'] ?? 'alive');
        $notes  = trim($_POST['notes'] ?? '');
        try {
            if ($id > 0) {
                $pdo->prepare('UPDATE animals SET tag_id=?,name=?,species=?,breed=?,gender=?,date_of_birth=?,status=?,notes=? WHERE id=?')
                    ->execute([$tag,$name,$species,$breed,$gender,$dob?:null,$status,$notes,$id]);
                $message = 'Animal record updated.';
            } else {
                $pdo->prepare('INSERT INTO animals (tag_id,name,species,breed,gender,date_of_birth,status,notes) VALUES (?,?,?,?,?,?,?,?)')
                    ->execute([$tag,$name,$species,$breed,$gender,$dob?:null,$status,$notes]);
                $message = 'Animal added successfully.';
            }
        } catch (Exception $e) { $error_message = $e->getMessage(); }
        $tab = 'animals';
    }

    /* ─ Herds ─ */
    if ($postAction === 'save_herd') {
        $id  = (int)($_POST['id'] ?? 0);
        $n   = trim($_POST['name'] ?? '');
        $type = trim($_POST['type'] ?? '');
        $loc  = trim($_POST['location'] ?? '');
        $cnt  = (int)($_POST['head_count'] ?? 0);
        try {
            if ($id > 0) {
                $pdo->prepare('UPDATE herds SET name=?,type=?,location=?,head_count=? WHERE id=?')->execute([$n,$type,$loc,$cnt,$id]);
                $message = 'Herd updated.';
            } else {
                $pdo->prepare('INSERT INTO herds (name,type,location,head_count) VALUES (?,?,?,?)')->execute([$n,$type,$loc,$cnt]);
                $message = 'Herd created.';
            }
        } catch (Exception $e) { $error_message = $e->getMessage(); }
        $tab = 'herds';
    }

    /* ─ Health Records ─ */
    if ($postAction === 'save_health') {
        $id     = (int)($_POST['id'] ?? 0);
        $animal = (int)($_POST['animal_id'] ?? 0);
        $date   = trim($_POST['record_date'] ?? date('Y-m-d'));
        $diag   = trim($_POST['diagnosis'] ?? '');
        $treat  = trim($_POST['treatment'] ?? '');
        $vet    = trim($_POST['vet_name'] ?? '');
        $cost   = (float)($_POST['cost'] ?? 0);
        $notes  = trim($_POST['notes'] ?? '');
        try {
            if ($id > 0) {
                $pdo->prepare('UPDATE health_records SET animal_id=?,record_date=?,diagnosis=?,treatment=?,vet_name=?,cost=?,notes=? WHERE id=?')
                    ->execute([$animal?:null,$date,$diag,$treat,$vet,$cost?:null,$notes,$id]);
                $message = 'Health record updated.';
            } else {
                $pdo->prepare('INSERT INTO health_records (animal_id,record_date,diagnosis,treatment,vet_name,cost,notes) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$animal?:null,$date,$diag,$treat,$vet,$cost?:null,$notes]);
                $message = 'Health record logged.';
            }
        } catch (Exception $e) { $error_message = $e->getMessage(); }
        $tab = 'health';
    }

    /* ─ Breeding ─ */
    if ($postAction === 'save_breeding') {
        $id     = (int)($_POST['id'] ?? 0);
        $sire   = trim($_POST['sire'] ?? '');
        $dam    = trim($_POST['dam'] ?? '');
        $date   = trim($_POST['breeding_date'] ?? date('Y-m-d'));
        $exp    = trim($_POST['expected_birth'] ?? '');
        $status = trim($_POST['status'] ?? 'Pending');
        $notes  = trim($_POST['notes'] ?? '');
        try {
            if ($id > 0) {
                $pdo->prepare('UPDATE breeding_records SET sire=?,dam=?,breeding_date=?,expected_birth=?,status=?,notes=? WHERE id=?')
                    ->execute([$sire,$dam,$date,$exp?:null,$status,$notes,$id]);
                $message = 'Breeding record updated.';
            } else {
                $pdo->prepare('INSERT INTO breeding_records (sire,dam,breeding_date,expected_birth,status,notes) VALUES (?,?,?,?,?,?)')
                    ->execute([$sire,$dam,$date,$exp?:null,$status,$notes]);
                $message = 'Breeding record saved.';
            }
        } catch (Exception $e) { $error_message = $e->getMessage(); }
        $tab = 'breeding';
    }
}

/* ── load data per tab ──────────────────────────────────────── */
$animals = $herds = $healthRecords = $breedingRecords = [];
if ($pdo) {
    try {
        if ($tab === 'animals') {
            $animals = $pdo->query('SELECT * FROM animals ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($tab === 'herds') {
            $herds = $pdo->query('SELECT * FROM herds ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($tab === 'health') {
            $healthRecords = $pdo->query('SELECT hr.*, a.name AS animal_name, a.tag_id FROM health_records hr LEFT JOIN animals a ON hr.animal_id = a.id ORDER BY hr.record_date DESC')->fetchAll(PDO::FETCH_ASSOC);
        } elseif ($tab === 'breeding') {
            $breedingRecords = $pdo->query('SELECT * FROM breeding_records ORDER BY breeding_date DESC')->fetchAll(PDO::FETCH_ASSOC);
        }
        $animalList = $pdo->query("SELECT id, CONCAT(COALESCE(tag_id,''), ' – ', COALESCE(name,'')) AS label FROM animals ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $animalList = [];
    }
}

$tabs = [
    'flocks'      => ['icon' => 'layers',           'label' => 'Flocks'],
    'animals'     => ['icon' => 'paw-print',         'label' => 'Animals'],
    'health'      => ['icon' => 'heart-pulse',       'label' => 'Health'],
    'breeding'    => ['icon' => 'dna',               'label' => 'Breeding'],
    'herds'       => ['icon' => 'users',             'label' => 'Herds'],
    'vaccinations'=> ['icon' => 'syringe',           'label' => 'Vaccinations'],
    'production'  => ['icon' => 'trending-up',       'label' => 'Production'],
];
?>

<!-- Page header -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <div>
        <h1 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.6rem;color:var(--admin-text-heading);font-weight:800;">Farm Operations</h1>
        <p style="margin:4px 0 0;color:#64748b;font-size:0.9rem;">Manage all your livestock, poultry, health, and breeding records in one place.</p>
    </div>
</div>

<!-- Alerts -->
<?php if ($message): ?>
<div style="padding:13px 18px;background:#dcfce7;border:1px solid #bbf7d0;border-radius:8px;color:#166534;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <i data-lucide="check-circle-2" style="width:18px;height:18px;"></i> <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>
<?php if ($error_message): ?>
<div style="padding:13px 18px;background:#fee2e2;border:1px solid #fecaca;border-radius:8px;color:#b91c1c;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
    <i data-lucide="alert-circle" style="width:18px;height:18px;"></i> <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
</div>
<?php endif; ?>

<!-- Tab Bar -->
<div style="display:flex;gap:4px;background:#f1f5f9;padding:5px;border-radius:10px;margin-bottom:24px;overflow-x:auto;scrollbar-width:none;">
<?php foreach ($tabs as $key => $info): ?>
    <a href="?tab=<?php echo $key; ?>"
       style="display:flex;align-items:center;gap:7px;padding:9px 16px;border-radius:7px;text-decoration:none;white-space:nowrap;font-weight:600;font-size:0.86rem;transition:all 0.2s;
              <?php echo $tab === $key ? 'background:#fff;color:var(--admin-primary);box-shadow:0 1px 6px rgba(15,23,42,0.08);' : 'color:#64748b;'; ?>">
        <i data-lucide="<?php echo $info['icon']; ?>" style="width:15px;height:15px;"></i>
        <?php echo $info['label']; ?>
    </a>
<?php endforeach; ?>
</div>

<!-- ═══════════════════ FLOCKS TAB ═══════════════════ -->
<?php if ($tab === 'flocks'): include __DIR__ . '/flocks.php'; ?>
<?php // flocks.php is self-contained API-driven, but we include it via iframe logic below ?>

<?php elseif ($tab === 'animals'): ?>
<!-- ═══════════════════ ANIMALS TAB ═══════════════════ -->
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Animal Records</h3>
            <p style="margin:4px 0 0;font-size:0.85rem;color:#64748b;">Tag and track individual animals on your farm.</p>
        </div>
        <button class="btn btn-primary" onclick="openAnimalModal()">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Add Animal
        </button>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Tag / ID</th><th>Name</th><th>Species</th><th>Breed</th><th>Gender</th><th>DOB</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($animals)): ?>
                <tr><td colspan="8" style="text-align:center;padding:28px;color:#94a3b8;">No animals registered yet. Click "Add Animal" to start.</td></tr>
            <?php else: foreach ($animals as $a): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($a['tag_id'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><?php echo htmlspecialchars($a['name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($a['species'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($a['breed'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($a['gender'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($a['date_of_birth'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge-pill <?php echo $a['status'] === 'alive' ? 'badge-pill-success' : 'badge-pill-danger'; ?>"><?php echo htmlspecialchars(ucfirst($a['status'] ?? 'alive'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td>
                        <div class="tbl-actions">
                            <button class="btn btn-trans btn-sm" onclick='openAnimalModal(<?php echo htmlspecialchars(json_encode($a), ENT_QUOTES, "UTF-8"); ?>)'>
                                <i data-lucide="pencil" style="width:13px;height:13px;"></i> Edit
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Animal Modal -->
<div id="animal-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;width:100%;max-width:560px;box-shadow:0 20px 40px rgba(0,0,0,0.15);max-height:90vh;overflow-y:auto;">
        <h3 id="animal-modal-title" style="margin:0 0 22px;font-family:'Outfit',sans-serif;">Add Animal</h3>
        <form method="POST">
            <input type="hidden" name="_action" value="save_animal">
            <input type="hidden" name="id" id="animal-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="admin-form-group"><label class="admin-form-label">Tag / ID</label><input class="admin-form-control" name="tag_id" id="a-tag" placeholder="e.g. A-001"></div>
                <div class="admin-form-group"><label class="admin-form-label">Name</label><input class="admin-form-control" name="name" id="a-name" placeholder="e.g. Bessie"></div>
                <div class="admin-form-group"><label class="admin-form-label">Species</label>
                    <select class="admin-form-control" name="species" id="a-species">
                        <?php foreach (['Chicken','Cow','Goat','Pig','Sheep','Duck','Rabbit','Other'] as $sp): ?>
                        <option><?php echo $sp; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group"><label class="admin-form-label">Breed</label><input class="admin-form-control" name="breed" id="a-breed" placeholder="e.g. ISA Brown"></div>
                <div class="admin-form-group"><label class="admin-form-label">Gender</label>
                    <select class="admin-form-control" name="gender" id="a-gender">
                        <option value="male">Male</option><option value="female">Female</option><option value="unknown">Unknown</option>
                    </select>
                </div>
                <div class="admin-form-group"><label class="admin-form-label">Date of Birth</label><input class="admin-form-control" type="date" name="dob" id="a-dob"></div>
                <div class="admin-form-group"><label class="admin-form-label">Status</label>
                    <select class="admin-form-control" name="status" id="a-status">
                        <option value="alive">Alive</option><option value="sold">Sold</option><option value="deceased">Deceased</option><option value="sick">Sick</option>
                    </select>
                </div>
                <div class="admin-form-group" style="grid-column:span 2"><label class="admin-form-label">Notes</label><textarea class="admin-form-control" name="notes" id="a-notes" rows="3"></textarea></div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeAnimalModal()"><i data-lucide="x" style="width:15px;height:15px;"></i> Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Animal</button>
            </div>
        </form>
    </div>
</div>

<?php elseif ($tab === 'health'): ?>
<!-- ═══════════════════ HEALTH TAB ═══════════════════ -->
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Health Records</h3>
            <p style="margin:4px 0 0;font-size:0.85rem;color:#64748b;">Log diagnoses, treatments, vet visits, and medication costs.</p>
        </div>
        <button class="btn btn-primary" onclick="openHealthModal()">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Log Health Record
        </button>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Date</th><th>Animal</th><th>Diagnosis</th><th>Treatment</th><th>Vet</th><th>Cost (KES)</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($healthRecords)): ?>
                <tr><td colspan="7" style="text-align:center;padding:28px;color:#94a3b8;">No health records yet. Click "Log Health Record" to start.</td></tr>
            <?php else: foreach ($healthRecords as $h): ?>
                <tr>
                    <td><?php echo htmlspecialchars($h['record_date'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars(trim(($h['tag_id'] ?? '') . ' ' . ($h['animal_name'] ?? 'General')), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($h['diagnosis'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($h['treatment'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($h['vet_name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo $h['cost'] ? number_format((float)$h['cost'], 2) : '-'; ?></td>
                    <td>
                        <div class="tbl-actions">
                            <button class="btn btn-trans btn-sm" onclick='openHealthModal(<?php echo htmlspecialchars(json_encode($h), ENT_QUOTES, "UTF-8"); ?>)'>
                                <i data-lucide="pencil" style="width:13px;height:13px;"></i> Edit
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Health Modal -->
<div id="health-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;width:100%;max-width:560px;box-shadow:0 20px 40px rgba(0,0,0,0.15);max-height:90vh;overflow-y:auto;">
        <h3 id="health-modal-title" style="margin:0 0 22px;font-family:'Outfit',sans-serif;">Log Health Record</h3>
        <form method="POST">
            <input type="hidden" name="_action" value="save_health">
            <input type="hidden" name="id" id="h-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="admin-form-group"><label class="admin-form-label">Date</label><input class="admin-form-control" type="date" name="record_date" id="h-date" value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="admin-form-group"><label class="admin-form-label">Animal (optional)</label>
                    <select class="admin-form-control" name="animal_id" id="h-animal">
                        <option value="">-- General / Flock --</option>
                        <?php foreach ($animalList as $al): ?>
                        <option value="<?php echo (int)$al['id']; ?>"><?php echo htmlspecialchars($al['label'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group" style="grid-column:span 2"><label class="admin-form-label">Diagnosis / Condition</label><input class="admin-form-control" name="diagnosis" id="h-diag" placeholder="e.g. Newcastle Disease"></div>
                <div class="admin-form-group" style="grid-column:span 2"><label class="admin-form-label">Treatment / Medication</label><input class="admin-form-control" name="treatment" id="h-treat" placeholder="e.g. Lasota Vaccine, 3ml IM"></div>
                <div class="admin-form-group"><label class="admin-form-label">Vet Name</label><input class="admin-form-control" name="vet_name" id="h-vet" placeholder="e.g. Dr. Kamau"></div>
                <div class="admin-form-group"><label class="admin-form-label">Cost (KES)</label><input class="admin-form-control" type="number" step="0.01" name="cost" id="h-cost" placeholder="0.00"></div>
                <div class="admin-form-group" style="grid-column:span 2"><label class="admin-form-label">Notes</label><textarea class="admin-form-control" name="notes" id="h-notes" rows="3"></textarea></div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeHealthModal()"><i data-lucide="x" style="width:15px;height:15px;"></i> Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Record</button>
            </div>
        </form>
    </div>
</div>

<?php elseif ($tab === 'breeding'): ?>
<!-- ═══════════════════ BREEDING TAB ═══════════════════ -->
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Breeding Records</h3>
            <p style="margin:4px 0 0;font-size:0.85rem;color:#64748b;">Track mating events, expected births, and offspring outcomes.</p>
        </div>
        <button class="btn btn-primary" onclick="openBreedingModal()">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Record Breeding
        </button>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Date</th><th>Sire (Father)</th><th>Dam (Mother)</th><th>Expected Birth</th><th>Status</th><th>Notes</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($breedingRecords)): ?>
                <tr><td colspan="7" style="text-align:center;padding:28px;color:#94a3b8;">No breeding records yet.</td></tr>
            <?php else: foreach ($breedingRecords as $b): ?>
                <tr>
                    <td><?php echo htmlspecialchars($b['breeding_date'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($b['sire'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($b['dam'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($b['expected_birth'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><span class="badge-pill <?php echo $b['status']==='Born' ? 'badge-pill-success' : ($b['status']==='Pending'?'badge-pill-warning':'badge-pill-danger'); ?>"><?php echo htmlspecialchars($b['status'] ?? 'Pending', ENT_QUOTES, 'UTF-8'); ?></span></td>
                    <td><?php echo htmlspecialchars(mb_strimwidth($b['notes'] ?? '-', 0, 40, '…'), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <div class="tbl-actions">
                            <button class="btn btn-trans btn-sm" onclick='openBreedingModal(<?php echo htmlspecialchars(json_encode($b), ENT_QUOTES, "UTF-8"); ?>)'>
                                <i data-lucide="pencil" style="width:13px;height:13px;"></i> Edit
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Breeding Modal -->
<div id="breeding-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;width:100%;max-width:520px;box-shadow:0 20px 40px rgba(0,0,0,0.15);">
        <h3 id="breeding-modal-title" style="margin:0 0 22px;font-family:'Outfit',sans-serif;">Record Breeding Event</h3>
        <form method="POST">
            <input type="hidden" name="_action" value="save_breeding">
            <input type="hidden" name="id" id="br-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="admin-form-group"><label class="admin-form-label">Sire (Father) ID/Tag</label><input class="admin-form-control" name="sire" id="br-sire" placeholder="e.g. A-003"></div>
                <div class="admin-form-group"><label class="admin-form-label">Dam (Mother) ID/Tag</label><input class="admin-form-control" name="dam" id="br-dam" placeholder="e.g. A-007"></div>
                <div class="admin-form-group"><label class="admin-form-label">Breeding Date</label><input class="admin-form-control" type="date" name="breeding_date" id="br-date" value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="admin-form-group"><label class="admin-form-label">Expected Birth</label><input class="admin-form-control" type="date" name="expected_birth" id="br-exp"></div>
                <div class="admin-form-group"><label class="admin-form-label">Status</label>
                    <select class="admin-form-control" name="status" id="br-status">
                        <option>Pending</option><option>Born</option><option>Failed</option><option>Aborted</option>
                    </select>
                </div>
                <div class="admin-form-group" style="grid-column:span 2"><label class="admin-form-label">Notes</label><textarea class="admin-form-control" name="notes" id="br-notes" rows="3"></textarea></div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeBreedingModal()"><i data-lucide="x" style="width:15px;height:15px;"></i> Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Record</button>
            </div>
        </form>
    </div>
</div>

<?php elseif ($tab === 'herds'): ?>
<!-- ═══════════════════ HERDS TAB ═══════════════════ -->
<div class="admin-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;">
        <div>
            <h3 style="margin:0;font-family:'Outfit',sans-serif;font-size:1.1rem;">Herd Groups</h3>
            <p style="margin:4px 0 0;font-size:0.85rem;color:#64748b;">Group animals into manageable herds or pens.</p>
        </div>
        <button class="btn btn-primary" onclick="openHerdModal()">
            <i data-lucide="plus-circle" style="width:16px;height:16px;"></i> Add Herd
        </button>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>Name</th><th>Type</th><th>Location / Pen</th><th>Head Count</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if (empty($herds)): ?>
                <tr><td colspan="5" style="text-align:center;padding:28px;color:#94a3b8;">No herds created yet.</td></tr>
            <?php else: foreach ($herds as $hd): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($hd['name'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong></td>
                    <td><?php echo htmlspecialchars($hd['type'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo htmlspecialchars($hd['location'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?php echo (int)($hd['head_count'] ?? 0); ?></td>
                    <td>
                        <div class="tbl-actions">
                            <button class="btn btn-trans btn-sm" onclick='openHerdModal(<?php echo htmlspecialchars(json_encode($hd), ENT_QUOTES, "UTF-8"); ?>)'>
                                <i data-lucide="pencil" style="width:13px;height:13px;"></i> Edit
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<!-- Herd Modal -->
<div id="herd-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:2000;align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px;border-radius:12px;width:100%;max-width:480px;box-shadow:0 20px 40px rgba(0,0,0,0.15);">
        <h3 id="herd-modal-title" style="margin:0 0 22px;font-family:'Outfit',sans-serif;">Add Herd / Group</h3>
        <form method="POST">
            <input type="hidden" name="_action" value="save_herd">
            <input type="hidden" name="id" id="herd-id">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="admin-form-group" style="grid-column:span 2"><label class="admin-form-label">Herd / Group Name</label><input class="admin-form-control" name="name" id="herd-name" required placeholder="e.g. Pen A – Layers"></div>
                <div class="admin-form-group"><label class="admin-form-label">Animal Type</label>
                    <select class="admin-form-control" name="type" id="herd-type">
                        <?php foreach (['Chicken','Cow','Goat','Pig','Sheep','Mixed','Other'] as $ht): ?><option><?php echo $ht; ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="admin-form-group"><label class="admin-form-label">Location / Pen</label><input class="admin-form-control" name="location" id="herd-loc" placeholder="e.g. Block B, Pen 3"></div>
                <div class="admin-form-group"><label class="admin-form-label">Head Count</label><input class="admin-form-control" type="number" name="head_count" id="herd-count" min="0" value="0"></div>
            </div>
            <div style="display:flex;gap:12px;margin-top:20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeHerdModal()"><i data-lucide="x" style="width:15px;height:15px;"></i> Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;"><i data-lucide="save" style="width:15px;height:15px;"></i> Save Herd</button>
            </div>
        </form>
    </div>
</div>

<?php elseif ($tab === 'vaccinations'): ?>
<!-- ═══════════════════ VACCINATIONS TAB ═══════════════════ -->
<?php include __DIR__ . '/vaccinations.php'; ?>

<?php elseif ($tab === 'production'): ?>
<!-- ═══════════════════ PRODUCTION TAB ═══════════════════ -->
<?php include __DIR__ . '/production.php'; ?>

<?php endif; ?>

<script>
/* ── Animal Modal ── */
function openAnimalModal(data) {
    const modal = document.getElementById('animal-modal');
    const isEdit = data && data.id;
    document.getElementById('animal-modal-title').textContent = isEdit ? 'Edit Animal' : 'Add Animal';
    document.getElementById('animal-id').value   = isEdit ? data.id : '';
    document.getElementById('a-tag').value        = data?.tag_id || '';
    document.getElementById('a-name').value       = data?.name || '';
    document.getElementById('a-species').value    = data?.species || 'Chicken';
    document.getElementById('a-breed').value      = data?.breed || '';
    document.getElementById('a-gender').value     = data?.gender || 'female';
    document.getElementById('a-dob').value        = data?.date_of_birth || '';
    document.getElementById('a-status').value     = data?.status || 'alive';
    document.getElementById('a-notes').value      = data?.notes || '';
    modal.style.display = 'flex';
}
function closeAnimalModal() { document.getElementById('animal-modal').style.display = 'none'; }

/* ── Health Modal ── */
function openHealthModal(data) {
    const modal = document.getElementById('health-modal');
    const isEdit = data && data.id;
    document.getElementById('health-modal-title').textContent = isEdit ? 'Edit Health Record' : 'Log Health Record';
    document.getElementById('h-id').value     = isEdit ? data.id : '';
    document.getElementById('h-date').value   = data?.record_date || '<?php echo date("Y-m-d"); ?>';
    document.getElementById('h-animal').value = data?.animal_id || '';
    document.getElementById('h-diag').value   = data?.diagnosis || '';
    document.getElementById('h-treat').value  = data?.treatment || '';
    document.getElementById('h-vet').value    = data?.vet_name || '';
    document.getElementById('h-cost').value   = data?.cost || '';
    document.getElementById('h-notes').value  = data?.notes || '';
    modal.style.display = 'flex';
}
function closeHealthModal() { document.getElementById('health-modal').style.display = 'none'; }

/* ── Breeding Modal ── */
function openBreedingModal(data) {
    const modal = document.getElementById('breeding-modal');
    const isEdit = data && data.id;
    document.getElementById('breeding-modal-title').textContent = isEdit ? 'Edit Breeding Record' : 'Record Breeding Event';
    document.getElementById('br-id').value     = isEdit ? data.id : '';
    document.getElementById('br-sire').value   = data?.sire || '';
    document.getElementById('br-dam').value    = data?.dam || '';
    document.getElementById('br-date').value   = data?.breeding_date || '<?php echo date("Y-m-d"); ?>';
    document.getElementById('br-exp').value    = data?.expected_birth || '';
    document.getElementById('br-status').value = data?.status || 'Pending';
    document.getElementById('br-notes').value  = data?.notes || '';
    modal.style.display = 'flex';
}
function closeBreedingModal() { document.getElementById('breeding-modal').style.display = 'none'; }

/* ── Herd Modal ── */
function openHerdModal(data) {
    const modal = document.getElementById('herd-modal');
    const isEdit = data && data.id;
    document.getElementById('herd-modal-title').textContent = isEdit ? 'Edit Herd' : 'Add Herd / Group';
    document.getElementById('herd-id').value    = isEdit ? data.id : '';
    document.getElementById('herd-name').value  = data?.name || '';
    document.getElementById('herd-type').value  = data?.type || 'Chicken';
    document.getElementById('herd-loc').value   = data?.location || '';
    document.getElementById('herd-count').value = data?.head_count || 0;
    modal.style.display = 'flex';
}
function closeHerdModal() { document.getElementById('herd-modal').style.display = 'none'; }

/* Close modals when clicking backdrop */
document.addEventListener('click', function(e) {
    ['animal-modal','health-modal','breeding-modal','herd-modal'].forEach(id => {
        const el = document.getElementById(id);
        if (el && e.target === el) el.style.display = 'none';
    });
});
</script>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
