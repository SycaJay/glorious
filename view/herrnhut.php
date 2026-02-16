<?php
// Include your database configuration file
require_once '../db/tvconfig.php';

// Fetch the latest video from the database
$conn = getDBConnection();

if (!$conn) {
    die("Database connection failed. Please try again later.");
}

$videoPath = "../uploads/herrnhut/default.mp4";
$videoTitle = "Featured Video";
$videoThumbnail = "../assets/default-thumbnail.jpg";
$scheduledVideo = null;

// Query to check for a scheduled video (where scheduled_date is in the future)
$currentDateTime = date('Y-m-d H:i:s');
$sqlScheduled = "SELECT video_title, video_file, scheduled_date 
                FROM herrnhut_videos 
                WHERE scheduled_date IS NOT NULL 
                AND scheduled_date > '$currentDateTime' 
                ORDER BY scheduled_date ASC 
                LIMIT 1";
$resultScheduled = $conn->query($sqlScheduled);

if ($resultScheduled && $resultScheduled->num_rows > 0) {
    // If there's a scheduled video
    $row = $resultScheduled->fetch_assoc();
    $scheduledVideo = [
        'title' => $row['video_title'],
        'file' => $row['video_file'],
        'scheduled_date' => $row['scheduled_date']
    ];
    $videoTitle = $row['video_title'];
} else {
    // If no scheduled video, fetch the latest non-scheduled video
    $sqlLatest = "SELECT video_title, video_file 
                 FROM herrnhut_videos 
                 WHERE scheduled_date IS NULL 
                 OR scheduled_date <= '$currentDateTime' 
                 ORDER BY created_at DESC 
                 LIMIT 1";
    $resultLatest = $conn->query($sqlLatest);

    if ($resultLatest && $resultLatest->num_rows > 0) {
        $row = $resultLatest->fetch_assoc();
        $videoTitle = $row['video_title'] ?? "Featured Video";
        $videoFileName = $row['video_file'];
        $videoPath = "../uploads/herrnhut/" . $videoFileName;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Herrnhut Television</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts for Modern Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/assets/Herrnhut.jpg" type="image/x-icon">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        .video-container {
            position: relative;
            width: 100%;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
        }
        
        .video-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #000;
        }

        .video-error {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            color: #64748b;
            text-align: center;
            padding: 1rem;
        }

        .vintage-border {
            border: 2px solid #1E3A8A;
            box-shadow: 0 0 15px rgba(30, 58, 138, 0.3);
        }

        .custom-controls {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.7);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .video-container:hover .custom-controls {
            opacity: 1;
        }

        .scheduled-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 1rem;
        }

        .scheduled-overlay h3 {
            font-size: 2rem;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }

        .scheduled-overlay p {
            font-size: 1.25rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 1024px) {
            .featured-section {
                width: 90% !important;
            }
        }

        @media (max-width: 768px) {
            .featured-section {
                width: 100% !important;
                padding: 0 0.5rem;
            }

            .scheduled-overlay h3 {
                font-size: 1.5rem;
            }

            .scheduled-overlay p {
                font-size: 1rem;
            }

            .video-title {
                font-size: 1.75rem !important;
            }
        }

        @media (max-width: 480px) {
            .scheduled-overlay h3 {
                font-size: 1.25rem;
            }

            .scheduled-overlay p {
                font-size: 0.875rem;
            }

            .video-title {
                font-size: 1.5rem !important;
            }

            .nav-logo {
                width: 48px !important;
                height: 48px !important;
            }

            .nav-title {
                font-size: 1.25rem !important;
            }

            .nav-subtitle {
                font-size: 0.75rem !important;
            }

            .nav-button {
                padding: 0.5rem 1rem !important;
                font-size: 0.875rem !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-stone-100" style="background-image: url('/assets/Sky1.mp4'); background-size: cover; background-attachment: fixed;">
<!-- Video Background -->
<div class="fixed inset-0 z-0 overflow-hidden">
  <video autoplay muted loop playsinline class="w-full h-full object-cover">
    <source src="/assets/Sky1.mp4" type="video/mp4" />
    Your browser does not support the video tag.
  </video>
</div>

    <!-- Navigation -->
    <nav class="relative z-20 bg-blue-900 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Logo and Title (Far Left) -->
                <div class="flex items-center">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white rounded-full vintage-border flex-shrink-0 nav-logo">
                        <img src="/assets/Herrnhut.jpg" alt="Herrnhut Logo" class="w-full h-full object-cover rounded-full">
                    </div>
                    <div class="flex flex-col ml-3 sm:ml-4">
                        <span class="text-xl sm:text-2xl font-bold text-white nav-title">Herrnhut Television</span>
                        <span class="text-xs sm:text-sm text-blue-200 italic nav-subtitle">The Lord's Watch</span>
                    </div>
                </div>
                <!-- Return to Homepage (Far Right) -->
                <div>
                    <a href="../index.php" class="bg-blue-500 text-white py-1.5 px-3 sm:py-2 sm:px-4 rounded hover:bg-blue-400 transition-colors duration-200 nav-button">
                        Return to Homepage
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 container mx-auto px-4 pt-8 sm:pt-12">
        <!-- Featured Section -->
        <div class="featured-section w-3/4 mx-auto mb-12 sm:mb-16">
            <h2 class="video-title text-3xl sm:text-4xl font-bold text-white mb-6 text-center drop-shadow-lg"><?php echo htmlspecialchars($videoTitle); ?></h2>
            <div class="vintage-border bg-white rounded-lg overflow-hidden">
                <div class="video-container">
                    <?php if ($scheduledVideo): ?>
                        <!-- Scheduled Video Overlay -->
                        <div class="scheduled-overlay">
                            <h3><?php echo htmlspecialchars($scheduledVideo['title']); ?></h3>
                            <i class="fas fa-play-circle text-4xl sm:text-5xl"></i>
                            <p>Premieres in: <span id="countdownTimer">Loading...</span></p>
                        </div>
                    <?php else: ?>
                        <!-- Playable Video -->
                        <video id="mainVideo" class="w-full" preload="metadata" poster="../assets/default-thumbnail.jpg">
                            <source src="<?php echo htmlspecialchars($videoPath); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <div id="videoError" class="video-error hidden">
                            <div>
                                <i class="fas fa-exclamation-circle text-4xl mb-2"></i>
                                <p>Sorry, there was an error loading the video. Please try again later.</p>
                            </div>
                        </div>
                        <div class="custom-controls">
                            <button id="playPauseBtn" class="text-white hover:text-blue-300">
                                <i class="fas fa-play"></i>
                            </button>
                            <div class="flex-grow">
                                <input type="range" id="progressBar" class="w-full" value="0">
                            </div>
                            <button id="muteBtn" class="text-white hover:text-blue-300">
                                <i class="fas fa-volume-up"></i>
                            </button>
                            <button id="fullscreenBtn" class="text-white hover:text-blue-300">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 sm:p-6 rounded-lg vintage-border">
            <h3 class="text-lg sm:text-xl font-semibold text-blue-900 mb-4">Prayer Watch</h3>
            <p class="text-stone-600 text-sm sm:text-base">Join our 24/7 prayer chain.</p>
        </div>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 bg-blue-900 mt-12 sm:mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 sm:gap-8">
                <div class="text-blue-100">
                    <h4 class="text-lg sm:text-xl font-semibold mb-4">Contact</h4>
                    <div class="space-y-2 text-sm sm:text-base">
                        <p><i class="fas fa-map-marker-alt mr-2"></i>Takoradi, Western Ghana</p>
                        <p><i class="fas fa-envelope mr-2"></i><a href="https://gloriousvisionstvplus.com" class="hover:text-white transition-colors duration-200">gloriousvisionstvplus.com</a></p>
                        <p><i class="fas fa-phone mr-2"></i>+233 20 126 0746</p>
                        <p><a href="https://wa.me/233201260746" class="text-blue-100 hover:text-white transition-colors duration-200">
                            <i class="fab fa-whatsapp text-xl sm:text-2xl"></i>
                        </a></p>
                    </div>
                </div>
                
                <div class="text-blue-100">
                    <h4 class="text-lg sm:text-xl font-semibold mb-4">News From Zion</h4>
                    <p class="italic text-sm sm:text-base">Subscribe to receive daily news from Zion</p>
                    <form class="mt-4">
                        <input type="email" placeholder="Enter your email" 
                               class="w-full px-4 py-2 rounded-lg bg-blue-50 text-blue-900 text-sm sm:text-base">
                    </form>
                </div>

                <div class="text-blue-100">
                    <h4 class="text-lg sm:text-xl font-semibold mb-4">Connect With Us</h4>
                    <div class="flex space-x-4">
                        <a href="https://www.facebook.com/groups/1454613778324060" class="text-blue-200 hover:text-white transition-colors duration-200">
                            <i class="fab fa-facebook-f text-xl sm:text-2xl"></i>
                        </a>
                        <a href="https://www.youtube.com/@gloriousvisionsherrnhuttel4605" class="text-blue-200 hover:text-white transition-colors duration-200">
                            <i class="fab fa-youtube text-xl sm:text-2xl"></i>
                        </a>
                        <a href="https://www.instagram.com/glorylife_today/" class="text-blue-200 hover:text-white transition-colors duration-200">
                            <i class="fab fa-instagram text-xl sm:text-2xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Video Player Controls (if no scheduled video)
            const video = document.getElementById('mainVideo');
            const videoError = document.getElementById('videoError');
            const playPauseBtn = document.getElementById('playPauseBtn');
            const muteBtn = document.getElementById('muteBtn');
            const fullscreenBtn = document.getElementById('fullscreenBtn');
            const progressBar = document.getElementById('progressBar');

            if (video) {
                // Error handling
                video.addEventListener('error', function() {
                    videoError.classList.remove('hidden');
                });

                // Play/Pause
                playPauseBtn.addEventListener('click', function() {
                    if (video.paused) {
                        video.play();
                        playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                    } else {
                        video.pause();
                        playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
                    }
                });

                // Mute
                muteBtn.addEventListener('click', function() {
                    video.muted = !video.muted;
                    muteBtn.innerHTML = video.muted ? 
                        '<i class="fas fa-volume-mute"></i>' : 
                        '<i class="fas fa-volume-up"></i>';
                });

                // Fullscreen
                fullscreenBtn.addEventListener('click', function() {
                    if (video.requestFullscreen) {
                        video.requestFullscreen();
                    } else if (video.webkitRequestFullscreen) {
                        video.webkitRequestFullscreen();
                    } else if (video.msRequestFullscreen) {
                        video.msRequestFullscreen();
                    }
                });

                // Progress bar
                video.addEventListener('timeupdate', function() {
                    const value = (video.currentTime / video.duration) * 100;
                    progressBar.value = value;
                });

                progressBar.addEventListener('change', function() {
                    const time = (progressBar.value / 100) * video.duration;
                    video.currentTime = time;
                });

                // Mobile touch events
                video.addEventListener('touchstart', function() {
                    if (video.paused) {
                        video.play();
                        playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                    } else {
                        video.pause();
                        playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
                    }
                });
            }

            // Countdown timer for scheduled video
            <?php if ($scheduledVideo): ?>
                const premiereTime = new Date('<?php echo $scheduledVideo['scheduled_date']; ?>').getTime();
                const countdownElement = document.getElementById('countdownTimer');

                function updateCountdown() {
                    const now = new Date().getTime();
                    const distance = premiereTime - now;

                    if (distance <= 0) {
                        countdownElement.textContent = "Now Premiering!";
                        // Optionally reload the page to load the video
                        setTimeout(() => location.reload(), 1000);
                        return;
                    }

                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    countdownElement.textContent = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                }

                if (countdownElement) {
                    updateCountdown();
                    setInterval(updateCountdown, 1000);
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>