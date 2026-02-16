<?php
header('Content-Type: application/json');

// Include config file for database connection
require_once '../db/tvconfig.php';

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// Read and parse input data
$input = json_decode(file_get_contents('php://input'), true);

// Log raw input for debugging
file_put_contents('callback.log', print_r($input, true) . "\n", FILE_APPEND);

// Validate callback payload
if (!$input || !isset($input['ResponseCode']) || !isset($input['Status']) || !isset($input['Data'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid callback data']);
    exit;
}

// Extract relevant data
$responseCode = $input['ResponseCode'];
$status = $input['Status'];
$data = $input['Data'];

if (!isset($data['CheckoutId']) || !isset($data['ClientReference']) || !isset($data['Status']) || !isset($data['Amount'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Incomplete callback data']);
    exit;
}

// Extract payment details
$checkoutId = $data['CheckoutId'];
$clientReference = $data['ClientReference'];
$paymentStatus = $data['Status'];
$amount = $data['Amount'];
$customerPhoneNumber = $data['CustomerPhoneNumber'] ?? '';
$paymentDetails = $data['PaymentDetails'] ?? [];
$description = $data['Description'] ?? '';

// Get database connection
$conn = getDBConnection();
if (!$conn) {
    file_put_contents('db_error.log', 'Database connection failed: ' . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Fetch existing transaction to get name, email, and purpose
$stmt = $conn->prepare("SELECT name, email, purpose FROM payments WHERE reference = ?");
$stmt->bind_param("s", $clientReference);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$stmt->close();

if (!$existing) {
    file_put_contents('callback_error.log', "No existing transaction for reference: $clientReference\n", FILE_APPEND);
    $conn->close();
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
    exit;
}

$name = $existing['name'];
$email = $existing['email'];
$purpose = $existing['purpose'];

// Update transaction in payments table
$stmt = $conn->prepare("UPDATE payments SET amount = ?, created_at = NOW() WHERE reference = ?");
$stmt->bind_param("ds", $amount, $clientReference);
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

// Log successful callback for debugging
$logEntry = [
    'timestamp' => date('Y-m-d H:i:s'),
    'checkoutId' => $checkoutId,
    'clientReference' => $clientReference,
    'status' => $paymentStatus,
    'amount' => $amount,
    'customerPhoneNumber' => $customerPhoneNumber,
    'paymentDetails' => $paymentDetails,
    'description' => $description
];
file_put_contents('callback_success.log', json_encode($logEntry) . "\n", FILE_APPEND);

// Check if payment was successful
if ($responseCode === '0000' && $status === 'Success' && $paymentStatus === 'Success') {
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Callback received and processed']);
} else {
    file_put_contents('callback_error.log', json_encode($input) . "\n", FILE_APPEND);
    http_response_code(200); // Hubtel expects a 200 response even for failed payments
    echo json_encode(['status' => 'success', 'message' => 'Callback received']);
}
?>