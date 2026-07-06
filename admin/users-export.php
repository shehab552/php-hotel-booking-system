<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من صلاحيات المدير
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// نوع التصدير
$type = $_GET['type'] ?? 'excel';
$format = $_GET['format'] ?? 'csv';

// جلب بيانات المستخدمين
$sql = "SELECT 
    u.id,
    u.username,
    u.email,
    u.full_name,
    u.user_type,
    u.phone,
    u.address,
    u.birth_date,
    u.is_active,
    u.created_at,
    u.last_login,
    COUNT(b.id) as booking_count,
    COALESCE(SUM(b.total_price), 0) as total_spent
    FROM users u
    LEFT JOIN bookings b ON u.id = b.user_id
    GROUP BY u.id
    ORDER BY u.created_at DESC";

$result = $conn->query($sql);

// إذا كان نوع التصدير CSV أو Excel
if ($format == 'csv' || $format == 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=users_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // رأس الملف
    fputcsv($output, [
        'ID', 'اسم المستخدم', 'البريد الإلكتروني', 'الاسم الكامل', 'نوع المستخدم',
        'رقم الهاتف', 'العنوان', 'تاريخ الميلاد', 'الحالة', 'تاريخ التسجيل',
        'آخر دخول', 'عدد الحجوزات', 'إجمالي المصروفات'
    ]);
    
    // البيانات
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['username'],
            $row['email'],
            $row['full_name'],
            $row['user_type'] == 'admin' ? 'مدير' : ($row['user_type'] == 'manager' ? 'مدير فندق' : 'عميل'),
            $row['phone'] ?? '',
            $row['address'] ?? '',
            $row['birth_date'] ?? '',
            $row['is_active'] ? 'مفعل' : 'غير مفعل',
            $row['created_at'],
            $row['last_login'] ?? 'لم يدخل',
            $row['booking_count'],
            $row['total_spent']
        ]);
    }
    
    fclose($output);
    exit;
}

// إذا كان نوع التصدير PDF (يتطلب مكتبة TCPDF)
if ($format == 'pdf') {
    // هنا يمكن إضافة كود لإنشاء ملف PDF
    // يتطلب تثبيت مكتبة TCPDF
    echo "تنسيق PDF غير متاح حالياً. يرجى استخدام CSV أو Excel.";
    exit;
}
?>