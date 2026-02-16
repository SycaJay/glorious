<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Replace with your actual Paystack secret key
$secret_key = 'sk_live_d35382a21a03bd28a6ed4bc0417152c06289a135'; // TODO: Update with your actual LIVE Paystack secret key

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
    // Store authorization_code if reusable: true
    $authorization = $response['data']['authorization'] ?? null;
    if ($authorization && isset($authorization['reusable']) && $authorization['reusable'] === true) {
        $authorization_code = $authorization['authorization_code'];
        $email = $response['data']['customer']['email'];
        $authorizations_file = 'authorizations.json';
        $authorizations = [];
        if (file_exists($authorizations_file)) {
            $file_content = file_get_contents($authorizations_file);
            $decoded = json_decode($file_content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $authorizations = $decoded;
            }
        }
        $authorizations[$email] = [
            'authorization_code' => $authorization_code,
            'reference' => $reference,
            'email' => $email,
            'timestamp' => date('c')
        ];
        file_put_contents($authorizations_file, json_encode($authorizations, JSON_PRETTY_PRINT));
        file_put_contents('verify_payment.log', "Stored authorization_code: $authorization_code for $email\n", FILE_APPEND);
    }

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