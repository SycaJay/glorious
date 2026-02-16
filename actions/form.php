<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Clear any output buffers to prevent JSON corruption
while (ob_get_level()) {
    ob_end_clean();
}

// Include the database configuration file
require_once '../db/tvconfig.php';

// Set the content type to JSON
header('Content-Type: application/json');

// Get the database connection
$conn = getDBConnection();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $contact = htmlspecialchars($_POST['contact'] ?? '', ENT_QUOTES, 'UTF-8');
    $country = htmlspecialchars($_POST['country'] ?? '', ENT_QUOTES, 'UTF-8');

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        exit;
    }

    // Validate contact (phone number)
    if (empty($contact) || !preg_match('/^[\+0-9\s\-]{7,15}$/', $contact)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid contact number (7-15 digits, may include +, spaces, or hyphens).']);
        exit;
    }

    // Validate country
    if (empty($country)) {
        echo json_encode(['success' => false, 'message' => 'Please select a country.']);
        exit;
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO viewers (email, contact, country, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $email, $contact, $country);

    // Execute the statement
    try {
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Thank you for signing up!']);
        } else {
            error_log("SQL Error: " . $stmt->error); // Log error for debugging
            echo json_encode(['success' => false, 'message' => 'Error saving data: ' . $stmt->error]);
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) { // Duplicate entry error
            echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
        } else {
            error_log("MySQL Exception: " . $e->getMessage()); // Log exception
            echo json_encode(['success' => false, 'message' => 'Error saving data: ' . $e->getMessage()]);
        }
    }

    // Close the statement
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Close the database connection
$conn->close();
?>