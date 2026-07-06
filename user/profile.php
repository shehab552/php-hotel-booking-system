<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

// التحقق من تسجيل الدخول
if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

$page_title = 'الملف الشخصي';
$user_id = $_SESSION['user_id'];

// جلب بيانات المستخدم
$user = getUserData($user_id);

// معالجة تحديث الملف الشخصي
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $profile_data = [
            'full_name' => $_POST['full_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'address' => $_POST['address'] ?? '',
            'birth_date' => $_POST['birth_date'] ?? ''
        ];
        
        if (updateProfile($user_id, $profile_data)) {
            // تحديث بيانات الجلسة
            $_SESSION['full_name'] = $profile_data['full_name'];
        }
    }
    
    // معالجة تغيير كلمة المرور
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_messages[] = 'جميع الحقول مطلوبة';
        } elseif ($new_password !== $confirm_password) {
            $error_messages[] = 'كلمات المرور غير متطابقة';
        } elseif (strlen($new_password) < 6) {
            $error_messages[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } elseif (password_verify($current_password, $user['password'])) {
            // تحديث كلمة المرور
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($stmt->execute()) {
                $success_messages[] = 'تم تغيير كلمة المرور بنجاح';
            } else {
                $error_messages[] = 'حدث خطأ أثناء تغيير كلمة المرور';
            }
        } else {
            $error_messages[] = 'كلمة المرور الحالية غير صحيحة';
        }
    }
    
    // معالجة تحديث الصورة
    if (isset($_POST['update_image']) && isset($_FILES['profile_image'])) {
        $image_result = uploadImage($_FILES['profile_image'], 'profile');
        
        if ($image_result['success']) {
            $new_image = $image_result['filename'];
            $old_image = $user['profile_image'];
            
            // تحديث قاعدة البيانات
            $sql = "UPDATE users SET profile_image = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_image, $user_id);
            
            if ($stmt->execute()) {
                // تحديث الجلسة
                $_SESSION['profile_image'] = $new_image;
                
                // حذف الصورة القديمة إذا لم تكن الصورة الافتراضية
                if ($old_image != 'default.jpg') {
                    $old_image_path = UPLOAD_PATH . 'profiles/' . $old_image;
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                $success_messages[] = 'تم تحديث الصورة الشخصية بنجاح';
                // تحديث بيانات المستخدم
                $user = getUserData($user_id);
            } else {
                $error_messages[] = 'حدث خطأ أثناء تحديث الصورة';
            }
        } else {
            $error_messages[] = $image_result['message'];
        }
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <!-- القائمة الجانبية -->
        <div class="card shadow-sm mb-4">
            <div class="card-body text-center">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-3">
                        <img src="<?php echo PROFILE_IMG_PATH . $user['profile_image']; ?>" 
                             class="profile-img mb-3" id="profileImagePreview" 
                             alt="صورة الملف الشخصي">
                        <div class="mb-3">
                            <input type="file" class="form-control" id="profile_image" 
                                   name="profile_image" accept="image/*" 
                                   onchange="previewImage(this)">
                        </div>
                        <button type="submit" name="update_image" class="btn btn-primary btn-sm">
                            <i class="fas fa-upload"></i> تحديث الصورة
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="list-group shadow-sm">
            <a href="dashboard.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt"></i> لوحة التحكم
            </a>
            <a href="bookings.php" class="list-group-item list-group-item-action">
                <i class="fas fa-calendar-check"></i> حجوزاتي
            </a>
            <a href="profile.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-user"></i> الملف الشخصي
            </a>
            <a href="../logout.php" class="list-group-item list-group-item-action text-danger">
                <i class="fas fa-sign-out-alt"></i> تسجيل الخروج
            </a>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-user-edit"></i> تعديل الملف الشخصي</h5>
            </div>
            <div class="card-body">
                <?php displayMessages(); ?>
                
                <!-- معلومات الحساب -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-info">
                            <div class="card-body">
                                <h6 class="card-title text-info"><i class="fas fa-info-circle"></i> معلومات الحساب</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th>اسم المستخدم:</th>
                                        <td><?php echo $user['username']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>البريد الإلكتروني:</th>
                                        <td><?php echo $user['email']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>نوع المستخدم:</th>
                                        <td>
                                            <?php
                                            $user_types = [
                                                'admin' => 'مدير النظام',
                                                'manager' => 'مدير فندق',
                                                'customer' => 'عميل'
                                            ];
                                            echo $user_types[$user['user_type']];
                                            ?>
                                        </td>
                                                                            </tr>
                                    <tr>
                                        <th>تاريخ التسجيل:</th>
                                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>حالة الحساب:</th>
                                        <td>
                                            <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $user['is_active'] ? 'مفعل' : 'غير مفعل'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="card-title text-success"><i class="fas fa-chart-line"></i> إحصائيات الحساب</h6>
                                <?php
                                $bookings = getUserBookings($user_id);
                                $total_bookings = count($bookings);
                                $total_spent = array_sum(array_column($bookings, 'total_price'));
                                ?>
                                <div class="text-center">
                                    <h1 class="display-4 text-success"><?php echo $total_bookings; ?></h1>
                                    <p class="text-muted">عدد الحجوزات</p>
                                    <h3 class="text-primary"><?php echo number_format($total_spent, 2); ?> ريال</h3>
                                    <p class="text-muted">إجمالي المصروفات</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- تحديث المعلومات الشخصية -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-user-cog"></i> المعلومات الشخصية</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">الاسم الكامل *</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">رقم الهاتف</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="birth_date" class="form-label">تاريخ الميلاد</label>
                                    <input type="date" class="form-control" id="birth_date" name="birth_date" 
                                           value="<?php echo $user['birth_date'] ?? ''; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">العنوان</label>
                                    <textarea class="form-control" id="address" name="address" 
                                              rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> حفظ التغييرات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- تغيير كلمة المرور -->
                <div class="card border-warning">
                    <div class="card-header bg-warning text-white">
                        <h6 class="mb-0"><i class="fas fa-key"></i> تغيير كلمة المرور</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="current_password" class="form-label">كلمة المرور الحالية *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="current_password" 
                                               name="current_password" required>
                                        <button type="button" class="btn btn-outline-secondary" 
                                                onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="new_password" class="form-label">كلمة المرور الجديدة *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" 
                                               name="new_password" required>
                                        <button type="button" class="btn btn-outline-secondary" 
                                                onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="confirm_password" class="form-label">تأكيد كلمة المرور *</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" 
                                               name="confirm_password" required>
                                        <button type="button" class="btn btn-outline-secondary" 
                                                onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" name="change_password" class="btn btn-warning">
                                    <i class="fas fa-key"></i> تغيير كلمة المرور
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- إعدادات الحساب -->
                <div class="card border-danger mt-4">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="fas fa-cog"></i> إعدادات الحساب المتقدمة</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="email_notifications" checked>
                                    <label class="form-check-label" for="email_notifications">
                                        إشعارات البريد الإلكتروني
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="sms_notifications">
                                    <label class="form-check-label" for="sms_notifications">
                                        إشعارات الرسائل النصية
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <button class="btn btn-outline-danger" onclick="confirmAction('هل تريد حقاً حذف حسابك؟')">
                                    <i class="fas fa-trash-alt"></i> حذف الحساب
                                </button>
                                <button class="btn btn-outline-secondary">
                                    <i class="fas fa-download"></i> تصدير بياناتي
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profileImagePreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// التحقق من تغيير كلمة المرور
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.querySelector('form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_password').value;
            
            if (newPass && confirmPass && newPass !== confirmPass) {
                e.preventDefault();
                alert('كلمات المرور غير متطابقة!');
            }
            
            if (newPass && newPass.length < 6) {
                e.preventDefault();
                alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل!');
            }
        });
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>