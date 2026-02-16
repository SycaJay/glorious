<?php
require_once('../db/tvconfig.php');

// Improved function to fetch content from database
function fetchContent($content_type) {
    global $conn;
    $sql = "SELECT * FROM teenstv WHERE content_type = ? AND (is_scheduled = FALSE OR (is_scheduled = TRUE AND schedule_start <= NOW() AND (schedule_end IS NULL OR schedule_end >= NOW()))) ORDER BY created_at DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $content_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Function to get scheduled content for countdown
function fetchScheduledContent($content_type) {
    global $conn;
    $sql = "SELECT * FROM teenstv WHERE content_type = ? AND is_scheduled = TRUE AND schedule_start > NOW() AND countdown_start_offset > 0 ORDER BY schedule_start ASC LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $content_type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// Function to get correct video path
function getVideoPath($dbPath, $default = '') {
    if (empty($dbPath)) {
        return $default;
    }
    if (strpos($dbPath, '../assets/') === 0) {
        return $dbPath;
    }
    if (strpos($dbPath, 'teenstv/') === 0) {
        return '../uploads/' . $dbPath;
    }
    if (strpos($dbPath, 'uploads/teenstv/') !== false) {
        return '../' . $dbPath;
    }
    return '../uploads/teenstv/' . basename($dbPath);
}

// Fetch content for each section
$teensService = fetchContent('Teens Service');
$liveContent = fetchContent('Live Now');

// Check for upcoming scheduled content for countdown
$scheduledTeensService = fetchScheduledContent('Teens Service');
$scheduledLiveContent = fetchScheduledContent('Live Now');

// Set video paths with defaults
$heroVideo = getVideoPath(
    $teensService['video_path'] ?? null,
    '../assets/hero-video.mp4'
);

$liveVideo = getVideoPath(
    $liveContent['video_path'] ?? null,
    '../assets/totateens.mp4'
);

// Set titles with defaults
$heroTitle = $teensService['title'] ?? 'Teens Church Service';
$heroDescription = $teensService['description'] ?? 'Join our weekly teens church';
$liveTitle = $liveContent['title'] ?? 'Live Stream';

// Determine countdown data
$heroCountdown = null;
$liveCountdown = null;

if ($scheduledTeensService && $scheduledTeensService['countdown_start_offset'] > 0) {
    $countdownStartTime = strtotime($scheduledTeensService['schedule_start']) - ($scheduledTeensService['countdown_start_offset'] * 60);
    if (time() >= $countdownStartTime && time() < strtotime($scheduledTeensService['schedule_start'])) {
        $heroCountdown = [
            'title' => $scheduledTeensService['title'],
            'start_time' => $scheduledTeensService['schedule_start'],
            'offset' => $scheduledTeensService['countdown_start_offset']
        ];
    }
}

if ($scheduledLiveContent && $scheduledLiveContent['countdown_start_offset'] > 0) {
    $countdownStartTime = strtotime($scheduledLiveContent['schedule_start']) - ($scheduledLiveContent['countdown_start_offset'] * 60);
    if (time() >= $countdownStartTime && time() < strtotime($scheduledLiveContent['schedule_start'])) {
        $liveCountdown = [
            'title' => $scheduledLiveContent['title'],
            'start_time' => $scheduledLiveContent['schedule_start'],
            'offset' => $scheduledLiveContent['countdown_start_offset']
        ];
    }
}

// Debug output
error_log("Hero Video Path: " . $heroVideo);
error_log("Live Video Path: " . $liveVideo);
error_log("Hero Countdown: " . json_encode($heroCountdown));
error_log("Live Countdown: " . json_encode($liveCountdown));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glorious Visions Teens & Kids TV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="../assets/TeenTv.jpg" type="image/x-icon">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap');
        
        :root {
            --primary: #3b82f6;
            --secondary: #06b6d4;
            --dark: #0f172a;
            --light: #f8fafc;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        
        .gradient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(-45deg, #0f172a, #1e293b, #334155, #475569);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            z-index: -2;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .video-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.7);
            z-index: -1;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        .float {
            animation: float 6s ease-in-out infinite;
        }
        
        .glow-text {
            text-shadow: 0 0 10px rgba(59, 130, 246, 0.7), 
                         0 0 20px rgba(59, 130, 246, 0.5),
                         0 0 30px rgba(59, 130, 246, 0.3);
        }
        
        .gradient-text {
            background: linear-gradient(45deg, var(--secondary), var(--primary));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        
        .card-hover:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 
                        0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--dark);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        
        .video-control {
            transition: all 0.2s ease;
        }
        
        .video-control:hover {
            transform: scale(1.1);
            background-color: rgba(59, 130, 246, 0.8) !important;
        }
        
        .countdown-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            text-align: center;
            padding: 1rem;
        }
        
        .countdown-timer {
            font-size: clamp(1.5rem, 5vw, 3rem);
            font-weight: 800;
            color: white;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
            margin-top: 1rem;
        }
        
        .countdown-title {
            font-size: clamp(1.75rem, 6vw, 3.5rem);
            font-weight: 700;
            color: white;
            text-shadow: 0 0 10px rgba(59, 130, 246, 0.7);
            margin-bottom: 0.5rem;
        }
        
        .countdown-label {
            font-size: clamp(1rem, 3vw, 1.5rem);
            font-weight: 600;
            color: #06b6d4;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.75rem !important;
            }
            
            .hero-description {
                font-size: 0.9rem !important;
            }
            
            .section-title {
                font-size: 1.5rem !important;
            }
            
            .category-icon {
                font-size: 1.5rem !important;
            }
            
            .countdown-title {
                font-size: 1.5rem !important;
            }
            
            .countdown-timer {
                font-size: 1.25rem !important;
            }
            
            .countdown-label {
                font-size: 0.875rem !important;
            }
        }
    </style>
</head>
<body class="min-h-screen text-gray-100">
    <!-- Background Elements -->
    <div class="gradient-bg"></div>
    <div class="video-overlay"></div>
    
    <!-- Animated Video Background -->
    <div class="fixed inset-0 z-0 overflow-hidden">
        <video autoplay muted loop playsinline class="w-full h-full object-cover opacity-30">
            <source src="../assets/Sky1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    
    <!-- Floating Particles -->
    <div class="fixed inset-0 z-0 pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-3 h-3 bg-blue-500 rounded-full opacity-70 float" style="animation-delay: 0s;"></div>
        <div class="absolute top-1/3 right-1/5 w-4 h-4 bg-teal-500 rounded-full opacity-70 float" style="animation-delay: 1s;"></div>
        <div class="absolute bottom-1/4 left-1/5 w-2 h-2 bg-sky-500 rounded-full opacity-70 float" style="animation-delay: 2s;"></div>
        <div class="absolute bottom-1/3 right-1/4 w-3 h-3 bg-cyan-500 rounded-full opacity-70 float" style="animation-delay: 3s;"></div>
    </div>

    <!-- Navigation -->
    <nav class="relative z-50 bg-gray-900/80 backdrop-blur-md border-b border-gray-800">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-600 to-cyan-500 flex items-center justify-center overflow-hidden shadow-lg">
                        <img src="../assets/TeenTv.jpg" alt="Teens TV Logo" class="w-11 h-11 rounded-full object-cover border-2 border-white/20">
                    </div>
                    <span class="text-2xl font-bold text-white gradient-text glow-text">Glorious Visions Teens TV</span>
                </div>
                <div>
                    <a href="../index.php" class="flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-cyan-500 text-white hover:from-blue-700 hover:to-cyan-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                        <i class="fas fa-home text-lg"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 container mx-auto px-4 py-8">
        <!-- Hero Section -->
        <section class="w-full mx-auto mb-16">
            <h1 class="text-5xl font-bold text-center mb-8 text-white hero-title">Glorious Visions Teens TV</h1>
            
            <div class="relative rounded-3xl overflow-hidden shadow-2xl transform transition-all duration-500 hover:shadow-blue-500/30">
                <div class="aspect-w-16 aspect-h-9 relative">
                    <?php if ($heroCountdown): ?>
                        <div class="countdown-container bg-gray-900/90">
                            <h2 class="countdown-title"><?php echo htmlspecialchars($heroCountdown['title']); ?></h2>
                            <p class="countdown-label">Premieres in</p>
                            <div class="countdown-timer" id="heroCountdownTimer"></div>
                        </div>
                        <script>
                            function startHeroCountdown() {
                                const startTime = new Date('<?php echo $heroCountdown['start_time']; ?>').getTime();
                                const countdownElement = document.getElementById('heroCountdownTimer');
                                
                                function updateCountdown() {
                                    const now = new Date().getTime();
                                    const distance = startTime - now;
                                    
                                    if (distance <= 0) {
                                        clearInterval(interval);
                                        window.location.reload(); // Reload to show the video
                                        return;
                                    }
                                    
                                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                    
                                    countdownElement.textContent = 
                                        (days > 0 ? `${days}d ` : '') +
                                        (hours > 0 || days > 0 ? `${hours}h ` : '') +
                                        `${minutes}m ${seconds}s`;
                                }
                                
                                updateCountdown();
                                const interval = setInterval(updateCountdown, 1000);
                            }
                            
                            startHeroCountdown();
                        </script>
                    <?php else: ?>
                        <video id="heroVideo" class="w-full h-full object-cover rounded-t-3xl" controlsList="nodownload">
                            <source src="<?php echo htmlspecialchars($heroVideo); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 via-gray-900/30 to-transparent flex flex-col justify-end p-6 md:p-8">
                            <h2 class="text-3xl md:text-4xl font-bold text-white mb-2 glow-text"><?php echo htmlspecialchars($heroTitle); ?></h2>
                            <p class="text-gray-300 text-lg md:text-xl max-w-3xl hero-description"><?php echo htmlspecialchars($heroDescription); ?></p>
                        </div>
                        
                        <div class="absolute bottom-4 left-4 flex space-x-3 z-10">
                            <button class="heroPlayPauseBtn video-control w-12 h-12 flex items-center justify-center bg-gray-900/80 rounded-full hover:bg-blue-600/90">
                                <i class="heroPlayPauseIcon fas fa-play text-white text-xl"></i>
                            </button>
                            <button class="heroFullscreenBtn video-control w-12 h-12 flex items-center justify-center bg-gray-900/80 rounded-full hover:bg-blue-600/90">
                                <i class="fas fa-expand text-white text-xl"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Live Stream Section -->
        <section class="mb-16">
            <h2 class="text-4xl font-bold text-center mb-8 text-white section-title">
                <span class="text-blue-500">Glorious Visions</span> Toons TV 📡
            </h2>
            <div class="max-w-6xl mx-auto">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl transform transition-all duration-500 hover:shadow-teal-500/30 card-hover">
                    <div class="aspect-w-16 aspect-h-9 relative">
                        <?php if ($liveCountdown): ?>
                            <div class="countdown-container bg-gray-900/90">
                                <h2 class="countdown-title"><?php echo htmlspecialchars($liveCountdown['title']); ?></h2>
                                <p class="countdown-label">Premieres in</p>
                                <div class="countdown-timer" id="liveCountdownTimer"></div>
                            </div>
                            <script>
                                function startLiveCountdown() {
                                    const startTime = new Date('<?php echo $liveCountdown['start_time']; ?>').getTime();
                                    const countdownElement = document.getElementById('liveCountdownTimer');
                                    
                                    function updateCountdown() {
                                        const now = new Date().getTime();
                                        const distance = startTime - now;
                                        
                                        if (distance <= 0) {
                                            clearInterval(interval);
                                            window.location.reload(); // Reload to show the video
                                            return;
                                        }
                                        
                                        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                                        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                        
                                        countdownElement.textContent = 
                                            (days > 0 ? `${days}d ` : '') +
                                            (hours > 0 || days > 0 ? `${hours}h ` : '') +
                                            `${minutes}m ${seconds}s`;
                                    }
                                    
                                    updateCountdown();
                                    const interval = setInterval(updateCountdown, 1000);
                                }
                                
                                startLiveCountdown();
                            </script>
                        <?php else: ?>
                            <video id="liveVideo" class="w-full h-full object-cover" controlsList="nodownload">
                                <source src="<?php echo htmlspecialchars($liveVideo); ?>" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                            
                            <div class="absolute top-4 left-4">
                                <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-bold flex items-center animate-pulse">
                                    <span class="w-2 h-2 bg-white rounded-full mr-2"></span>
                                    LIVE NOW
                                </span>
                            </div>
                            
                            <div class="absolute bottom-4 left-4 flex space-x-3 z-10">
                                <button class="livePlayPauseBtn video-control w-12 h-12 flex items-center justify-center bg-gray-900/80 rounded-full hover:bg-teal-600/90">
                                    <i class="livePlayPauseIcon fas fa-play text-white text-xl"></i>
                                </button>
                                <button class="liveFullscreenBtn video-control w-12 h-12 flex items-center justify-center bg-gray-900/80 rounded-full hover:bg-teal-600/90">
                                    <i class="fas fa-expand text-white text-xl"></i>
                                </button>
                            </div>
                            
                            <?php if (!empty($liveTitle)): ?>
                            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-gray-900/90 via-gray-900/50 to-transparent p-4 md:p-6">
                                <h3 class="text-white text-xl md:text-2xl font-bold glow-text"><?php echo htmlspecialchars($liveTitle); ?></h3>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Grid -->
        <section class="mb-16">
            <h2 class="text-3xl font-bold text-center mb-8 text-white section-title">Explore Our Content</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-gray-900/60 backdrop-blur-sm rounded-2xl p-6 text-center border border-gray-800 hover:border-blue-500/50 transition-all duration-300 card-hover">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center bg-gradient-to-br from-blue-600 to-cyan-500 rounded-2xl text-white text-2xl category-icon">
                        <i class="fas fa-music"></i>
                    </div>
                    <h3 class="text-white font-bold text-xl mb-2">Music</h3>
                    <p class="text-gray-400 text-sm">Inspirational teen music</p>
                </div>
                
                <div class="bg-gray-900/60 backdrop-blur-sm rounded-2xl p-6 text-center border border-gray-800 hover:border-indigo-500/50 transition-all duration-300 card-hover">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center bg-gradient-to-br from-indigo-600 to-blue-500 rounded-2xl text-white text-2xl category-icon">
                        <i class="fas fa-gamepad"></i>
                    </div>
                    <h3 class="text-white font-bold text-xl mb-2">TOTATEENS Games</h3>
                    <p class="text-gray-400 text-sm">Coming Soon...</p>
                </div>
                
                <div class="bg-gray-900/60 backdrop-blur-sm rounded-2xl p-6 text-center border border-gray-800 hover:border-emerald-500/50 transition-all duration-300 card-hover">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center bg-gradient-to-br from-emerald-600 to-teal-500 rounded-2xl text-white text-2xl category-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="text-white font-bold text-xl mb-2">Education</h3>
                    <p class="text-gray-400 text-sm">Learning for teens</p>
                </div>
                
                <div class="bg-gray-900/60 backdrop-blur-sm rounded-2xl p-6 text-center border border-gray-800 hover:border-rose-500/50 transition-all duration-300 card-hover">
                    <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center bg-gradient-to-br from-rose-600 to-pink-500 rounded-2xl text-white text-2xl category-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3 class="text-white font-bold text-xl mb-2">Lifestyle</h3>
                    <p class="text-gray-400 text-sm">Teen lifestyle content</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="relative z-10 bg-gray-900/80 backdrop-blur-md border-t border-gray-800 mt-16">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h4 class="text-xl font-bold text-white mb-4">Join Our Community</h4>
                    <div class="flex space-x-4">
                        <a href="https://gloriousvisionstvplus.com/view/herrnhut.php" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-800 hover:bg-blue-600 transition-all duration-300 text-white text-lg">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.instagram.com/glorylife_today/" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-800 hover:bg-gradient-to-br hover:from-fuchsia-600 hover:to-rose-600 transition-all duration-300 text-white text-lg">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-800 hover:bg-black transition-all duration-300 text-white text-lg">
                            <i class="fab fa-tiktok"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-xl font-bold text-white mb-4">Stay Updated</h4>
                    <form class="flex flex-col sm:flex-row gap-2">
                        <input type="email" placeholder="Your email" 
                               class="flex-1 px-4 py-3 rounded-full bg-gray-800 text-white border border-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/50 outline-none transition-all duration-300">
                        <button class="px-6 py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white rounded-full font-medium hover:from-blue-700 hover:to-cyan-700 transition-all duration-300 shadow-lg hover:shadow-blue-500/30">
                            Subscribe
                        </button>
                    </form>
                </div>
                
                <div>
                    <h4 class="text-xl font-bold text-white mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="../index.php" class="text-gray-400 hover:text-white transition-all duration-300 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2 text-blue-500"></i> Home
                        </a></li>
                        <li><a href="https://www.instagram.com/glorylife_today/" class="text-gray-400 hover:text-white transition-all duration-300 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2 text-blue-500"></i> About Us
                        </a></li>
                        <li><a href="https://www.instagram.com/glorylife_today/" class="text-gray-400 hover:text-white transition-all duration-300 flex items-center">
                            <i class="fas fa-chevron-right text-xs mr-2 text-blue-500"></i> Contact
                        </a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-500 text-sm">
                <p>&copy; <?php echo date('Y'); ?> Glorious Visions Teens & Kids TV. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Enhanced video controls with better error handling
        function setupVideoControls(videoId, playPauseBtnClass, playPauseIconClass, fullscreenBtnClass) {
            const video = document.getElementById(videoId);
            const playPauseBtn = document.querySelector('.' + playPauseBtnClass);
            const playPauseIcon = document.querySelector('.' + playPauseIconClass);
            const fullscreenBtn = document.querySelector('.' + fullscreenBtnClass);

            if (!video || !playPauseBtn || !playPauseIcon || !fullscreenBtn) {
                console.error(`One or more elements not found for video ${videoId}`);
                return;
            }

            // Play/Pause functionality
            playPauseBtn.addEventListener('click', () => {
                if (video.paused) {
                    document.querySelectorAll('video').forEach(v => {
                        if (v.id !== videoId && !v.paused) {
                            v.pause();
                            const otherIcon = document.querySelector('.' + v.id.replace('Video', 'PlayPauseIcon'));
                            if (otherIcon) {
                                otherIcon.classList.remove('fa-pause');
                                otherIcon.classList.add('fa-play');
                            }
                        }
                    });

                    video.play().then(() => {
                        playPauseIcon.classList.remove('fa-play');
                        playPauseIcon.classList.add('fa-pause');
                    }).catch(error => {
                        console.error('Error playing video:', error);
                        alert('Error playing video. Please try again later.');
                    });
                } else {
                    video.pause();
                    playPauseIcon.classList.remove('fa-pause');
                    playPauseIcon.classList.add('fa-play');
                }
            });

            // Fullscreen functionality
            fullscreenBtn.addEventListener('click', () => {
                if (video.requestFullscreen) {
                    video.requestFullscreen().catch(err => {
                        console.error('Error attempting to enable fullscreen:', err);
                    });
                } else if (video.webkitRequestFullscreen) {
                    video.webkitRequestFullscreen();
                } else if (video.msRequestFullscreen) {
                    video.msRequestFullscreen();
                }
            });

            // Update play/pause icon when video ends
            video.addEventListener('ended', () => {
                playPauseIcon.classList.remove('fa-pause');
                playPauseIcon.classList.add('fa-play');
            });

            // Error handling for video loading
            video.addEventListener('error', (e) => {
                console.error(`Error loading video ${videoId}:`, e);
                const errorElement = document.createElement('div');
                errorElement.className = 'absolute inset-0 flex items-center justify-center bg-red-900/70 text-white p-4';
                errorElement.innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-exclamation-triangle text-3xl mb-2"></i>
                        <p>Error loading video content.</p>
                        <p class="text-sm">Please try again later.</p>
                    </div>
                `;
                video.parentNode.insertBefore(errorElement, video.nextSibling);
            });
        }

        // Check if video source exists
        function checkVideoSource(videoId) {
            const video = document.getElementById(videoId);
            if (video) {
                const source = video.querySelector('source');
                if (source) {
                    fetch(source.src, { method: 'HEAD' })
                        .then(response => {
                            if (!response.ok) {
                                console.warn(`Video source for ${videoId} not found:`, source.src);
                            }
                        })
                        .catch(error => {
                            console.error(`Error checking video source for ${videoId}:`, error);
                        });
                }
            }
        }

        // Initialize all video controls when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            setupVideoControls('heroVideo', 'heroPlayPauseBtn', 'heroPlayPauseIcon', 'heroFullscreenBtn');
            checkVideoSource('heroVideo');
            
            setupVideoControls('liveVideo', 'livePlayPauseBtn', 'livePlayPauseIcon', 'liveFullscreenBtn');
            checkVideoSource('liveVideo');
            
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
            
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fadeInUp');
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('section, .card-hover').forEach(section => {
                observer.observe(section);
                section.classList.add('opacity-0', 'transition-all', 'duration-500', 'ease-out');
            });
            
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fadeInUp {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .animate-fadeInUp {
                    animation: fadeInUp 0.6s forwards;
                    opacity: 1 !important;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html>