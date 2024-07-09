<?php
// Включение вывода ошибок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Обработка preflight-запросов CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:3000");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Access-Control-Max-Age: 86400"); // 1 day
    exit;
}

// Разрешение запросов с localhost:3000
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit;
}

require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
error_log("Received data: " . print_r($data, true));

// Проверка обязательных полей
if (!isset($data['card_id']) || !isset($data['name']) || !isset($data['text']) || !isset($data['color'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$id = $data['card_id'];
$name = $data['name'];
$text = $data['text'];
$color = $data['color'];

$conn = getDB();
error_log("Database connection established.");

$stmt = $conn->prepare("UPDATE card SET name = ?, text = ?, color = ? WHERE card_id = ?");
if (!$stmt) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("sssi", $name, $text, $color, $id);
error_log("Statement prepared and parameters bound.");

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
    error_log("Statement executed successfully.");
} else {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => "Execute failed: " . $stmt->error]);
    error_log("Statement execution failed: " . $stmt->error);
}

$stmt->close();
$conn->close();
error_log("Database connection closed.");
?>
