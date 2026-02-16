<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include your database configuration file
require_once 'db/tvconfig.php';

// Fetch the latest video from the database
$conn = getDBConnection(); // Use the function from tvconfig.php to get the connection

if (!$conn) {
    die("Database connection failed. Please try again later.");
}

$videoPath = "/uploads/24hourchannel/Address.mp4"; // Default video path
$videoTitle = "Address on National Outreach Month & Maximum Impact Expo-Campus Leaders and Pastors Connect With Rev Dr Elliot Abraham"; // Default title
$scheduleTime = null;
$endTime = null;
$hasScheduledVideo = false;
$videoId = null;
$isLive = false;
$twitchChannel = "newjerusalemgen"; // Your Twitch channel name

// Get current datetime in MySQL format
$currentTime = date('Y-m-d H:i:s');

// First check if there's a livestream currently active
$liveSql = "SELECT hero_id, title, stream_url FROM hero_video 
            WHERE is_live = 1 
            LIMIT 1";
$result = $conn->query($liveSql);

if ($result && $result->num_rows > 0) {
    // Livestream is active
    $row = $result->fetch_assoc();
    $videoTitle = $row['title'] . " (LIVE)";
    $videoId = $row['hero_id'];
    $isLive = true;
    $twitchChannel = $row['stream_url'];
} else {
    // No livestream, check if there's a currently active video
    $activeVideoSql = "SELECT hero_id, video, title, scheduled_time, end_time FROM hero_video 
                       WHERE scheduled_time <= ? AND end_time >= ? 
                       ORDER BY scheduled_time ASC LIMIT 1";
    $stmt = $conn->prepare($activeVideoSql);
    $stmt->bind_param("ss", $currentTime, $currentTime);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // Active scheduled video found
        $row = $result->fetch_assoc();
        $videoPath = "/uploads/24hourchannel/" . $row['video'];
        $videoTitle = $row['title'];
        $scheduleTime = $row['scheduled_time'];
        $endTime = $row['end_time'];
        $videoId = $row['hero_id'];
        $hasScheduledVideo = true;
    } else {
        // No active video, check for upcoming video
        $upcomingVideoSql = "SELECT hero_id, video, title, scheduled_time, end_time FROM hero_video 
                             WHERE scheduled_time > ? 
                             ORDER BY scheduled_time ASC LIMIT 1";
        $stmt = $conn->prepare($upcomingVideoSql);
        $stmt->bind_param("s", $currentTime);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            // Upcoming scheduled video found
            $row = $result->fetch_assoc();
            $videoPath = "/uploads/24hourchannel/Address.mp4"; // Default video until scheduled time
            $scheduledVideoPath = "/uploads/24hourchannel/" . $row['video']; // Store scheduled video path
            $videoTitle = $row['title'];
            $scheduleTime = $row['scheduled_time'];
            $endTime = $row['end_time'];
            $videoId = $row['hero_id'];
            $hasScheduledVideo = true;
        }
    }
    $stmt->close();
}

$conn->close(); // Close the database connection

// Fetch countries using HTTP request
$countries = [];
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://restcountries.com/v3.1/all?fields=name",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "GET",
]);
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    error_log("cURL Error: " . $err);
} else {
    $data = json_decode($response, true);
    if ($data) {
        foreach ($data as $country) {
            $countries[] = $country['name']['common'];
        }
        sort($countries); // Sort countries alphabetically
    }
}

