<?php
session_start();

// Check if the user is logged in as an admin.
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Database connection | Ensure you have a config.php file with the correct database connection settings
require '../config.php';

// Process notification deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_notification'])) {
    $notificationId = intval($_POST['notification_id']);
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = :id");
    $stmt->execute(['id' => $notificationId]);
    $delete_feedback = "Notification deleted successfully.";
}

// Process notification submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_notification'])) {
    $notification_message = trim($_POST['notification_message']);
    if (!empty($notification_message)) {
        $stmt = $pdo->prepare("INSERT INTO notifications (message, admin) VALUES (:message, :admin)");
        $stmt->execute([
          'message' => $notification_message,
          'admin'   => $_SESSION['admin']
        ]);
        $_SESSION['notification_feedback'] = "Notification posted successfully.";
        header("Location: home.php");
        exit();
    }
}

// Fetch all notifications for management
$stmt = $pdo->query("SELECT id, message, created_at FROM notifications ORDER BY created_at DESC");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Auto versioning for static files | Cache fix
function auto_version($file) {
    if (file_exists($file)) {
        return $file . '?v=' . filemtime($file);
    }
    return $file;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <!-- Metadata Information -->
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="منصتك الشاملة للموارد والأدوات الأكاديمية لطلاب كلية الحاسبات والمعلومات" />
    <!-- Website Information -->
    <title>لوحة التحكم — بوابة المسؤول</title>
    <!-- Favicons & Stylesheets -->
    <link rel="apple-touch-icon" sizes="180x180" href="../static/img/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="../static/img/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="../static/img/favicon-16x16.png" />
    <link rel="manifest" href="../static/img/site.webmanifest" />
    <link href="<?php echo auto_version('../static/css/main.css'); ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>

<body>
    <!-- Navigation Bar (Hamburger included for mobile) -->
    <nav class="nav-container">
        <div class="nav-content">
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>

            <div class="nav-links" id="nav-links">
                <a href="/" class="nav-button">
                    <i class="fas fa-home" aria-hidden="true"></i>
                    <span>الصفحة الرئيسية</span>
                </a>
                <a href="/student/" class="nav-button">
                    <i class="fas fa-book" aria-hidden="true"></i>
                    <span>بوابة الطلاب</span>
                </a>
                <a href="/lookup/" class="nav-button">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>البحث عن طالب</span>
                </a>
                <a href="/results/" class="nav-button">
                    <i class="fas fa-graduation-cap" aria-hidden="true"></i>
                    <span>نتائج الطلاب</span>
                </a>
                <a href="/links/" class="nav-button">
                    <i class="fas fa-link" aria-hidden="true"></i>
                    <span>روابط الجروبات</span>
                </a>
                <a href="logout.php" class="nav-button">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    <span>تسجيل الخروج</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Header Container -->
    <header>
        <div class="header-content">
            <h1>بوابة المسؤول</h1>
            <p>متاحة فقط لقادة الطلاب</p>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- File Manager Section -->
            <section id="file-manager-section" class="info-section">
                <h2>
                    <i class="fas fa-file-alt"></i>
                    مدير الملفات
                </h2>
                <div id="file-browser">
                    <div class="browser-header">
                        <div class="form-group">
                            <select id="subject" name="subject" required>
                                <option value="" selected disabled>اختر المادة</option>
                                <option value="or">بحوث العمليات</option>
                                <option value="discrete">التراكيب المحددة</option>
                                <option value="ethics">الاخلاقيات</option>
                                <option value="dld">التصميم المنطقي</option>
                                <option value="maths">رياضيات II</option>
                                <option value="hr">حقوق الانسان</option>
                            </select>
                        </div>
                    </div>
                    <div id="files-container"></div>
                </div>                <div class="form-group">
                    <div class="form-actions">
                        <button type="button" id="back-button" class="btn btn-secondary" disabled>
                            <i class="fas fa-arrow-right"></i> العودة للخلف
                        </button>
                        <button type="button" id="new-folder-btn" class="btn btn-secondary" disabled>
                            <i class="fas fa-folder-plus"></i> إنشاء مجلد جديد
                        </button>
                    </div>
                </div>

                <div id="new-folder-form" class="form-group" style="display: none;">
                    <h3><i class="fas fa-folder-plus"></i> إنشاء مجلد جديد</h3>
                    <div class="form-group">
                        <label for="new-folder-name">اسم المجلد الجديد</label>
                        <input type="text" id="new-folder-name" name="folderName" placeholder="أدخل اسم المجلد">
                    </div>
                    <div class="form-actions">
                        <button type="button" id="create-folder-btn" class="btn">إنشاء</button>
                        <button type="button" id="cancel-folder-btn" class="btn btn-secondary">إلغاء</button>
                    </div>
                </div>                <input type="hidden" id="current-folder" readonly>
                <input type="hidden" id="folder-path" name="folderPath" value="">
            </section>

            <!-- Notification Section -->
            <section id="notification-section" class="info-section">
                <h2>
                    <i class="fas fa-bell"></i>
                    إرسال رسائل المسؤول
                </h2>
                <?php if(isset($_SESSION['notification_feedback'])): ?>
                <div class="success-message">تم نشر الإشعار بنجاح.</div>
                <?php 
                 unset($_SESSION['notification_feedback']);
                ?>
                <?php endif; ?>
                <form method="post" action="home.php" class="notification-form">
                    <textarea name="notification_message" rows="3" required></textarea>
                    <div class="form-hint">
                        <i class="fas fa-info-circle"></i>
                        يمكنك إضافة روابط بطريقتين: إما مباشرة مثل https://example.com أو بصيغة Markdown مثل [نص
                        الرابط](https://example.com)
                    </div>
                    <button type="submit" name="submit_notification" class="btn">إرسال الرسالة</button>
                </form>
            </section>

            <section id="manage-notifications" class="info-section">
                <h2>
                    <i class="fas fa-tasks"></i>
                    إدارة الإشعارات
                </h2>
                <?php if(!empty($notifications)): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>الرسالة</th>
                            <th>التاريخ</th>
                            <th>الإجراء</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($notifications as $notification): ?>
                        <tr>
                            <td>
                                <?php 
                                    $message = htmlspecialchars($notification['message']); 
                                    echo $message;
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($notification['created_at']); ?></td>
                            <td>
                                <form method="post" action="home.php" onsubmit="return confirm('هل أنت متأكد؟');">
                                    <input type="hidden" name="notification_id"
                                        value="<?php echo $notification['id']; ?>">
                                    <button type="submit" name="delete_notification" class="delete-btn">حذف</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>لم يتم العثور على إشعارات.</p>
                <?php endif; ?>
            </section>
        </div>
    </main>


    <!-- Footer Message -->
    <footer class="copyright-footer">
        <p style="direction: ltr;">&copy; <?php echo date("Y"); ?> ichiwi.me</p>
    </footer>

    <script src="<?php echo auto_version('../static/js/main.js'); ?>"></script>
    <script src="<?php echo auto_version('../static/js/admin-files.js'); ?>"></script>
</body>

</html>
