<?php
include('../../db/tvconfig.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['id'])) {
    $movie_id = intval($_GET['id']);
    
    if ($movie_id <= 0) {
        header("Location: ../../view/admin/managemovies.php?error=Invalid movie ID");
        exit();
    }
    
    // First, get the file paths to potentially delete the files
    $query = "SELECT video_path, image_path FROM movies WHERE movie_id = $movie_id";
    $result = mysqli_query($conn, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $video_path = $row['video_path'];
        $image_path = $row['image_path'];
        
        // Now delete the record from the database
        $delete_query = "DELETE FROM movies WHERE movie_id = $movie_id";
        
        if (mysqli_query($conn, $delete_query)) {
            // Attempt to delete the physical files if they exist
            if (!empty($video_path)) {
                $full_video_path = '../../' . $video_path;
                if (file_exists($full_video_path)) {
                    unlink($full_video_path);
                }
            }
            
            if (!empty($image_path)) {
                $full_image_path = '../../' . $image_path;
                if (file_exists($full_image_path)) {
                    unlink($full_image_path);
                }
            }
            
            header("Location: ../../view/admin/managemovies.php?success=3");
            exit();
        } else {
            header("Location: ../../view/admin/managemovies.php?error=" . urlencode("Failed to delete movie: " . mysqli_error($conn)));
            exit();
        }
    } else {
        header("Location: ../../view/admin/managemovies.php?error=" . urlencode("Movie not found"));
        exit();
    }
} else {
    header("Location: ../../view/admin/managemovies.php?error=" . urlencode("No movie ID provided"));
    exit();
}
?>