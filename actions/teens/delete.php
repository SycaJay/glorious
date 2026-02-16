<?php
// delete.php
// Include your database connection here
include('../../db/tvconfig.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teens_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($teens_id <= 0) {
        http_response_code(400);
        echo "Invalid ID provided";
        exit;
    }

    // First get the current record to get file paths for deletion
    $query = "SELECT video_path, audio_path FROM teenstv WHERE teens_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teens_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $video_path = $row['video_path'];
        $audio_path = $row['audio_path'];
        
        // Delete files if they exist
        if ($video_path && file_exists('../../' . $video_path)) {
            unlink('../../' . $video_path);
        }
        if ($audio_path && file_exists('../../' . $audio_path)) {
            unlink('../../' . $audio_path);
        }
    }
    $stmt->close();

    // Delete record from database
    $query = "DELETE FROM teenstv WHERE teens_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teens_id);
    
    if ($stmt->execute()) {
        http_response_code(200);
        echo "Content deleted successfully";
    } else {
        http_response_code(500);
        echo "Error deleting content: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    http_response_code(405);
    echo "Method not allowed";
}
$conn->close();
?>