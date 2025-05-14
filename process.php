<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: index.html');
    exit;
}

// Function to detect mobile devices
function isMobileDevice() {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    return preg_match('/(android|iphone|ipod|ipad|blackberry|windows phone)/i', $userAgent);
}

$historyFile = 'history.json';
$history = [];
if (file_exists($historyFile)) {
    $history = json_decode(file_get_contents($historyFile), true);
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $history = array_filter($history, function($entry) use ($id) {
        return $entry['id'] !== $id;
    });
    file_put_contents($historyFile, json_encode(array_values($history)));
    $redirect = isMobileDevice() ? 'dashboardph.php' : 'dashboardpc.php';
    header("Location: $redirect");
    exit;
}

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];
    
    foreach ($history as &$entry) {
        if ($entry['id'] === $id) {
            $entry['mobile'] = $_POST['mobile'] ?? null;
            $entry['name'] = $_POST['name'] ?? null;
            $entry['products'] = $_POST['products'] ?? null;
            $entry['total'] = $_POST['total'] ?? null;
            $entry['paid'] = $_POST['paid'] ?? null;
            $entry['due'] = ($_POST['total'] ?? 0) - ($_POST['paid'] ?? 0);
            $entry['date'] = $_POST['date'] ?? null;
            break;
        }
    }
    
    file_put_contents($historyFile, json_encode($history));
    $redirect = isMobileDevice() ? 'dashboardph.php' : 'dashboardpc.php';
    header("Location: $redirect");
    exit;
}

// Handle new entry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $total = $_POST['total'] ?? 0;
    $paid = $_POST['paid'] ?? 0;
    $due = $total - $paid;
    
    $newEntry = [
        'id' => uniqid(),
        'mobile' => $_POST['mobile'] ?? null,
        'name' => $_POST['name'] ?? null,
        'products' => $_POST['products'] ?? null,
        'total' => $total,
        'paid' => $paid,
        'due' => $due,
        'date' => $_POST['date'] ?? null,
        'timestamp' => time()
    ];
    
    array_unshift($history, $newEntry);
    file_put_contents($historyFile, json_encode($history));
    $redirect = isMobileDevice() ? 'dashboardph.php' : 'dashboardpc.php';
    header("Location: $redirect");
    exit;
}

// Final redirect based on device
$redirect = isMobileDevice() ? 'dashboardph.php' : 'dashboardpc.php';
header("Location: $redirect");
exit;
?>