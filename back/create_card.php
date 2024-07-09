<?php
// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:3000");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit;
}

// Allow requests from localhost:3000
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit;
}

// Decode JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input parameters
if (!isset($input['name']) || !isset($input['text']) || !isset($input['color']) || !isset($input['column_id'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$name = $input['name'];
$text = $input['text'];
$color = $input['color'];
$column_id = $input['column_id'];

// Create database connection
$conn = new mysqli('localhost', 'root', '', 'registeruser');
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка подключения к базе данных: ' . $conn->connect_error]);
    exit;
}

// Подготовка SQL запроса
$stmt = $conn->prepare('INSERT INTO card (name, text, color, column_id, `order`) VALUES (?, ?, ?, ?, ?)');
$order = 0;
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка подготовки запроса: ' . $conn->error]);
    exit;
}

// Привязка параметров и выполнение запроса
$stmt->bind_param('sssii', $name, $text, $color, $column_id, $order);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Ошибка выполнения запроса: ' . $stmt->error]);
    exit;
}

// Получение ID вставленной записи
$id = $stmt->insert_id;

// Закрытие запроса и соединения с базой данных
$stmt->close();
$conn->close();

// Возвращение успешного ответа
echo json_encode(['success' => true, 'id' => $id]);
?>
