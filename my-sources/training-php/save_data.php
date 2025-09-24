<?php
// save_data.php
session_start();
header('Content-Type: application/json');

// 1) Chỉ cho POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// 2) Lấy token từ header
$headers = getallheaders();
$clientToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
if (!is_string($clientToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $clientToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// 3) Đọc JSON body
$body = file_get_contents('php://input');
$data = json_decode($body, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// 4) Validate / sanitize fields (example: title, content)
$title = trim($data['title'] ?? '');
$content = trim($data['content'] ?? '');

// Basic validation
if ($title === '' && $content === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Empty payload']);
    exit;
}

// Sanitize: strip tags or use more advanced escaping when output later
$safeTitle = strip_tags($title);
$safeContent = strip_tags($content);

// 5) Prepare directory and filename (avoid user-controlled path!)
$storeDir = __DIR__ . '/data';  // store inside project/data
if (!is_dir($storeDir)) {
    // create with safe permissions
    mkdir($storeDir, 0750, true);
}

// Use timestamp + random suffix to avoid collisions
$filename = sprintf('entry_%s_%s.json', date('Ymd_His'), bin2hex(random_bytes(6)));
$filepath = $storeDir . DIRECTORY_SEPARATOR . $filename;

// 6) Write atomically using file_put_contents with LOCK_EX
$entry = [
    'title' => $safeTitle,
    'content' => $safeContent,
    'created_at' => date('c'),
    'user_id' => $_SESSION['id'] ?? null,
    'username' => $_SESSION['username'] ?? null,
];

$written = file_put_contents($filepath, json_encode($entry, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT), LOCK_EX);
if ($written === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
    exit;
}

// 7) Return success
echo json_encode(['status' => 'ok', 'file' => basename($filepath)]);
exit;
