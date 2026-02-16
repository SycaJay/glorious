<?php
include '../../db/tvconfig.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $hero_id = $_POST['hero_id'];
    $title = $_POST['title'];
    $current_video = $_POST['current_video'];
    $scheduled_time = !empty($_POST['scheduled_time']) ? $_POST['scheduled_time'] : NULL;
    $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : NULL;
    
    // Convert datetime-local format to MySQL format if not NULL
    if ($scheduled_time) {
        $scheduled_time = str_replace('T', ' ', $scheduled_time);
    }
    
    if ($end_time) {
        $end_time = str_replace('T', ' ', $end_time);
    }
    
    // Check if a new file was uploaded
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $allowed_types = ['video/mp4', 'video/webm', 'video/ogg', 'video/x-msvideo', 'video/mpeg', 'video/quicktime'];
        $file_type = $_FILES['video_file']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            // Create uploads directory if it doesn't exist
            $upload_dir = '../../uploads/24hourchannel/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename to avoid overwriting
            $file_name = uniqid() . '_' . basename($_FILES['video_file']['name']);
            $target_file = $upload_dir . $file_name;
            
            // Move the uploaded file to the specified directory
            if (move_uploaded_file($_FILES['video_file']['tmp_name'], $target_file)) {
                // Delete the old file if it exists and it's not a URL
                if (!empty($current_video) && file_exists($current_video)) {
                    unlink($current_video);
                }
                
                // File was uploaded successfully, now update database
                $video_path = $target_file;
                
                // Prepare SQL statement to avoid SQL injection
                $stmt = $conn->prepare("UPDATE hero_video SET title = ?, video = ?, scheduled_time = ?, end_time = ? WHERE hero_id = ?");
                $stmt->bind_param("ssssi", $title, $video_path, $scheduled_time, $end_time, $hero_id);
            } else {
                $_SESSION['message'] = "Error uploading file";
                $_SESSION['message_type'] = "error";
                header("Location: ../../views/admin/managelive.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "Invalid file type. Please upload a valid video file";
            $_SESSION['message_type'] = "error";
            header("Location: ../../views/admin/managelive.php");
            exit();
        }
    } else {
        // No new file uploaded, keep the current video path
        $stmt = $conn->prepare("UPDATE hero_video SET title = ?, scheduled_time = ?, end_time = ? WHERE hero_id = ?");
        $stmt->bind_param("sssi", $title, $scheduled_time, $end_time, $hero_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Stream updated successfully";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating stream: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    
    $stmt->close();
    $conn->close();
    
    // Redirect back to the main page
    header("Location: ../../view/admin/managelive.php");
    exit();
} else {
    // If someone tries to access this file directly without POST data
    header("Location: ../../view/admin/managelive.php");
    exit();
}
?>