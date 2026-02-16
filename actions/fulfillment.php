<?php
   require_once __DIR__ . '/../vendor/autoload.php'; // Load Composer dependencies

   // Read JSON input
   $input = json_decode(file_get_contents('php://input'), true);

   // Extract key data
   $sessionId = $input['SessionId'] ?? '';
   $orderId = $input['OrderId'] ?? '';
   $itemName = $input['OrderInfo']['Items'][0]['Name'] ?? '';
   $amount = $input['OrderInfo']['Items'][0]['UnitPrice'] ?? 0;
   $mobile = $input['OrderInfo']['CustomerMobileNumber'] ?? '';

   // Log payment (replace with your database/storage logic)
   error_log("Payment received: SessionId=$sessionId, OrderId=$orderId, Item=$itemName, Amount=$amount, Mobile=$mobile");

   // Send callback to Hubtel
   $callbackUrl = 'https://gs-callback.hubtel.com:9055/callback';
   $callbackPayload = [
       'SessionId' => $sessionId,
       'OrderId' => $orderId,
       'ServiceStatus' => 'success',
       'MetaData' => null
   ];

   // Use cURL to send callback
   $ch = curl_init($callbackUrl);
   curl_setopt($ch, CURLOPT_POST, true);
   curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($callbackPayload));
   curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   $result = curl_exec($ch);
   curl_close($ch);

   // Output response
   header('Content-Type: application/json');
   echo json_encode(['status' => 'received']);
   ?>