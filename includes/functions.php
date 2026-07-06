<?php
// === أصلح السطر الأول ===
require_once __DIR__ . '/../config.php'; // غير database.php إلى config.php

// دالة تحميل ملف التصميم
function loadTemplate($template) {
    include __DIR__ . '/../includes/' . $template . '.php';
}

// دالة تحميل الصفحة
function loadPage($page, $title = '') {
    global $page_title;
    $page_title = $title;
    include __DIR__ . '/../pages/' . $page . '.php';
}

// دالة عرض الرسائل
function displayMessages() {
    global $error_messages, $success_messages;
    
    if (!empty($error_messages)) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        foreach ($error_messages as $error) {
            echo '<p class="mb-0">' . $error . '</p>';
        }
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
    
    if (!empty($success_messages)) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        foreach ($success_messages as $success) {
            echo '<p class="mb-0">' . $success . '</p>';
        }
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// دالة رفع الصور
function uploadImage($file, $type = 'profile') {
    // === أضف هذا الجزء في البداية ===
    // التحقق مما إذا تم رفع ملف
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'لم يتم اختيار صورة'];
    }
    
    // التحقق من أخطاء الرفع
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        if (isset($file['error'])) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $message = 'حجم الصورة أكبر من المسموح به';
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $message = 'حجم الصورة كبير جداً';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = 'تم رفع جزء من الصورة فقط';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = 'لم يتم اختيار أي صورة';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = 'مجلد التخزين المؤقت غير موجود';
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = 'لا يمكن حفظ الملف على الخادم';
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = 'نوع الملف غير مسموح';
                    break;
                default:
                    $message = 'حدث خطأ غير معروف أثناء الرفع';
            }
        } else {
            $message = 'حدث خطأ في عملية الرفع';
        }
        return ['success' => false, 'message' => $message];
    }
    // === نهاية الجزء المضاف ===
    
    // === تأكد أن هذه الثوابت معرفة في config.php ===
    if (!defined('PROFILE_IMG_PATH') || !defined('UPLOAD_PATH')) {
        return ['success' => false, 'message' => 'إعدادات المسارات غير معرفة'];
    }
    
    $target_dir = ($type == 'profile') ? PROFILE_IMG_PATH : HOTEL_IMG_PATH;
    $upload_dir = ($type == 'profile') ? UPLOAD_PATH . 'profiles/' : UPLOAD_PATH . 'hotels/';
    
    // إنشاء المجلد إذا لم يكن موجوداً
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '_' . time() . '.' . $imageFileType;
    $target_file = $upload_dir . $new_filename;
    
    // التحقق من أن الملف صورة
    $check = @getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'الملف ليس صورة صالحة'];
    }
    
    // التحقق من حجم الملف
    $max_size = defined('MAX_FILE_SIZE') ? MAX_FILE_SIZE : (2 * 1024 * 1024);
    if ($file["size"] > $max_size) {
        $max_size_mb = round($max_size / (1024 * 1024), 1);
        return ['success' => false, 'message' => "حجم الملف كبير جداً. الحد الأقصى {$max_size_mb} ميجابايت"];
    }
    
    // السماح بصيغ معينة
    $allowed_types = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowed_types)) {
        return ['success' => false, 'message' => 'نوع الملف غير مسموح. المسموح: JPG, PNG, GIF'];
    }
    
    // رفع الملف
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // تحقق من أن BASE_URL معرف لعرض الصورة
        $image_url = defined('BASE_URL') ? BASE_URL . $target_dir . $new_filename : $target_dir . $new_filename;
        
        return [
            'success' => true, 
            'filename' => $new_filename, 
            'path' => $target_dir . $new_filename,
            'full_url' => $image_url
        ];
    } else {
        return ['success' => false, 'message' => 'حدث خطأ أثناء رفع الملف'];
    }
}

// دالة تسجيل الدخول
function loginUser($username, $password) {
    global $conn, $error_messages;
    
    $username = cleanInput($username);
    $password = cleanInput($password);
    
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // التحقق من كلمة المرور
        if (password_verify($password, $user['password'])) {
            // التحقق من تفعيل الحساب
            if ($user['is_active'] == 1) {
                // إنشاء جلسة
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['profile_image'] = $user['profile_image'];
                
                return true;
            } else {
                $error_messages[] = 'الحساب غير مفعل، يرجى التواصل مع الإدارة';
            }
        } else {
            $error_messages[] = 'كلمة المرور غير صحيحة';
        }
    } else {
        $error_messages[] = 'اسم المستخدم أو البريد الإلكتروني غير موجود';
    }
    
    return false;
}

