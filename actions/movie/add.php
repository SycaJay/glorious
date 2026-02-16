<?php
include('../../db/tvconfig.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $video_path = '';
    $image_path = '';
    $subtitle_path = '';

    $upload_dir = '../../uploads/movies/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $errors[] = "Failed to create uploads directory";
        }
    }

    $title = isset($_POST['title']) ? mysqli_real_escape_string($conn, $_POST['title']) : '';
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
    $status = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : '';
    $scheduled_time = isset($_POST['scheduled_time']) ? mysqli_real_escape_string($conn, $_POST['scheduled_time']) : null;

    if (empty($title)) $errors[] = "Movie title is required";
    if (empty($status)) $errors[] = "Status is required";

    // Chunked video upload: append chunk; on last chunk finalize video then handle image/subtitle
    if (isset($_POST['chunk']) && isset($_POST['totalChunks']) && isset($_FILES['video'])) {
        $chunk = (int)$_POST['chunk'];
        $totalChunks = (int)$_POST['totalChunks'];
        $fileName = $_POST['fileName'] ?? '';

        $temp_file = $upload_dir . 'temp_' . $fileName;
        file_put_contents($temp_file, file_get_contents($_FILES['video']['tmp_name']), FILE_APPEND);

        if ($chunk + 1 == $totalChunks) {
            $video_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed_video_extensions = ['mp4', 'mov', 'avi', 'webm'];
            $allowed_types = ['video/mp4', 'video/webm', 'video/ogg', 'video/x-msvideo', 'video/mpeg', 'video/quicktime'];
            $file_type = mime_content_type($temp_file);

            if (in_array($video_ext, $allowed_video_extensions) && in_array($file_type, $allowed_types)) {
                $video_new_name = uniqid('movie_', true) . '.' . $video_ext;
                $final_video_path = $upload_dir . $video_new_name;
                rename($temp_file, $final_video_path);
                $video_path = 'uploads/movies/' . $video_new_name;
            } else {
                if (file_exists($temp_file)) unlink($temp_file);
                $errors[] = "Invalid video format. Only MP4, MOV, AVI, and WEBM are allowed.";
            }

            // Handle image (sent on last chunk)
            if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $image_name = $_FILES['image']['name'];
                $image_tmp_name = $_FILES['image']['tmp_name'];
                $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
                $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($image_ext, $allowed_image_extensions)) {
                    $image_new_name = uniqid('movie_img_', true) . '.' . $image_ext;
                    $image_full_path = $upload_dir . $image_new_name;
                    if (move_uploaded_file($image_tmp_name, $image_full_path)) {
                        $image_path = 'uploads/movies/' . $image_new_name;
                    }
                }
            }

            // Handle subtitle (sent on last chunk)
            if (empty($errors) && isset($_FILES['subtitle']) && $_FILES['subtitle']['error'] == 0) {
                $subtitle_name = $_FILES['subtitle']['name'];
                $subtitle_tmp_name = $_FILES['subtitle']['tmp_name'];
                $subtitle_ext = strtolower(pathinfo($subtitle_name, PATHINFO_EXTENSION));
                $allowed_subtitle_extensions = ['vtt', 'srt'];
                if (in_array($subtitle_ext, $allowed_subtitle_extensions)) {
                    $subtitle_new_name = uniqid('subtitle_', true) . '.' . $subtitle_ext;
                    $subtitle_full_path = $upload_dir . $subtitle_new_name;
                    if (move_uploaded_file($subtitle_tmp_name, $subtitle_full_path)) {
                        $subtitle_path = 'uploads/movies/' . $subtitle_new_name;
                    }
                }
            }

            if (empty($errors)) {
                $video_path = mysqli_real_escape_string($conn, $video_path);
                $image_path = mysqli_real_escape_string($conn, $image_path);
                $subtitle_path = mysqli_real_escape_string($conn, $subtitle_path);
                $query = "INSERT INTO movies (title, description, video_path, image_path, subtitle_path, status, scheduled_time) 
                          VALUES ('$title', '$description', '$video_path', '$image_path', '$subtitle_path', '$status', '$scheduled_time')";
                if (mysqli_query($conn, $query)) {
                    header("Location: ../../view/admin/managemovies.php?success=1");
                    exit();
                } else {
                    $errors[] = "Database Error: " . mysqli_error($conn);
                }
            }
            if (!empty($errors)) {
                header("Location: ../../view/admin/managemovies.php?error=" . urlencode(implode('; ', $errors)));
                exit();
            }
        }
        header("Location: ../../view/admin/managemovies.php");
        exit();
    }

    // Non-chunked: single request with optional video, image, subtitle
    if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
        $video_name = $_FILES['video']['name'];
        $video_tmp_name = $_FILES['video']['tmp_name'];
        $video_size = $_FILES['video']['size'];
        $video_ext = strtolower(pathinfo($video_name, PATHINFO_EXTENSION));
        $allowed_video_extensions = ['mp4', 'mov', 'avi', 'webm'];
        if ($video_size > 5 * 1024 * 1024 * 1024) {
            $errors[] = "The video file is too large. Maximum allowed size is 5GB.";
        } elseif (in_array($video_ext, $allowed_video_extensions)) {
            $video_new_name = uniqid('movie_', true) . '.' . $video_ext;
            $video_full_path = $upload_dir . $video_new_name;
            $video_path = 'uploads/movies/' . $video_new_name;
            if (!move_uploaded_file($video_tmp_name, $video_full_path)) {
                $errors[] = "Failed to upload video.";
            }
        } else {
            $errors[] = "Invalid video format. Only MP4, MOV, AVI, and WEBM are allowed.";
        }
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image_name = $_FILES['image']['name'];
        $image_tmp_name = $_FILES['image']['tmp_name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($image_ext, $allowed_image_extensions)) {
            $image_new_name = uniqid('movie_img_', true) . '.' . $image_ext;
            $image_full_path = $upload_dir . $image_new_name;
            $image_path = 'uploads/movies/' . $image_new_name;
            if (!move_uploaded_file($image_tmp_name, $image_full_path)) {
                $errors[] = "Failed to upload image.";
            }
        } else {
            $errors[] = "Invalid image format.";
        }
    }

    if (isset($_FILES['subtitle']) && $_FILES['subtitle']['error'] == 0) {
        $subtitle_name = $_FILES['subtitle']['name'];
        $subtitle_tmp_name = $_FILES['subtitle']['tmp_name'];
        $subtitle_ext = strtolower(pathinfo($subtitle_name, PATHINFO_EXTENSION));
        $allowed_subtitle_extensions = ['vtt', 'srt'];
        if (in_array($subtitle_ext, $allowed_subtitle_extensions)) {
            $subtitle_new_name = uniqid('subtitle_', true) . '.' . $subtitle_ext;
            $subtitle_full_path = $upload_dir . $subtitle_new_name;
            $subtitle_path = 'uploads/movies/' . $subtitle_new_name;
            if (!move_uploaded_file($subtitle_tmp_name, $subtitle_full_path)) {
                $errors[] = "Failed to upload subtitle.";
            }
        } else {
            $errors[] = "Invalid subtitle format.";
        }
    }

    if (empty($errors)) {
        $video_path = mysqli_real_escape_string($conn, $video_path);
        $image_path = mysqli_real_escape_string($conn, $image_path);
        $subtitle_path = mysqli_real_escape_string($conn, $subtitle_path);
        $query = "INSERT INTO movies (title, description, video_path, image_path, subtitle_path, status, scheduled_time) 
                  VALUES ('$title', '$description', '$video_path', '$image_path', '$subtitle_path', '$status', '$scheduled_time')";
        if (mysqli_query($conn, $query)) {
            header("Location: ../../view/admin/managemovies.php?success=1");
            exit();
        } else {
            $errors[] = "Database Error: " . mysqli_error($conn);
        }
    }
    if (!empty($errors)) {
        header("Location: ../../view/admin/managemovies.php?error=" . urlencode(implode('; ', $errors)));
        exit();
    }
}
?>