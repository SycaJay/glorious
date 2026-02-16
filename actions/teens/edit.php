<?php
include('../../db/tvconfig.php');

$upload_dir = "../../uploads/teenstv/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teens_id = (int)($_POST['teens_id'] ?? 0);
    $content_type = mysqli_real_escape_string($conn, $_POST['content_type'] ?? '');
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
    $is_scheduled = isset($_POST['is_scheduled']) ? 1 : 0;
    $schedule_start = !empty($_POST['schedule_start']) ? mysqli_real_escape_string($conn, $_POST['schedule_start']) : null;
    $schedule_end = !empty($_POST['schedule_end']) ? mysqli_real_escape_string($conn, $_POST['schedule_end']) : null;
    $countdown_start_offset = !empty($_POST['countdown_start_offset']) ? (int)$_POST['countdown_start_offset'] : 0;

    $query = "SELECT video_path, audio_path FROM teenstv WHERE teens_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teens_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if (!$row) {
        http_response_code(404);
        echo "Record not found";
        exit;
    }
    $current_video_path = $row['video_path'];
    $current_audio_path = $row['audio_path'];
    $video_path = $current_video_path;
    $audio_path = $current_audio_path;

    // Chunked video upload (edit)
    if (isset($_POST['chunk'], $_POST['totalChunks'], $_POST['uploadType']) && $_POST['uploadType'] === 'video' && isset($_FILES['video_file'])) {
        $chunk = (int)$_POST['chunk'];
        $totalChunks = (int)$_POST['totalChunks'];
        $fileName = $_POST['fileName'] ?? '';
        $temp_file = $upload_dir . 'temp_edit_video_' . $teens_id . '_' . $fileName;
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
                if ($current_video_path && file_exists('../../' . $current_video_path)) {
                    unlink('../../' . $current_video_path);
                }
            } else {
                if (file_exists($temp_file)) unlink($temp_file);
                http_response_code(400);
                echo "Invalid video file.";
                exit;
            }
            if (!empty($_FILES['audio_file']['name']) && $_FILES['audio_file']['error'] == 0) {
                $audio_filename = time() . '_' . basename($_FILES['audio_file']['name']);
                if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_dir . $audio_filename)) {
                    if ($current_audio_path && file_exists('../../' . $current_audio_path)) unlink('../../' . $current_audio_path);
                    $audio_path = "uploads/teenstv/" . $audio_filename;
                }
            }
            $query = "UPDATE teenstv SET content_type = ?, title = ?, description = ?, video_path = ?, audio_path = ?, is_scheduled = ?, schedule_start = ?, schedule_end = ?, countdown_start_offset = ? WHERE teens_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssissii", $content_type, $title, $description, $video_path, $audio_path, $is_scheduled, $schedule_start, $schedule_end, $countdown_start_offset, $teens_id);
            if ($stmt->execute()) {
                http_response_code(200);
                echo "Content updated successfully";
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

    // Chunked audio upload (edit)
    if (isset($_POST['chunk'], $_POST['totalChunks'], $_POST['uploadType']) && $_POST['uploadType'] === 'audio' && isset($_FILES['audio_file'])) {
        $chunk = (int)$_POST['chunk'];
        $totalChunks = (int)$_POST['totalChunks'];
        $fileName = $_POST['fileName'] ?? '';
        $temp_file = $upload_dir . 'temp_edit_audio_' . $teens_id . '_' . $fileName;
        file_put_contents($temp_file, file_get_contents($_FILES['audio_file']['tmp_name']), FILE_APPEND);

        if ($chunk + 1 == $totalChunks) {
            $audio_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowed = ['mp3', 'wav', 'ogg', 'm4a', 'aac'];
            $audio_filename = time() . '_' . uniqid() . '.' . $audio_ext;
            if (in_array($audio_ext, $allowed)) {
                rename($temp_file, $upload_dir . $audio_filename);
                $audio_path = "uploads/teenstv/" . $audio_filename;
                if ($current_audio_path && file_exists('../../' . $current_audio_path)) unlink('../../' . $current_audio_path);
            } else {
                if (file_exists($temp_file)) unlink($temp_file);
                http_response_code(400);
                echo "Invalid audio file.";
                exit;
            }
            if (!empty($_FILES['video_file']['name']) && $_FILES['video_file']['error'] == 0) {
                $video_filename = time() . '_' . basename($_FILES['video_file']['name']);
                if (move_uploaded_file($_FILES['video_file']['tmp_name'], $upload_dir . $video_filename)) {
                    if ($current_video_path && file_exists('../../' . $current_video_path)) unlink('../../' . $current_video_path);
                    $video_path = "uploads/teenstv/" . $video_filename;
                }
            }
            $query = "UPDATE teenstv SET content_type = ?, title = ?, description = ?, video_path = ?, audio_path = ?, is_scheduled = ?, schedule_start = ?, schedule_end = ?, countdown_start_offset = ? WHERE teens_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssissii", $content_type, $title, $description, $video_path, $audio_path, $is_scheduled, $schedule_start, $schedule_end, $countdown_start_offset, $teens_id);
            if ($stmt->execute()) {
                http_response_code(200);
                echo "Content updated successfully";
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

    // Non-chunked
    if (!empty($_FILES['video_file']['name'])) {
        $video_filename = time() . '_' . basename($_FILES['video_file']['name']);
        $upload_video_path = $upload_dir . $video_filename;
        if (move_uploaded_file($_FILES['video_file']['tmp_name'], $upload_video_path)) {
            if ($current_video_path && file_exists('../../' . $current_video_path)) unlink('../../' . $current_video_path);
            $video_path = "uploads/teenstv/" . $video_filename;
        } else {
            http_response_code(500);
            echo "Error uploading video file";
            exit;
        }
    }
    if (!empty($_FILES['audio_file']['name'])) {
        $audio_filename = time() . '_' . basename($_FILES['audio_file']['name']);
        $upload_audio_path = $upload_dir . $audio_filename;
        if (move_uploaded_file($_FILES['audio_file']['tmp_name'], $upload_audio_path)) {
            if ($current_audio_path && file_exists('../../' . $current_audio_path)) unlink('../../' . $current_audio_path);
            $audio_path = "uploads/teenstv/" . $audio_filename;
        } else {
            http_response_code(500);
            echo "Error uploading audio file";
            exit;
        }
    }

    $query = "UPDATE teenstv SET content_type = ?, title = ?, description = ?, video_path = ?, audio_path = ?, is_scheduled = ?, schedule_start = ?, schedule_end = ?, countdown_start_offset = ? WHERE teens_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssssissii", $content_type, $title, $description, $video_path, $audio_path, $is_scheduled, $schedule_start, $schedule_end, $countdown_start_offset, $teens_id);
    if ($stmt->execute()) {
        http_response_code(200);
        echo "Content updated successfully";
    } else {
        http_response_code(500);
        echo "Error updating content: " . $stmt->error;
    }
    $stmt->close();
} else {
    http_response_code(405);
    echo "Method not allowed";
}
$conn->close();
?>