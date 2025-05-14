<?php
// Secure Session Termination Function
function secure_logout() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Clear session data
    $_SESSION = [];
    
    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    session_destroy();
}

// Execute secure logout
secure_logout();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Logout | System</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a0ca3;
            --accent: #f72585;
            --success: #4cc9f0;
            --white: #ffffff;
            --dark: #212529;
            --light-bg: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--white);
            overflow: hidden;
        }
        
        .logout-wrapper {
            position: relative;
            width: 100%;
            max-width: 480px;
            padding: 0 20px;
        }
        
        .logout-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }
        
        .logout-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            position: relative;
        }
        
        .checkmark {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            display: block;
            stroke-width: 3;
            stroke: var(--success);
            stroke-miterlimit: 10;
            box-shadow: inset 0 0 0 rgba(76, 201, 240, 0.4);
            animation: fillCheckmark 0.6s ease-in-out 0.6s forwards, pulse 1.5s ease-in-out 1.5s infinite;
        }
        
        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 3;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        
        .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        
        .logout-progress {
            width: 100%;
            height: 6px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            margin: 32px 0 24px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, var(--accent), var(--primary));
            border-radius: 3px;
            transition: width 3s linear;
        }
        
        .logout-message h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 12px;
            background: linear-gradient(to right, var(--white), rgba(255, 255, 255, 0.8));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .logout-message p {
            font-size: 16px;
            line-height: 1.6;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        
        .goodbye-text {
            font-size: 18px;
            font-weight: 500;
            margin-top: 16px;
            opacity: 0;
            animation: fadeIn 0.6s ease-out 1.2s forwards;
            background: linear-gradient(to right, var(--accent), var(--success));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
        
        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }
        
        @keyframes fillCheckmark {
            to {
                box-shadow: inset 0 0 0 40px rgba(76, 201, 240, 0.1);
            }
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(76, 201, 240, 0.4);
            }
            70% {
                box-shadow: 0 0 0 12px rgba(76, 201, 240, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(76, 201, 240, 0);
            }
        }
        
        /* Floating particles */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <!-- Floating particles background -->
    <div class="particles" id="particles-js"></div>
    
    <div class="logout-wrapper">
        <div class="logout-card">
            <div class="logout-icon">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                    <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            
            <div class="logout-message">
                <h1>Logout Successful</h1>
                <p>You've been securely logged out of your account.</p>
                <p>All session data has been cleared.</p>
            </div>
            
            <div class="logout-progress">
                <div class="progress-bar" id="progressBar"></div>
            </div>
            
            <p class="goodbye-text">See you again soon!</p>
        </div>
    </div>
    
    <script>
        // Animate progress bar
        document.addEventListener('DOMContentLoaded', function() {
            const progressBar = document.getElementById('progressBar');
            progressBar.style.width = '100%';
            
            // Redirect after 3 seconds
            setTimeout(function() {
                window.location.href = "index.html";
            }, 3000);
            
            // Create floating particles
            createParticles();
        });
        
        // Particle animation
        function createParticles() {
            const container = document.getElementById('particles-js');
            const particleCount = window.innerWidth < 768 ? 20 : 40;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random properties
                const size = Math.random() * 6 + 2;
                const posX = Math.random() * window.innerWidth;
                const delay = Math.random() * 5;
                const duration = Math.random() * 10 + 10;
                const opacity = Math.random() * 0.4 + 0.1;
                
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${posX}px`;
                particle.style.bottom = `-${size}px`;
                particle.style.opacity = opacity;
                particle.style.animation = `float ${duration}s linear ${delay}s infinite`;
                
                container.appendChild(particle);
            }
        }
        
        // CSS for particle animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes float {
                0% {
                    transform: translateY(0) rotate(0deg);
                }
                100% {
                    transform: translateY(-100vh) rotate(360deg);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>