// دالة تسجيل مستخدم جديد
function registerUser($data) {
    global $conn, $error_messages, $success_messages;
    
    // تنقية البيانات
    $username = cleanInput($data['username']);
    $email = cleanInput($data['email']);
    $password = cleanInput($data['password']);
    $confirm_password = cleanInput($data['confirm_password']);
    $full_name = cleanInput($data['full_name']);
    $phone = cleanInput($data['phone'] ?? '');
    $address = cleanInput($data['address'] ?? '');
    $birth_date = cleanInput($data['birth_date'] ?? '');
    $user_type = 'customer';
    
    // التحقق من كلمات المرور
    if ($password !== $confirm_password) {
        $error_messages[] = 'كلمات المرور غير متطابقة';
        return false;
    }
    
    // التحقق من قوة كلمة المرور
    if (strlen($password) < 6) {
        $error_messages[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        return false;
    }
    
    // التحقق من أن اسم المستخدم غير مستخدم
    $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error_messages[] = 'اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل';
        return false;
    }
    
    // تشفير كلمة المرور
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // إدخال المستخدم في قاعدة البيانات
    $sql = "INSERT INTO users (username, email, password, full_name, phone, address, birth_date, user_type) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $username, $email, $hashed_password, $full_name, $phone, $address, $birth_date, $user_type);
    
    if ($stmt->execute()) {
        $success_messages[] = 'تم التسجيل بنجاح، يمكنك الآن تسجيل الدخول';
        return true;
    } else {
        $error_messages[] = 'حدث خطأ أثناء التسجيل: ' . $conn->error;
        return false;
    }
}

// دالة جلب بيانات المستخدم
function getUserData($user_id) {
    global $conn;
    
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// دالة تحديث الملف الشخصي
function updateProfile($user_id, $data) {
    global $conn, $error_messages, $success_messages;
    
    $full_name = cleanInput($data['full_name']);
    $phone = cleanInput($data['phone'] ?? '');
    $address = cleanInput($data['address'] ?? '');
    $birth_date = cleanInput($data['birth_date'] ?? '');
    
    $sql = "UPDATE users SET full_name = ?, phone = ?, address = ?, birth_date = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $full_name, $phone, $address, $birth_date, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $success_messages[] = 'تم تحديث الملف الشخصي بنجاح';
        return true;
    } else {
        $error_messages[] = 'حدث خطأ أثناء التحديث: ' . $conn->error;
        return false;
    }
}

// دالة جلب الفنادق
function getHotels($limit = null, $city = null) {
    global $conn;
    
    $sql = "SELECT h.*, u.full_name as manager_name 
            FROM hotels h 
            LEFT JOIN users u ON h.manager_id = u.id 
            WHERE h.is_active = 1";
    
    if ($city) {
        $sql .= " AND h.city = '" . $conn->real_escape_string($city) . "'";
    }
    
    $sql .= " ORDER BY h.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . intval($limit);
    }
    
    $result = $conn->query($sql);
    $hotels = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $hotels[] = $row;
        }
    } else {
        error_log("SQL Error in getHotels: " . $conn->error);
    }
    
    return $hotels;
}

// دالة جلب فندق بواسطة ID
function getHotelById($id) {
    global $conn;
    
    $sql = "SELECT h.*, u.full_name as manager_name 
            FROM hotels h 
            LEFT JOIN users u ON h.manager_id = u.id 
            WHERE h.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// دالة جلب غرف الفندق
function getHotelRooms($hotel_id) {
    global $conn;
    
    $sql = "SELECT * FROM rooms WHERE hotel_id = ? AND available_rooms > 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
    
    return $rooms;
}
// دالة جلب غرفة بواسطة ID (غرفة متاحة فقط)
function getRoomById($id) {
    global $conn;

    $sql = "SELECT r.*, h.name AS hotel_name, h.city, h.country
            FROM rooms r
            JOIN hotels h ON r.hotel_id = h.id
            WHERE r.id = ?
              AND r.available_rooms > 0";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();
    return $result->fetch_assoc(); // ترجع null لو ما في غرفة متاحة
}

// دالة إنشاء حجز
function createBooking($user_id, $room_id, $check_in, $check_out, $guests, $special_requests = '') {
    global $conn, $error_messages, $success_messages;
    
    $room = getRoomById($room_id);
    if (!$room) {
        $error_messages[] = 'الغرفة غير موجودة';
        return false;
    }
    
    if ($room['available_rooms'] < 1) {
        $error_messages[] = 'لا توجد غرف متاحة من هذا النوع';
        return false;
    }
    
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $interval = $check_in_date->diff($check_out_date);
    $total_nights = $interval->days;
    
    if ($total_nights < 1) {
        $error_messages[] = 'يجب أن تكون مدة الإقامة ليلة واحدة على الأقل';
        return false;
    }
    
    $total_price = $room['price_per_night'] * $total_nights;
    
    $sql = "INSERT INTO bookings (user_id, room_id, hotel_id, check_in, check_out, total_nights, total_price, guests, special_requests) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissdiiss", $user_id, $room_id, $room['hotel_id'], $check_in, $check_out, $total_nights, $total_price, $guests, $special_requests);
    
    if ($stmt->execute()) {
        $update_sql = "UPDATE rooms SET available_rooms = available_rooms - 1 WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("i", $room_id);
        $update_stmt->execute();
        
        $success_messages[] = 'تم الحجز بنجاح، رقم الحجز: ' . $conn->insert_id;
        return $conn->insert_id;
    } else {
        $error_messages[] = 'حدث خطأ أثناء الحجز: ' . $conn->error;
        return false;
    }
}

// دالة جلب حجوزات المستخدم
function getUserBookings($user_id) {
    global $conn;
    
    $sql = "SELECT b.*, h.name as hotel_name, r.room_type, r.price_per_night 
            FROM bookings b 
            JOIN hotels h ON b.hotel_id = h.id 
            JOIN rooms r ON b.room_id = r.id 
            WHERE b.user_id = ? 
            ORDER BY b.booking_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    return $bookings;
}
?>