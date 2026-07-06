<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من صلاحيات المدير
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$page_title = 'إضافة مستخدم جديد';

// معالجة إضافة المستخدم
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = cleanInput($_POST['password'] ?? '');
    $confirm_password = cleanInput($_POST['confirm_password'] ?? '');
    $full_name = cleanInput($_POST['full_name'] ?? '');
    $user_type = cleanInput($_POST['user_type'] ?? 'customer');
    $phone = cleanInput($_POST['phone'] ?? '');
    $address = cleanInput($_POST['address'] ?? '');
    $birth_date = cleanInput($_POST['birth_date'] ?? '');
    
    // التحقق من صحة البيانات
    $errors = [];
    
    if (empty($username)) $errors[] = 'اسم المستخدم مطلوب';
    if (empty($email)) $errors[] = 'البريد الإلكتروني مطلوب';
    if (empty($password)) $errors[] = 'كلمة المرور مطلوبة';
    if (empty($full_name)) $errors[] = 'الاسم الكامل مطلوب';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'صيغة البريد الإلكتروني غير صحيحة';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'كلمات المرور غير متطابقة';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    }
    
    // التحقق من عدم وجود اسم مستخدم أو بريد مكرر
    if (empty($errors)) {
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = 'اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل';
        }
    }
    
    // إذا لم توجد أخطاء، إضافة المستخدم
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (username, email, password, full_name, user_type, phone, address, birth_date, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", 
            $username, $email, $hashed_password, $full_name, $user_type, $phone, $address, $birth_date
        );
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            $success_messages[] = 'تم إضافة المستخدم بنجاح!';
            
            // إذا كان نوع المستخدم مدير فندق، ربطه بفندق
            if ($user_type == 'manager' && isset($_POST['hotel_id']) && !empty($_POST['hotel_id'])) {
                $hotel_id = intval($_POST['hotel_id']);
                $update_sql = "UPDATE hotels SET manager_id = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $user_id, $hotel_id);
                $update_stmt->execute();
            }
            
            // إعادة تعيين النموذج
            $_POST = [];
        } else {
            $error_messages[] = 'حدث خطأ أثناء إضافة المستخدم: ' . $conn->error;
        }
    } else {
        foreach ($errors as $error) {
            $error_messages[] = $error;
        }
    }
}

// جلب الفنادق لربطها بمدير الفندق
$hotels_sql = "SELECT id, name, city FROM hotels WHERE manager_id IS NULL ORDER BY name";
$hotels_result = $conn->query($hotels_sql);
?>

