<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Replace with your actual Paystack secret key
$secret_key = 'sk_live_d35382a21a03bd28a6ed4bc0417152c06289a135'; // TODO: Update with your actual secret key

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log the input
file_put_contents('verify_payment.log', 'Input: ' . $input . PHP_EOL, FILE_APPEND);

// Validate secret key
if (empty($secret_key)) {
    file_put_contents('verify_payment.log', 'Error: PAYSTACK_SECRET_KEY not set' . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server configuration error: Secret key not set'
    ]);
    exit;
}

// Validate input
if (!isset($data['reference']) || empty(trim($data['reference']))) {
    file_put_contents('verify_payment.log', 'Error: Missing or empty reference' . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing or empty reference'
    ]);
    exit;
}

$reference = $data['reference'];

// Initialize cURL
if (!function_exists('curl_init')) {
    file_put_contents('verify_payment.log', 'Error: cURL extension not available' . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: cURL extension not available'
    ]);
    exit;
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/$reference");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $secret_key",
    "Content-Type: application/json",
    "Cache-Control: no-cache"
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request
$result = curl_exec($ch);
if ($result === false) {
    $curl_error = curl_error($ch);
    file_put_contents('verify_payment.log', 'cURL Error: ' . $curl_error . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => 'cURL error: ' . $curl_error
    ]);
    curl_close($ch);
    exit;
}
curl_close($ch);

// Log the response
file_put_contents('verify_payment.log', 'Paystack Response: ' . $result . PHP_EOL, FILE_APPEND);

// Process response
$response = json_decode($result, true);
if ($response && isset($response['status']) && $response['status'] && $response['data']['status'] === 'success') {
    // Example: Store verified transaction details in a database (modify as needed)
    // $db = new PDO("mysql:host=localhost;dbname=your_database", "username", "password");
    // $stmt = $db->prepare("UPDATE transactions SET status = 'verified' WHERE reference = ?");
    // $stmt->execute([$reference]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction verified successfully',
        'data' => $response['data']
    ]);
} else {
    file_put_contents('verify_payment.log', 'Paystack Error: ' . ($response['message'] ?? 'No message provided') . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => $response['message'] ?? 'Failed to verify transaction'
    ]);
}
?>