<?php
require_once '../config.php';
require_once '../includes/functions.php';

// التحقق من صلاحيات المدير
if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

$page_title = 'إدارة المستخدمين';

// البحث والتصفية
$search = $_GET['search'] ?? '';
$user_type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';

// بناء الاستعلام
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (username LIKE ? OR email LIKE ? OR full_name LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
    $types .= 'sss';
}

if (!empty($user_type)) {
    $sql .= " AND user_type = ?";
    $params[] = $user_type;
    $types .= 's';
}

if ($status === 'active') {
    $sql .= " AND is_active = 1";
} elseif ($status === 'inactive') {
    $sql .= " AND is_active = 0";
}

$sql .= " ORDER BY created_at DESC";

// تنفيذ الاستعلام
if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// معالجة الإجراءات
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $user_id = intval($_GET['id']);
    
    switch ($action) {
        case 'activate':
            $conn->query("UPDATE users SET is_active = 1 WHERE id = $user_id");
            $success_messages[] = 'تم تفعيل الحساب بنجاح';
            break;
            
        case 'deactivate':
            $conn->query("UPDATE users SET is_active = 0 WHERE id = $user_id");
            $success_messages[] = 'تم تعطيل الحساب بنجاح';
            break;
            
        case 'delete':
            // التحقق إذا كان المستخدم لديه حجوزات
            $has_bookings = $conn->query("SELECT COUNT(*) FROM bookings WHERE user_id = $user_id")->fetch_row()[0];
            
            if ($has_bookings > 0) {
                $error_messages[] = 'لا يمكن حذف مستخدم لديه حجوزات';
            } else {
                $conn->query("DELETE FROM users WHERE id = $user_id");
                $success_messages[] = 'تم حذف الحساب بنجاح';
            }
            break;
            
        case 'make_admin':
            $conn->query("UPDATE users SET user_type = 'admin' WHERE id = $user_id");
            $success_messages[] = 'تم تعيين المستخدم كمدير';
            break;
            
        case 'make_manager':
            $conn->query("UPDATE users SET user_type = 'manager' WHERE id = $user_id");
            $success_messages[] = 'تم تعيين المستخدم كمدير فندق';
            break;
            
        case 'make_customer':
            $conn->query("UPDATE users SET user_type = 'customer' WHERE id = $user_id");
            $success_messages[] = 'تم تعيين المستخدم كعميل';
            break;
    }
    
    // إعادة التوجيه لنفس الصفحة بدون معلمات الإجراء
    redirect('users.php?' . http_build_query($_GET));
}
?>
<?php include '../includes/header.php'; ?>

