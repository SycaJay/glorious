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
    
    $title = $_POST['title'];
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $uploaded_date = isset($_POST['uploaded_date']) ? $_POST['uploaded_date'] : null;
    
    // Define file upload paths
    $upload_dir = "../../uploads/";
    $file_path = null;
    $image_path = null;
    
    // Get existing data
    $field_name = ($content_type == 'video_sermons' || $content_type == 'glorious_highlights') ? 'video_path' : 'audio_path';
    $sql = "SELECT $field_name, image_path FROM $content_type WHERE $id_field = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $content_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing = $result->fetch_assoc();
    
    // Process file upload if a file was submitted
    if (isset($_FILES['file_path']) && $_FILES['file_path']['size'] > 0) {
        $file_name = time() . '_' . basename($_FILES['file_path']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['file_path']['tmp_name'], $target_file)) {
            $file_path = 'uploads/' . $file_name;
            
            // Delete old file if exists
            if ($existing && !empty($existing[$field_name])) {
                $old_file = "../../" . $existing[$field_name];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
        } else {
            die("Error uploading file.");
        }
    } else {
        $file_path = $existing[$field_name];
    }
    
    // Process thumbnail image upload if submitted
    if (isset($_FILES['image_path']) && $_FILES['image_path']['size'] > 0) {
        $image_name = time() . '_thumb_' . basename($_FILES['image_path']['name']);
        $target_image = $upload_dir . $image_name;
        
        if (move_uploaded_file($_FILES['image_path']['tmp_name'], $target_image)) {
            $image_path = 'uploads/' . $image_name;
            
            // Delete old image if exists
            if ($existing && !empty($existing['image_path'])) {
                $old_image = "../../" . $existing['image_path'];
                if (file_exists($old_image)) {
                    unlink($old_image);
                }
            }
        } else {
            die("Error uploading thumbnail image.");
        }
    } else {
        $image_path = $existing['image_path'];
    }
    
    // Update data based on content type
    switch ($content_type) {
        case 'video_sermons':
            $sql = "UPDATE $content_type SET title = ?, video_path = ?, image_path = ?, description = ? WHERE $id_field = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $file_path, $image_path, $description, $content_id);
            break;
            
        case 'podcasts':
            $sql = "UPDATE $content_type SET title = ?, audio_path = ?, image_path = ?, description = ? WHERE $id_field = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $file_path, $image_path, $description, $content_id);
            break;
            
        case 'audio_sermons':
            $sql = "UPDATE $content_type SET title = ?, audio_path = ?, image_path = ?, description = ? WHERE $id_field = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $title, $file_path, $image_path, $description, $content_id);
            break;
            
        case 'glorious_highlights':
            $sql = "UPDATE $content_type SET title = ?, video_path = ?, uploaded_date = ? WHERE $id_field = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $title, $file_path, $uploaded_date, $content_id);
            break;
            
        default:
            die("Invalid content type.");
    }
    
    // Execute the query
    if ($stmt->execute()) {
        header('Location: ../../view/admin/managePEDS.php?status=updated');
        exit;
    } else {
        die("Error updating content: " . $stmt->error);
    }
}
?>