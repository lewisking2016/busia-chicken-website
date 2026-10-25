<?php
/**
 * M-Pesa Payment Callback Handler
 * Receives async result from Safaricom Daraja after STK Push.
 * Updates order status and mpesa_transactions table automatically.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

// Always respond 200 to Safaricom immediately
http_response_code(200);
header('Content-Type: application/json');

$input = file_get_contents('php://input');
error_log('[M-Pesa Callback] Raw: ' . $input);

try {
    $data = json_decode($input, true);
    if ($data === null) {
        throw new Exception('Invalid JSON received');
    }

    $callback         = $data['Body']['stkCallback'] ?? [];
    $resultCode       = (int)($callback['ResultCode']       ?? 1);
    $merchantReqId    = $callback['MerchantRequestID']      ?? '';
    $checkoutReqId    = $callback['CheckoutRequestID']      ?? '';
    $resultDesc       = $callback['ResultDesc']             ?? '';

    $pdo = getDatabaseConnection();

    if ($resultCode === 0) {
        // ── Payment successful — extract metadata ──────────────────
        $items = $callback['CallbackMetadata']['Item'] ?? [];
        $meta  = [];
        foreach ($items as $item) {
            $meta[$item['Name'] ?? ''] = $item['Value'] ?? '';
        }

        $mpesaReceipt   = (string)($meta['MpesaReceiptNumber'] ?? '');
        $amount         = (int)($meta['Amount']                ?? 0);
        $phone          = (string)($meta['PhoneNumber']        ?? '');
        $transactionDate = (string)($meta['TransactionDate']   ?? '');

        // Update mpesa_transactions row
        $pdo->prepare("UPDATE mpesa_transactions
                        SET status = 'completed',
                            mpesa_receipt_number = ?,
                            phone_number = ?,
                            amount = ?,
                            updated_at = NOW()
                        WHERE checkout_request_id = ?")
            ->execute([$mpesaReceipt, $phone, $amount, $checkoutReqId]);

        // Find the linked order and mark it paid
        $tx = $pdo->prepare("SELECT order_id FROM mpesa_transactions WHERE checkout_request_id = ?");
        $tx->execute([$checkoutReqId]);
        $row = $tx->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['order_id']) {
            $pdo->prepare("UPDATE orders SET status = 'confirmed', payment_status = 'paid', updated_at = NOW() WHERE id = ?")
                ->execute([$row['order_id']]);

            // Also write a payment record
            $pdo->prepare("INSERT IGNORE INTO mpesa_transactions
                (order_id, checkout_request_id, merchant_request_id, mpesa_receipt_number,
                 phone_number, amount, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, 'completed', NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    status = 'completed', mpesa_receipt_number = ?, updated_at = NOW()")
                ->execute([
                    $row['order_id'], $checkoutReqId, $merchantReqId,
                    $mpesaReceipt, $phone, $amount, $mpesaReceipt
                ]);
        }

        logSecurityEvent("M-Pesa payment SUCCESS: Receipt={$mpesaReceipt}, Amount={$amount}, Phone={$phone}", 'INFO');
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Payment processed successfully']);

    } else {
        // ── Payment failed or cancelled ─────────────────────────────
        $pdo->prepare("UPDATE mpesa_transactions
                        SET status = 'failed', result_desc = ?, updated_at = NOW()
                        WHERE checkout_request_id = ?")
            ->execute([$resultDesc, $checkoutReqId]);

        logSecurityEvent("M-Pesa payment FAILED: Code={$resultCode}, Desc={$resultDesc}", 'WARNING');
        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
    }

} catch (Exception $e) {
    error_log('[M-Pesa Callback ERROR] ' . $e->getMessage());
    logSecurityEvent('M-Pesa callback error: ' . $e->getMessage(), 'ERROR');
    echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Acknowledged']);
}