// Prepare video data for JavaScript
$videoData = [
    'hasScheduled' => $hasScheduledVideo,
    'scheduleTime' => $scheduleTime ? date('c', strtotime($scheduleTime)) : null,
    'endTime' => $endTime ? date('c', strtotime($endTime)) : null,
    'scheduledPath' => isset($scheduledVideoPath) ? $scheduledVideoPath : null,
    'scheduledTitle' => $hasScheduledVideo ? $videoTitle : null,
    'defaultPath' => "/uploads/24hourchannel/Addresss.mp4"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Glorious Visions TV Plus is a premier TV network by the Glory Life New Jerusalem Generation that brings to the mind of God concerning these last days to viewers worldwide. Tune in for a variety of shows that enrich lives and uplift the spirit.">
<meta name="keywords" content="Glorious Visions TV Plus,Glorious Visions TV network, inspirational TV, educational TV, entertainment, global TV, Christian TV, uplifting content, shows, live TV, 24 hour channel,Pastor Elliot Digital Studio, Herrnhut Tv,Teens Tv,New Jerusalem Generation,Glory Life,Glorious Vision Tv Plus">
<meta name="author" content="Glorious Visions TV Plus">
<meta name="robots" content="index, follow">
<title>Glorious Visions TV Plus</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://embed.twitch.tv/embed/v1.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
<link rel="icon" href="../assets/TvLogo.jpg" type="image/x-icon">
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#000000">
<!-- Open Graph meta tags for link preview -->
<meta property="og:title" content="Glorious Visions 24-Hour Channel - <?php echo htmlspecialchars($videoTitle); ?>">
<meta property="og:image" content="<?php echo $isLive ? 'https://www.twitch.tv/' . $twitchChannel . '/thumbnail' : $videoPath . '#t=10'; ?>">
<meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
<meta property="og:type" content="video">

<style>
    body {
    font-family: 'Poppins', sans-serif;
}
.content-box {
    transition: all 0.5s ease;
}
.content-box.active {
    z-index: 10;
}
.door-animation {
    transition: all 0.8s ease;
    transform-origin: top;
}
.door-animation.hidden {
    transform: perspective(1000px) rotateX(-90deg);
}
.video-overlay {
    background: none;
    z-index: 1;
    pointer-events: none;
}
.video-overlay .premiere-container,
.video-overlay .countdown-number {
    pointer-events: auto;
}
#centerPlayBtn {
    z-index: 10;
    pointer-events: auto;
}
#centerPlayBtn button {
    pointer-events: auto;
}
#slider {
    transition: transform 0.5s ease-out;
}
.video-container:hover .custom-controls {
    opacity: 1;
}
.custom-controls {
    opacity: 0;
    transition: opacity 0.3s;
    z-index: 2;
}
video::-webkit-media-controls {
    display: none !important;
}
video::-webkit-media-controls-enclosure {
    display: none !important;
}
.premiere-container {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
    max-width: 90%;
    z-index: 3;
}
.premiere-title {
    font-size: 1.5rem;
    font-weight: bold;
    margin-bottom: 0.75rem;
}
.premiere-countdown {
    font-size: 1rem;
}
.countdown-number {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 4rem;
    color: white;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8);
    animation: pop 1s ease-out forwards;
    display: none;
    z-index: 4;
}
@keyframes pop {
    0% { transform: translate(-50%, -50%) scale(0); opacity: 0; }
    50% { transform: translate(-50%, -50%) scale(1.2); opacity: 1; }
    100% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
}
@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}
.bounce {
    animation: bounce 1s ease infinite;
}
.popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    transition: opacity 0.3s ease;
}
.popup-content {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    max-width: 90%;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    position: relative;
    background: linear-gradient(135deg, #e6f3ff, #ffffff);
    border: 2px solid #3b82f6;
    animation: slideIn 0.5s ease-out;
}
@keyframes slideIn {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.confirmation-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    transition: opacity 0.3s ease;
}
.confirmation-content {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    max-width: 90%;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    background: linear-gradient(135deg, #e6f3ff, #ffffff);
    border: 2px solid #3b82f6;
    animation: slideIn 0.5s ease-out;
}
.thank-you-message {
    display: none;
    text-align: center;
    padding: 1rem;
    background: #e6f3ff;
    border-radius: 0.5rem;
    margin-bottom: 1rem;
    color: #1e40af;
    font-weight: 600;
}
/* Enhanced Click Me Button */
#clickMeBtn {
    background: linear-gradient(45deg, #3b82f6, #60a5fa);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.5);
    border: none;
    border-radius: 50px;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    position: fixed;
    z-index: 1000; /* Increased z-index to ensure visibility */
}
#clickMeBtn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.7);
    background: linear-gradient(45deg, #2563eb, #3b82f6);
}
#clickMeBtn i {
    font-size: 1.25rem;
    color: white;
}
/* Enhanced Channel Slider */
.content-box {
    perspective: 1000px;
}
.content-box .card {
    position: relative;
    transition: transform 0.6s ease, box-shadow 0.3s ease;
    transform-style: preserve-3d;
    background: linear-gradient(135deg, #1e3a8a, #3b82f6);
    border: none;
    border-radius: 1rem;
    overflow: hidden;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
}
.content-box .card:hover {
    transform: translateY(-10px) rotateY(10deg);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
}
.content-box .card img {
    transition: transform 0.5s ease;
}
.content-box .card:hover img {
    transform: scale(1.1);
}
.content-box .card .overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.content-box .card:hover .overlay {
    opacity: 1;
}
.content-box .card .btn {
    background: linear-gradient(45deg, #3b82f6, #60a5fa);
    border-radius: 50px;
    padding: 0.5rem 1.5rem;
    font-weight: 600;
    transition: all 0.3s ease;
    transform: translateY(20px);
    opacity: 0;
}
.content-box .card:hover .btn {
    transform: translateY(0);
    opacity: 1;
}
.content-box .card .btn:hover {
    background: linear-gradient(45deg, #2563eb, #3b82f6);
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.5);
}
/* Navigation Buttons */
#prevBtn, #nextBtn {
    background: linear-gradient(45deg, #3b82f6, #60a5fa);
    box-shadow: 0 4px 10px rgba(59, 130, 246, 0.5);
    transition: all 0.3s ease;
}
#prevBtn:hover, #nextBtn:hover {
    background: linear-gradient(45deg, #2563eb, #3b82f6);
    transform: scale(1.1);
}
/* Responsive Adjustments */
@media (max-width: 640px) {
    .popup-content {
        padding: 0.75rem; /* Reduced padding */
        max-width: 80%; /* Smaller max-width */
        max-height: 80vh; /* Limit height to avoid overflow */
        overflow-y: auto; /* Allow scrolling if content overflows */
    }
    .popup-content h2 {
        font-size: 1.25rem; /* Smaller heading */
    }
    .popup-content p {
        font-size: 0.875rem; /* Smaller paragraph text */
    }
    .popup-content input,
    .popup-content select {
        font-size: 0.75rem; /* Smaller input text */
        padding: 0.5rem; /* Reduced input padding */
    }
    .popup-content button {
        font-size: 0.75rem; /* Smaller button text */
        padding: 0.5rem 1rem; /* Reduced button padding */
    }
    .thank-you-message {
        font-size: 0.75rem; /* Smaller thank-you message */
        padding: 0.5rem; /* Reduced padding */
    }
    .confirmation-content {
        padding: 1rem;
    }
    .premiere-title {
        font-size: 1.25rem;
    }
    .premiere-countdown {
        font-size: 0.875rem;
    }
    .countdown-number {
        font-size: 3rem;
    }
    #clickMeBtn {
        padding: 0.5rem 1rem; /* Reduced padding for mobile */
        font-size: 0.75rem; /* Smaller font size */
        top: 100px; /* Position near the top-right, below the nav */
        right: 1rem;
        bottom: auto; /* Remove bottom positioning */
    }
    #clickMeBtn i {
        font-size: 0.875rem; /* Smaller icon size */
    }
    .content-box .card {
        height: 56vw;
    }
    .content-box .card img {
        height: 70%;
    }
}
@media (min-width: 768px) {
    .premiere-title {
        font-size: 2rem;
    }
    .premiere-countdown {
        font-size: 1.25rem;
    }
    .countdown-number {
        font-size: 5rem;
    }
    .content-box .card {
        height: 400px;
    }
}
@media (max-width: 1024px) {
    .content-box .card {
        height: 350px;
    }
}
</style>
</head>
<body class="min-h-screen bg-gray-900">
    <!-- Background Video -->
    <div class="fixed inset-0 z-0">
        <video autoplay loop muted class="w-full h-full object-cover opacity-60">
            <source src="../assets/Sky1.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>

    <!-- Pop-up Form -->
    <div id="viewerFormPopup" class="popup-overlay hidden">
        <div class="popup-content">
            <div id="thankYouMessage" class="thank-you-message">
                Thank you for signing up! You'll receive exciting updates soon!
            </div>
            <h2 class="text-2xl font-bold mb-4 text-center text-blue-600">Welcome to Glorious Visions TV Plus!</h2>
            <p class="text-center mb-6 text-gray-700">To continue having this glorious experience, please fill the form below to receive updates on our programs and live sessions!</p>
            <form id="viewerForm" action="actions/form.php" method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="mt-1 p-2 w-full border rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required
                    />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Contact</label>
                    <input
                        type="tel"
                        name="contact"
                        class="mt-1 p-2 w-full border rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required
                    />
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Country</label>
                    <select
                        name="country"
                        class="mt-1 p-2 w-full border rounded-md focus:ring-blue-500 focus:border-blue-500"
                        required
                    >
                        <option value="">Select a country</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?php echo htmlspecialchars($country); ?>"><?php echo htmlspecialchars($country); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button
                        type="button"
                        id="closeFormBtn"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                    >
                        Close
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                    >
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Pop-up -->
    <div id="confirmationPopup" class="confirmation-overlay hidden">
        <div class="confirmation-content">
            <h3 class="text-lg font-bold mb-4 text-blue-600">Are you sure?</h3>
            <p class="mb-6 text-gray-700">Would you like to miss out on exciting updates from Glorious Visions TV Plus?</p>
            <div class="flex justify-center space-x-4">
                <button
                    id="reopenFormBtn"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
                >
                    Fill Form
                </button>
                <button
                    id="dismissConfirmationBtn"
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                >
                    No, Thanks
                </button>
            </div>
        </div>
    </div>

    <!-- Click Me Button -->
    <button id="clickMeBtn" class="fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-2 rounded-full shadow-lg hover:bg-blue-700 flex items-center space-x-2 bounce z-50 hidden">
        <i class="fas fa-mouse-pointer"></i>
        <span>Click Me</span>
    </button>

    <!-- Top Navigation -->
    <nav class="fixed w-full z-50 bg-blue-900/90 backdrop-blur-sm shadow-lg door-animation">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center justify-between h-auto md:h-16 py-4 md:py-0">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white rounded-full overflow-hidden">
                        <img src="../assets/TvLogo.jpg" alt="Logo" class="w-full h-full object-cover">
                    </div>
                    <div class="flex flex-col ml-4">
                        <span class="text-lg md:text-xl font-bold text-white uppercase tracking-wider text-center md:text-left">GLORIOUS VISIONS TV PLUS</span>
                        <span class="text-xs text-gray-300 text-center md:text-left">Imparting You with Heaven's Vision</span>
                    </div>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="../view/partner.php">
                        <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition">
                            Partner With Us
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Spacer after nav -->
    <div class="h-40 md:h-32"></div>

    <!-- Channel Title -->
    <div class="w-full max-w-5xl mx-auto px-4 text-center">
        <h2 class="text-lg sm:text-xl md:text-2xl lg:text-3xl font-bold text-white flex items-center justify-center">
            <span class="inline-block mr-2"><?php echo $isLive ? '🔴 LIVE' : ''; ?></span>
            <i class="fa-solid fa-satellite-dish"></i>
            <span>Glorious Visions 24 Hour Channel</span>
        </h2>
    </div>

    <!-- Hero Video Section -->
    <div class="relative w-full max-w-5xl mx-auto px-4 mb-8 mt-2">
        <div class="w-full h-[200px] sm:h-[50vh] md:h-[80vh] bg-blue-600 relative video-container border-4 border-white rounded-lg p-4">
            <div class="relative w-[98%] h-[95%] mx-auto group">
                <?php if ($isLive): ?>
                <!-- Twitch Embed when live -->
                <div id="twitchEmbed" class="w-full h-full rounded-lg">
                    <!-- Twitch embed will be loaded here -->
                </div>
                <?php else: ?>
                <!-- Regular video when not live -->
                <video 
                    id="mainVideo" 
                    class="w-full h-full object-contain rounded-lg cursor-pointer"
                    loop
                    preload="metadata">
                    <source src="<?php echo $videoPath; ?>" type="video/mp4">
                </video>
                
                <!-- Center Play Button -->
                <div id="centerPlayBtn" class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-300" style="z-index: 10;">
                    <button type="button" class="bg-white/20 hover:bg-white/30 rounded-full p-6 backdrop-blur-sm transition-all transform hover:scale-110" aria-label="Play or pause video">
                        <i class="fas fa-play text-white text-4xl center-play-icon"></i>
                    </button>
                </div>
                <?php endif; ?>

                <!-- Title Overlay -->
                <div class="video-overlay absolute inset-0 flex flex-col items-start justify-between pt-4 bg-gradient-to-b from-black/10 to-transparent">
                    <!-- Premiere Container -->
                    <div id="premiereContainer" class="premiere-container hidden">
                        <div id="premiereTitle" class="premiere-title"><?php echo htmlspecialchars($videoTitle); ?></div>
                        <div id="premiereCountdown" class="premiere-countdown">
                            <span id="countdownLabel">Premieres In: </span>
                            <span id="countdownTimer">Loading...</span>
                        </div>
                    </div>
                    
                    <!-- Countdown Numbers -->
                    <div id="countdownNumber" class="countdown-number"></div>
                </div>

                <?php if (!$isLive): ?>
                <!-- Enhanced Video Controls (only show for regular video) -->
                <div class="custom-controls absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black via-black/70 to-transparent pt-16 pb-4 px-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <!-- Progress Bar Container -->
                    <div class="relative w-full group/progress">
                        <!-- Video Preview (hidden by default) -->
                        <div id="videoPreview" class="absolute bottom-8 transform -translate-x-1/2 bg-black rounded-lg p-1 hidden">
                            <span id="previewTime" class="text-white text-xs block text-center mb-1"></span>
                        </div>
                        
                        <!-- Progress Bar -->
                        <div class="w-full h-1 group-hover/progress:h-3 bg-white/30 rounded-full cursor-pointer transition-all duration-200" id="progressContainer">
                            <div class="relative h-full">
                                <div class="absolute inset-0 bg-blue-500 rounded-full transition-all duration-200" id="progressBar">
                                    <div class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-4 bg-blue-500 rounded-full scale-0 group-hover/progress:scale-100 transition-transform duration-200 border-2 border-white"></div>
                                </div>
                                <!-- Buffered Progress -->
                                <div class="absolute inset-0 bg-white/40 rounded-full" id="bufferedBar"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Controls Row -->
                    <div class="flex items-center justify-between mt-4">
                        <div class="flex items-center space-x-6">
                            <!-- Play/Pause Button -->
                            <button id="playPauseBtn" class="text-white hover:text-blue-400 transition group/play">
                                <i class="fas fa-play text-xl md:text-2xl group-hover/play:scale-110 transform transition-transform"></i>
                            </button>
                            
                            <!-- Volume Control -->
                            <div class="flex items-center space-x-2 group/volume">
                                <button id="muteBtn" class="text-white hover:text-blue-400 transition">
                                    <i class="fas fa-volume-up text-xl"></i>
                                </button>
                                <div class="w-0 group-hover/volume:w-24 overflow-hidden transition-all duration-300">
                                    <input type="range" id="volumeSlider" class="w-24 accent-blue-500 cursor-pointer" min="0" max="100" value="100">
                                </div>
                            </div>

                            <!-- Time Display -->
                            <div class="text-white text-sm font-medium">
                                <span id="currentTime">0:00</span>
                                <span class="mx-1">/</span>
                                <span id="duration">0:00</span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-4">
                            <!-- Playback Speed -->
                            <div class="relative group/speed">
                                <button class="text-white hover:text-blue-400 transition px-2 py-1 text-sm">
                                    <i class="fas fa-tachometer-alt mr-1"></i>
                                    <span id="speedDisplay">1x</span>
                                </button>
                                <div class="absolute bottom-full right-0 mb-2 hidden group-hover/speed:block">
                                    <div class="bg-black/90 rounded-lg py-1 w-24">
                                        <button class="w-full px-4 py-1 text-white hover:bg-blue-500 text-sm text-left speed-option" data-speed="0.5">0.5x</button>
                                        <button class="w-full px-4 py-1 text-white hover:bg-blue-500 text-sm text-left speed-option" data-speed="1">1x</button>
                                        <button class="w-full px-4 py-1 text-white hover:bg-blue-500 text-sm text-left speed-option" data-speed="1.5">1.5x</button>
                                        <button class="w-full px-4 py-1 text-white hover:bg-blue-500 text-sm text-left speed-option" data-speed="2">2x</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Fullscreen Button -->
                            <button id="fullscreenBtn" class="text-white hover:text-blue-400 transition">
                                <i class="fas fa-expand text-xl"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Current Video Title below the video box -->
        <div id="bottomTitle" class="mt-2 bg-blue-900/80 text-white text-base sm:text-lg font-semibold text-center py-2 px-4 rounded-lg shadow-lg max-w-[98%] mx-auto z-10 hidden">
            <span id="videoTitle"><?php echo htmlspecialchars($videoTitle); ?></span>
        </div>
    </div>

    <!-- Content Slider Section -->
    <div class="container mx-auto px-4 my-8 overflow-hidden">
        <div class="relative">
            <!-- Left Navigation Button -->
            <button id="prevBtn" class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-blue-900/80 text-white p-3 rounded-r-lg hover:bg-blue-800 transition-colors">
                <i class="fas fa-chevron-left"></i>
            </button>
            
            <!-- Right Navigation Button -->
            <button id="nextBtn" class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-blue-900/80 text-white p-3 rounded-l-lg hover:bg-blue-800 transition-colors">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <div class="flex space-x-4 md:space-x-8" id="slider">
                <!-- Movie Channel -->
                <div class="content-box flex-none w-full md:w-96 animate__animated animate__fadeIn">
                    <div class="card h-64 flex flex-col">
                        <div class="w-full h-48 rounded-lg overflow-hidden relative">
                            <img src="/assets/Movie.jpg" alt="Movie Channel Logo" class="w-full h-full object-contain">
                            <div class="overlay"></div>
                        </div>
                        <div class="mt-auto pt-2 px-4 flex flex-col items-center">
                            <h3 class="text-xl font-bold text-white mb-2">Movie Channel</h3>
                            <a href="../view/movie.php" class="btn inline-block text-white">Watch Now</a>
                        </div>
                    </div>
                </div>

                <!-- Herrnhut Television -->
                <div class="content-box flex-none w-full md:w-96 animate__animated animate__fadeIn">
                    <div class="card h-64 flex flex-col">
                        <div class="w-full h-48 rounded-lg overflow-hidden relative">
                            <img src="../assets/Herrnhut.jpg" alt="Herrnhut TV Logo" class="w-full h-full object-contain">
                            <div class="overlay"></div>
                        </div>
                        <div class="mt-auto pt-2 px-4 flex flex-col items-center">
                            <h3 class="text-xl font-bold text-white mb-2">Herrnhut Television</h3>
                            <a href="../view/herrnhut.php" class="btn inline-block text-white">Watch Now</a>
                        </div>
                    </div>
                </div>

                <!-- Pastor Elliot Digital Studio -->
                <div class="content-box flex-none w-full md:w-96 animate__animated animate__fadeIn">
                    <div class="card h-64 flex flex-col">
                        <div class="w-full h-48 rounded-lg overflow-hidden relative">
                            <img src="../assets/PEDS.jpg" alt="Digital Studio Logo" class="w-full h-full object-contain">
                            <div class="overlay"></div>
                        </div>
                        <div class="mt-auto pt-2 px-4 flex flex-col items-center">
                            <h3 class="text-xl font-bold text-white mb-2">Pastor Elliot Digital Studio</h3>
                            <a href="../view/studio.php" class="btn inline-block text-white">Watch Now</a>
                        </div>
                    </div>
                </div>

                <!-- Teens Television -->
                <div class="content-box flex-none w-full md:w-96 animate__animated animate__fadeIn">
                    <div class="card h-64 flex flex-col">
                        <div class="w-full h-48 rounded-lg overflow-hidden relative">
                            <img src="../assets/TeenTv.jpg" alt="Teens TV Logo" class="w-full h-full object-contain">
                            <div class="overlay"></div>
                        </div>
                        <div class="mt-auto pt-2 px-4 flex flex-col items-center">
                            <h3 class="text-xl font-bold text-white mb-2">Glorious Visions Teens Television</h3>
                            <a href="../view/teens.php" class="btn inline-block text-white">Watch Now</a>
                        </div>
                    </div>
                </div>

                <!-- TOTA Online -->
                <div class="content-box flex-none w-full md:w-96 animate__animated animate__fadeIn">
                    <div class="card h-64 flex flex-col">
                        <div class="w-full h-48 rounded-lg overflow-hidden relative">
                            <img src="../assets/TOTALogo.png" alt="TOTA Online Logo" class="w-full h-full object-contain">
                            <div class="overlay"></div>
                        </div>
                        <div class="mt-auto pt-2 px-4 flex flex-col items-center">
                            <h3 class="text-xl font-bold text-white mb-2">TOTA Television</h3>
                            <a href="https://totaonline.com" class="btn inline-block text-white">Watch Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-900/90 backdrop-blur-sm py-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Contact Section -->
                <div class="text-white text-center md:text-left">
                    <h4 class="text-base font-bold mb-4">Contact Us</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-center md:justify-start space-x-3">
                            <i class="fas fa-phone"></i>
                            <span>+233 20 126 0746</span>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-3">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:contact@gloriousvisionstvplus.com" class="text-white-500 hover:underline">
                                contact@gloriousvisionstvplus.com
                            </a>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-3">
                            <i class="fab fa-whatsapp"></i>
                            <a href="https://wa.me/+233201260746" class="hover:text-blue-400 transition">WhatsApp</a>
                        </div>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="text-white text-center md:text-left">
                    <h4 class="text-base font-bold mb-4">Newsletter</h4>
                    <form class="flex max-w-md mx-auto md:mx-0">
                        <input type="email" placeholder="Enter your email" 
                               class="flex-1 px-4 py-2 rounded-l-lg bg-blue-800/50 text-white placeholder-gray-400 border-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700 transition text-sm">
                            Subscribe
                        </button>
                    </form>
                </div>

                <!-- Social Links -->
                <div class="text-white text-center md:text-left">
                    <h4 class="text-base font-bold mb-4">Follow Us</h4>
                    <div class="flex justify-center md:justify-start space-x-6">
                        <a href="https://www.facebook.com/groups/1454613778324060" class="text-xl hover:text-blue-400 transition">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="https://www.instagram.com/glorylife_today/" class="text-xl hover:text-blue-400 transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="https://www.youtube.com/@gloriousvisionsherrnhuttel4605" class="text-xl hover:text-blue-400 transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Video data from PHP
        const videoData = <?php echo json_encode($videoData); ?>;
        console.log('Video Data:', videoData); // Debug: Check video data

        <?php if ($isLive): ?>
        // Initialize Twitch embed when live
        window.onload = function() {
            const twitchChannel = <?php echo json_encode($twitchChannel); ?>;
            console.log("Twitch Channel:", twitchChannel);
            if (twitchChannel) {
                new Twitch.Embed("twitchEmbed", {
                    width: "100%",
                    height: "100%",
                    channel: twitchChannel,
                    theme: "dark",
                    layout: "video",
                    autoplay: true,
                    muted: false,
                    parent: ["<?php echo $_SERVER['HTTP_HOST']; ?>"]
                });
            } else {
                console.error("No Twitch channel defined!");
            }
            showViewerForm();
        };
        <?php else: ?>
        window.onload = function() {
            showViewerForm();
            checkScheduledContent(); // Ensure countdown is checked on load
        };
        <?php endif; ?>
        
        // Content slider functionality
        const slider = document.getElementById('slider');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const boxes = document.querySelectorAll('.content-box');
        let currentIndex = 0;
        const totalBoxes = boxes.length;

        // Video player elements
        const video = document.getElementById('mainVideo');
        const playPauseBtn = document.getElementById('playPauseBtn');
        const centerPlayBtn = document.getElementById('centerPlayBtn');
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        const muteBtn = document.getElementById('muteBtn');
        const volumeSlider = document.getElementById('volumeSlider');
        const currentTimeDisplay = document.getElementById('currentTime');
        const durationDisplay = document.getElementById('duration');
        const progressBar = document.getElementById('progressBar');
        const bufferedBar = document.getElementById('bufferedBar');
        const progressContainer = document.getElementById('progressContainer');
        const videoPreview = document.getElementById('videoPreview');
        const previewTime = document.getElementById('previewTime');
        const speedOptions = document.querySelectorAll('.speed-option');
        const speedDisplay = document.getElementById('speedDisplay');
        const playIcon = playPauseBtn.querySelector('i');
        const videoTitle = document.getElementById('videoTitle');
        const premiereContainer = document.getElementById('premiereContainer');
        const premiereTitle = document.getElementById('premiereTitle');
        const countdownLabel = document.getElementById('countdownLabel');
        const countdownTimer = document.getElementById('countdownTimer');
        const countdownNumber = document.getElementById('countdownNumber');
        const bottomTitle = document.getElementById('bottomTitle');

        // Form elements
        const viewerFormPopup = document.getElementById('viewerFormPopup');
        const closeFormBtn = document.getElementById('closeFormBtn');
        const clickMeBtn = document.getElementById('clickMeBtn');
        const confirmationPopup = document.getElementById('confirmationPopup');
        const reopenFormBtn = document.getElementById('reopenFormBtn');
        const dismissConfirmationBtn = document.getElementById('dismissConfirmationBtn');
        const viewerForm = document.getElementById('viewerForm');
        const thankYouMessage = document.getElementById('thankYouMessage');

        // State variables
        let isDragging = false;
        let isPlaying = false;
        let hasClosedForm = localStorage.getItem('formClosed') === 'true';

        // Form handling functions
        function showViewerForm() {
            if (!hasClosedForm) {
                viewerFormPopup.classList.remove('hidden');
            } else {
                clickMeBtn.classList.remove('hidden');
            }
        }

        function hideViewerForm() {
            viewerFormPopup.classList.add('hidden');
            clickMeBtn.classList.remove('hidden');
        }

        function showConfirmation() {
            confirmationPopup.classList.remove('hidden');
        }

        function hideConfirmation() {
            confirmationPopup.classList.add('hidden');
        }

        closeFormBtn.addEventListener('click', () => {
            if (!hasClosedForm) {
                showConfirmation();
                localStorage.setItem('formClosed', 'true');
                hasClosedForm = true;
            } else {
                hideViewerForm();
            }
        });

        reopenFormBtn.addEventListener('click', () => {
            hideConfirmation();
            viewerFormPopup.classList.remove('hidden');
        });

        dismissConfirmationBtn.addEventListener('click', () => {
            hideConfirmation();
            hideViewerForm();
        });

        clickMeBtn.addEventListener('click', () => {
            viewerFormPopup.classList.remove('hidden');
            clickMeBtn.classList.add('hidden');
        });

        viewerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = viewerForm.querySelector('input[name="email"]').value;
            const contact = viewerForm.querySelector('input[name="contact"]').value;
            const country = viewerForm.querySelector('select[name="country"]').value;

            // Client-side validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const contactRegex = /^[\+0-9\s\-]{7,15}$/;
            if (!emailRegex.test(email)) {
                alert('Please enter a valid email address.');
                return;
            }
            if (!contactRegex.test(contact)) {
                alert('Please enter a valid contact number (7-15 digits, may include +, spaces, or hyphens).');
                return;
            }
            if (!country) {
                alert('Please select a country.');
                return;
            }

            const formData = new FormData(viewerForm);
            try {
                const response = await fetch('actions/form.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                console.log('Form Response:', result); // Debug: Log server response
                if (result.success) {
                    thankYouMessage.textContent = 'Thank you for signing up! You\'ll receive exciting updates soon!';
                    thankYouMessage.style.background = '#e6f3ff';
                    thankYouMessage.style.color = '#1e40af';
                    thankYouMessage.style.display = 'block';
                    viewerForm.reset();
                    setTimeout(() => {
                        hideViewerForm();
                        thankYouMessage.style.display = 'none';
                    }, 2000);
                } else {
                    alert(result.message);
                }
            } catch (err) {
                console.error('Form Submission Error:', err); // Debug: Log network errors
                alert('Error submitting form. Please try again.');
            }
        });

        // Content slider functions
        function updateSlider() {
            const boxWidth = window.innerWidth >= 768 ? 384 : window.innerWidth - 32;
            const margin = window.innerWidth >= 768 ? 32 : 16;
            const offset = -currentIndex * (boxWidth + margin);
            
            slider.style.transition = 'transform 0.5s ease-out';
            slider.style.transform = `translateX(${offset}px)`;
        }

        function moveNext() {
            if (currentIndex < totalBoxes - 1) {
                currentIndex++;
                updateSlider();
            } else {
                currentIndex = 0;
                updateSlider();
            }
        }

        function movePrev() {
            if (currentIndex > 0) {
                currentIndex--;
                updateSlider();
            } else {
                currentIndex = totalBoxes - 1;
                updateSlider();
            }
        }

        nextBtn.addEventListener('click', moveNext);
        nextBtn.addEventListener('touchstart', function(e) {
            e.preventDefault();
            moveNext();
        });

        prevBtn.addEventListener('click', movePrev);
        prevBtn.addEventListener('touchstart', function(e) {
            e.preventDefault();
            movePrev();
        });

        window.addEventListener('resize', updateSlider);
        updateSlider();

        // Video player functions
        function formatTime(seconds) {
            if (isNaN(seconds)) return "0:00";
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = Math.floor(seconds % 60);
            if (hours > 0) {
                return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            }
            return `${minutes}:${secs.toString().padStart(2, '0')}`;
        }

        function updateDisplays() {
            currentTimeDisplay.textContent = formatTime(video.currentTime);
            durationDisplay.textContent = formatTime(video.duration);
            
            if (!isDragging) {
                const progress = (video.currentTime / video.duration) * 100;
                progressBar.style.width = `${progress}%`;
            }
            
            if (video.buffered.length > 0) {
                const bufferedEnd = video.buffered.end(video.buffered.length - 1);
                const bufferedProgress = (bufferedEnd / video.duration) * 100;
                bufferedBar.style.width = `${bufferedProgress}%`;
            }
        }

        function togglePlay() {
            if (video.paused) {
                video.play()
                    .then(() => {
                        playIcon.classList.remove('fa-play');
                        playIcon.classList.add('fa-pause');
                        isPlaying = true;
                        updateCenterPlayButton();
                        if (!videoData.hasScheduled || new Date(videoData.scheduleTime) <= new Date()) {
                            premiereContainer.classList.add('hidden');
                            bottomTitle.classList.remove('hidden');
                        }
                    })
                    .catch(() => { /* blocked by browser until user interacts */ });
            } else {
                video.pause();
                playIcon.classList.remove('fa-pause');
                playIcon.classList.add('fa-play');
                isPlaying = false;
                updateCenterPlayButton();
            }
        }

        function updateCenterPlayButton() {
            centerPlayBtn.style.opacity = isPlaying ? '0' : '1';
            const icon = centerPlayBtn.querySelector('.center-play-icon, .fa-play, .fa-pause');
            if (icon) icon.className = 'fas fa-play text-white text-4xl center-play-icon';
        }

        function seek(e) {
            const rect = progressContainer.getBoundingClientRect();
            const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            video.currentTime = pos * video.duration;
        }

        function updatePreview(e) {
            const rect = progressContainer.getBoundingClientRect();
            const pos = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            const previewTimeValue = pos * video.duration;
            
            videoPreview.style.display = 'block';
            videoPreview.style.left = `${e.clientX}px`;
            previewTime.textContent = formatTime(previewTimeValue);
        }

        function updateVideoSource(newSource, newTitle) {
            video.pause();
            video.src = newSource;
            video.load();
            videoTitle.textContent = newTitle;
            premiereTitle.textContent = newTitle;
            togglePlay();
        }

        function updateCountdown() {
            if (!videoData.hasScheduled || !videoData.scheduleTime) {
                console.log('No scheduled video or schedule time'); // Debug
                return;
            }
            
            const now = new Date();
            const scheduleTime = new Date(videoData.scheduleTime);
            
            console.log('Now:', now, 'Schedule Time:', scheduleTime); // Debug
            
            if (scheduleTime > now) {
                premiereContainer.classList.remove('hidden');
                bottomTitle.classList.add('hidden');
                
                const timeDiff = scheduleTime - now;
                
                if (timeDiff <= 0) {
                    if (videoData.scheduledPath) {
                        updateVideoSource(videoData.scheduledPath, videoData.scheduledTitle);
                        premiereContainer.classList.add('hidden');
                        bottomTitle.classList.remove('hidden');
                    } else {
                        location.reload();
                    }
                    return;
                }
                
                const days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
                const hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
                
                let countdownText = '';
                if (days > 0) countdownText += `${days}d `;
                countdownText += `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                countdownTimer.textContent = countdownText;
                
                if (timeDiff <= 10000) {
                    const remainingSeconds = Math.ceil(timeDiff / 1000);
                    countdownNumber.textContent = remainingSeconds;
                    countdownNumber.style.display = 'block';
                    setTimeout(() => {
                        countdownNumber.style.display = 'none';
                    }, 1000);
                } else {
                    countdownNumber.style.display = 'none';
                }
            } else {
                if (videoData.scheduledPath && video.src.indexOf(videoData.scheduledPath) === -1) {
                    updateVideoSource(videoData.scheduledPath, videoData.scheduledTitle);
                }
                premiereContainer.classList.add('hidden');
                bottomTitle.classList.remove('hidden');
                countdownNumber.style.display = 'none';
            }
        }

        function checkScheduledContent() {
            console.log('Checking scheduled content:', videoData); // Debug
            if (videoData.hasScheduled && videoData.scheduleTime && new Date(videoData.scheduleTime) > new Date()) {
                video.pause();
                centerPlayBtn.style.opacity = '1';
                premiereContainer.classList.remove('hidden');
                bottomTitle.classList.add('hidden');
                
                const disablePlay = function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert('This content will be available at the scheduled time.');
                };
                
                video.addEventListener('play', disablePlay, true);
                playPauseBtn.addEventListener('click', disablePlay, true);
                centerPlayBtn.addEventListener('click', disablePlay, true);
                
                const scheduleCheck = setInterval(function() {
                    if (new Date(videoData.scheduleTime) <= new Date()) {
                        clearInterval(scheduleCheck);
                        video.removeEventListener('play', disablePlay, true);
                        playPauseBtn.removeEventListener('click', disablePlay, true);
                        centerPlayBtn.removeEventListener('click', disablePlay, true);
                        if (videoData.scheduledPath) {
                            updateVideoSource(videoData.scheduledPath, videoData.scheduledTitle);
                        } else {
                            location.reload();
                        }
                    }
                }, 1000);
            } else if (videoData.endTime && new Date(videoData.endTime) <= new Date()) {
                location.reload();
            }
        }

        // Set up video event listeners
        if (video) {
            video.addEventListener('click', togglePlay);
            playPauseBtn.addEventListener('click', togglePlay);
            centerPlayBtn.addEventListener('click', (e) => {
                e.preventDefault();
                togglePlay();
            });

            document.addEventListener('keydown', (e) => {
                if (e.code === 'Space' && video) {
                    const active = document.activeElement?.tagName;
                    if (active !== 'INPUT' && active !== 'TEXTAREA' && active !== 'SELECT') {
                        e.preventDefault();
                        togglePlay();
                    }
                }
            });

            video.addEventListener('play', () => {
                isPlaying = true;
                updateCenterPlayButton();
            });

            video.addEventListener('pause', () => {
                isPlaying = false;
                updateCenterPlayButton();
            });

            progressContainer.addEventListener('mousedown', (e) => {
                isDragging = true;
                seek(e);
            });

            progressContainer.addEventListener('mousemove', (e) => {
                if (isDragging) {
                    seek(e);
                }
                updatePreview(e);
            });

            document.addEventListener('mouseup', () => {
                isDragging = false;
            });

            progressContainer.addEventListener('mouseleave', () => {
                videoPreview.style.display = 'none';
            });

            muteBtn.addEventListener('click', () => {
                video.muted = !video.muted;
                muteBtn.querySelector('i').className = video.muted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
                volumeSlider.value = video.muted ? 0 : video.volume * 100;
            });

            volumeSlider.addEventListener('input', (e) => {
                video.volume = e.target.value / 100;
                video.muted = false;
                muteBtn.querySelector('i').className = video.volume === 0 ? 'fas fa-volume-mute' : 'fas fa-volume-up';
            });

            speedOptions.forEach(option => {
                option.addEventListener('click', () => {
                    const speed = parseFloat(option.dataset.speed);
                    video.playbackRate = speed;
                    speedDisplay.textContent = `${speed}x`;
                });
            });

            fullscreenBtn.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    const videoContainer = video.closest('.video-container');
                    if (videoContainer) {
                        videoContainer.requestFullscreen()
                            .catch(err => {
                            console.error(`Error attempting to enable fullscreen: ${err.message}`);
                        });
                    }
                } else {
                    document.exitFullscreen();
                }
            });

            document.addEventListener('fullscreenchange', () => {
                if (document.fullscreenElement) {
                    fullscreenBtn.querySelector('i').classList.remove('fa-expand');
                    fullscreenBtn.querySelector('i').classList.add('fa-compress');
                } else {
                    fullscreenBtn.querySelector('i').classList.remove('fa-compress');
                    fullscreenBtn.querySelector('i').classList.add('fa-expand');
                }
            });

            video.addEventListener('timeupdate', updateDisplays);
            video.addEventListener('loadedmetadata', () => {
                updateDisplays();
                checkScheduledContent();
            });
            video.addEventListener('progress', updateDisplays);

            video.addEventListener('error', (e) => {
                console.error('Video error:', e);
                videoTitle.textContent = 'Error loading video. Please try again later.';
            });

            video.addEventListener('ended', () => {
                if (!videoData.hasScheduled) {
                    video.currentTime = 0;
                    video.play();
                } else {
                    location.reload();
                }
            });
        }

        // Initialize countdown if scheduled
        if (videoData.hasScheduled) {
            updateCountdown();
            setInterval(updateCountdown, 1000);
        }

        // Auto-start video after a short delay (if not scheduled for future)
        if (!videoData.scheduleTime || new Date(videoData.scheduleTime) <= new Date()) {
            setTimeout(() => {
                if (video && video.paused) {
                    togglePlay();
                }
            }, 1500);
        }

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(() => console.log("Service Worker Registered"))
                .catch((err) => console.error("Service Worker Registration Failed", err));
        }
    </script>
</body>
</html>