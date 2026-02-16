<?php
// tvconfig.php
$host = 'localhost';
$dbname = 'u986208592_tvplus'; 
$username = 'u986208592_sycajay';
$password = 'RuachElohim.1';

function getDBConnection() {
    global $host, $username, $password, $dbname;
    
    try {
        $conn = new mysqli($host, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            error_log("Connection failed: " . $conn->connect_error);
            return false;
        }
        
        return $conn;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

// Maintain your existing connection code for backward compatibility
try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    error_log($e->getMessage()); 
    die("Database connection failed. Please try again later.");
}
?>