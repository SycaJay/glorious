<?php
include('../../db/tvconfig.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['herrnhut_id'])) {
    $errors = [];
    $herrnhut_id = intval($_POST['herrnhut_id']);
    if ($herrnhut_id <= 0) {
        $errors[] = "Invalid herrnhut ID";
    }

    $upload_dir = '../../uploads/herrnhut/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Handle chunked upload (new video replace)
    if (isset($_POST['chunk']) && isset($_POST['totalChunks']) && isset($_FILES['video_file'])) {
        $chunk = (int)$_POST['chunk'];
        $totalChunks = (int)$_POST['totalChunks'];
        $fileName = $_POST['fileName'];
        $video_title = isset($_POST['video_title']) ? trim($_POST['video_title']) : '';
        $scheduled_date = isset($_POST['scheduled_date']) && $_POST['scheduled_date'] !== '' ? trim($_POST['scheduled_date']) : null;

        $temp_file = $upload_dir . 'temp_edit_' . $herrnhut_id . '_' . $fileName;
        file_put_contents($temp_file, file_get_contents($_FILES['video_file']['tmp_name']), FILE_APPEND);

        if ($chunk + 1 == $totalChunks) {
            $video_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed_video_extensions = ['mp4', 'mov', 'avi', 'webm'];
            $allowed_types = ['video/mp4', 'video/webm', 'video/ogg', 'video/x-msvideo', 'video/mpeg', 'video/quicktime'];
            $file_type = mime_content_type($temp_file);

            if (in_array($video_ext, $allowed_video_extensions) && in_array($file_type, $allowed_types)) {
                $stmt = mysqli_prepare($conn, "SELECT video_file FROM herrnhut_videos WHERE herrnhut_id = ?");
                mysqli_stmt_bind_param($stmt, "i", $herrnhut_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $current_video = mysqli_fetch_assoc($result);
                $current_filename = $current_video['video_file'] ?? '';

                $new_filename = date('Y-m-d-His') . '-' . uniqid() . '.' . $video_ext;
                $final_file = $upload_dir . $new_filename;
                rename($temp_file, $final_file);

                if (!empty($current_filename) && file_exists($upload_dir . $current_filename)) {
                    unlink($upload_dir . $current_filename);
                }

                $query = "UPDATE herrnhut_videos SET video_title = ?, video_file = ?, scheduled_date = ? WHERE herrnhut_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "sssi", $video_title, $new_filename, $scheduled_date, $herrnhut_id);
                if (mysqli_stmt_execute($stmt)) {
                    header("Location: ../../view/admin/manageherrnhut.php?success=2");
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

    // Non-chunked (single file) edit
    $video_title = isset($_POST['video_title']) ? trim($_POST['video_title']) : '';
    $scheduled_date = isset($_POST['scheduled_date']) && !empty($_POST['scheduled_date']) ? 
                      trim($_POST['scheduled_date']) : null;

    $stmt = mysqli_prepare($conn, "SELECT video_file FROM herrnhut_videos WHERE herrnhut_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $herrnhut_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $current_video = mysqli_fetch_assoc($result);
    $current_filename = $current_video['video_file'] ?? '';

    $new_filename = $current_filename;

    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $video_tmp_name = $_FILES['video_file']['tmp_name'];
        $video_size = $_FILES['video_file']['size'];
        $original_filename = $_FILES['video_file']['name'];
        $video_ext = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
        $allowed_video_extensions = ['mp4', 'mov', 'avi', 'webm'];

        if ($video_size > 5 * 1024 * 1024 * 1024) {
            $errors[] = "The video file is too large. Maximum allowed size is 5GB.";
        } elseif (!in_array($video_ext, $allowed_video_extensions)) {
            $errors[] = "Invalid video format. Only MP4, MOV, AVI, and WEBM are allowed.";
        } else {
            $new_filename = date('Y-m-d-His') . '-' . uniqid() . '.' . $video_ext;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($video_tmp_name, $upload_path)) {
                if (!empty($current_filename) && file_exists($upload_dir . $current_filename)) {
                    unlink($upload_dir . $current_filename);
                }
            } else {
                $errors[] = "Failed to move uploaded file. Check directory permissions.";
            }
        }
    }

    if (empty($errors)) {
        $query = "UPDATE herrnhut_videos SET 
                video_title = ?, 
                video_file = ?, 
                scheduled_date = ?
                WHERE herrnhut_id = ?";
                
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $video_title, $new_filename, $scheduled_date, $herrnhut_id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: ../../view/admin/manageherrnhut.php?success=2");
            exit();
        } else {
            $errors[] = "Database Error: " . mysqli_error($conn);
        }
    }

    if (!empty($errors)) {
        header("Location: ../../view/admin/manageherrnhut.php?error=" . urlencode(implode('; ', $errors)));
        exit();
    }
}
?>
