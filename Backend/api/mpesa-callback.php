<?php
/**
 * M-Pesa Payment Callback Handler
 * Receives and processes Safaricom Daraja API callbacks
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

// Log all incoming requests
$input = file_get_contents('php://input');
error_log('[M-Pesa Callback] Raw input: ' . $input);

try {
    $data = json_decode($input, true);

    if ($data === null) {
        throw new Exception('Invalid JSON received');
    }

    $result_code = (int)($data['Body']['stkCallback']['ResultCode'] ?? 1);
    $merchant_request_id = $data['Body']['stkCallback']['MerchantRequestID'] ?? '';
    $checkout_request_id = $data['Body']['stkCallback']['CheckoutRequestID'] ?? '';
    $result_desc = $data['Body']['stkCallback']['ResultDesc'] ?? '';

    // Handle successful payment
    if ($result_code === 0) {
        $callback_metadata = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
        $mpesa_data = [];

        foreach ($callback_metadata as $item) {
            $key = $item['Name'] ?? '';
            $value = $item['Value'] ?? '';
            $mpesa_data[$key] = $value;
        }

        $amount = (int)($mpesa_data['Amount'] ?? 0);
        $mpesa_receipt = $mpesa_data['MpesaReceiptNumber'] ?? '';
        $transaction_date = $mpesa_data['TransactionDate'] ?? '';
        $phone_number = $mpesa_data['PhoneNumber'] ?? '';

        // TODO: Update order status in database
        logSecurityEvent(
            "M-Pesa payment successful: Amount={$amount}, Receipt={$mpesa_receipt}, Phone={$phone_number}",
            'INFO'
        );

        $response = [
            'ResultCode' => 0,
            'ResultDesc' => 'Payment received successfully'
        ];
    } else {
        // Payment failed
        logSecurityEvent(
            "M-Pesa payment failed: Result={$result_code}, Description={$result_desc}",
            'WARNING'
        );

        $response = [
            'ResultCode' => 1,
            'ResultDesc' => 'Payment failed: ' . $result_desc
        ];
    }

    // Send response back to M-Pesa
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    logSecurityEvent('M-Pesa callback error: ' . $e->getMessage(), 'ERROR');
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
