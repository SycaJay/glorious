<?php
include('../../db/tvconfig.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define upload directory
$upload_dir = '../../uploads/herrnhut/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];

    // Handle chunked upload (same pattern as Live TV)
    if (isset($_POST['chunk']) && isset($_POST['totalChunks']) && isset($_FILES['video_file'])) {
        $chunk = (int)$_POST['chunk'];
        $totalChunks = (int)$_POST['totalChunks'];
        $fileName = $_POST['fileName'];
        $video_title = isset($_POST['video_title']) ? mysqli_real_escape_string($conn, $_POST['video_title']) : '';
        $scheduled_date = isset($_POST['scheduled_date']) && $_POST['scheduled_date'] !== '' ? mysqli_real_escape_string($conn, $_POST['scheduled_date']) : null;

        $temp_file = $upload_dir . 'temp_' . $fileName;
        file_put_contents($temp_file, file_get_contents($_FILES['video_file']['tmp_name']), FILE_APPEND);

        if ($chunk + 1 == $totalChunks) {
            $video_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed_video_extensions = ['mp4', 'mov', 'avi', 'webm'];
            $allowed_types = ['video/mp4', 'video/webm', 'video/ogg', 'video/x-msvideo', 'video/mpeg', 'video/quicktime'];
            $file_type = mime_content_type($temp_file);

            if (in_array($video_ext, $allowed_video_extensions) && in_array($file_type, $allowed_types)) {
                $unique_filename = date('Y-m-d-His') . '-' . uniqid() . '.' . $video_ext;
                $final_file = $upload_dir . $unique_filename;
                rename($temp_file, $final_file);

                $query = "INSERT INTO herrnhut_videos (video_title, video_file, scheduled_date, created_at) 
                         VALUES (?, ?, " . ($scheduled_date ? "?" : "NULL") . ", NOW())";
                $stmt = mysqli_prepare($conn, $query);
                if ($scheduled_date) {
                    mysqli_stmt_bind_param($stmt, "sss", $video_title, $unique_filename, $scheduled_date);
                } else {
                    mysqli_stmt_bind_param($stmt, "ss", $video_title, $unique_filename);
                }

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: ../../view/admin/manageherrnhut.php?success=1");
                    exit();
                } else {
                    $errors[] = "Database Error: " . mysqli_error($conn);
                    unlink($final_file);
                }
            } else {
                if (file_exists($temp_file)) unlink($temp_file);
                $errors[] = "Invalid video file. Only MP4, MOV, AVI, and WEBM are allowed.";
            }
        }
        if (!empty($errors)) {
            header("Location: ../../view/admin/manageherrnhut.php?error=" . urlencode(implode('; ', $errors)));
            exit();
        }
        header("Location: ../../view/admin/manageherrnhut.php");
        exit();
    }

    // Non-chunked (single file) upload
    $video_title = isset($_POST['video_title']) ? mysqli_real_escape_string($conn, $_POST['video_title']) : '';
    $scheduled_date = isset($_POST['scheduled_date']) ? mysqli_real_escape_string($conn, $_POST['scheduled_date']) : null;

    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $video_tmp_name = $_FILES['video_file']['tmp_name'];
        $video_size = $_FILES['video_file']['size'];
        $original_filename = $_FILES['video_file']['name'];
        $video_ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $allowed_video_extensions = ['mp4', 'mov', 'avi', 'webm'];

        $unique_filename = date('Y-m-d-His') . '-' . uniqid() . '.' . $video_ext;
        $upload_path = $upload_dir . $unique_filename;

        if ($video_size > 5 * 1024 * 1024 * 1024) {
            $errors[] = "The video file is too large. Maximum allowed size is 5GB.";
        } elseif (!in_array($video_ext, $allowed_video_extensions)) {
            $errors[] = "Invalid video format. Only MP4, MOV, AVI, and WEBM are allowed.";
        } else {
            if (move_uploaded_file($video_tmp_name, $upload_path)) {
                $query = "INSERT INTO herrnhut_videos (video_title, video_file, scheduled_date, created_at) 
                         VALUES (?, ?, " . ($scheduled_date ? "?" : "NULL") . ", NOW())";
                
                $stmt = mysqli_prepare($conn, $query);
                if ($scheduled_date) {
                    mysqli_stmt_bind_param($stmt, "sss", $video_title, $unique_filename, $scheduled_date);
                } else {
                    mysqli_stmt_bind_param($stmt, "ss", $video_title, $unique_filename);
                }

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: ../../view/admin/manageherrnhut.php?success=1");
                    exit();
                } else {
                    $errors[] = "Database Error: " . mysqli_error($conn);
                    unlink($upload_path);
                }
            } else {
                $errors[] = "Failed to move uploaded file. Check directory permissions.";
            }
        }
    } else {
        $errors[] = "Video file is required";
    }

    if (!empty($errors)) {
        header("Location: ../../view/admin/manageherrnhut.php?error=" . urlencode(implode('; ', $errors)));
        exit();
    }
}
?>