<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <?php include 'admin-sidebar.php'; ?>
    </div>
    
    <div class="col-md-9">
        <div class="card shadow-lg">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-user-plus"></i> إضافة مستخدم جديد</h5>
                <a href="users.php" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-arrow-right"></i> العودة إلى القائمة
                </a>
            </div>
            <div class="card-body">
                <?php displayMessages(); ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <!-- معلومات الحساب -->
                        <div class="col-md-6">
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-user-circle"></i> معلومات الحساب</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="user_type" class="form-label">نوع المستخدم *</label>
                                        <select class="form-select" id="user_type" name="user_type" required>
                                            <option value="customer" <?php echo ($_POST['user_type'] ?? 'customer') == 'customer' ? 'selected' : ''; ?>>عميل</option>
                                            <option value="manager" <?php echo ($_POST['user_type'] ?? '') == 'manager' ? 'selected' : ''; ?>>مدير فندق</option>
                                            <option value="admin" <?php echo ($_POST['user_type'] ?? '') == 'admin' ? 'selected' : ''; ?>>مدير نظام</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="username" class="form-label">اسم المستخدم *</label>
                                        <input type="text" class="form-control" id="username" name="username" 
                                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                                               required minlength="3" maxlength="50">
                                        <small class="text-muted">3-50 حرف، يجب أن يكون فريداً</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="email" class="form-label">البريد الإلكتروني *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="password" class="form-label">كلمة المرور *</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="password" name="password" 
                                                       required minlength="6">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="togglePassword('password')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="confirm_password" class="form-label">تأكيد كلمة المرور *</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="confirm_password" 
                                                       name="confirm_password" required minlength="6">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                        onclick="togglePassword('confirm_password')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" checked>
                                        <label class="form-check-label" for="send_welcome_email">
                                            إرسال بريد ترحيبي بالمستخدم الجديد
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="require_password_change" name="require_password_change">
                                        <label class="form-check-label" for="require_password_change">
                                            مطالبة المستخدم بتغيير كلمة المرور عند أول دخول
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
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">الاسم الكامل *</label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" 
                                               required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="phone" class="form-label">رقم الهاتف</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" 
                                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="birth_date" class="form-label">تاريخ الميلاد</label>
                                            <input type="date" class="form-control" id="birth_date" name="birth_date" 
                                                   value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>"
                                                   max="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="address" class="form-label">العنوان</label>
                                        <textarea class="form-control" id="address" name="address" 
                                                  rows="3"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <!-- حقل إضافي لمدير الفندق -->
                                    <div id="managerFields" style="display: none;">
                                        <div class="mb-3">
                                            <label for="hotel_id" class="form-label">الفندق المختص</label>
                                            <select class="form-select" id="hotel_id" name="hotel_id">
                                                <option value="">اختر فندق (اختياري)</option>
                                                <?php while ($hotel = $hotels_result->fetch_assoc()): ?>
                                                    <option value="<?php echo $hotel['id']; ?>">
                                                        <?php echo htmlspecialchars($hotel['name'] . ' - ' . $hotel['city']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                            <small class="text-muted">يمكن ربط مدير الفندق بفندق معين لإدارته</small>
                                        </div>
                                    </div>
                                    
                                    <!-- حقل إضافي للأدمن -->
                                    <div id="adminFields" style="display: none;">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i> 
                                            مدير النظام لديه صلاحيات كاملة على جميع أجزاء النظام
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- أزرار الحفظ -->
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="btn-group" role="group">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i> حفظ المستخدم
                                </button>
                                <button type="reset" class="btn btn-outline-secondary btn-lg">
                                    <i class="fas fa-redo"></i> إعادة تعيين
                                </button>
                                <a href="users.php" class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-times"></i> إلغاء
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- معلومات مساعدة -->
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-4">
                        <h6><i class="fas fa-lightbulb text-warning"></i> نصائح:</h6>
                        <ul class="small mb-0">
                            <li>استخدم كلمات مرور قوية</li>
                            <li>تحقق من صحة البريد الإلكتروني</li>
                            <li>حدد نوع المستخدم بدقة</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-shield-alt text-primary"></i> الأمان:</h6>
                        <ul class="small mb-0">
                            <li>كلمة المرور مشفرة</li>
                            <li>بيانات محمية</li>
                            <li>صلاحيات محددة</li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h6><i class="fas fa-cogs text-success"></i> الصلاحيات:</h6>
                        <ul class="small mb-0">
                            <li><strong>مدير النظام:</strong> جميع الصلاحيات</li>
                            <li><strong>مدير فندق:</strong> إدارة فندق محدد</li>
                            <li><strong>عميل:</strong> حجز الفنادق فقط</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// عرض/إخفاء الحقول حسب نوع المستخدم
document.getElementById('user_type').addEventListener('change', function() {
    const userType = this.value;
    const managerFields = document.getElementById('managerFields');
    const adminFields = document.getElementById('adminFields');
    
    if (userType === 'manager') {
        managerFields.style.display = 'block';
        adminFields.style.display = 'none';
    } else if (userType === 'admin') {
        managerFields.style.display = 'none';
        adminFields.style.display = 'block';
    } else {
        managerFields.style.display = 'none';
        adminFields.style.display = 'none';
    }
});

// عرض الحقول عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    const userType = document.getElementById('user_type');
    if (userType) {
        userType.dispatchEvent(new Event('change'));
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
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const fullName = document.getElementById('full_name').value.trim();
    
    // التحقق من الحقول المطلوبة
    if (!username || !email || !password || !fullName) {
        e.preventDefault();
        alert('يرجى ملء جميع الحقول المطلوبة (*)');
        return;
    }
    
    // التحقق من طول اسم المستخدم
    if (username.length < 3 || username.length > 50) {
        e.preventDefault();
        alert('اسم المستخدم يجب أن يكون بين 3 و 50 حرفاً');
        return;
    }
    
    // التحقق من صحة البريد الإلكتروني
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('صيغة البريد الإلكتروني غير صحيحة');
        return;
    }
    
    // التحقق من تطابق كلمات المرور
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('كلمات المرور غير متطابقة');
        return;
    }
    
    // التحقق من قوة كلمة المرور
    if (password.length < 6) {
        e.preventDefault();
        alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
        return;
    }
    
    // التأكيد على حفظ المستخدم
    const userType = document.getElementById('user_type').value;
    const userTypeText = userType === 'admin' ? 'مدير نظام' : 
                        userType === 'manager' ? 'مدير فندق' : 'عميل';
    
    if (!confirm(`هل تريد إضافة مستخدم جديد كنوع "${userTypeText}"؟`)) {
        e.preventDefault();
    }
});

// توليد اسم مستخدم تلقائياً من الاسم
document.getElementById('full_name').addEventListener('blur', function() {
    const fullName = this.value.trim();
    const usernameInput = document.getElementById('username');
    
    if (fullName && !usernameInput.value) {
        // تحويل الاسم إلى اسم مستخدم
        let username = fullName
            .toLowerCase()
            .replace(/\s+/g, '.')
            .replace(/[^a-z0-9.]/g, '')
            .substring(0, 20);
        
        // إضافة أرقام عشوائية لتجنب التكرار
        const randomNum = Math.floor(1000 + Math.random() * 9000);
        username = username + randomNum;
        
        usernameInput.value = username;
    }
});

// توليد بريد إلكتروني تلقائياً
document.getElementById('username').addEventListener('blur', function() {
    const username = this.value.trim();
    const emailInput = document.getElementById('email');
    
    if (username && !emailInput.value) {
        emailInput.value = username + '@hotel.com';
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
    
    alert('تم توليد كلمة مرور عشوائية: ' + password);
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

<style>
.card {
    border-radius: 10px;
    overflow: hidden;
}

.card-header {
    border-bottom: none;
    font-weight: 600;
}

.form-control:focus, .form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.btn-group .btn {
    border-radius: 8px;
    margin: 0 5px;
}

#managerFields, #adminFields {
    animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?php include '../includes/footer.php'; ?>