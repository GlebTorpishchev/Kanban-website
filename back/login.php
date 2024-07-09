<?php
session_start(); // Start session handling

header('Access-Control-Allow-Origin: http://localhost:3000'); // Разрешаем запросы только с конкретного домена
header('Access-Control-Allow-Methods: POST, GET, OPTIONS'); // Разрешаем методы POST, GET и OPTIONS
header('Access-Control-Allow-Headers: Content-Type'); // Разрешаем заголовок Content-Type
header('Access-Control-Allow-Credentials: true'); // Разрешаем передачу учётных данных (куки, авторизация)

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Возвращаем ответ для preflight-запроса
    exit;
}

// Подключение к БД
require_once('db.php');
$conn = getDB();

// Получение данных POST
$data = json_decode(file_get_contents('php://input'), true);
$login = isset($data['login']) ? $data['login'] : '';
$pass = isset($data['password']) ? $data['password'] : '';

$response = [
    'success' => false,
    'user_found' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($login) || empty($pass)) {
        $error = (empty($login)) ? 'login' : 'password';
        $response['empty_data'] = $error;
    } else {
        $sql = "SELECT * FROM `users` WHERE login = ? AND pass = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $login, $pass);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response['login'] = $row['login'];
            $response['user_id'] = (int)$row['user_id'];
            $response['success'] = true;
            $response['user_found'] = true;

            // Сохранение данных в сессию
            $_SESSION['user_id'] = (int)$row['user_id'];
        } else {
            $response['error'] = "Неверные учетные данные";
        }

        $stmt->close();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
        $sql = "SELECT * FROM `users` WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $response['login'] = $row['login'];
            $response['user_id'] = (int)$row['user_id'];
            $response['success'] = true;
            $response['user_found'] = true;
        } else {
            $response['error'] = "Неверные учетные данные";
        }

        $stmt->close();
    } else {
        $response['error'] = "Пользователь не авторизован";
    }
}

echo json_encode($response);

$conn->close();
?>
