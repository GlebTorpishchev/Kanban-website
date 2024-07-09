<?php
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Content-Type: application/json');

require 'db.php';

$conn = getDB();
if ($conn->connect_error) {
    die(json_encode(['error' => 'Ошибка подключения к базе данных: ' . $conn->connect_error]));
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($user_id <= 0) {
    die(json_encode(['error' => 'Неверный идентификатор пользователя']));
}

$sql = "SELECT * FROM `column` WHERE `user_id` = $user_id";
$result = $conn->query($sql);

$columns = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $column_id = $row['column_id'];
        $sql_cards = "SELECT * FROM `card` WHERE `column_id` = $column_id";
        $result_cards = $conn->query($sql_cards);

        $cards = [];
        if ($result_cards->num_rows > 0) {
            while ($card_row = $result_cards->fetch_assoc()) {
                $cards[] = $card_row;
            }
        }

        $row['color'] = json_decode($row['color']);
        $row['cards'] = $cards;
        $columns[] = $row;
    }
}

echo json_encode($columns);
$conn->close();
