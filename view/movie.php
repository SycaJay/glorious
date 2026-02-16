<?php
require_once('../db/tvconfig.php');

// Function to get the currently showing movie
function getNowShowingMovie($conn) {
    $stmt = $conn->prepare("SELECT * FROM movies WHERE status = 'now_showing' ORDER BY movie_id DESC LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return [
        'title' => 'RAPTURE',
        'description' => 'For the Lord himself shall descend from heaven with a shout, with the voice of the archangel, and with the trump of God: and the dead in Christ shall rise first:Then we which are alive and remain shall be caught up together with them in the clouds, to meet the Lord in the air: and so shall we ever be with the Lord.',
        'video_path' => '../uploads/movies/RAPTURE-MOVIE.mp4',
        'subtitle_path' => '../uploads/movies/RAPTURE-MOVIE.srt'
    ];
}

// Function to get upcoming movies
function getUpcomingMovies($conn) {
    $stmt = $conn->prepare("SELECT * FROM movies WHERE status = 'upcoming' ORDER BY movie_id DESC LIMIT 3");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $movies = [];
    while ($row = $result->fetch_assoc()) {
        $movies[] = $row;
    }
    
    $defaultMovies = [
        ['title' => 'The Forge', 'image_path' => '../assets/forge.jpg'],
        ['title' => 'Left Behind', 'image_path' => '../assets/left.jpg']
    ];
    
    // Modified logic: new movie goes to third position, shifts others left
    if (count($movies) > 0) {
        // Take only the latest movie
        $newMovie = array_shift($movies);
        // Base array starts with Left Behind (second default movie)
        $upcomingList = [$defaultMovies[1]];
        // Add second position - either from DB or first default
        $upcomingList[] = count($movies) > 0 ? array_shift($movies) : $defaultMovies[0];
        // Add the new movie as third
        $upcomingList[] = $newMovie;
        return $upcomingList;
    }
    
    return [
        $defaultMovies[1],  // Left Behind
        $defaultMovies[0],  // The Forge
        ['title' => 'Coming Soon', 'image_path' => '../assets/placeholder.jpg']
    ];
}

// Path handling functions
function getVideoPath($dbPath) {
    if (strpos($dbPath, '../uploads/movies/') === 0) return $dbPath;
    if (strpos($dbPath, 'movies/') === 0) return '../uploads/' . $dbPath;
    if (strpos($dbPath, 'uploads/movies/') === 0) return '../' . $dbPath;
    return '../uploads/movies/' . basename($dbPath);
}

function getImagePath($dbPath) {
    if (strpos($dbPath, '../assets/') === 0) return $dbPath;
    if (strpos($dbPath, 'movies/') === 0) return '../uploads/' . $dbPath;
    if (strpos($dbPath, 'uploads/movies/') === 0) return '../' . $dbPath;
    return '../uploads/movies/' . basename($dbPath);
}

function getSubtitlePath($movieData) {
    $videoPath = getVideoPath($movieData['video_path']);
    $basePath = preg_replace('/\.(mp4|webm|mov)$/i', '', $videoPath);
    $subtitlePath = $basePath . '.srt';
    
    // Debug path
    $absolutePath = $_SERVER['DOCUMENT_ROOT'] . str_replace('..', '', $subtitlePath);
    if (file_exists($absolutePath)) {
        error_log("Subtitle found at: " . $subtitlePath);
        return $subtitlePath;
    } else {
        error_log("Subtitle NOT found at: " . $absolutePath);
    }
    
    // Fallback to explicit subtitle_path if set
    if (isset($movieData['subtitle_path']) && !empty($movieData['subtitle_path'])) {
        $path = getVideoPath($movieData['subtitle_path']);
        $absolutePath = $_SERVER['DOCUMENT_ROOT'] . str_replace('..', '', $path);
        if (file_exists($absolutePath)) {
            error_log("Fallback subtitle found at: " . $path);
            return $path;
        } else {
            error_log("Fallback subtitle NOT found at: " . $absolutePath);
        }
    }
    
    error_log("No subtitles available for: " . $videoPath);
    return '';
}

