<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'تسجيل الدخول';

// إذا كان المستخدم مسجل دخول بالفعل
if (isLoggedIn()) {
    redirect('user/dashboard.php');
}

// معالجة تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (loginUser($username, $password)) {
        // التوجيه حسب نوع المستخدم
        if (isAdmin()) {
           redirect('admin/dashboard.php');
        } else {
            redirect('user/dashboard.php');
        }
    }
}
?>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</h4>
            </div>
            <div class="card-body p-4">
                <?php displayMessages(); ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="fas fa-user"></i> اسم المستخدم أو البريد الإلكتروني
                        </label>
                        <input type="text" class="form-control" id="username" name="username" 
                               required placeholder="أدخل اسم المستخدم أو البريد الإلكتروني"
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> كلمة المرور
                        </label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" 
                                   required placeholder="أدخل كلمة المرور">
                            <button type="button" class="btn btn-outline-secondary" 
                                    onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">تذكرني</label>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center">
                    <p class="mb-2">ليس لديك حساب؟</p>
                    <a href="<?php echo BASE_URL; ?>auth/register.php" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus"></i> إنشاء حساب جديد
                    </a>
                    <p class="mt-3">
                        <a href="<?php echo BASE_URL; ?>auth/forgot-password.php" class="text-muted">نسيت كلمة المرور؟</a>
                    </p>
                </div>
            </div>
        </div>
        
        <!-- بيانات تجريبية -->
        <div class="card mt-4 border-info">
            <div class="card-header bg-info text-white">
                <i class="fas fa-info-circle"></i> بيانات تجريبية للاختبار
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>مدير النظام:</strong></p>
                <p class="mb-1">اسم المستخدم: admin</p>
                <p class="mb-1">كلمة المرور: admin123</p>
                <hr>
                <p class="mb-1"><strong>مدير فندق:</strong></p>
                <p class="mb-1">اسم المستخدم: manager</p>
                <p class="mb-1">كلمة المرور: manager123</p>
                <hr>
                <p class="mb-1"><strong>عميل:</strong></p>
                <p class="mb-1">اسم المستخدم: customer</p>
                <p class="mb-0">كلمة المرور: customer123</p>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>