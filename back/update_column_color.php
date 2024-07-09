<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require 'db.php';

$response = ['success' => false];

try {
    $conn = getDB(); // Подключаемся к базе данных

    $data = json_decode(file_get_contents('php://input'), true);

    // Проверка наличия необходимых параметров
    if (!isset($data['column_id']) || !isset($data['column_name']) || !isset($data['user_id'])) {
        throw new Exception("Missing required parameters or invalid data format");
    }

    $column_id = $data['column_id'];
    $column_name = $data['column_name'];
    $user_id = $data['user_id'];
    
    // Если цвет не передан, оставляем текущий цвет без изменений
    $color = isset($data['color']) ? $data['color'] : null;

    // Подготовленный запрос для обновления данных
    $sql = 'UPDATE `column` SET column_name = ?, user_id = ?';
    $params = [$column_name, $user_id];

    // Добавляем цвет в запрос, если он передан
    if (!is_null($color)) {
        $sql .= ', color = ?';
        $params[] = $color;
    }

    $sql .= ' WHERE column_id = ?';
    $params[] = $column_id;

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    // Привязка параметров и выполнение запроса
    $types = str_repeat('s', count($params) - 1) . 'i'; // Определяем типы данных: 's' для строк, 'i' для целого числа
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $response['success'] = true;
    } else {
        $response['error'] = 'Ошибка при выполнении запроса: ' . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>
