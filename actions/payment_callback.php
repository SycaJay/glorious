<?php
header('Content-Type: application/json');

// Replace with your Paystack secret key
$secret_key = 'sk_live_d35382a21a03bd28a6ed4bc0417152c06289a135'; // TODO: Update with your actual LIVE Paystack secret key

// Get the raw POST data from Paystack webhook
$input = file_get_contents('php://input');
$event = json_decode($input, true);

// Verify the event is from Paystack
$signature = hash_hmac('sha512', $input, $secret_key);
if ($signature !== $_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid signature'
    ]);
    exit;
}

// Handle the event
if ($event['event'] === 'charge.success') {
    $reference = $event['data']['reference'];
    $amount = $event['data']['amount']; // Amount in kobo
    $email = $event['data']['customer']['email'];
    $metadata = $event['data']['metadata'];
    $authorization = $event['data']['authorization'] ?? null;

    // Convert amount back to OLAM for logging (1 OLAM = 10 GHS, 1 GHS = 100 kobo)
    $amount_in_ghs = $amount / 100;
    $amount_in_olam = $amount_in_ghs / 10;

    // Store authorization_code if reusable: true
    if ($authorization && isset($authorization['reusable']) && $authorization['reusable'] === true) {
        $authorization_code = $authorization['authorization_code'];
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
        file_put_contents('paystack_webhook.log', "Stored authorization_code: $authorization_code for $email\n", FILE_APPEND);
    }

    // Log for debugging
    file_put_contents('paystack_webhook.log', json_encode($event) . PHP_EOL, FILE_APPEND);

    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Transaction verified'
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Unhandled event type'
    ]);
}
?>