<?php
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Log the cancellation
file_put_contents('cancel_payment.log', 'Payment cancelled: ' . date('Y-m-d H:i:s') . ' ' . json_encode($_GET) . PHP_EOL, FILE_APPEND);

// Return response to indicate cancellation
echo json_encode([
    'status' => 'cancelled',
    'message' => 'Payment cancelled by user'
]);
?>