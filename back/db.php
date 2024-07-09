<?php
function getDB() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "registeruser";

    // Создаем подключение
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Проверяем подключение
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>