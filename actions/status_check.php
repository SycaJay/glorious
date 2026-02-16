<?php
header('Content-Type: application/json');

// Include config file for database connection
require_once '../db/tvconfig.php';

// Hubtel API credentials (replace with your actual credentials)
$clientId = '86n0ZZL'; // Your Hubtel Client ID
$clientSecret = '9b54d93efa294c2fad3e3f5aa5870a19'; // Your Hubtel Client Secret
$posSalesId = '2030422'; // Your Hubtel POS Sales ID 

// Hubtel Transaction Status Check API endpoint
$apiUrl = "https://api-txnstatus.hubtel.com/transactions/{$posSalesId}/status";

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $error = ['status' => 'error', 'message' => 'Method Not Allowed'];
    file_put_contents('transaction_status.log', json_encode($error, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
    http_response_code(405);
    echo json_encode($error);
    exit;
}

// Get clientReference from query parameters
$clientReference = isset($_GET['clientReference']) ? htmlspecialchars($_GET['clientReference']) : '';

if (!$clientReference) {
    $error = ['status' => 'error', 'message' => 'clientReference is required'];
    file_put_contents('transaction_status.log', json_encode($error, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
    http_response_code(400);
    echo json_encode($error);
    exit;
}

// Initialize cURL
$ch = curl_init($apiUrl . "?clientReference=" . urlencode($clientReference));

// Base64 encode credentials for Basic Authentication
$auth = base64_encode("$clientId:$clientSecret");

curl_setopt_array($ch, [
    CURLOPT_HTTPGET => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Basic ' . $auth,
        'Content-Type: application/json',
        'Accept: application/json',
        'Cache-Control: no-cache'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_VERBOSE => true, // Enable verbose output for debugging
    CURLOPT_STDERR => $verbose = fopen('curl_debug.log', 'a+') // Log cURL debug info
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);
fclose($verbose);

// Log cURL errors if any
if ($response === false) {
    $error = ['status' => 'error', 'message' => 'cURL error: ' . $curlError];
    file_put_contents('transaction_status.log', json_encode($error, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode($error);
    exit;
}

$responseData = json_decode($response, true);

// Check for successful response
if ($httpCode === 200 && isset($responseData['responseCode']) && $responseData['responseCode'] === '0000' && isset($responseData['data'])) {
    $data = $responseData['data'];
    $status = $data['status'] ?? 'unknown';
    $amount = $data['amount'] ?? 0;
    $transactionId = $data['transactionId'] ?? '';
    $externalTransactionId = $data['externalTransactionId'] ?? '';
    $paymentMethod = $data['paymentMethod'] ?? '';
    $date = $data['date'] ?? '';
    $charges = $data['charges'] ?? 0;
    $amountAfterCharges = $data['amountAfterCharges'] ?? 0;
    $isFulfilled = $data['isFulfilled'] ?? false;

    // Format log data to match the desired JSON structure
    $logData = [
        'message' => $responseData['message'] ?? 'Successful',
        'responseCode' => $responseData['responseCode'] ?? '0000',
        'data' => [
            'date' => $date,
            'status' => $status,
            'transactionId' => $transactionId,
            'externalTransactionId' => $externalTransactionId,
            'paymentMethod' => $paymentMethod,
            'clientReference' => $clientReference,
            'currencyCode' => $data['currencyCode'] ?? null,
            'amount' => $amount,
            'charges' => $charges,
            'amountAfterCharges' => $amountAfterCharges,
            'isFulfilled' => $isFulfilled
        ]
    ];

    // Log to transaction_status.log
    file_put_contents('transaction_status.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

    // Update transaction status in database
    $conn = getDBConnection();
    if (!$conn) {
        $error = ['status' => 'error', 'message' => 'Database connection failed'];
        file_put_contents('transaction_status.log', json_encode($error, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        http_response_code(500);
        echo json_encode($error);
        exit;
    }

    $stmt = $conn->prepare("UPDATE payments SET amount = ?, status = ?, transaction_id = ?, external_transaction_id = ?, payment_method = ?, updated_at = NOW() WHERE reference = ?");
    $statusToStore = strtolower($status);
    $stmt->bind_param("dsssss", $amount, $statusToStore, $transactionId, $externalTransactionId, $paymentMethod, $clientReference);
    if (!$stmt->execute()) {
        $error = ['status' => 'error', 'message' => 'Database error: ' . $stmt->error];
        file_put_contents('transaction_status.log', json_encode($error, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        $stmt->close();
        $conn->close();
        http_response_code(500);
        echo json_encode($error);
        exit;
    }
    $stmt->close();
    $conn->close();

    // Return response in the specified format
    echo json_encode($logData);
} else {
    $error = ['status' => 'error', 'message' => $responseData['message'] ?? 'Status check failed', 'httpCode' => $httpCode];
    file_put_contents('transaction_status.log', json_encode($error, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
    http_response_code($httpCode);
    echo json_encode($error);
}
?>