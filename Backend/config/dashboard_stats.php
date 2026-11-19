<?php
/**
 * Dashboard statistics — single source of truth for the admin cockpit.
 *
 * Every figure the dashboard shows is read from the database here, so the
 * KPI cards, the charts and the analytics API all report the same live
 * numbers. Missing tables degrade to zero instead of breaking the page.
 */
declare(strict_types=1);

/**
 * Build a continuous day window ending today and fill gaps with zeroes.
 * A day without records is a real 0 (not "no data"), so charts keep a line.
 */
function busiaDashboardZeroFill(array $rows, int $days, string $valueKey, string $outKey = 'total'): array
{
    $days = max(1, $days);
    $map = [];
    foreach ($rows as $row) {
        $day = substr((string)($row['day'] ?? ''), 0, 10);
        if ($day === '') {
            continue;
        }
        $map[$day] = ($map[$day] ?? 0) + (float)($row[$valueKey] ?? 0);
    }

    $start = (new DateTimeImmutable('today'))->modify('-' . ($days - 1) . ' days');
    $series = [];
    for ($i = 0; $i < $days; $i++) {
        $day = $start->modify('+' . $i . ' day')->format('Y-m-d');
        $series[] = ['day' => $day, $outKey => $map[$day] ?? 0.0];
    }

    return $series;
}

function busiaDashboardFetch(PDO $pdo, string $sql, array $params = []): array
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

function busiaDashboardScalar(PDO $pdo, string $sql, array $params = [], float $default = 0.0): float
{
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $value = $stmt->fetchColumn();
        return $value === false || $value === null ? $default : (float)$value;
    } catch (Throwable $e) {
        return $default;
    }
}

/**
 * @return array<string,mixed> Live dashboard figures and 30-day series.
 */
function busiaDashboardStats(PDO $pdo, int $days = 30): array
{
    $start = (new DateTimeImmutable('today'))->modify('-' . ($days - 1) . ' days')->format('Y-m-d');

    // Revenue: cashbook receipts, falling back to website orders.
    $salesRows = busiaDashboardFetch(
        $pdo,
        "SELECT DATE(entry_date) AS day, SUM(amount) AS total
           FROM cashbook_entries
          WHERE direction = 'in' AND entry_date >= ?
          GROUP BY DATE(entry_date)",
        [$start]
    );
    if (!$salesRows) {
        $salesRows = busiaDashboardFetch(
            $pdo,
            "SELECT DATE(created_at) AS day, SUM(total_amount) AS total
               FROM orders
              WHERE created_at >= ?
              GROUP BY DATE(created_at)",
            [$start]
        );
    }
    $salesSeries = busiaDashboardZeroFill($salesRows, $days, 'total', 'total');

    // Order volumes.
    $orderRows = busiaDashboardFetch(
        $pdo,
        "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
           FROM orders
          WHERE created_at >= ?
          GROUP BY DATE(created_at)",
        [$start]
    );
    $orderSeries = busiaDashboardZeroFill($orderRows, $days, 'cnt', 'cnt');

    // All-time revenue, so the headline card is never a stale zero.
    $totalRevenue = busiaDashboardScalar(
        $pdo,
        "SELECT COALESCE(SUM(amount), 0) FROM cashbook_entries WHERE direction = 'in'"
    );
    if ($totalRevenue <= 0) {
        $totalRevenue = busiaDashboardScalar($pdo, "SELECT COALESCE(SUM(total_amount), 0) FROM orders");
    }

    // Live birds come from batches; older installations only have flocks.
    $activeBirds = busiaDashboardScalar($pdo, "SELECT COALESCE(SUM(current_birds), 0) FROM batches WHERE status = 'active'");
    $activeBatches = busiaDashboardScalar($pdo, "SELECT COUNT(*) FROM batches WHERE status = 'active'");
    if ($activeBirds <= 0 && $activeBatches <= 0) {
        $activeBirds = busiaDashboardScalar($pdo, "SELECT COALESCE(SUM(current_count), 0) FROM flocks WHERE status = 'active'");
        $activeBatches = busiaDashboardScalar($pdo, "SELECT COUNT(*) FROM flocks WHERE status = 'active'");
    }

    // Finished feed stock and raw material stock.
    $feedStockBags = busiaDashboardScalar(
        $pdo,
        "SELECT COALESCE(SUM(stock_quantity), 0) FROM products WHERE product_type = 'feed' AND is_active = 1"
    );
    $rawMaterialsKg = busiaDashboardScalar($pdo, "SELECT COALESCE(SUM(current_stock), 0) FROM raw_materials");

    $sales30d = 0.0;
    foreach ($salesSeries as $point) {
        $sales30d += (float)$point['total'];
    }

    return [
        'generated_at' => date('c'),
        'sales' => $salesSeries,
        'orders' => $orderSeries,
        'total_revenue' => $totalRevenue,
        'total_sales_30d' => $sales30d,
        'active_birds' => (int)round($activeBirds),
        'active_batches' => (int)round($activeBatches),
        'feed_stock_bags' => (int)round($feedStockBags),
        'raw_materials_kg' => round($rawMaterialsKg, 2),
    ];
}
