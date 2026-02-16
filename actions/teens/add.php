<?php
include('../../db/tvconfig.php');

$upload_dir = "../../uploads/teenstv/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content_type = mysqli_real_escape_string($conn, $_POST['content_type'] ?? '');
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $is_scheduled = isset($_POST['is_scheduled']) ? 1 : 0;
    $schedule_start = !empty($_POST['schedule_start']) ? mysqli_real_escape_string($conn, $_POST['schedule_start']) : null;
    $schedule_end = !empty($_POST['schedule_end']) ? mysqli_real_escape_string($conn, $_POST['schedule_end']) : null;
    $countdown_start_offset = !empty($_POST['countdown_start_offset']) ? (int)$_POST['countdown_start_offset'] : 0;

    $video_path = null;
    $audio_path = null;

    // Chunked video upload
    if (isset($_POST['chunk'], $_POST['totalChunks'], $_POST['uploadType']) && $_POST['uploadType'] === 'video' && isset($_FILES['video_file'])) {
        $chunk = (int)$_POST['chunk'];
        $totalChunks = (int)$_POST['totalChunks'];
        $fileName = $_POST['fileName'] ?? '';
        $temp_file = $upload_dir . 'temp_video_' . $fileName;
        file_put_contents($temp_file, file_get_contents($_FILES['video_file']['tmp_name']), FILE_APPEND);

        if ($chunk + 1 == $totalChunks) {
            $video_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['mp4', 'mov', 'avi', 'webm'];
            $mime = mime_content_type($temp_file);
            $allowed_mime = ['video/mp4', 'video/webm', 'video/ogg', 'video/x-msvideo', 'video/mpeg', 'video/quicktime'];
            if (in_array($video_ext, $allowed) && in_array($mime, $allowed_mime)) {
                $video_filename = time() . '_' . uniqid() . '.' . $video_ext;
                $final_path = $upload_dir . $video_filename;
                rename($temp_file, $final_path);
                $video_path = "uploads/teenstv/" . $video_filename;
            } else {
                if (file_exists($temp_file)) unlink($temp_file);
                http_response_code(400);
                echo "Invalid video file.";
                exit;
            }
            if (!empty($_FILES['audio_file']['name']) && $_FILES['audio_file']['error'] == 0) {
                $audio_filename = time() . '_' . basename($_FILES['audio_file']['name']);
                if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_dir . $audio_filename)) {
                    $audio_path = "uploads/teenstv/" . $audio_filename;
                }
            }
            $stmt = $conn->prepare("INSERT INTO teenstv (content_type, title, description, video_path, audio_path, is_scheduled, schedule_start, schedule_end, countdown_start_offset) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssissi", $content_type, $title, $description, $video_path, $audio_path, $is_scheduled, $schedule_start, $schedule_end, $countdown_start_offset);
            if ($stmt->execute()) {
                http_response_code(200);
                echo "Content added successfully.";
            } else {
                http_response_code(500);
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
            exit;
        }
        http_response_code(200);
        echo "OK";
        exit;
    }

    // Chunked audio upload
    if (isset($_POST['chunk'], $_POST['totalChunks'], $_POST['uploadType']) && $_POST['uploadType'] === 'audio' && isset($_FILES['audio_file'])) {
        $chunk = (int)$_POST['chunk'];
        $totalChunks = (int)$_POST['totalChunks'];
        $fileName = $_POST['fileName'] ?? '';
        $temp_file = $upload_dir . 'temp_audio_' . $fileName;
        file_put_contents($temp_file, file_get_contents($_FILES['audio_file']['tmp_name']), FILE_APPEND);

        if ($chunk + 1 == $totalChunks) {
            $audio_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];
            $audio_filename = time() . '_' . uniqid() . '.' . $audio_ext;
            $final_path = $upload_dir . $audio_filename;
            if (in_array($audio_ext, $allowed)) {
                rename($temp_file, $final_path);
                $audio_path = "uploads/teenstv/" . $audio_filename;
            } else {
                if (file_exists($temp_file)) unlink($temp_file);
                http_response_code(400);
                echo "Invalid audio file.";
                exit;
            }
            if (!empty($_FILES['video_file']['name']) && $_FILES['video_file']['error'] == 0) {
                $video_filename = time() . '_' . basename($_FILES['video_file']['name']);
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $upload_dir . $video_filename)) {
                    $video_path = "uploads/teenstv/" . $video_filename;
                }
            }
            $stmt = $conn->prepare("INSERT INTO teenstv (content_type, title, description, video_path, audio_path, is_scheduled, schedule_start, schedule_end, countdown_start_offset) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssissi", $content_type, $title, $description, $video_path, $audio_path, $is_scheduled, $schedule_start, $schedule_end, $countdown_start_offset);
            if ($stmt->execute()) {
                http_response_code(200);
                echo "Content added successfully.";
            } else {
                http_response_code(500);
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
            exit;
        }
        http_response_code(200);
        echo "OK";
        exit;
    }

    // Non-chunked (single request)
    if (!empty($_FILES['video_file']['name'])) {
        $video_filename = time() . '_' . basename($_FILES['video_file']['name']);
        $video_full = $upload_dir . $video_filename;
        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $video_full)) {
            $video_path = "uploads/teenstv/" . $video_filename;
        } else {
            http_response_code(500);
            echo "Error uploading video file.";
            exit;
        }
    }
    if (!empty($_FILES['audio_file']['name'])) {
        $audio_filename = time() . '_' . basename($_FILES['audio_file']['name']);
        $audio_full = $upload_dir . $audio_filename;
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $audio_full)) {
            $audio_path = "uploads/teenstv/" . $audio_filename;
        } else {
            http_response_code(500);
            echo "Error uploading audio file.";
            exit;
        }
    }

    $stmt = $conn->prepare("INSERT INTO teenstv (content_type, title, description, video_path, audio_path, is_scheduled, schedule_start, schedule_end, countdown_start_offset) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssissi", $content_type, $title, $description, $video_path, $audio_path, $is_scheduled, $schedule_start, $schedule_end, $countdown_start_offset);
    if ($stmt->execute()) {
        http_response_code(200);
        echo "Content added successfully.";
    } else {
        http_response_code(500);
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
} else {
    http_response_code(405);
    echo "Method not allowed.";
}
?>