<?php
header('Content-Type: application/json');

// Include config file for database connection
require_once '../db/tvconfig.php';

// Hubtel API credentials (replace with your actual credentials)
$clientId = '86n0ZZL'; // Your Hubtel Client ID
$clientSecret = '9b54d93efa294c2fad3e3f5aa5870a19'; // Your Hubtel Client Secret
$merchantAccount = '2030422'; // Your Hubtel Merchant Account ID

// Hubtel Online Checkout API endpoint
$apiUrl = "https://payproxyapi.hubtel.com/items/initiate";

// Hubtel Transaction Status Check API endpoint
$statusCheckUrl = "https://api-txnstatus.hubtel.com/transactions/{$posSalesId}/status";

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// Read and parse input data
$input = json_decode(file_get_contents('php://input'), true);

// Log input for debugging
file_put_contents('input.log', print_r($input, true) . "\n", FILE_APPEND);

if (!$input || !isset($input['email']) || !isset($input['amount']) || !isset($input['purpose']) || !isset($input['name']) || !isset($input['phone'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid input data']);
    exit;
}

// Extract and validate input
$email = filter_var($input['email'], FILTER_VALIDATE_EMAIL);
$amount = filter_var($input['amount'], FILTER_VALIDATE_FLOAT);
$purpose = htmlspecialchars($input['purpose']);
$name = htmlspecialchars($input['name']);
$phone = htmlspecialchars($input['phone']);

// Validate phone format (must start with 233 and be 12 digits total)
if (!preg_match('/^233[0-9]{9}$/', $phone)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid phone number format. Use international format (e.g., 233249111411)']);
    exit;
}

if (!$email || $amount <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email or amount']);
    exit;
}

// Format amount to 2 decimal places
$amount = number_format($amount, 2, '.', '');

// Generate unique client reference (max 32 characters)
$clientReference = 'seed-' . substr(uniqid(), 0, 27);

// Prepare data for Hubtel Online Checkout API
$data = [
    'totalAmount' => $amount,
    'description' => $purpose,
    'callbackUrl' => 'https://gloriousvisionstvplus.com/actions/callback.php',
    'returnUrl' => 'https://gloriousvisionstvplus.com/view/partner.php',
    'merchantAccountNumber' => $merchantAccount,
    'cancellationUrl' => 'https://gloriousvisionstvplus.com/view/partner.php',
    'clientReference' => $clientReference,
    'payeeName' => $name,
    'payeeEmail' => $email,
    'payeeMobileNumber' => $phone
];

// Initialize cURL for payment initiation
$ch = curl_init($apiUrl);

// Base64 encode credentials for Basic Authentication
$auth = base64_encode("$clientId:$clientSecret");

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Authorization: Basic ' . $auth,
        'Content-Type: application/json',
        'Accept: application/json',
        'Cache-Control: no-cache'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30
]);

// Execute payment initiation request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Handle cURL errors
if ($response === false) {
    file_put_contents('curl_error.log', $curlError . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'cURL error: ' . $curlError]);
    exit;
}

$responseData = json_decode($response, true);

// Log response for debugging
file_put_contents('debug.log', print_r($responseData, true) . "\n", FILE_APPEND);

// Check for successful payment initiation
if ($httpCode === 200 && isset($responseData['responseCode']) && $responseData['responseCode'] === '0000' && isset($responseData['data']['clientReference'])) {
    // Store transaction in payments table
    $conn = getDBConnection();
    if (!$conn) {
        file_put_contents('db_error.log', 'Database connection failed: ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO payments (name, email, amount, reference, purpose, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssdss", $name, $email, $amount, $clientReference, $purpose);
    if (!$stmt->execute()) {
        file_put_contents('db_error.log', 'Database error: ' . $stmt->error . "\n", FILE_APPEND);
        $stmt->close();
        $conn->close();
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
        exit;
    }
    $stmt->close();
    $conn->close();

    // Perform automatic status check
    $ch = curl_init($statusCheckUrl . "?clientReference=" . urlencode($clientReference));
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
        CURLOPT_VERBOSE => true,
        CURLOPT_STDERR => $verbose = fopen('curl_debug.log', 'a+')
    ]);

    $statusResponse = curl_exec($ch);
    $statusHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $statusCurlError = curl_error($ch);
    curl_close($ch);
    fclose($verbose);

    if ($statusResponse !== false) {
        $statusResponseData = json_decode($statusResponse, true);
        if ($statusHttpCode === 200 && isset($statusResponseData['responseCode']) && $statusResponseData['responseCode'] === '0000' && isset($statusResponseData['data'])) {
            $data = $statusResponseData['data'];
            $logData = [
                'message' => $statusResponseData['message'] ?? 'Successful',
                'responseCode' => $statusResponseData['responseCode'] ?? '0000',
                'data' => [
                    'date' => $data['date'] ?? '',
                    'status' => $data['status'] ?? 'unknown',
                    'transactionId' => $data['transactionId'] ?? '',
                    'externalTransactionId' => $data['externalTransactionId'] ?? '',
                    'paymentMethod' => $data['paymentMethod'] ?? '',
                    'clientReference' => $clientReference,
                    'currencyCode' => $data['currencyCode'] ?? null,
                    'amount' => $data['amount'] ?? 0,
                    'charges' => $data['charges'] ?? 0,
                    'amountAfterCharges' => $data['amountAfterCharges'] ?? 0,
                    'isFulfilled' => $data['isFulfilled'] ?? false
                ]
            ];
            file_put_contents('transaction_status.log', json_encode($logData, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

            // Update database with status
            $conn = getDBConnection();
            if ($conn) {
                $stmt = $conn->prepare("UPDATE payments SET amount = ?, status = ?, transaction_id = ?, external_transaction_id = ?, payment_method = ?, updated_at = NOW() WHERE reference = ?");
                $statusToStore = strtolower($data['status'] ?? 'unknown');
                $stmt->bind_param("dsssss", $data['amount'], $statusToStore, $data['transactionId'], $data['externalTransactionId'], $data['paymentMethod'], $clientReference);
                if (!$stmt->execute()) {
                    file_put_contents('db_error.log', 'Database error: ' . $stmt->error . "\n", FILE_APPEND);
                }
                $stmt->close();
                $conn->close();
            }
        } else {
            $error = ['status' => 'error', 'message' => $statusResponseData['message'] ?? 'Status check failed', 'httpCode' => $statusHttpCode];
            file_put_contents('transaction_status.log', json_encode($error, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
        }
    } else {
        $error = ['status' => 'error', 'message' => 'cURL error: ' . $statusCurlError];
        file_put_contents('transaction_status.log', json_encode($error, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);
    }

    echo json_encode([
        'status' => 'success',
        'message' => $responseData['data']['message'] ?? 'Payment initiated successfully',
        'clientReference' => $responseData['data']['clientReference'],
        'checkoutUrl' => $responseData['data']['checkoutUrl']
    ]);
} else {
    $errorMessage = $responseData['data']['message'] ?? 'Payment initiation failed';
    http_response_code($httpCode);
    echo json_encode([
        'status' => 'error',
        'message' => $errorMessage
    ]);
}
?>