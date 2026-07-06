<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من أن الطلب AJAX
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('Access denied');
}

header('Content-Type: application/json');

if (!isset($_GET['hotel_id']) || !isset($_GET['room_type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$hotel_id = intval($_GET['hotel_id']);
$room_type = $conn->real_escape_string($_GET['room_type']);

$sql = "SELECT * FROM rooms 
        WHERE hotel_id = ? 
        AND room_type = ? 
        AND available_rooms > 0
        ORDER BY price_per_night ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $hotel_id, $room_type);
$stmt->execute();
$result = $stmt->get_result();

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

echo json_encode([
    'success' => true,
    'rooms' => $rooms
]);
?>