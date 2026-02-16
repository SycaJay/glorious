<?php
// editchannel.php
include('../../db/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $channel_id = $_POST['channel_id'];
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $link = mysqli_real_escape_string($conn, $_POST['link']);
    $errors = [];

    // Update name and link
    $update_query = "UPDATE channels SET name = '$name', link = '$link'";

    // Handle image upload if new image is provided
    if (isset($_FILES['channel_image']) && $_FILES['channel_image']['error'] == 0) {
        // Get old image path
        $query = "SELECT channel_image FROM channels WHERE channel_id = '$channel_id'";
        $result = mysqli_query($conn, $query);
        $old_image = mysqli_fetch_assoc($result)['channel_image'];

        // Upload new image
        $image_name = $_FILES['channel_image']['name'];
        $image_tmp_name = $_FILES['channel_image']['tmp_name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        
        $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($image_ext, $allowed_image_extensions)) {
            $image_new_name = uniqid('channel_', true) . '.' . $image_ext;
            $image_full_path = '../../../uploads/' . $image_new_name;
            $image_path = '../uploads/' . $image_new_name;

            if (move_uploaded_file($image_tmp_name, $image_full_path)) {
                // Delete old image
                if (!empty($old_image)) {
                    $old_path = "../../../" . ltrim($old_image, '../');
                    if (file_exists($old_path) && is_file($old_path)) {
                        unlink($old_path);
                    }
                }

                $image_path = mysqli_real_escape_string($conn, $image_path);
                $update_query .= ", channel_image = '$image_path'";
            } else {
                $errors[] = "Failed to upload image";
            }
        } else {
            $errors[] = "Invalid image format";
        }
    }

    $update_query .= " WHERE channel_id = '$channel_id'";

    if (empty($errors)) {
        if (mysqli_query($conn, $update_query)) {
            header("Location: ../../view/admin/managechannels.php?success=updated");
            exit();
        } else {
            $errors[] = "Database Error: " . mysqli_error($conn);
        }
    }

    if (!empty($errors)) {
        header("Location: ../../view/admin/managechannels.php?error=" . urlencode(implode('; ', $errors)));
        exit();
    }
}
?>