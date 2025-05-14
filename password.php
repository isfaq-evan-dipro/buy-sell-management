<?php
session_start();

$correct_password = "demonic";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered_password = $_POST['password'];
    
    if ($entered_password === $correct_password) {
        $_SESSION['authenticated'] = true;
        $_SESSION['last_activity'] = time();
        
        // Enhanced device detection
        $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $isMobile = false;
        
        // Check for mobile devices with more comprehensive pattern
        $mobileKeywords = [
            'android', 'webos', 'iphone', 'ipad', 'ipod', 
            'blackberry', 'windows phone', 'mobile', 'tablet',
            'kindle', 'silk', 'opera mini', 'opera mobi'
        ];
        
        foreach ($mobileKeywords as $keyword) {
            if (strpos($userAgent, $keyword) !== false) {
                $isMobile = true;
                break;
            }
        }
        
        // Redirect based on device type
        if ($isMobile) {
            header('Location: dashboardph.php');
        } else {
            header('Location: dashboardpc.php');
        }
        exit;
    } else {
        $error = "ভুল পাসওয়ার্ড! আবার চেষ্টা করুন।";
    }
}

if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
    // Same device detection for authenticated users
    $userAgent = strtolower($_SERVER['HTTP_USER_AGENT']);
    $isMobile = false;
    
    $mobileKeywords = [
        'android', 'webos', 'iphone', 'ipad', 'ipod', 
        'blackberry', 'windows phone', 'mobile', 'tablet',
        'kindle', 'silk', 'opera mini', 'opera mobi'
    ];
    
    foreach ($mobileKeywords as $keyword) {
        if (strpos($userAgent, $keyword) !== false) {
            $isMobile = true;
            break;
        }
    }
    
    if ($isMobile) {
        header('Location: dashboardph.php');
    } else {
        header('Location: dashboardpc.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>পাসওয়ার্ড প্রবেশ করুন</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Bangla';
            src: url('bd.ttf') format('truetype');
        }
        
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --dark-color: #2d3436;
            --light-color: #f5f6fa;
            --success-color: #00b894;
            --error-color: #d63031;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Bangla', 'Segoe UI', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            position: relative;
        }
        
        .floating-bubbles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }
        
        .bubble {
            position: absolute;
            bottom: -100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 15s infinite ease-in;
        }
        
        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(-1000px) rotate(720deg);
                opacity: 0;
            }
        }
        
        .password-container {
            position: relative;
            z-index: 1;
            width: 90%;
            max-width: 400px;
        }
        
        .password-box {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            transform-style: preserve-3d;
            transition: all 0.5s ease;
        }
        
        .password-box:hover {
            transform: translateY(-5px) rotateX(5deg);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .password-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .password-header h2 {
            color: var(--dark-color);
            font-size: 1.8rem;
            margin-bottom: 10px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .password-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .password-icon svg {
            width: 40px;
            height: 40px;
            fill: white;
        }
        
        .password-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 2px solid #ddd;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }
        
        .input-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
            outline: none;
            background-color: white;
        }
        
        .input-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
        }
        
        .input-icon svg {
            width: 20px;
            height: 20px;
        }
        
        .password-submit {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 15px;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }
        
        .password-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(108, 92, 231, 0.6);
        }
        
        .password-submit:active {
            transform: translateY(0);
        }
        
        .password-submit svg {
            width: 15px;
            height: 15px;
            fill: white;
            transition: transform 0.3s ease;
        }
        
        .password-submit:hover svg {
            transform: translateX(5px);
        }
        
        .error-message {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--error-color);
            background-color: rgba(214, 48, 49, 0.1);
            padding: 12px 15px;
            border-radius: 8px;
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        .error-message svg {
            width: 20px;
            height: 20px;
            fill: var(--error-color);
        }
        
        .password-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9rem;
            color: #666;
        }
        
        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            z-index: 10;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
        }
        
        .theme-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(30deg);
        }
        
        /* Dark mode styles */
        body.dark-mode {
            background: linear-gradient(135deg, #2d3436, #1e272e);
        }
        
        body.dark-mode .password-box {
            background: rgba(45, 52, 54, 0.95);
            color: #f5f6fa;
        }
        
        body.dark-mode .password-header h2 {
            color: #f5f6fa;
        }
        
        body.dark-mode .input-group input {
            background-color: #2d3436;
            border-color: #636e72;
            color: #f5f6fa;
        }
        
        body.dark-mode .input-group input:focus {
            background-color: #1e272e;
            border-color: var(--primary-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 480px) {
            .password-box {
                padding: 20px;
            }
            
            .password-header h2 {
                font-size: 1.5rem;
            }
            
            .password-icon {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>
<body>
    <button class="theme-toggle" id="themeToggle">
        <i class="fas fa-moon"></i>
    </button>
    
    <div class="floating-bubbles" id="bubblesContainer"></div>
    
    <div class="password-container animate__animated animate__fadeIn">
        <div class="password-box">
            <div class="password-header">
                <h2>দ্বীপ্র মেডিসিন কর্ণার</h2>
                <div class="password-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M12,3A4,4 0 0,0 8,7V9H6V7A6,6 0 0,1 12,1A6,6 0 0,1 18,7V9H16V7A4,4 0 0,0 12,3M12,9A2,2 0 0,0 10,11A2,2 0 0,0 12,13A2,2 0 0,0 14,11A2,2 0 0,0 12,9M6,15L6,19H18V15H6Z" />
                    </svg>
                </div>
            </div>
            <form method="POST" class="password-form" id="passwordForm">
                <div class="input-group">
                    <input type="password" name="password" id="passwordInput" placeholder="পাসওয়ার্ড দিন" required>
                    <span class="input-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M12,17C10.89,17 10,16.1 10,15C10,13.89 10.89,13 12,13A2,2 0 0,1 14,15A2,2 0 0,1 12,17M18,20V10H6V20H18M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V10C4,8.89 4.89,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z" />
                        </svg>
                    </span>
                </div>
                <?php if (isset($error)): ?>
                    <div class="error-message animate__animated animate__shakeX">
                        <svg viewBox="0 0 24 24">
                            <path d="M13,13H11V7H13M13,17H11V15H13M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2Z" />
                        </svg>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>
                <button type="submit" class="password-submit">
                    <span>প্রবেশ করুন</span>
                    <svg viewBox="0 0 13 10" height="10px" width="15px">
                        <path d="M1,5 L11,5"></path>
                        <polyline points="8 1 12 5 8 9"></polyline>
                    </svg>
                </button>
            </form>
            <div class="password-footer">
                <p>সঠিক পাসওয়ার্ড দিন এবং প্রবেশ করুন</p>
            </div>
        </div>
    </div>

    <script>
        // Create floating bubbles
        function createBubbles() {
            const container = document.getElementById('bubblesContainer');
            const bubbleCount = 15;
            
            for (let i = 0; i < bubbleCount; i++) {
                const bubble = document.createElement('div');
                bubble.classList.add('bubble');
                
                // Random size between 10px and 60px
                const size = Math.random() * 50 + 10;
                bubble.style.width = `${size}px`;
                bubble.style.height = `${size}px`;
                
                // Random position
                bubble.style.left = `${Math.random() * 100}%`;
                
                // Random animation duration between 10s and 20s
                bubble.style.animationDuration = `${Math.random() * 10 + 10}s`;
                
                // Random delay
                bubble.style.animationDelay = `${Math.random() * 5}s`;
                
                container.appendChild(bubble);
            }
        }
        
        // Toggle dark mode
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            const icon = document.getElementById('themeToggle').querySelector('i');
            
            if (document.body.classList.contains('dark-mode')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                localStorage.setItem('darkMode', 'disabled');
            }
        }
        
        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            const icon = document.getElementById('themeToggle').querySelector('i');
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        }
        
        // Add password visibility toggle
        function setupPasswordToggle() {
            const input = document.getElementById('passwordInput');
            const icon = document.querySelector('.input-icon svg');
            
            // Change icon to eye when focused
            input.addEventListener('focus', () => {
                icon.innerHTML = '<path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z" />';
            });
            
            // Change back when blurred
            input.addEventListener('blur', function() {
                if (!this.value) {
                    icon.innerHTML = '<path d="M12,17C10.89,17 10,16.1 10,15C10,13.89 10.89,13 12,13A2,2 0 0,1 14,15A2,2 0 0,1 12,17M18,20V10H6V20H18M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V10C4,8.89 4.89,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z" />';
                }
            });
            
            // Add click to toggle visibility
            const iconContainer = document.querySelector('.input-icon');
            iconContainer.addEventListener('click', function() {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.innerHTML = '<path d="M11.83,9L15,12.16C15,12.11 15,12.05 15,12A3,3 0 0,0 12,9C11.94,9 11.89,9 11.83,9M7.53,9.8L9.08,11.35C9.03,11.56 9,11.77 9,12A3,3 0 0,0 12,15C12.22,15 12.44,14.97 12.65,14.92L14.2,16.47C13.53,16.8 12.79,17 12,17A5,5 0 0,1 7,12C7,11.21 7.2,10.47 7.53,9.8M2,4.27L4.28,6.55L4.73,7C3.08,8.3 1.78,10 1,12C2.73,16.39 7,19.5 12,19.5C13.55,19.5 15.03,19.2 16.38,18.66L16.81,19.08L19.73,22L21,20.73L3.27,3M12,7A5,5 0 0,1 17,12C17,12.64 16.87,13.26 16.64,13.82L19.57,16.75C21.07,15.5 22.27,13.86 23,12C21.27,7.61 17,4.5 12,4.5C10.6,4.5 9.26,4.75 8,5.2L10.17,7.35C10.74,7.13 11.35,7 12,7Z" />';
                } else {
                    input.type = 'password';
                    icon.innerHTML = '<path d="M12,17C10.89,17 10,16.1 10,15C10,13.89 10.89,13 12,13A2,2 0 0,1 14,15A2,2 0 0,1 12,17M18,20V10H6V20H18M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V10C4,8.89 4.89,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z" />';
                }
            });
        }
        
        // Add form submission animation
        function setupFormAnimation() {
            const form = document.getElementById('passwordForm');
            
            form.addEventListener('submit', function(e) {
                if (!e.defaultPrevented) {
                    const button = form.querySelector('button[type="submit"]');
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> প্রক্রিয়াকরণ হচ্ছে...';
                }
            });
        }
        
        // Initialize everything when DOM loads
        document.addEventListener('DOMContentLoaded', function() {
            createBubbles();
            setupPasswordToggle();
            setupFormAnimation();
            
            document.getElementById('themeToggle').addEventListener('click', toggleDarkMode);
        });
    </script>
</body>
</html>