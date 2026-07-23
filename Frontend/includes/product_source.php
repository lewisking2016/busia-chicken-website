<?php
declare(strict_types=1);

/**
 * Centralized product source used by both shop and homepage
 * Tries DB first, otherwise returns the local sample dataset.
 */
function loadDisplayProducts(?PDO $pdo = null): array
{
    if (!$pdo) {
        return [];
    }

    try {
        $stmt = $pdo->query("SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Failed to load products from database: " . $e->getMessage());
        return [];
    }
}

?>