// Get movies and subtitle path
$nowShowingMovie = getNowShowingMovie($conn);
$upcomingMovies = getUpcomingMovies($conn);
$subtitlePath = getSubtitlePath($nowShowingMovie);
$hasSubtitles = !empty($subtitlePath);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glorious Visions Movie Channel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700&family=Montserrat:wght@300;400;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/assets/Movie.jpg" type="image/x-icon">
    <style>
        :root {
            --primary: #e50914;
            --secondary: #221f1f;
            --accent: #f5f5f1;
            --text: #ffffff;
            --dark: #000000;
        }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: var(--dark);
            color: var(--text);
            overflow-x: hidden;
        }
        
        .hero-title {
            font-family: 'Cinzel', serif;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .section-title {
            font-family: 'Playfair Display', serif;
            position: relative;
            display: inline-block;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60%;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }
        
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }
        
        .video-background video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.4);
        }
        
        .video-background:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.8) 100%);
        }
        
        .navbar {
            backdrop-filter: blur(10px);
            background: rgba(0, 0, 0, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .featured-video-container {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            width: 100%;
            max-width: 100%;
        }
        
        .featured-video-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 35px 60px -12px rgba(0, 0, 0, 0.6);
        }
        
        .featured-video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            background: #000;
        }
        
        /* Responsive: ensure video and controls scale on all devices */
        @media (max-width: 768px) {
            .featured-video-container {
                border-radius: 8px;
                aspect-ratio: 16/9;
                min-height: 200px;
            }
            .featured-video-container:hover {
                transform: none;
            }
        }
        
        @media (max-width: 480px) {
            .featured-video-container {
                min-height: 180px;
            }
        }
        
        .movie-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }
        
        .movie-card:hover {
            transform: scale(1.05) translateY(-10px);
            z-index: 10;
        }
        
        .movie-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 50%);
            z-index: 1;
        }
        
        .movie-card img {
            transition: transform 0.5s ease;
        }
        
        .movie-card:hover img {
            transform: scale(1.1);
        }
        
        .movie-info {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            padding: 20px;
            z-index: 2;
        }
        
        .countdown-timer {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .countdown-number {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 10rem;
            color: var(--primary);
            font-weight: bold;
            text-shadow: 0 0 20px rgba(229, 9, 20, 0.8);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease-in-out, transform 0.5s ease-in-out;
        }
        
        .countdown-number.show {
            opacity: 1;
            animation: popIn 0.5s ease-in-out;
        }
        
        @keyframes popIn {
            0% { transform: translate(-50%, -50%) scale(0); opacity: 0; }
            50% { transform: translate(-50%, -50%) scale(1.2); opacity: 1; }
            100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        }
        
        .subtitle-control {
            position: absolute;
            right: 20px;
            bottom: 20px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            padding: 8px 15px;
            cursor: pointer;
            z-index: 20;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        
        .subtitle-control:hover {
            background: var(--primary);
            transform: scale(1.05);
        }
        
        .subtitle-control.active {
            background: var(--primary);
        }
        
        .play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 80px;
            height: 80px;
            background: rgba(229, 9, 20, 0.8);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
            border: none;
        }
        
        .play-button:hover {
            background: var(--primary);
            transform: translate(-50%, -50%) scale(1.1);
        }
        
        .play-button i {
            color: white;
            font-size: 30px;
            margin-left: 5px;
        }
        
        .video-slider {
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding: 20px 0;
        }
        
        .video-slider::-webkit-scrollbar {
            display: none;
        }
        
        .scroll-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: rgba(0, 0, 0, 0.7);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 10;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .scroll-arrow:hover {
            background: var(--primary);
            transform: translateY(-50%) scale(1.1);
        }
        
        .scroll-arrow.left {
            left: -20px;
        }
        
        .scroll-arrow.right {
            right: -20px;
        }
        
        .footer {
            background: linear-gradient(to right, rgba(0,0,0,0.9), rgba(34,31,31,0.9), rgba(0,0,0,0.9));
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
        }
        
        .footer-content {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        
        .social-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: center;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background: var(--primary);
            transform: translateY(-5px);
        }
        
        /* Mobile specific styles */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.2rem !important;
                letter-spacing: 1px;
            }
            
            .section-title {
                font-size: 1.5rem !important;
            }
            
            .section-title:after {
                width: 40%;
                bottom: -5px;
            }
            
            .countdown-timer {
                font-size: 1rem !important;
            }
            
            .countdown-number {
                font-size: 6rem;
            }
            
            .play-button {
                width: 60px;
                height: 60px;
            }
            
            .movie-card {
                min-width: 180px;
            }
            
            .scroll-arrow {
                width: 30px;
                height: 30px;
            }
            
            .scroll-arrow.left {
                left: -15px;
            }
            
            .scroll-arrow.right {
                right: -15px;
            }
            
            .navbar {
                padding-top: 1rem;
                padding-bottom: 1rem;
            }
            
            .logo-text {
                font-size: 1rem;
            }
            
            .back-button {
                padding: 0.5rem;
                width: 40px;
                height: 40px;
                justify-content: center;
            }
            
            .back-button span {
                display: none;
            }
            
            .back-button i {
                margin-left: 0;
            }
            
            main {
                padding-top: 6rem;
            }
            
            .mobile-home-button {
                display: flex;
                margin: 1rem 0;
                background: rgba(0, 0, 0, 0.7);
                color: white;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                justify-content: center;
                align-items: center;
                font-size: 1.2rem;
                animation: bounce 2s infinite;
            }

            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
                40% {transform: translateY(-15px);}
                60% {transform: translateY(-7px);}
            }
        }

        /* Desktop specific styles */
        @media (min-width: 769px) {
            .mobile-home-button {
                display: none;
            }
            
            .section-title-container {
                display: flex;
                align-items: flex-end;
                gap: 1rem;
                margin-bottom: 1rem;
            }
            
            .section-title {
                margin-bottom: 0;
            }
            
            .movie-description {
                font-style: italic;
                margin-top: 0.5rem;
                max-width: 80%;
            }
            
            .movie-info-container {
                display: flex;
                align-items: flex-start;
                gap: 2rem;
                margin-top: 1.5rem;
            }
            
            .movie-text {
                flex: 1;
            }
        }
        
        /* Particle background effect */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            pointer-events: none;
        }
        
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            pointer-events: none;
        }
        
        /* Glow effect for featured video */
        .glow-effect {
            position: relative;
        }
        
        .glow-effect:after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 12px;
            box-shadow: 0 0 30px 10px rgba(229, 9, 20, 0.4);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .glow-effect:hover:after {
            opacity: 1;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Video Background -->
    <div class="video-background">
        <video autoplay muted loop playsinline>
            <source src="/assets/Sky1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
    
    <!-- Particle Background -->
    <div class="particles" id="particles"></div>
    
    <!-- Navigation -->
    <nav class="navbar fixed top-0 left-0 w-full z-50 py-4 px-6">
        <div class="container mx-auto flex items-center justify-between">
            <a href="../index.php" class="back-button flex items-center justify-center bg-black bg-opacity-70 rounded-full text-white hover:text-white transition">
                <i class="fas fa-home"></i>
                <span class="md:ml-2 md:block hidden">Back to Home</span>
            </a>
            
            <div class="logo-container flex items-center space-x-4">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-white rounded-full overflow-hidden shadow-lg">
                    <img src="/assets/Movie.jpg" alt="Logo" class="w-full h-full object-cover">
                </div>
                <div class="logo-text">
                    <span class="text-lg md:text-xl font-bold text-white hero-title">GLORIOUS VISIONS</span>
                    <span class="hidden md:block text-xs text-gray-300 uppercase tracking-wider">Movie Channel</span>
                </div>
            </div>
            
            <button class="search-button flex items-center justify-center w-10 h-10 rounded-full bg-black bg-opacity-70 text-white hover:text-red-500 transition">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="relative z-10 pt-24 pb-16 px-4 sm:px-6 lg:px-8">
        <div class="container mx-auto">
            <!-- Now Showing Section -->
            <section class="mb-20" data-aos="fade-up">
                <h2 class="section-title text-3xl font-bold text-white mb-8">Now Showing</h2>
                
                <div class="featured-video-container glow-effect relative aspect-video w-full max-w-6xl mx-auto">
                    <video id="featured-video" class="featured-video w-full h-full" poster="<?php echo isset($nowShowingMovie['image_path']) ? htmlspecialchars(getImagePath($nowShowingMovie['image_path'])) : ''; ?>" controls playsinline>
                        <source src="<?php echo htmlspecialchars(getVideoPath($nowShowingMovie['video_path'])); ?>" type="video/mp4">
                        <?php if ($hasSubtitles): ?>
                        <track id="subtitle-track" 
                               kind="subtitles" 
                               src="<?php echo htmlspecialchars($subtitlePath); ?>" 
                               srclang="en" 
                               label="English" 
                               default>
                        <?php endif; ?>
                        Your browser does not support the video tag.
                    </video>
                    
                    <?php if ($hasSubtitles): ?>
                    <button id="subtitle-toggle" class="subtitle-control flex items-center space-x-2" title="Toggle Subtitles">
                        <i class="fas fa-closed-captioning"></i>
                        <span>Subtitles</span>
                    </button>
                    <?php endif; ?>
                    
                    <button id="play-button" class="play-button hidden">
                        <i class="fas fa-play"></i>
                    </button>
                    
                    <?php if (isset($nowShowingMovie['scheduled_time'])): ?>
                    <div id="countdown-overlay" class="absolute inset-0 flex flex-col items-center justify-center bg-black bg-opacity-80 z-10">
                        <h3 class="text-4xl font-bold text-white mb-4 hero-title"><?php echo htmlspecialchars($nowShowingMovie['title']); ?></h3>
                        <p class="text-xl text-gray-300 mb-6">Premieres in:</p>
                        <div class="countdown-timer text-2xl" id="countdown-nowshowing"></div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Mobile Home Button -->
                <a href="../index.php" class="mobile-home-button md:hidden">
                    <i class="fas fa-home"></i>
                </a>
                
                <div class="movie-info-container">
                    <div class="movie-text">
                        <h3 class="text-2xl font-bold text-white mb-4 hero-title"><?php echo htmlspecialchars($nowShowingMovie['title']); ?></h3>
                        <p class="movie-description text-gray-300 leading-relaxed"><?php echo htmlspecialchars($nowShowingMovie['description']); ?></p>
                    </div>
                </div>
            </section>
            
            <!-- Upcoming Movies Section -->
            <section class="mb-20" data-aos="fade-up" data-aos-delay="100">
                <h2 class="section-title text-3xl font-bold text-white mb-8">Upcoming</h2>
                
                <div class="relative">
                    <div class="video-slider flex overflow-x-auto space-x-6 pb-6 -mx-4 px-4" id="upcoming-slider">
                        <?php foreach ($upcomingMovies as $index => $movie): ?>
                        <div class="movie-card flex-none w-64 sm:w-72 md:w-80" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="relative aspect-[2/3] overflow-hidden">
                                <img src="<?php echo htmlspecialchars(getImagePath($movie['image_path'])); ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>" class="w-full h-full object-cover">
                                <div class="movie-info">
                                    <h4 class="text-xl font-bold text-white"><?php echo htmlspecialchars($movie['title']); ?></h4>
                                    <button class="mt-2 px-4 py-1 bg-red-600 bg-opacity-80 text-white rounded-full text-sm hover:bg-opacity-100 transition">
                                        Upcoming
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button class="scroll-arrow left hidden md:flex" id="scroll-left">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <button class="scroll-arrow right hidden md:flex" id="scroll-right">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </section>
        </div>
    </main>
    
    <!-- Footer -->
    <footer class="footer relative z-10">
        <div class="footer-content container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="text-center md:text-left">
                    <h4 class="text-xl font-bold text-white mb-4">Contact Us</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-center md:justify-start space-x-3 text-gray-300">
                            <i class="fas fa-phone"></i>
                            <span>+233 20 126 0746</span>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-3 text-gray-300">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:info@gloriousvisionstvplus.com" class="hover:text-white">info@gloriousvisionstvplus.com</a>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-3 text-gray-300">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Accra, Ghana</span>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <h4 class="text-xl font-bold text-white mb-4">Follow Us</h4>
                    <div class="flex justify-center space-x-4">
                        <a href="https://www.instagram.com/glorylife_today/" class="social-icon text-white hover:text-white">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.facebook.com/groups/1454613778324060" class="social-icon text-white hover:text-white">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://www.youtube.com/@gloriousvisionsherrnhuttel4605" class="social-icon text-white hover:text-white">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-800 mt-6 pt-4 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Glorious Visions Movie Channel. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Countdown Overlay -->
    <div id="countdown-number" class="countdown-number"></div>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.4/gsap.min.js"></script>
    <script>
        // Initialize AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Subtitle Toggle
        const subtitleToggle = document.getElementById('subtitle-toggle');
        const subtitleTrack = document.getElementById('subtitle-track');
        const video = document.getElementById('featured-video');
        const playButton = document.getElementById('play-button');
        
        if (subtitleToggle && subtitleTrack) {
            let subtitlesEnabled = true;
            
            video.addEventListener('loadedmetadata', () => {
                if (video.textTracks.length > 0) {
                    video.textTracks[0].mode = 'showing';
                }
            });
            
            subtitleToggle.addEventListener('click', () => {
                if (video.textTracks.length > 0) {
                    subtitlesEnabled = !subtitlesEnabled;
                    video.textTracks[0].mode = subtitlesEnabled ? 'showing' : 'hidden';
                    
                    subtitleToggle.classList.toggle('active', subtitlesEnabled);
                }
            });
        }
        
        // Play Button
        if (playButton) {
            playButton.addEventListener('click', () => {
                video.play();
                playButton.classList.add('hidden');
            });
            
            video.addEventListener('play', () => {
                playButton.classList.add('hidden');
            });
            
            video.addEventListener('pause', () => {
                playButton.classList.remove('hidden');
            });
        }
        
        // Slider Navigation
        const slider = document.getElementById('upcoming-slider');
        const scrollLeft = document.getElementById('scroll-left');
        const scrollRight = document.getElementById('scroll-right');
        
        if (slider && scrollLeft && scrollRight) {
            scrollLeft.addEventListener('click', () => {
                slider.scrollBy({ left: -300, behavior: 'smooth' });
            });
            
            scrollRight.addEventListener('click', () => {
                slider.scrollBy({ left: 300, behavior: 'smooth' });
            });
            
            // Hide/show arrows based on scroll position
            slider.addEventListener('scroll', () => {
                scrollLeft.style.display = slider.scrollLeft > 0 ? 'flex' : 'none';
                scrollRight.style.display = slider.scrollLeft < (slider.scrollWidth - slider.clientWidth) ? 'flex' : 'none';
            });
            
            // Initial check
            slider.dispatchEvent(new Event('scroll'));
        }
        
        // Countdown Timer
        <?php if (isset($nowShowingMovie['scheduled_time'])): ?>
        (function() {
            const scheduledTime = new Date('<?php echo $nowShowingMovie['scheduled_time']; ?>').getTime();
            const countdownElement = document.getElementById('countdown-nowshowing');
            const countdownOverlay = document.getElementById('countdown-overlay');
            const countdownNumber = document.getElementById('countdown-number');
            const videoContainer = document.querySelector('.featured-video-container');
            const video = document.getElementById('featured-video');
            
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = scheduledTime - now;
                
                if (distance <= 0) {
                    // Time's up - show the video
                    countdownOverlay.style.display = 'none';
                    video.play().catch(function() { /* user can click play */ });
                    return;
                }
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                countdownElement.innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                
                // Final 10-second countdown
                if (distance <= 10000 && distance > 0) {
                    startFinalCountdown();
                }
            }
            
            function startFinalCountdown() {
                let count = 10;
                
                const finalCountdown = setInterval(() => {
                    countdownNumber.textContent = count;
                    countdownNumber.classList.add('show');
                    
                    // Add pulse effect
                    gsap.to(countdownNumber, {
                        scale: 1.2,
                        duration: 0.3,
                        yoyo: true,
                        repeat: 1
                    });
                    
                    setTimeout(() => {
                        countdownNumber.classList.remove('show');
                    }, 900);
                    
                    count--;
                    
                    if (count < 0) {
                        clearInterval(finalCountdown);
                        countdownNumber.textContent = '';
                        
                        // Show video with animation
                        gsap.to(countdownOverlay, {
                            opacity: 0,
                            duration: 0.5,
                            onComplete: () => {
                                countdownOverlay.style.display = 'none';
                                video.play().catch(function() { /* user can click play */ });
                                
                                // Animate video appearance
                                gsap.from(video, {
                                    opacity: 0,
                                    scale: 0.9,
                                    duration: 1,
                                    ease: 'power2.out'
                                });
                            }
                        });
                    }
                }, 1000);
            }
            
            // Initial check
            const now = new Date().getTime();
            if (now >= scheduledTime) {
                countdownOverlay.style.display = 'none';
                video.play().catch(function() { /* user can click play */ });
            } else {
                const countdownInterval = setInterval(updateCountdown, 1000);
                updateCountdown();
            }
        })();
        <?php endif; ?>
        
        // Particle Background
        function createParticles() {
            const particlesContainer = document.getElementById('particles');
            const particleCount = window.innerWidth < 768 ? 30 : 50;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random size between 1px and 3px
                const size = Math.random() * 2 + 1;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random position
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;
                
                // Random opacity
                particle.style.opacity = Math.random() * 0.5 + 0.1;
                
                // Animation
                const duration = Math.random() * 20 + 10;
                const delay = Math.random() * 5;
                
                particle.style.animation = `float ${duration}s ease-in-out ${delay}s infinite`;
                
                particlesContainer.appendChild(particle);
            }
        }
        
        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            createParticles();
            
            // Add hover effect to movie cards
            const movieCards = document.querySelectorAll('.movie-card');
            movieCards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    gsap.to(card, {
                        scale: 1.05,
                        y: -10,
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                });
                
                card.addEventListener('mouseleave', () => {
                    gsap.to(card, {
                        scale: 1,
                        y: 0,
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                });
            });
            
            // Animate featured video container on hover
            const featuredContainer = document.querySelector('.featured-video-container');
            if (featuredContainer) {
                featuredContainer.addEventListener('mouseenter', () => {
                    gsap.to(featuredContainer, {
                        y: -5,
                        duration: 0.5,
                        ease: 'power2.out'
                    });
                });
                
                featuredContainer.addEventListener('mouseleave', () => {
                    gsap.to(featuredContainer, {
                        y: 0,
                        duration: 0.5,
                        ease: 'power2.out'
                    });
                });
            }
            
            // Animate navbar on scroll
            const navbar = document.querySelector('.navbar');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('bg-black', 'shadow-lg');
                    navbar.classList.remove('bg-opacity-70');
                } else {
                    navbar.classList.remove('bg-black', 'shadow-lg');
                    navbar.classList.add('bg-opacity-70');
                }
            });
        });
    </script>
</body>
</html>