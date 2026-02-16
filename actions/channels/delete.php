<?php
// deletechannel.php
include('../../db/config.php');

if (isset($_GET['id'])) {
    $channel_id = $_GET['id'];

    // Fetch channel data before deletion
    $query = "SELECT channel_image FROM channels WHERE channel_id = '$channel_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Delete from database
        $delete_query = "DELETE FROM channels WHERE channel_id = '$channel_id'";
        if (mysqli_query($conn, $delete_query)) {
            // Delete image file if it exists
            if (!empty($row['channel_image'])) {
                $image_path = "../../../" . ltrim($row['channel_image'], '../');
                if (file_exists($image_path) && is_file($image_path)) {
                    unlink($image_path);
                }
            }
            header("Location: ../../view/admin/managechannels.php?success=deleted");
            exit();
        } else {
            header("Location: ../../view/admin/managechannels.php?error=" . urlencode(mysqli_error($conn)));
            exit();
        }
    } else {
        header("Location: ../../view/admin/managechannels.php?error=not_found");
        exit();
    }
}
?>

