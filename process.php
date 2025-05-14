<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session securely
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Enable if using HTTPS
ini_set('session.use_strict_mode', 1);
session_start();

// Check authentication
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: index.html');
    exit;
}

// Function to safely detect mobile devices
function isMobileDevice() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return preg_match('/(android|iphone|ipod|ipad|blackberry|windows phone)/i', $userAgent);
}

// Initialize history handling
$historyFile = __DIR__ . '/history.json';
$history = [];

// Safely handle JSON file operations
try {
    if (file_exists($historyFile)) {
        $content = file_get_contents($historyFile);
        if ($content !== false) {
            $history = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $history = []; // Reset if corrupted
                error_log("JSON decode error: " . json_last_error_msg());
            }
        }
    }
} catch (Exception $e) {
    error_log("File read error: " . $e->getMessage());
    $history = [];
}

// Process delete request
if (isset($_GET['delete']) && !empty(trim($_GET['delete']))) {
    $id = trim($_GET['delete']);
    $history = array_values(array_filter($history, function($entry) use ($id) {
        return isset($entry['id']) && $entry['id'] === $id;
    }));
    
    try {
        if (file_put_contents($historyFile, json_encode($history, JSON_PRETTY_PRINT), LOCK_EX) === false) {
            error_log("Failed to write history file");
        }
    } catch (Exception $e) {
        error_log("File write error: " . $e->getMessage());
    }
    
    redirectToDashboard();
}

// Process edit request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit']) && !empty(trim($_POST['id']))) {
    $id = trim($_POST['id']);
    $total = (float)($_POST['total'] ?? 0);
    $paid = (float)($_POST['paid'] ?? 0);
    $due = $total - $paid;
    
    foreach ($history as &$entry) {
        if (isset($entry['id']) && $entry['id'] === $id) {
            $entry = [
                'id' => $id,
                'mobile' => trim($_POST['mobile'] ?? ''),
                'name' => trim($_POST['name'] ?? ''),
                'products' => trim($_POST['products'] ?? ''),
                'total' => $total,
                'paid' => $paid,
                'due' => $due,
                'date' => trim($_POST['date'] ?? ''),
                'timestamp' => $entry['timestamp'] ?? time() // Preserve original timestamp
            ];
            break;
        }
    }
    
    saveHistory($historyFile, $history);
    redirectToDashboard();
}

// Process new entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $total = (float)($_POST['total'] ?? 0);
    $paid = (float)($_POST['paid'] ?? 0);
    $due = $total - $paid;
    
    $newEntry = [
        'id' => uniqid(),
        'mobile' => trim($_POST['mobile'] ?? ''),
        'name' => trim($_POST['name'] ?? ''),
        'products' => trim($_POST['products'] ?? ''),
        'total' => $total,
        'paid' => $paid,
        'due' => $due,
        'date' => trim($_POST['date'] ?? ''),
        'timestamp' => time()
    ];
    
    array_unshift($history, $newEntry);
    saveHistory($historyFile, $history);
    redirectToDashboard();
}

// Helper function to save history
function saveHistory($file, $data) {
    try {
        $result = file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX);
        if ($result === false) {
            error_log("Failed to write history file");
        }
    } catch (Exception $e) {
        error_log("History save error: " . $e->getMessage());
    }
}

// Helper function to redirect
function redirectToDashboard() {
    $redirect = isMobileDevice() ? 'dashboardph.php' : 'dashboardpc.php';
    header("Location: $redirect");
    exit;
}

// Default redirect if nothing else matched
redirectToDashboard();
?>
