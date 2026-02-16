<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
include('../../db/tvconfig.php');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $content_type = $_POST['content_type'];
    $content_id = $_POST['content_id'];
    $id_field = '';
    
    // Determine ID field based on content type
    switch ($content_type) {
        case 'video_sermons':
            $id_field = 'video_id';
            break;
        case 'podcasts':
            $id_field = 'podcast_id';
            break;
        case 'audio_sermons':
            $id_field = 'audio_id';
            break;
        case 'glorious_highlights':
            $id_field = 'glory_id';
            break;
        default:
            die("Invalid content type.");
    }
    
    // Get file paths before deletion to remove files
    $field_name = ($content_type == 'video_sermons' || $content_type == 'glorious_highlights') ? 'video_path' : 'audio_path';
    $sql = "SELECT $field_name, image_path FROM $content_type WHERE $id_field = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $content = $result->fetch_assoc();
    
    // Delete the record from database
    $sql = "DELETE FROM $content_type WHERE $id_field = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $content_id);
    
    // Execute the query
    if ($stmt->execute()) {
        // Delete associated files if they exist
        if ($content) {
            // Delete main file (video or audio)
            if (!empty($content[$field_name])) {
                $file_path = "../../" . $content[$field_name];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // Delete thumbnail image
            if (!empty($content['image_path'])) {
                $image_path = "../../" . $content['image_path'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }
        
        // Redirect back to manage content page
        header('Location: ../../view/admin/managePEDS.php?status=deleted');
        exit;
    } else {
        die("Error deleting content: " . $stmt->error);
    }
}
?>