<?php
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-type');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Content-Type: application/json');

require 'db.php';

// Получение данных POST
$data = json_decode(file_get_contents('php://input'), true);

// Проверка наличия обязательных полей
if (!isset($data['column_name']) || !isset($data['user_id'])) {
    echo json_encode(["error" => "Missing required data"]);
    exit;
}

$title = $data['column_name'];
$user_id = $data['user_id'];
$color = json_encode($data['color']);
$conn = getDB();

// Подготовленный запрос для вставки данных
$sql = "INSERT INTO `column` (user_id, column_name, color, `order`) VALUES (?, ?, ?, ?)";
$order = 0; // Начальное значение для нового столбца
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(["error" => "Database error: " . $conn->error]);
    exit;
}

$stmt->bind_param("issi", $user_id, $title, $color, $order);

if ($stmt->execute()) {
    echo json_encode(["id" => $stmt->insert_id]);
} else {
    echo json_encode(["error" => "Error creating column: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>