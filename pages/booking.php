<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$page_title = 'إتمام الحجز';
$user_id = $_SESSION['user_id'];

// التحقق من وجود hotel_id
if (!isset($_GET['hotel_id']) || empty($_GET['hotel_id'])) {
    redirect('hotels.php');
}

$hotel_id = (int) $_GET['hotel_id'];

// المتغيرات القادمة من الرابط
$room_id  = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;
$check_in = isset($_GET['check_in']) ? cleanInput($_GET['check_in']) : date('Y-m-d', strtotime('+1 day'));
$nights   = isset($_GET['nights']) ? (int) $_GET['nights'] : 1;
$guests   = isset($_GET['guests']) ? (int) $_GET['guests'] : 1;

// حساب تاريخ المغادرة
$check_out = date('Y-m-d', strtotime($check_in . " + $nights days"));

// جلب بيانات الفندق
$hotel = getHotelById($hotel_id);

// التحقق من صحة الفندق
if (!$hotel || (int)$hotel['is_active'] !== 1) {
    redirect('hotels.php');
}

// جلب الغرف الخاصة بالفندق
$rooms = getHotelRooms($hotel_id);

// إذا لم يتم تحديد غرفة نأخذ أول غرفة متاحة
if ($room_id === 0 && !empty($rooms)) {
    $room_id = (int) $rooms[0]['id'];
}

// جلب بيانات الغرفة
$room = getRoomById($room_id);

// ❌ حماية كاملة من null
if (!$room || (int)$room['hotel_id'] !== $hotel_id) {
    redirect('hotels.php');
}

// التأكد من وجود السعر
if (!isset($room['price_per_night'])) {
    die('خطأ في بيانات سعر الغرفة');
}

// حساب السعر
$total_price = (float) $room['price_per_night'] * $nights;

// معالجة تأكيد الحجز
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {

    $special_requests = cleanInput($_POST['special_requests'] ?? '');
    $payment_method   = cleanInput($_POST['payment_method'] ?? 'pay_at_hotel');

    $sql = "INSERT INTO bookings
        (user_id, room_id, hotel_id, check_in, check_out, guests, total_nights, total_price, status, special_requests, payment_method)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "iiissiidss",
        $user_id,
        $room_id,
        $hotel_id,
        $check_in,
        $check_out,
        $guests,
        $nights,
        $total_price,
        $special_requests,
        $payment_method
    );

  if ($stmt->execute()) {

    $booking_id = $stmt->insert_id;

    // تقليل عدد الغرف المتاحة (بشكل آمن)
    $update_sql = "UPDATE rooms 
                   SET available_rooms = available_rooms - 1 
                   WHERE id = ? AND available_rooms > 0";

    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("i", $room_id);
    $update_stmt->execute();

    // لو ما تم التحديث → ما في غرف متاحة
    if ($update_stmt->affected_rows === 0) {
        // الأفضل حذف الحجز الذي أُضيف
        $conn->query("DELETE FROM bookings WHERE id = $booking_id");
        die('❌ لا توجد غرف متاحة للحجز');
    }

    redirect("pages/booking-confirmation.php?id=$booking_id");
}

}

include '../includes/header.php';
?>

<div class="container mt-4">
    <h2 class="text-center mb-4 text-primary">
        <i class="fas fa-calendar-check"></i> تأكيد الحجز
    </h2>

    <!-- بيانات الفندق -->
    <div class="card border-primary mb-3">
        <div class="card-header bg-primary text-white">
            <strong><?= htmlspecialchars($hotel['name']) ?></strong>
        </div>
        <div class="card-body">
            <p>
                <strong>العنوان:</strong>
                <?= htmlspecialchars($hotel['address']) ?> -
                <?= htmlspecialchars($hotel['city']) ?>،
                <?= htmlspecialchars($hotel['country']) ?>
            </p>
        </div>
    </div>

    <!-- بيانات الغرفة -->
    <div class="card border-success mb-3">
        <div class="card-header bg-success text-white">
            <strong><?= htmlspecialchars($room['room_type']) ?></strong>
        </div>
        <div class="card-body">
            <p><strong>السعر لليلة:</strong>
                <?= number_format($room['price_per_night'], 2) ?> ريال
            </p>
            <p><strong>عدد الليالي:</strong> <?= $nights ?></p>
            <p><strong>عدد الضيوف:</strong> <?= $guests ?></p>
            <p><strong>الإجمالي:</strong>
                <?= number_format($total_price, 2) ?> ريال
            </p>
        </div>
    </div>

    <!-- نموذج التأكيد -->
    <form method="POST">
        <div class="card border-info mb-3">
            <div class="card-header bg-info text-white">ملاحظات إضافية</div>
            <div class="card-body">
                <textarea name="special_requests" class="form-control" rows="4"
                    placeholder="طلبات خاصة (اختياري)"></textarea>
            </div>
        </div>

        <button type="submit" name="confirm_booking" class="btn btn-lg btn-success w-100">
            <i class="fas fa-check-circle"></i> تأكيد الحجز الآن
        </button>
    </form>

    <div class="text-center mt-3">
        <a href="hotels.php" class="btn btn-secondary">العودة للقائمة</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
