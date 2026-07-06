<?php
require_once '../config.php';
require_once '../includes/functions.php';

$page_title = 'تسجيل مدير جديد';

// التحقق من أن المستخدم الحالي هو مدير
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// معالجة التسجيل
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = cleanInput($_POST['password'] ?? '');
    $confirm_password = cleanInput($_POST['confirm_password'] ?? '');
    $full_name = cleanInput($_POST['full_name'] ?? '');
    $user_type = cleanInput($_POST['user_type'] ?? 'manager');
    
    // التحقق من صحة البيانات
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error_messages[] = 'جميع الحقول المميزة ب * مطلوبة';
    } elseif ($password !== $confirm_password) {
        $error_messages[] = 'كلمات المرور غير متطابقة';
    } elseif (strlen($password) < 6) {
        $error_messages[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
    } else {
        // التحقق من عدم وجود اسم مستخدم أو بريد مكرر
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $username, $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error_messages[] = 'اسم المستخدم أو البريد الإلكتروني مستخدم بالفعل';
        } else {
            // تشفير كلمة المرور
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // إدخال المستخدم في قاعدة البيانات
            $sql = "INSERT INTO users (username, email, password, full_name, user_type) 
                    VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $username, $email, $hashed_password, $full_name, $user_type);
            
            if ($stmt->execute()) {
                $success_messages[] = 'تم إنشاء الحساب بنجاح';
                
                // إرسال بريد إلكتروني إلى المستخدم الجديد
                $subject = 'مرحباً بك في نظام حجز الفنادق';
                $message = "مرحباً $full_name,\n\n";
                $message .= "تم إنشاء حسابك بنجاح في نظام حجز الفنادق.\n";
                $message .= "بيانات الدخول:\n";
                $message .= "اسم المستخدم: $username\n";
                $message .= "البريد الإلكتروني: $email\n";
                $message .= "نوع المستخدم: " . ($user_type == 'admin' ? 'مدير النظام' : 'مدير فندق') . "\n\n";
               $message .= "يمكنك تسجيل الدخول من خلال الرابط: " . BASE_URL . "auth/login.php\n\n";
                $message .= "مع أطيب التحيات،\nفريق نظام حجز الفنادق";
                
                // mail($email, $subject, $message);
                
                // إعادة تعيين النموذج
                $_POST = [];
            } else {
                $error_messages[] = 'حدث خطأ أثناء إنشاء الحساب: ' . $conn->error;
            }
        }
    }
}
?>

<?php include '../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg">
            <div class="card-header bg-danger text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-user-shield"></i> إنشاء حساب جديد (للمدراء فقط)</h4>
            </div>
            <div class="card-body p-4">
                <?php displayMessages(); ?>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    هذه الصفحة مخصصة للمدراء فقط لإنشاء حسابات جديدة للمدراء أو مديري الفنادق.
                </div>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">الاسم الكامل *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="user_type" class="form-label">نوع المستخدم *</label>
                            <select class="form-select" id="user_type" name="user_type" required>
                                <option value="manager" <?php echo ($_POST['user_type'] ?? '') == 'manager' ? 'selected' : ''; ?>>
                                    مدير فندق
                                </option>
                                <option value="admin" <?php echo ($_POST['user_type'] ?? '') == 'admin' ? 'selected' : ''; ?>>
                                    مدير النظام
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">اسم المستخدم *</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
                            <small class="text-muted">يجب أن يكون اسم المستخدم فريداً</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
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
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">رقم الهاتف</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="birth_date" class="form-label">تاريخ الميلاد</label>
                            <input type="date" class="form-control" id="birth_date" name="birth_date" 
                                   value="<?php echo htmlspecialchars($_POST['birth_date'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">العنوان</label>
                        <textarea class="form-control" id="address" name="address" 
                                  rows="2"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="send_welcome_email" name="send_welcome_email" checked>
                        <label class="form-check-label" for="send_welcome_email">
                            إرسال بريد ترحيبي بالمستخدم الجديد
                        </label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg">
                            <i class="fas fa-user-plus"></i> إنشاء الحساب
                        </button>
                        <a href="users.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right"></i> العودة إلى قائمة المستخدمين
                        </a>
                    </div>
                </form>
                
                <!-- قائمة الحسابات الأخيرة -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-history"></i> آخر الحسابات المضافة</h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $recent_users_sql = "SELECT username, full_name, email, user_type, created_at 
                                            FROM users 
                                            ORDER BY created_at DESC 
                                            LIMIT 5";
                        $recent_users = $conn->query($recent_users_sql);
                        ?>
                        
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>اسم المستخدم</th>
                                        <th>الاسم</th>
                                        <th>النوع</th>
                                        <th>التاريخ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $recent_users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['user_type'] == 'admin' ? 'danger' : 'warning'; ?>">
                                                    <?php echo $user['user_type'] == 'admin' ? 'مدير' : 'مدير فندق'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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

// التحقق من تطابق كلمات المرور
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    
    if (password !== confirm) {
        e.preventDefault();
        alert('كلمات المرور غير متطابقة!');
        document.getElementById('confirm_password').focus();
    }
    
    if (password.length < 6) {
        e.preventDefault();
        alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل!');
    }
});
</script>

<?php include '../includes/footer.php'; ?>