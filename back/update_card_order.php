<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registeruser";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->begin_transaction();

try {
    foreach ($input as $card) {
        if (!isset($card['card_id'], $card['column_id'], $card['order'])) {
            throw new Exception('Missing required fields');
        }

        $card_id = $card['card_id'];
        $column_id = $card['column_id'];
        $order = $card['order'];

        $sql = "UPDATE `card` SET `order` = ?, `column_id` = ? WHERE `card_id` = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }

        $stmt->bind_param('iii', $order, $column_id, $card_id);

        if (!$stmt->execute()) {
            throw new Exception('Execute failed: ' . $stmt->error);
        }

        $stmt->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'updatedCards' => $input]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>
