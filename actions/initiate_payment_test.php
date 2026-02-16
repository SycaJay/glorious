<?php
// Setting up error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Prevent errors from being displayed
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_error_log.txt'); // Log errors to a file

require_once __DIR__ . '/../vendor/autoload.php';

use OVAC\HubtelPayment\Config;
use OVAC\HubtelPayment\Api\Transaction\ReceiveMoney;

// Hardcoded Hubtel credentials (replace with your actual credentials)
$accountNumber = '2030422';
$clientId = '86n0ZZL';
$clientSecret = '9b54d93efa294c2fad3e3f5aa5870a19';

// Validate credentials
if (empty($accountNumber) || empty($clientId) || empty($clientSecret)) {
    file_put_contents(__DIR__ . '/payment_test_log.txt', 'Error: Missing Hubtel credentials' . PHP_EOL, FILE_APPEND);
    echo json_encode(['error' => 'Missing Hubtel credentials']);
    http_response_code(500);
    exit;
}

// Initialize Hubtel configuration
try {
    $config = new Config($accountNumber, $clientId, $clientSecret);
} catch (Exception $e) {
    $error = 'Error initializing Hubtel config: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/payment_test_log.txt', $error . PHP_EOL, FILE_APPEND);
    echo json_encode(['error' => $error]);
    http_response_code(500);
    exit;
}

// Test payment request
$testData = [
    'amount' => '0.10',
    'customerPhoneNumber' => '233202260756',
    'clientReference' => 'test-' . uniqid(),
    'purchaseDescription' => 'Test Payment',
    'customerName' => 'Test User',
    'customerEmail' => 'test@example.com'
];

// Convert OLAM to GHS (1 OLAM = 10 GHS)
$amountInGHS = floatval($testData['amount']) * 10;

try {
    $payment = ReceiveMoney::from($testData['customerPhoneNumber'])
        ->amount($amountInGHS)
        ->description($testData['purchaseDescription'])
        ->customerName($testData['customerName'])
        ->customerEmail($testData['customerEmail'])
        ->reference($testData['clientReference']) // Changed from clientReference to reference
        ->channel('mtn-gh')
        ->callback('https://gloriousvisionstvplus.com/actions/payment_callback.php')
        ->injectConfig($config);

    $response = $payment->run();
    $rawResponse = json_encode($response, JSON_PRETTY_PRINT);
    file_put_contents(__DIR__ . '/payment_test_log.txt', 'API Response: ' . $rawResponse . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'success',
        'data' => $response->data,
        'checkoutUrl' => $response->data['checkoutUrl'] ?? null
    ]);
} catch (Exception $e) {
    $error = 'Error executing payment: ' . $e->getMessage();
    file_put_contents(__DIR__ . '/payment_test_log.txt', $error . PHP_EOL, FILE_APPEND);
    error_log($error); // Fallback to stdout
    echo json_encode(['error' => $error]);
    http_response_code(500);
    exit;
}
?>