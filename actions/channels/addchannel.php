<?php
include('../../db/config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $errors = [];
    $image_path = '';
    
    $upload_dir = '../../uploads/';
    
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            $errors[] = "Failed to create uploads directory";
        }
    }

    // Get channel name and link
    $name = isset($_POST['name']) ? mysqli_real_escape_string($conn, $_POST['name']) : '';
    $link = isset($_POST['link']) ? mysqli_real_escape_string($conn, $_POST['link']) : '';

    if (empty($name)) {
        $errors[] = "Channel name is required";
    }
    if (empty($link)) {
        $errors[] = "Channel link is required";
    }

    if (isset($_FILES['channel_image']) && $_FILES['channel_image']['error'] == 0) {
        $image_name = $_FILES['channel_image']['name'];
        $image_tmp_name = $_FILES['channel_image']['tmp_name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        $allowed_image_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_ext, $allowed_image_extensions)) {
            $image_new_name = uniqid('channel_', true) . '.' . $image_ext;
            $image_full_path = $upload_dir . $image_new_name;
            $image_path = '../uploads/' . $image_new_name;

            if (!move_uploaded_file($image_tmp_name, $image_full_path)) {
                $errors[] = "Failed to upload image. Error: " . error_get_last()['message'];
            }
        } else {
            $errors[] = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
        }
    } else {
        $errors[] = "Channel image is required";
    }

    if (empty($errors)) {
        $image_path = mysqli_real_escape_string($conn, $image_path);

        $query = "INSERT INTO channels (name, channel_image, link) 
                  VALUES ('$name', '$image_path', '$link')";

        if (mysqli_query($conn, $query)) {
            header("Location: ../../view/admin/managechannels.php?success=1");
            exit();
        } else {
            $errors[] = "Database Error: " . mysqli_error($conn);
            header("Location: ../../view/admin/managechannels.php?error=" . urlencode(implode('; ', $errors)));
            exit();
        }
    } else {
        header("Location: ../../view/admin/managechannels.php?error=" . urlencode(implode('; ', $errors)));
        exit();
    }
}
?>