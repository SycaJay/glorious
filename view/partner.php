<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner With Us</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        }

        .container:hover {
            transform: translateY(-5px) scale(1.01);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        label {
            display: block;
            margin-top: 1.5rem;
            font-size: 1rem;
            font-weight: 500;
            text-align: left;
            color: rgba(255, 255, 255, 0.9);
            position: relative;
            padding-left: 12px;
        }

        label::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 16px;
            background: var(--accent);
            border-radius: 2px;
        }

        select, input {
            width: 100%;
            padding: 15px 20px;
            margin-top: 0.5rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.9);
            color: var(--dark);
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            appearance: none;
        }

        select:focus, input:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.3);
        }

        select {
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 1em;
        }

        .hidden {
            display: none;
        }

        .buttons {
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
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

        /* Floating particles */
        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            pointer-events: none;
            z-index: -1;
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

            select, input {
                padding: 12px 15px;
            }
        }

        /* Animation classes */
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
    </style>
</head>
<body>
    <h1 class="animate__animated animate__fadeInDown">Partner With Us</h1>
    <div class="container animate-pop">
        <form id="giving-form">
            <label for="purpose">What are you giving seed for?</label>
            <select id="purpose" name="purpose" required>
                <option value="" disabled selected>Select an option</option>
                <option value="TV Ministry">TV Ministry</option>
                <option value="TOTA">TOTA</option>
                <option value="New Jerusalem Event">New Jerusalem Event</option>
                <option value="Other">Other</option>
            </select>

            <div id="other-purpose" class="hidden">
                <label for="other-input">Please specify:</label>
                <input type="text" id="other-input" name="other-input" placeholder="Enter purpose">
            </div>

            <label for="name">Your Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter your name">
            
            <label for="email">Your Email (required):</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email address">

            <label for="phone">Your Phone Number (required):</label>
            <input type="tel" id="phone" name="phone" required placeholder="Enter phone number (e.g., 233249111411)">

            <label for="amount">Amount (GHS):</label>
            <input type="number" id="amount" name="amount" min="1" step="0.01" required placeholder="Enter amount in Cedis">

            <div class="buttons">
                <button type="button" id="pay-now">
                    <i class="fas fa-seedling"></i> Proceed to Payment
                </button>
                <button type="button" class="home-btn" onclick="window.location.href='../index.php'">
                    <i class="fas fa-home"></i> Return to Homepage
                </button>
            </div>
        </form>
    </div>

    <script>
        // Create floating particles
        function createParticles() {
            const colors = ['rgba(255,255,255,0.3)', 'rgba(255,255,255,0.2)', 'rgba(255,255,255,0.4)'];
            
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                const size = Math.random() * 10 + 5;
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                const duration = Math.random() * 20 + 10;
                const delay = Math.random() * 5;
                const color = colors[Math.floor(Math.random() * colors.length)];
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                particle.style.background = color;
                particle.style.opacity = '0';
                
                document.body.appendChild(particle);
                
                const keyframes = [
                    { 
                        transform: `translate(0, 0) rotate(0deg)`,
                        opacity: 0 
                    },
                    { 
                        transform: `translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px) rotate(${Math.random() * 360}deg)`,
                        opacity: 1 
                    },
                    { 
                        transform: `translate(${Math.random() * 200 - 100}px, ${Math.random() * 200 - 100}px) rotate(${Math.random() * 720}deg)`,
                        opacity: 0 
                    }
                ];
                
                const animation = particle.animate(keyframes, {
                    duration: duration * 1000,
                    delay: delay * 1000,
                    iterations: Infinity
                });
            }
        }

        // Celebration effect
        function celebratePayment() {
            const container = document.querySelector('.container');
            const confettiCount = 50;
            
            for (let i = 0; i < confettiCount; i++) {
                const confetti = document.createElement('div');
                confetti.classList.add('particle');
                confetti.style.background = `hsl(${Math.random() * 360}, 100%, 50%)`;
                confetti.style.width = `${Math.random() * 10 + 5}px`;
                confetti.style.height = `${Math.random() * 10 + 5}px`;
                confetti.style.left = `${Math.random() * 100}%`;
                confetti.style.top = `-10px`;
                confetti.style.borderRadius = '50%';
                confetti.style.position = 'fixed';
                confetti.style.zIndex = '1000';
                
                document.body.appendChild(confetti);
                
                const animation = confetti.animate([
                    { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                    { transform: `translateY(${window.innerHeight + 100}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                ], {
                    duration: Math.random() * 3000 + 2000,
                    delay: Math.random() * 500,
                    easing: 'cubic-bezier(0.1, 0.8, 0.4, 1)'
                });
                
                animation.onfinish = () => confetti.remove();
            }
        }

        // Initialize on load
        window.addEventListener('DOMContentLoaded', () => {
            createParticles();
            
            // Add ripple effect to buttons
            document.querySelectorAll('button').forEach(button => {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.background = 'rgba(255,255,255,0.4)';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.pointerEvents = 'none';
                    
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size/2;
                    const y = e.clientY - rect.top - size/2;
                    
                    ripple.style.width = `${size}px`;
                    ripple.style.height = `${size}px`;
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 600);
                });
            });

            // Handle purpose selection
            const purposeSelect = document.getElementById('purpose');
            const otherPurpose = document.getElementById('other-purpose');

            purposeSelect.addEventListener('change', function() {
                if (this.value === 'Other') {
                    otherPurpose.classList.remove('hidden');
                    document.getElementById('other-input').required = true;
                } else {
                    otherPurpose.classList.add('hidden');
                    document.getElementById('other-input').required = false;
                }
            });

            // Payment handling - Modified for Redirect Checkout
            document.getElementById('pay-now').addEventListener('click', async function() {
                const amount = document.getElementById('amount').value;
                const name = document.getElementById('name').value || 'Anonymous';
                const email = document.getElementById('email').value;
                const phone = document.getElementById('phone').value;
                const purpose = document.getElementById('purpose').value;
                const otherInput = document.getElementById('other-input').value;

                // Validate inputs
                if (!email) {
                    alert("Please enter your email address.");
                    return;
                }

                if (!phone || !/^233[0-9]{9}$/.test(phone)) {
                    alert("Please enter a valid phone number in international format (e.g., 233249111411).");
                    return;
                }

                if (!amount || amount <= 0 || !/^\d+(\.\d{1,2})?$/.test(amount)) {
                    alert("Please enter a valid amount with up to 2 decimal places (e.g., 10.50).");
                    return;
                }

                if (!purpose) {
                    alert("Please select a purpose for your payment.");
                    return;
                }

                if (purpose === 'Other' && !otherInput.trim()) {
                    alert("Please specify the purpose for your payment.");
                    return;
                }

                const finalPurpose = purpose === 'Other' ? otherInput : purpose;

                try {
                    const response = await fetch('../actions/partner.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email: email,
                            amount: parseFloat(amount).toFixed(2),
                            purpose: finalPurpose,
                            name: name,
                            phone: phone
                        })
                    });

                    const result = await response.json();

                    if (result.status === 'success' && result.checkoutUrl) {
                        // Redirect to Hubtel checkout page
                        window.location.href = result.checkoutUrl;
                    } else {
                        alert(result.message || 'Payment initiation failed. Please try again.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert(`An error occurred: ${error.message || 'Please try again later.'}`);
                }
            });
        });

        // Add ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>