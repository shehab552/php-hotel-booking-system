<?php
require_once '../config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['hotel_id']) || !isset($data['check_in']) || !isset($data['check_out'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$hotel_id = intval($data['hotel_id']);
$check_in = $conn->real_escape_string($data['check_in']);
$check_out = $conn->real_escape_string($data['check_out']);

// التحقق من توفر الغرف في هذه الفترة
$sql = "SELECT r.*, 
               (r.available_rooms - COALESCE(SUM(CASE 
                    WHEN b.check_in <= ? AND b.check_out >= ? AND b.status IN ('confirmed', 'pending') 
                    THEN 1 ELSE 0 END), 0)) as actual_available
        FROM rooms r
        LEFT JOIN bookings b ON r.id = b.room_id
        WHERE r.hotel_id = ?
        GROUP BY r.id
        HAVING actual_available > 0";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $check_out, $check_in, $hotel_id);
$stmt->execute();
$result = $stmt->get_result();

$rooms = [];
while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

echo json_encode([
    'success' => true,
    'available_rooms' => $rooms,
    'count' => count($rooms)
]);
?>