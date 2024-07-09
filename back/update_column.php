<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

require 'db.php';

// Получение данных POST
$data = json_decode(file_get_contents('php://input'), true);

// Проверка наличия обязательных полей
if (!isset($data['id']) || !isset($data['column_name']) || !isset($data['color'])) {
    echo json_encode(["error" => "Missing required data"]);
    exit;
}

$id = $data['id'];
$column_name = $data['column_name'];
$color = json_decode($data['color'], true); // Десериализуем JSON-строку в массив

// Проверка успешности десериализации
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["error" => "Invalid color data"]);
    exit;
}

// Преобразуем массив `color` обратно в строку для хранения в базе данных
$color = json_encode($color);

$conn = getDB();

// Подготовленный запрос для обновления данных
$sql = "UPDATE `column` SET column_name = ?, color = ? WHERE column_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(["error" => "Database error: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssi", $column_name, $color, $id); // "ssi" означает string, string, integer

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Error updating column: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
