<?php
// إعدادات الجلسات
session_start();

// إعدادات الوقت
date_default_timezone_set('Asia/Riyadh');

// ====== المسارات ======
define('BASE_URL', 'http://localhost/hotel-booking-system/');
define('SITE_NAME', 'نظام حجز الفنادق');

// مسارات الملفات
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/hotel-booking-system/uploads/');
define('PROFILE_IMG_PATH', 'uploads/profiles/');
define('HOTEL_IMG_PATH', 'uploads/hotels/');

// ====== اتصال قاعدة البيانات ======
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'hotel_booking');

// محاولة الاتصال بقاعدة البيانات
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// تعيين الترميز
$conn->set_charset("utf8mb4");

// ====== إعدادات التطبيق ======
define('MAX_IMAGES_PER_HOTEL', 5);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// ====== رسائل الخطأ ======
$error_messages = [];
$success_messages = [];

// ====== الدوال الأساسية ======

// دالة التحقق من تسجيل الدخول
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// دالة التحقق من صلاحية المدير
function isAdmin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

// دالة التحقق من صلاحية مدير الفندق
function isManager() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'manager';
}

// دالة التحقق من صلاحية عميل
function isCustomer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'customer';
}

// دالة إعادة التوجيه
function redirect($url) {
    header("Location: " . BASE_URL . $url);
    exit();
}

// دالة تنقية المدخلات
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>