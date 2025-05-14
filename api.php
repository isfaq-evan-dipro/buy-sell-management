<?php
header('Content-Type: application/json');

// API Configuration
$api_config = [
    'api_key' => 'put your api key',
    'type' => 'text',
    'senderid' => 'put your sneder id',
    'contact_number' => 'put your number'
];

// Function to send SMS
function sendSms($number, $message) {
    global $api_config;
    
    $url = "https://bulksmsbd.net/api/smsapi";
    $data = [
        'api_key' => $api_config['api_key'],
        'type' => $api_config['type'],
        'number' => $number,
        'senderid' => $api_config['senderid'],
        'message' => $message
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return $response;
}

// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'send_sms') {
        $number = $_POST['number'];
        $message = $_POST['message'];
        
        $result = sendSms($number, $message);
        echo json_encode(['status' => 'success', 'response' => json_decode($result, true)]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid request']);