<?php
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-type');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Content-Type: application/json');

require 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$column_id = $data['id'];

$conn = getDB();
if ($conn) {
    $sql = "DELETE FROM `column` WHERE column_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $column_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => $stmt->error]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["error" => "Failed to connect to the database"]);
}
?>
