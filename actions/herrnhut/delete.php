<?php
include('../../db/tvconfig.php');

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $herrnhut_id = intval($_GET['id']);

    $query = "SELECT video_file FROM herrnhut_videos WHERE herrnhut_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $herrnhut_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $video = mysqli_fetch_assoc($result);
        $filename = $video['video_file'];

        $upload_dir = '../../uploads/herrnhut/';
        if (!empty($filename)) {
            $file_path = $upload_dir . $filename;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $delete_query = "DELETE FROM herrnhut_videos WHERE herrnhut_id = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "i", $herrnhut_id);

        if (mysqli_stmt_execute($delete_stmt)) {
            header("Location: ../../view/admin/manageherrnhut.php?success=3");
            exit();
        } else {
            header("Location: ../../view/admin/manageherrnhut.php?error=" . urlencode("Failed to delete herrnhut video: " . mysqli_error($conn)));
            exit();
        }
    } else {
        header("Location: ../../view/admin/manageherrnhut.php?error=" . urlencode("Herrnhut video not found"));
        exit();
    }
}
?>
