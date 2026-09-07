<?php
/**
 * Backend API - Poultry Operations Management (Flocks, Production, Vaccinations, Expenses)
 */
declare(strict_types=1);

header('Content-Type: application/json');

$temp_dir = sys_get_temp_dir();
if (is_writable($temp_dir)) session_save_path($temp_dir);
session_start();

require_once __DIR__ . '/../config/database.php';

/**
 * Classify a breed name into a bird-type key the admin uses to configure the
 * vaccine program. This is only a lookup key — the actual vaccines and day
 * offsets come from the vaccination_plans table the admin manages.
 */
function busiaDetectBirdType(string $breed): string
{
    $b = strtolower($breed);
    if (str_contains($b, 'broiler') || str_contains($b, 'cobb') || str_contains($b, 'ross') || str_contains($b, 'kroiler') || str_contains($b, 'aseel')) {
        return 'broiler';
    }
    if (str_contains($b, 'kienyeji') || str_contains($b, 'indigenous') || str_contains($b, 'sasso') || str_contains($b, 'rainbow')) {
        return 'kienyeji';
    }
    return 'layer';
}

/** Bird-type keys the admin can define a program for. */
function busiaPlanBirdTypes(): array
{
    return ['layer', 'broiler', 'kienyeji', 'general'];
}

/**
 * The active program the ADMIN configured for a bird type (falls back to the
 * 'general' program when the specific type has none). Empty = nothing set.
 */
function busiaActiveVaccinePlan(\PDO $pdo, string $type): array
{
    foreach (array_unique([$type, 'general']) as $t) {
        $st = $pdo->prepare('SELECT vaccine_name, day_after_hatch FROM vaccination_plans WHERE bird_type = ? AND is_active = 1 ORDER BY sort_order ASC, day_after_hatch ASC, id ASC');
        $st->execute([$t]);
        $got = $st->fetchAll(PDO::FETCH_ASSOC);
        if ($got) {
            return $got;
        }
    }
    return [];
}

/**
 * Schedule a NEW flock's vaccinations from the admin-configured program.
 * Returns how many were scheduled (0 when the admin has not set a program).
 */
function busiaAutoScheduleVaccinations(\PDO $pdo, int $flockId, string $breed, string $hatchDate): int
{
    if ($flockId <= 0 || $hatchDate === '') {
        return 0;
    }
    // Safety: never double-schedule a flock that already has vaccinations.
    $has = $pdo->prepare('SELECT COUNT(*) FROM vaccinations WHERE flock_id = ?');
    $has->execute([$flockId]);
    if ((int)$has->fetchColumn() > 0) {
        return 0;
    }
    $plan = busiaActiveVaccinePlan($pdo, busiaDetectBirdType($breed));
    if (!$plan) {
        return 0;
    }
    $ins = $pdo->prepare('INSERT INTO vaccinations (flock_id, vaccine_name, scheduled_date, status) VALUES (?, ?, ?, ?)');
    $added = 0;
    foreach ($plan as $row) {
        $scheduled = date('Y-m-d', strtotime($hatchDate . ' +' . (int)$row['day_after_hatch'] . ' day'));
        $ins->execute([$flockId, $row['vaccine_name'], $scheduled, 'scheduled']);
        $added++;
    }
    return $added;
}

