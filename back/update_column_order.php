<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$input = json_decode(file_get_contents('php://input'), true);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registeruser";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]));
}

if (is_array($input) && count($input) > 0) {
    foreach ($input as $column) {
        $column_id = intval($column['column_id']);
        $order = intval($column['order']);

        $stmt = $conn->prepare('UPDATE `column` SET `order` = ? WHERE column_id = ?');
        $stmt->bind_param('ii', $order, $column_id);

        if (!$stmt->execute()) {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
            $stmt->close();
            $conn->close();
            exit;
        }

        $stmt->close();
    }

    $conn->close();
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
?>
