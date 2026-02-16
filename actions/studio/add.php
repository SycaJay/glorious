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
    $title = $_POST['title'];
    $description = isset($_POST['description']) ? $_POST['description'] : null;
    $uploaded_date = isset($_POST['uploaded_date']) ? $_POST['uploaded_date'] : null;
    
    // Define file upload paths
    $upload_dir = "../../uploads/";
    $file_path = null;
    $image_path = null;
    
    // Process file upload (video or audio) if a file was submitted
    if (isset($_FILES['file_path']) && $_FILES['file_path']['error'] == 0) {
        $file_name = time() . '_' . basename($_FILES['file_path']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Move uploaded file to target directory
        if (move_uploaded_file($_FILES['file_path']['tmp_name'], $target_file)) {
            $file_path = 'uploads/' . $file_name;
        } else {
            die("Error uploading file.");
        }
    }
    
    // Process thumbnail image upload if submitted
    if (isset($_FILES['image_path']) && $_FILES['image_path']['error'] == 0) {
        $image_name = time() . '_thumb_' . basename($_FILES['image_path']['name']);
        $target_image = $upload_dir . $image_name;
        
        // Move uploaded image to target directory
        if (move_uploaded_file($_FILES['image_path']['tmp_name'], $target_image)) {
            $image_path = 'uploads/' . $image_name;
        } else {
            die("Error uploading thumbnail image.");
        }
    }
    
    // Insert data based on content type
    switch ($content_type) {
        case 'video_sermons':
            $sql = "INSERT INTO video_sermons (title, video_path, image_path, description, created_at) 
                   VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $title, $file_path, $image_path, $description);
            break;
            
        case 'podcasts':
            $sql = "INSERT INTO podcasts (title, audio_path, image_path, description, created_at) 
                   VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $title, $file_path, $image_path, $description);
            break;
            
        case 'audio_sermons':
            $sql = "INSERT INTO audio_sermons (title, audio_path, image_path, description, created_at) 
                   VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $title, $file_path, $image_path, $description);
            break;
            
        case 'glorious_highlights':
            $sql = "INSERT INTO glorious_highlights (title, video_path, uploaded_date, created_at) 
                   VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $title, $file_path, $uploaded_date);
            break;
            
        default:
            die("Invalid content type.");
    }
    
    // Execute the query
    if ($stmt->execute()) {
        // Redirect back to manage content page
        header('Location: ../../view/admin/managePEDS.php?status=added');
        exit;
    } else {
        die("Error adding content: " . $stmt->error);
    }
}
?>