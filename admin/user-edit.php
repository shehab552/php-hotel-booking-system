<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من صلاحيات المدير
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$page_title = 'تعديل بيانات المستخدم';

// التحقق من وجود معرف المستخدم
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('users.php');
}

$user_id = intval($_GET['id']);

// جلب بيانات المستخدم
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    redirect('users.php');
}

// جلب الفنادق
$hotels_sql = "SELECT id, name, city FROM hotels ORDER BY name";
$hotels_result = $conn->query($hotels_sql);

// جلب فندق المستخدم إذا كان مدير فندق
$user_hotel = null;
if ($user['user_type'] == 'manager') {
    $hotel_sql = "SELECT id, name FROM hotels WHERE manager_id = ?";
    $hotel_stmt = $conn->prepare($hotel_sql);
    $hotel_stmt->bind_param("i", $user_id);
    $hotel_stmt->execute();
    $user_hotel = $hotel_stmt->get_result()->fetch_assoc();
}

// معالجة تحديث البيانات
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = cleanInput($_POST['full_name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $user_type = cleanInput($_POST['user_type'] ?? 'customer');
    $phone = cleanInput($_POST['phone'] ?? '');
    $address = cleanInput($_POST['address'] ?? '');
    $birth_date = cleanInput($_POST['birth_date'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // حقل كلمة المرور (اختياري)
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // التحقق من الحقول المطلوبة
    if (empty($full_name)) $errors[] = 'الاسم الكامل مطلوب';
    if (empty($email)) $errors[] = 'البريد الإلكتروني مطلوب';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'صيغة البريد الإلكتروني غير صحيحة';
    }
    
    // التحقق من عدم تكرار البريد الإلكتروني
    $check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("si", $email, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $errors[] = 'البريد الإلكتروني مستخدم بالفعل من قبل مستخدم آخر';
    }
    
    // التحقق من كلمة المرور إذا كانت مدخلة
    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $errors[] = 'كلمات المرور غير متطابقة';
        }
        if (strlen($password) < 6) {
            $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        }
    }
    
    // إذا لم توجد أخطاء، تحديث البيانات
    if (empty($errors)) {
        // بناء استعلام التحديث
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET 
                    full_name = ?, 
                    email = ?, 
                    password = ?,
                    user_type = ?, 
                    phone = ?, 
                    address = ?, 
                    birth_date = ?, 
                    is_active = ? 
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssii", 
                $full_name, $email, $hashed_password, $user_type, 
                $phone, $address, $birth_date, $is_active, $user_id
            );
        } else {
            $sql = "UPDATE users SET 
                    full_name = ?, 
                    email = ?, 
                    user_type = ?, 
                    phone = ?, 
                    address = ?, 
                    birth_date = ?, 
                    is_active = ? 
                    WHERE id = ?";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssii", 
                $full_name, $email, $user_type, 
                $phone, $address, $birth_date, $is_active, $user_id
            );
        }
        
        if ($stmt->execute()) {
            // إذا كان مدير فندق، تحديث الفندق المرتبط
            if ($user_type == 'manager' && isset($_POST['hotel_id']) && !empty($_POST['hotel_id'])) {
                $hotel_id = intval($_POST['hotel_id']);
                
                // إزالة المدير من الفنادق الأخرى
                $reset_sql = "UPDATE hotels SET manager_id = NULL WHERE manager_id = ? AND id != ?";
                $reset_stmt = $conn->prepare($reset_sql);
                $reset_stmt->bind_param("ii", $user_id, $hotel_id);
                $reset_stmt->execute();
                
                // ربط المدير بالفندق الجديد
                $update_hotel_sql = "UPDATE hotels SET manager_id = ? WHERE id = ?";
                $update_hotel_stmt = $conn->prepare($update_hotel_sql);
                $update_hotel_stmt->bind_param("ii", $user_id, $hotel_id);
                $update_hotel_stmt->execute();
            } elseif ($user_type != 'manager') {
                // إزالة المدير من جميع الفنادق إذا لم يعد مدير فندق
                $remove_sql = "UPDATE hotels SET manager_id = NULL WHERE manager_id = ?";
                $remove_stmt = $conn->prepare($remove_sql);
                $remove_stmt->bind_param("i", $user_id);
                $remove_stmt->execute();
            }
            
            $success_messages[] = 'تم تحديث بيانات المستخدم بنجاح!';
            
            // تحديث بيانات الجلسة إذا كان المستخدم الحالي هو نفسه الذي يتم تعديله
            if ($_SESSION['user_id'] == $user_id) {
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $_SESSION['user_type'] = $user_type;
            }
            
            // إعادة جلب بيانات المستخدم المحدثة
            $sql = "SELECT * FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error_messages[] = 'حدث خطأ أثناء تحديث البيانات: ' . $conn->error;
        }
    } else {
        foreach ($errors as $error) {
            $error_messages[] = $error;
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <?php include 'admin-sidebar.php'; ?>
    </div>
    
    <div class="col-md-9">
        <div class="card shadow-lg">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-user-edit"></i> تعديل بيانات المستخدم
                    <small class="text-muted">#<?php echo $user['id']; ?></small>
                </h5>
                <div>
                    <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                        <?php echo $user['is_active'] ? 'مفعل' : 'غير مفعل'; ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <?php displayMessages(); ?>
                
                <!-- معلومات الحساب -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <img src="<?php echo PROFILE_IMG_PATH . $user['profile_image']; ?>" 
                                     class="rounded-circle mb-3" width="100" height="100">
                                <h6><?php echo htmlspecialchars($user['full_name']); ?></h6>
                                <p class="text-muted mb-1">@<?php echo htmlspecialchars($user['username']); ?></p>
                                <p class="text-muted mb-0">
                                    <?php 
                                    $types = [
                                        'admin' => 'مدير النظام',
                                        'manager' => 'مدير فندق',
                                        'customer' => 'عميل'
                                    ];
                                    echo $types[$user['user_type']] ?? $user['user_type'];
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <h6><i class="fas fa-info-circle text-primary"></i> معلومات الحساب</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <th>تاريخ التسجيل:</th>
                                        <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>آخر دخول:</th>
                                        <td>
                                            <?php 
                                            if ($user['last_login']) {
                                                echo date('Y-m-d H:i', strtotime($user['last_login']));
                                            } else {
                                                echo 'لم يسجل دخول بعد';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>عدد الحجوزات:</th>
                                        <td>
                                            <?php 
                                            $bookings_count = $conn->query("SELECT COUNT(*) FROM bookings WHERE user_id = $user_id")->fetch_row()[0];
                                            echo $bookings_count;
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <form method="POST" action="">
                    <div class="row">
                        <!-- المعلومات الأساسية -->
                        <div class="col-md-6">
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-user-cog"></i> المعلومات الأساسية</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">الاسم الكامل *</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">البريد الإلكتروني *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="user_type" class="form-label">نوع المستخدم *</label>
                                        <select class="form-select" id="user_type" name="user_type" required>
                                            <option value="customer" <?php echo $user['user_type'] == 'customer' ? 'selected' : ''; ?>>عميل</option>
                                            <option value="manager" <?php echo $user['user_type'] == 'manager' ? 'selected' : ''; ?>>مدير فندق</option>
                                            <option value="admin" <?php echo $user['user_type'] == 'admin' ? 'selected' : ''; ?>>مدير نظام</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            تفعيل الحساب
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- المعلومات الشخصية -->
                        <div class="col-md-6">
                            <div class="card border-success mb-4">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-id-card"></i> المعلومات الشخصية</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">رقم الهاتف</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="birth_date" class="form-label">تاريخ الميلاد</label>
                                            <input type="date" class="form-control" id="birth_date" name="birth_date" 
                                                   value="<?php echo htmlspecialchars($user['birth_date'] ?? ''); ?>"
                                                   max="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label">العنوان</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <!-- حقل إضافي لمدير الفندق -->
                                    <div id="managerFields" style="<?php echo $user['user_type'] == 'manager' ? '' : 'display: none;'; ?>">
                                        <div class="mb-3">
                                            <label for="hotel_id" class="form-label">الفندق المختص</label>
                                            <select class="form-select" id="hotel_id" name="hotel_id">
                                                <option value="">اختر فندق (اختياري)</option>
                                                <?php 
                                                $hotels_result->data_seek(0);
                                                while ($hotel = $hotels_result->fetch_assoc()): 
                                                    $selected = ($user_hotel && $user_hotel['id'] == $hotel['id']) ? 'selected' : '';
                                                ?>
                                                    <option value="<?php echo $hotel['id']; ?>" <?php echo $selected; ?>>
                                                        <?php echo htmlspecialchars($hotel['name'] . ' - ' . $hotel['city']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- تغيير كلمة المرور -->
                    <div class="card border-warning mb-4">
                        <div class="card-header bg-warning text-white">
                            <h6 class="mb-0"><i class="fas fa-key"></i> تغيير كلمة المرور (اختياري)</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">كلمة المرور الجديدة</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password">
                                        <button type="button" class="btn btn-outline-secondary" 
                                                onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">اتركه فارغاً إذا كنت لا تريد تغيير كلمة المرور</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">تأكيد كلمة المرور</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                        <button type="button" class="btn btn-outline-secondary" 
                                                onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="force_password_change" name="force_password_change">
                                <label class="form-check-label" for="force_password_change">
                                    مطالبة المستخدم بتغيير كلمة المرور عند الدخول التالي
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- أزرار الحفظ -->
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="btn-group" role="group">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i> حفظ التغييرات
                                </button>
                                <a href="users.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-arrow-right"></i> العودة
                                </a>
                                <a href="user-add.php" class="btn btn-outline-info btn-lg">
                                    <i class="fas fa-user-plus"></i> إضافة مستخدم جديد
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// عرض/إخفاء حقول مدير الفندق
document.getElementById('user_type').addEventListener('change', function() {
    const userType = this.value;
    const managerFields = document.getElementById('managerFields');
    
    if (userType === 'manager') {
        managerFields.style.display = 'block';
    } else {
        managerFields.style.display = 'none';
    }
});

// عرض/إخفاء كلمة المرور
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// التحقق من صحة النموذج
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // التحقق من كلمة المرور إذا كانت مدخلة
    if (password || confirmPassword) {
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('كلمات المرور غير متطابقة');
            return;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
            return;
        }
    }
    
    // تأكيد التحديث
    if (!confirm('هل تريد حفظ التغييرات؟')) {
        e.preventDefault();
    }
});

// توليد كلمة مرور عشوائية
function generatePassword() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
    let password = '';
    
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    
    document.getElementById('password').value = password;
    document.getElementById('confirm_password').value = password;
    
    alert('تم توليد كلمة مرور عشوائية');
}

// إضافة زر توليد كلمة المرور
document.addEventListener('DOMContentLoaded', function() {
    const passwordGroup = document.querySelector('#password').closest('.mb-3');
    const generateBtn = document.createElement('button');
    generateBtn.type = 'button';
    generateBtn.className = 'btn btn-outline-info btn-sm mt-1';
    generateBtn.innerHTML = '<i class="fas fa-key"></i> توليد كلمة مرور عشوائية';
    generateBtn.onclick = generatePassword;
    
    passwordGroup.appendChild(generateBtn);
});
</script>

<?php include '../includes/footer.php'; ?>