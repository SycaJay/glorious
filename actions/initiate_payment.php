<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Replace with your actual LIVE Paystack secret key
$secret_key = 'sk_live_d35382a21a03bd28a6ed4bc0417152c06289a135'; // TODO: Update with your actual LIVE secret key

// Get the raw POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log the input
file_put_contents('initiate_payment.log', 'Input: ' . $input . PHP_EOL, FILE_APPEND);

// Validate secret key
if (empty($secret_key) || $secret_key === '1') {
    file_put_contents('initiate_payment.log', 'Error: PAYSTACK_SECRET_KEY not set' . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server configuration error: Secret key not set'
    ]);
    exit;
}

// Validate input
$required_fields = ['amount', 'customerEmail', 'clientReference', 'customerName', 'customerPhoneNumber', 'purchaseDescription'];
foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        file_put_contents('initiate_payment.log', 'Error: Missing or empty field: ' . $field . PHP_EOL, FILE_APPEND);
        echo json_encode([
            'status' => 'error',
            'message' => "Missing or empty required field: $field"
        ]);
        exit;
    }
}

// Check for duplicate reference server-side
$reference = $data['clientReference'];
$recent_references_file = 'recent_references.json';
$recent_references = [];
if (file_exists($recent_references_file)) {
    $file_content = file_get_contents($recent_references_file);
    $decoded = json_decode($file_content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $recent_references = $decoded;
    } else {
        file_put_contents('initiate_payment.log', 'Error: Invalid JSON in recent_references.json' . PHP_EOL, FILE_APPEND);
    }
}
if (in_array($reference, $recent_references)) {
    file_put_contents('initiate_payment.log', 'Error: Duplicate reference detected server-side: ' . $reference . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => 'Duplicate transaction reference',
        'code' => 'duplicate_reference'
    ]);
    exit;
}
// Store the reference
$recent_references[] = $reference;
if (count($recent_references) > 100) {
    array_shift($recent_references);
}
file_put_contents($recent_references_file, json_encode($recent_references));

// Convert amount from OLAM to kobo (1 OLAM = 10 GHS, 1 GHS = 100 kobo)
$amount_in_olam = floatval($data['amount']);
$amount_in_kobo = $amount_in_olam * 10 * 100;

// Determine payment channels
$channels = isset($data['channel']) && in_array($data['channel'], ['card', 'mobile_money'])
    ? [$data['channel']]
    : ['mobile_money'];

// Prepare JSON payload
$payload = [
    'email' => $data['customerEmail'],
    'amount' => (int)$amount_in_kobo,
    'reference' => $reference,
    'callback_url' => 'https://gloriousvisionstvplus.com/actions/payment_callback.php',
    'metadata' => [
        'customer_name' => $data['customerName'],
        'phone_number' => $data['customerPhoneNumber'],
        'purchase_description' => $data['purchaseDescription']
    ],
    'channels' => $channels
];

// Initialize cURL
if (!function_exists('curl_init')) {
    file_put_contents('initiate_payment.log', 'Error: cURL extension not available' . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error: cURL extension not available'
    ]);
    exit;
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.paystack.co/transaction/initialize');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
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
    file_put_contents('initiate_payment.log', 'cURL Error: ' . $curl_error . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => 'cURL error: ' . $curl_error
    ]);
    curl_close($ch);
    exit;
}
curl_close($ch);

// Log the response
file_put_contents('initiate_payment.log', 'Paystack Response: ' . $result . PHP_EOL, FILE_APPEND);

// Process response
$response = json_decode($result, true);
if ($response && isset($response['status']) && $response['status']) {
    echo json_encode([
        'status' => 'Success',
        'data' => [
            'access_code' => $response['data']['access_code']
        ]
    ]);
    // Clear the reference on success
    $recent_references = array_diff($recent_references, [$reference]);
    file_put_contents($recent_references_file, json_encode($recent_references));
} else {
    file_put_contents('initiate_payment.log', 'Paystack Error: ' . ($response['message'] ?? 'No message provided') . PHP_EOL, FILE_APPEND);
    echo json_encode([
        'status' => 'error',
        'message' => $response['message'] ?? 'Failed to initialize transaction',
        'code' => $response['code'] ?? null
    ]);
}
?>