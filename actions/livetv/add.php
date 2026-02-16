<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../../db/tvconfig.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $title = $_POST['title'];
    $is_live = isset($_POST['is_live']) ? $_POST['is_live'] : 0;
    $stream_url = isset($_POST['stream_url']) ? $_POST['stream_url'] : NULL;
    
    // Process scheduled and end times
    $scheduled_time = !empty($_POST['scheduled_time']) ? $_POST['scheduled_time'] : NULL;
    $end_time = !empty($_POST['end_time']) ? $_POST['end_time'] : NULL;
    
    // Convert datetime-local format to MySQL format if not NULL
    if ($scheduled_time) {
        $scheduled_time = str_replace('T', ' ', $scheduled_time);
    }
    
    if ($end_time) {
        $end_time = str_replace('T', ' ', $end_time);
    }
    
    // Initialize video path
    $video_path = NULL;
    
    // Process differently based on whether it's a live stream or uploaded video
    if ($is_live == 1 && !empty($stream_url)) {
        // For live streams, explicitly allow NULL for scheduled_time and end_time
        $scheduled_time = ($scheduled_time === NULL || $scheduled_time === '') ? NULL : $scheduled_time;
        $end_time = ($end_time === NULL || $end_time === '') ? NULL : $end_time;
        
        $stmt = $conn->prepare("INSERT INTO hero_video (title, is_live, stream_url, scheduled_time, end_time) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $title, $is_live, $stream_url, $scheduled_time, $end_time);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "New live stream added successfully";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
            $_SESSION['message_type'] = "error";
        }
        
        $stmt->close();
    } else {
        // Handle chunked upload
        if (isset($_POST['chunk']) && isset($_POST['totalChunks']) && isset($_FILES['video_file'])) {
            $chunk = (int)$_POST['chunk'];
            $totalChunks = (int)$_POST['totalChunks'];
            $fileName = $_POST['fileName'];
            $upload_dir = '../../uploads/24hourchannel/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $temp_file = $upload_dir . 'temp_' . $fileName;
            $final_file = $upload_dir . uniqid() . '_' . $fileName;

            // Append chunk to temporary file
            file_put_contents($temp_file, file_get_contents($_FILES['video_file']['tmp_name']), FILE_APPEND);
            
            // If this is the last chunk, finalize the file
            if ($chunk + 1 == $totalChunks) {
                $allowed_types = ['video/mp4', 'video/webm', 'video/ogg', 'video/x-msvideo', 'video/mpeg', 'video/quicktime'];
                $file_type = mime_content_type($temp_file);
                
                if (in_array($file_type, $allowed_types)) {
                    rename($temp_file, $final_file);
                    $video_path = $final_file;
                    
                    $stmt = $conn->prepare("INSERT INTO hero_video (title, video, scheduled_time, end_time, is_live) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("ssssi", $title, $video_path, $scheduled_time, $end_time, $is_live);
                    
                    if ($stmt->execute()) {
                        $_SESSION['message'] = "New video added successfully";
                        $_SESSION['message_type'] = "success";
                    } else {
                        $_SESSION['message'] = "Database Error: " . $stmt->error;
                        $_SESSION['message_type'] = "error";
                    }
                    
                    $stmt->close();
                } else {
                    unlink($temp_file);
                    $_SESSION['message'] = "Invalid file type: " . $file_type . ". Please upload a valid video file";
                    $_SESSION['message_type'] = "error";
                }
            }
        } else {
            $error_messages = [
                0 => "No error, file uploaded successfully",
                1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
                2 => "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form",
                3 => "The uploaded file was only partially uploaded",
                4 => "No file was uploaded",
                6 => "Missing a temporary folder",
                7 => "Failed to write file to disk",
                8 => "A PHP extension stopped the file upload"
            ];
            
            $error_code = isset($_FILES['video_file']['error']) ? $_FILES['video_file']['error'] : 'unknown';
            $error_message = isset($error_messages[$error_code]) ? $error_messages[$error_code] : "Unknown error";
            
            $_SESSION['message'] = "Error uploading file: " . $error_message;
            $_SESSION['message_type'] = "error";
        }
    }
    
    $conn->close();
    
    header("Location: ../../view/admin/managelive.php");
    exit();
} else {
    header("Location: ../../view/admin/managelive.php");
    exit();
}
?>