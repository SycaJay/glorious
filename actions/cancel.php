<?php
// Extract query parameters (optional, for reference)
$clientReference = isset($_GET['clientReference']) ? htmlspecialchars($_GET['clientReference']) : 'Unknown';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Animation -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #6a11cb;
            --secondary: #2575fc;
            --accent: #ff9800;
            --success: #4CAF50;
            --light: #ffffff;
            --dark: #2c3e50;
            --glass: rgba(255, 255, 255, 0.1);
            --shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--light);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, rgba(255,255,255,0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 70%, rgba(255,255,255,0.1) 0%, transparent 50%);
            z-index: -1;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
            background: linear-gradient(to right, #fff, #e0e0e0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            display: inline-block;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }

        .container {
            background: var(--glass);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 2.5rem;
            width: 100%;
            max-width: 600px;
            margin: 1rem auto;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform-style: preserve-3d;
            perspective: 1000px;
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            text-align: center;
        }

        .container:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        p {
            font-size: 1.2rem;
            margin-bottom: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .buttons {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        button {
            position: relative;
            background-color: var(--accent);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            overflow: hidden;
            transition: all 0.4s ease;
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.4);
            z-index: 1;
            min-width: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to right, #ff9800, #ff6b00);
            z-index: -1;
            transition: all 0.4s ease;
            transform: scaleX(0);
            transform-origin: left;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 152, 0, 0.6);
        }

        button:hover::before {
            transform: scaleX(1);
        }

        .home-btn {
            background-color: var(--success);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        .home-btn::before {
            background: linear-gradient(to right, #4CAF50, #2E7D32);
        }

        .animate-pop {
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes popIn {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 1.8rem;
            }
            .buttons {
                flex-direction: column;
            }
            button {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <h1 class="animate__animated animate__fadeInDown">Payment Cancelled</h1>
    <div class="container animate-pop">
        <p>Your payment was cancelled. Would you like to try again?</p>
        <p>Transaction Reference: <?php echo $clientReference; ?></p>
        <div class="buttons">
            <button onclick="window.location.href='index.html'">
                <i class="fas fa-seedling"></i> Try Again
            </button>
            <button class="home-btn" onclick="window.location.href='index.php'">
                <i class="fas fa-home"></i> Return to Homepage
            </button>
        </div>
    </div>
</body>
</html>