try {
    if (empty($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager', 'stock_manager'], true)) {
        throw new Exception('Unauthorized access');
    }

    $pdo = getDatabaseConnection();
    if (!$pdo) throw new Exception('Database connection failed');

    $action = $_GET['action'] ?? '';
    $method = $_SERVER['REQUEST_METHOD'];

    switch ($action) {
        // --- FLOCKS ---
        case 'get_flocks':
            $flocks = $pdo->query("SELECT * FROM flocks ORDER BY hatch_date DESC, id DESC")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $flocks]);
            break;

        case 'save_flock':
            if ($method !== 'POST') throw new Exception('Invalid request method');
            $id = (int)($_POST['id'] ?? 0);
            $flock_name = trim($_POST['flock_name'] ?? '');
            $breed = trim($_POST['breed'] ?? '');
            $initial_count = (int)($_POST['initial_count'] ?? 0);
            $current_count = (int)($_POST['current_count'] ?? $initial_count);
            $hatch_date = $_POST['hatch_date'] ?? '';
            $status = $_POST['status'] ?? 'active';

            if ($flock_name === '' || $breed === '' || $initial_count <= 0 || $hatch_date === '') {
                throw new Exception('Please fill in all required fields');
            }

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE flocks SET flock_name = ?, breed = ?, initial_count = ?, current_count = ?, hatch_date = ?, status = ? WHERE id = ?");
                $stmt->execute([$flock_name, $breed, $initial_count, $current_count, $hatch_date, $status, $id]);
                $msg = 'Flock updated';
            } else {
                $stmt = $pdo->prepare("INSERT INTO flocks (flock_name, breed, initial_count, current_count, hatch_date, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$flock_name, $breed, $initial_count, $initial_count, $hatch_date, $status]);
                $msg = 'Flock created';
            }
            $flockId = $id > 0 ? $id : (int)$pdo->lastInsertId();

            // NEW FLOCKS ONLY: build the vaccination schedule automatically.
            $vaccinesAdded = 0;
            if ($id <= 0) {
                $vaccinesAdded = busiaAutoScheduleVaccinations($pdo, $flockId, $breed, $hatch_date);
                if ($vaccinesAdded > 0) {
                    $msg .= " — {$vaccinesAdded} vaccines scheduled automatically";
                }
            }
            logActivity($pdo, 'save', 'flocks', "{$msg}: {$flock_name} ({$breed}, {$initial_count} birds)", $flockId, 'flock');
            echo json_encode([
                'success' => true,
                'message' => $msg,
                'data'    => ['id' => $flockId, 'vaccines_added' => $vaccinesAdded],
            ]);
            break;

        case 'delete_flock':
            if ($method !== 'POST') throw new Exception('Invalid request method');
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM flocks WHERE id = ?");
            $stmt->execute([$id]);
            logActivity($pdo, 'delete', 'flocks', "Deleted flock #{$id}", $id, 'flock');
            echo json_encode(['success' => true, 'message' => 'Flock deleted']);
            break;

        // --- PRODUCTION RECORDS ---
        case 'get_production':
            $stmt = $pdo->query("
                SELECT p.*, f.flock_name 
                FROM production_records p
                JOIN flocks f ON p.flock_id = f.id
                ORDER BY p.record_date DESC, p.id DESC
            ");
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $records]);
            break;

        case 'save_production':
            if ($method !== 'POST') throw new Exception('Invalid request method');
            $id = (int)($_POST['id'] ?? 0);
            $flock_id = (int)($_POST['flock_id'] ?? 0);
            $record_date = $_POST['record_date'] ?? '';
            $eggs_collected = (int)($_POST['eggs_collected'] ?? 0);
            $cracked_eggs = (int)($_POST['cracked_eggs'] ?? 0);
            $meat_weight_kg = (float)($_POST['meat_weight_kg'] ?? 0.0);
            $mortality = (int)($_POST['mortality'] ?? 0);
            $feed_consumed_kg = (float)($_POST['feed_consumed_kg'] ?? 0.0);
            $notes = trim($_POST['notes'] ?? '');

            if ($flock_id <= 0 || $record_date === '') {
                throw new Exception('Flock and Record Date are required');
            }

            if ($id > 0) {
                // If updating mortality, let's adjust current_count inside the flock table
                $old = $pdo->prepare("SELECT flock_id, mortality FROM production_records WHERE id = ?");
                $old->execute([$id]);
                $oldRecord = $old->fetch(PDO::FETCH_ASSOC);
                
                $stmt = $pdo->prepare("UPDATE production_records SET flock_id = ?, record_date = ?, eggs_collected = ?, cracked_eggs = ?, meat_weight_kg = ?, mortality = ?, feed_consumed_kg = ?, notes = ? WHERE id = ?");
                $stmt->execute([$flock_id, $record_date, $eggs_collected, $cracked_eggs, $meat_weight_kg, $mortality, $feed_consumed_kg, $notes, $id]);

                // Sync flock current_count if mortality changed
                if ($oldRecord) {
                    $mortalityDiff = $mortality - (int)$oldRecord['mortality'];
                    if ($mortalityDiff !== 0) {
                        $upFlock = $pdo->prepare("UPDATE flocks SET current_count = current_count - ? WHERE id = ?");
                        $upFlock->execute([$mortalityDiff, $flock_id]);
                    }
                }
                $msg = 'Production record updated';
            } else {
                $stmt = $pdo->prepare("INSERT INTO production_records (flock_id, record_date, eggs_collected, cracked_eggs, meat_weight_kg, mortality, feed_consumed_kg, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$flock_id, $record_date, $eggs_collected, $cracked_eggs, $meat_weight_kg, $mortality, $feed_consumed_kg, $notes]);

                // Auto-deduct flock current_count by daily mortality
                if ($mortality > 0) {
                    $upFlock = $pdo->prepare("UPDATE flocks SET current_count = current_count - ? WHERE id = ?");
                    $upFlock->execute([$mortality, $flock_id]);
                }
                $msg = 'Production record logged';
            }
            logActivity($pdo, 'save', 'production', "{$msg}: {$eggs_collected} eggs, {$mortality} mortality, flock #{$flock_id}", $id > 0 ? $id : (int)$pdo->lastInsertId(), 'production_record');
            echo json_encode(['success' => true, 'message' => $msg]);
            break;

        case 'delete_production':
            if ($method !== 'POST') throw new Exception('Invalid request method');
            $id = (int)($_POST['id'] ?? 0);

            // Revert mortality count back to flock before deleting
            $old = $pdo->prepare("SELECT flock_id, mortality FROM production_records WHERE id = ?");
            $old->execute([$id]);
            $oldRecord = $old->fetch(PDO::FETCH_ASSOC);
            if ($oldRecord && (int)$oldRecord['mortality'] > 0) {
                $upFlock = $pdo->prepare("UPDATE flocks SET current_count = current_count + ? WHERE id = ?");
                $upFlock->execute([(int)$oldRecord['mortality'], (int)$oldRecord['flock_id']]);
            }

            $stmt = $pdo->prepare("DELETE FROM production_records WHERE id = ?");
            $stmt->execute([$id]);
            logActivity($pdo, 'delete', 'production', "Deleted production record #{$id}", $id, 'production_record');
            echo json_encode(['success' => true, 'message' => 'Record deleted']);
            break;

        // --- VACCINATIONS ---
        case 'get_vaccinations':
            $stmt = $pdo->query("
                SELECT v.*, f.flock_name, u.username as user_name 
                FROM vaccinations v
                JOIN flocks f ON v.flock_id = f.id
                LEFT JOIN users u ON v.administered_by = u.id
                ORDER BY v.scheduled_date ASC, v.id DESC
            ");
            $vaccinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $vaccinations]);
            break;

        case 'save_vaccination':
            if ($method !== 'POST') throw new Exception('Invalid request method');
            $id = (int)($_POST['id'] ?? 0);
            $flock_id = (int)($_POST['flock_id'] ?? 0);
            $vaccine_name = trim($_POST['vaccine_name'] ?? '');
            $scheduled_date = $_POST['scheduled_date'] ?? '';
            $administered_date = $_POST['administered_date'] ?? null;
            if ($administered_date === '') $administered_date = null;
            $status = $_POST['status'] ?? 'scheduled';
            $administered_by = $status === 'completed' ? (int)$_SESSION['user_id'] : null;

            if ($flock_id <= 0 || $vaccine_name === '' || $scheduled_date === '') {
                throw new Exception('Flock, Vaccine Name, and Scheduled Date are required');
            }

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE vaccinations SET flock_id = ?, vaccine_name = ?, scheduled_date = ?, administered_date = ?, status = ?, administered_by = ? WHERE id = ?");
                $stmt->execute([$flock_id, $vaccine_name, $scheduled_date, $administered_date, $status, $administered_by, $id]);
                $msg = 'Vaccination schedule updated';
            } else {
                $stmt = $pdo->prepare("INSERT INTO vaccinations (flock_id, vaccine_name, scheduled_date, administered_date, status, administered_by) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$flock_id, $vaccine_name, $scheduled_date, $administered_date, $status, $administered_by]);
                $msg = 'Vaccination scheduled';
            }
            logActivity($pdo, 'save', 'vaccinations', "{$msg}: {$vaccine_name} for flock #{$flock_id}", $id > 0 ? $id : (int)$pdo->lastInsertId(), 'vaccination');
            echo json_encode(['success' => true, 'message' => $msg]);
            break;

        case 'delete_vaccination':
            if ($method !== 'POST') throw new Exception('Invalid request method');
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM vaccinations WHERE id = ?");
            $stmt->execute([$id]);
            logActivity($pdo, 'delete', 'vaccinations', "Deleted vaccination #{$id}", $id, 'vaccination');
            echo json_encode(['success' => true, 'message' => 'Vaccination schedule deleted']);
            break;

        // --- VACCINE PROGRAM (admin-configured defaults for NEW flocks) ---
        case 'get_vaccine_plans':
            $rows = $pdo->query('SELECT * FROM vaccination_plans ORDER BY FIELD(bird_type, \'layer\', \'broiler\', \'kienyeji\', \'general\'), bird_type ASC, sort_order ASC, day_after_hatch ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        case 'resolve_vaccine_plan':
            // Given a breed, tell the UI which type applies and what the
            // admin-configured program contains for it (empty when unset).
            $breed = trim($_GET['breed'] ?? '');
            $type = $breed === '' ? 'general' : busiaDetectBirdType($breed);
            echo json_encode([
                'success' => true,
                'bird_type' => $type,
                'data' => busiaActiveVaccinePlan($pdo, $type),
            ]);
            break;

        case 'save_vaccine_plan':
            if ($method !== 'POST') throw new Exception('Invalid request method');
            if (!in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
                throw new Exception('Only the Farm Manager can change the vaccine program');
            }
            $id = (int)($_POST['id'] ?? 0);
            $bird_type = trim($_POST['bird_type'] ?? '');
            $vaccine_name = trim($_POST['vaccine_name'] ?? '');
            $day_after_hatch = (int)($_POST['day_after_hatch'] ?? 0);
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            $is_active = isset($_POST['is_active']) ? ((int)$_POST['is_active'] === 0 ? 0 : 1) : 1;

            if (!in_array($bird_type, busiaPlanBirdTypes(), true) || $vaccine_name === '' || $day_after_hatch < 0) {
                throw new Exception('Bird type, vaccine name, and a valid day are required');
            }

            if ($id > 0) {
                $stmt = $pdo->prepare('UPDATE vaccination_plans SET bird_type = ?, vaccine_name = ?, day_after_hatch = ?, sort_order = ?, is_active = ? WHERE id = ?');
                $stmt->execute([$bird_type, $vaccine_name, $day_after_hatch, $sort_order, $is_active, $id]);
                $msg = 'Vaccine program item updated';
            } else {
                $stmt = $pdo->prepare('INSERT INTO vaccination_plans (bird_type, vaccine_name, day_after_hatch, sort_order, is_active) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$bird_type, $vaccine_name, $day_after_hatch, $sort_order, $is_active]);
                $msg = 'Vaccine program item added';
            }
            logActivity($pdo, 'save', 'vaccination_plans', "{$msg}: {$vaccine_name} ({$bird_type}, day {$day_after_hatch})", $id > 0 ? $id : (int)$pdo->lastInsertId(), 'vaccination_plan');
            echo json_encode(['success' => true, 'message' => $msg]);
            break;

        case 'delete_vaccine_plan':
            if ($method !== 'POST') throw new Exception('Invalid request method');
            if (!in_array($_SESSION['role'] ?? '', ['super_admin', 'farm_manager'], true)) {
                throw new Exception('Only the Farm Manager can change the vaccine program');
            }
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('DELETE FROM vaccination_plans WHERE id = ?');
            $stmt->execute([$id]);
            logActivity($pdo, 'delete', 'vaccination_plans', "Deleted vaccine program item #{$id}", $id, 'vaccination_plan');
            echo json_encode(['success' => true, 'message' => 'Vaccine program item removed']);
            break;

        // --- EXPENSES (FINANCIAL RECORDS) ---
        case 'get_expenses':
            $stmt = $pdo->query("SELECT * FROM financial_records WHERE type = 'expense' ORDER BY transaction_date DESC, id DESC");
            $expenses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $expenses]);
            break;

        case 'save_expense':
            if ($method !== 'POST') throw new Exception('Invalid request method');
            $id = (int)($_POST['id'] ?? 0);
            $category = trim($_POST['category'] ?? '');
            $amount = (float)($_POST['amount'] ?? 0.0);
            $transaction_date = $_POST['transaction_date'] ?? '';
            $description = trim($_POST['description'] ?? '');

            if ($category === '' || $amount <= 0 || $transaction_date === '') {
                throw new Exception('Category, amount, and transaction date are required');
            }

            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE financial_records SET type = 'expense', category = ?, amount = ?, transaction_date = ?, description = ? WHERE id = ?");
                $stmt->execute([$category, $amount, $transaction_date, $description, $id]);
                $msg = 'Expense updated';
            } else {
                $stmt = $pdo->prepare("INSERT INTO financial_records (type, category, amount, transaction_date, description) VALUES ('expense', ?, ?, ?, ?)");
                $stmt->execute([$category, $amount, $transaction_date, $description]);
                $msg = 'Expense logged';
            }
            logActivity($pdo, 'save', 'expenses', "{$msg}: {$category} KES {$amount} ({$transaction_date})", $id > 0 ? $id : (int)$pdo->lastInsertId(), 'financial_record');
            echo json_encode(['success' => true, 'message' => $msg]);
            break;

        case 'delete_expense':
            if ($method !== 'POST') throw new Exception('Invalid request method');
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM financial_records WHERE id = ? AND type = 'expense'");
            $stmt->execute([$id]);
            logActivity($pdo, 'delete', 'expenses', "Deleted expense record #{$id}", $id, 'financial_record');
            echo json_encode(['success' => true, 'message' => 'Expense record deleted']);
            break;

        default:
            throw new Exception('Action not found');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