<div class="row">
    <div class="col-md-3">
        <?php include 'admin-sidebar.php'; ?>
    </div>
    
    <div class="col-md-9">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users"></i> إدارة المستخدمين</h5>
                <a href="user-add.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus"></i> إضافة مستخدم
                </a>
            </div>
            <div class="card-body">
                <?php displayMessages(); ?>
                
                <!-- أدوات البحث والتصفية -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <form method="GET" action="" class="row g-2">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="بحث بالاسم أو البريد..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="type">
                                    <option value="">جميع الأنواع</option>
                                    <option value="admin" <?php echo $user_type == 'admin' ? 'selected' : ''; ?>>مدير</option>
                                    <option value="manager" <?php echo $user_type == 'manager' ? 'selected' : ''; ?>>مدير فندق</option>
                                    <option value="customer" <?php echo $user_type == 'customer' ? 'selected' : ''; ?>>عميل</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="status">
                                    <option value="">جميع الحالات</option>
                                    <option value="active" <?php echo $status == 'active' ? 'selected' : ''; ?>>مفعل</option>
                                    <option value="inactive" <?php echo $status == 'inactive' ? 'selected' : ''; ?>>غير مفعل</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="users-export.php" class="btn btn-outline-success">
                            <i class="fas fa-file-export"></i> تصدير
                        </a>
                    </div>
                </div>
                
                <!-- جدول المستخدمين -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>النوع</th>
                                <th>الحالة</th>
                                <th>تاريخ التسجيل</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows == 0): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-users-slash fa-2x text-muted mb-3"></i>
                                        <p class="text-muted">لا توجد نتائج</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php while ($user = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo PROFILE_IMG_PATH . $user['profile_image']; ?>" 
                                                     class="rounded-circle me-2" width="40" height="40">
                                                <div>
                                                    <strong><?php echo $user['full_name']; ?></strong><br>
                                                    <small class="text-muted">@<?php echo $user['username']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $user['email']; ?></td>
                                        <td>
                                            <?php
                                            $badge_class = [
                                                'admin' => 'danger',
                                                'manager' => 'warning',
                                                'customer' => 'info'
                                            ];
                                            $type_text = [
                                                'admin' => 'مدير',
                                                'manager' => 'مدير فندق',
                                                'customer' => 'عميل'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $badge_class[$user['user_type']]; ?>">
                                                <?php echo $type_text[$user['user_type']]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $user['is_active'] ? 'مفعل' : 'غير مفعل'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="user-edit.php?id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-outline-primary" title="تعديل">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                                                            data-bs-toggle="dropdown">
                                                        <i class="fas fa-cog"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <?php if ($user['user_type'] != 'admin'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="?action=make_admin&id=<?php echo $user['id']; ?>&<?php echo http_build_query($_GET); ?>">
                                                                    <i class="fas fa-user-shield"></i> تعيين كمدير
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($user['user_type'] != 'manager'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="?action=make_manager&id=<?php echo $user['id']; ?>&<?php echo http_build_query($_GET); ?>">
                                                                    <i class="fas fa-user-tie"></i> تعيين كمدير فندق
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        
                                                        <?php if ($user['user_type'] != 'customer'): ?>
                                                            <li>
                                                                <a class="dropdown-item" href="?action=make_customer&id=<?php echo $user['id']; ?>&<?php echo http_build_query($_GET); ?>">
                                                                    <i class="fas fa-user"></i> تعيين كعميل
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        
                                                        <li><hr class="dropdown-divider"></li>
                                                        
                                                        <?php if ($user['is_active']): ?>
                                                            <li>
                                                                <a class="dropdown-item text-warning" href="?action=deactivate&id=<?php echo $user['id']; ?>&<?php echo http_build_query($_GET); ?>" 
                                                                   onclick="return confirm('هل تريد تعطيل هذا الحساب؟')">
                                                                    <i class="fas fa-user-slash"></i> تعطيل
                                                                </a>
                                                            </li>
                                                        <?php else: ?>
                                                            <li>
                                                                <a class="dropdown-item text-success" href="?action=activate&id=<?php echo $user['id']; ?>&<?php echo http_build_query($_GET); ?>">
                                                                    <i class="fas fa-user-check"></i> تفعيل
                                                                </a>
                                                            </li>
                                                        <?php endif; ?>
                                                        
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="?action=delete&id=<?php echo $user['id']; ?>&<?php echo http_build_query($_GET); ?>" 
                                                               onclick="return confirm('هل أنت متأكد من حذف هذا الحساب؟')">
                                                                <i class="fas fa-trash"></i> حذف
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- ترقيم الصفحات -->
                <nav aria-label="ترقيم الصفحات" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1">السابق</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">التالي</a>
                        </li>
                    </ul>
                </nav>
            </div>
            
            <!-- إحصائيات -->
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-3">
                        <small class="text-muted">إجمالي المستخدمين:</small>
                        <strong><?php echo $result->num_rows; ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">المدراء:</small>
                        <strong><?php echo $conn->query("SELECT COUNT(*) FROM users WHERE user_type = 'admin'")->fetch_row()[0]; ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">مديري الفنادق:</small>
                        <strong><?php echo $conn->query("SELECT COUNT(*) FROM users WHERE user_type = 'manager'")->fetch_row()[0]; ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">العملاء:</small>
                        <strong><?php echo $conn->query("SELECT COUNT(*) FROM users WHERE user_type = 'customer'")->fetch_row()[0]; ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// تأكيد الإجراءات
document.addEventListener('DOMContentLoaded', function() {
    const deleteLinks = document.querySelectorAll('a[href*="action=delete"]');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('هل أنت متأكد من حذف هذا الحساب؟\nلا يمكن التراجع عن هذا الإجراء.')) {
                e.preventDefault();
            }
        });
    });
    
    const deactivateLinks = document.querySelectorAll('a[href*="action=deactivate"]');
    deactivateLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('هل تريد تعطيل هذا الحساب؟\nلن يتمكن المستخدم من تسجيل الدخول.')) {
                e.preventDefault();
            }
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>