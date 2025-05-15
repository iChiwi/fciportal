<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';
$stmt = $pdo->query("
    SELECT notifications.message, notifications.created_at, admins.name AS admin_name
    FROM notifications
    JOIN admins ON notifications.admin = admins.username
    ORDER BY notifications.created_at DESC
");
$notifications = $stmt->fetchAll();

function auto_version($file) {
    if (file_exists($file)) {
        return $file . '?v=' . filemtime($file);
    }
    return $file;
}

function processLinks($text) {    
    $processed = '';
    $position = 0;
    
    while (preg_match('/\[([^\]]+)\]\(([^)]+)\)/', $text, $matches, PREG_OFFSET_CAPTURE, $position)) {
        $beforeLink = substr($text, $position, $matches[0][1] - $position);
        $processed .= htmlspecialchars($beforeLink);
        
        $linkText = $matches[1][0];
        $linkUrl = $matches[2][0];
        
        $processed .= '<a href="' . htmlspecialchars($linkUrl) . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($linkText) . '</a>';
        
        $position = $matches[0][1] + strlen($matches[0][0]);
    }
    
    if ($position < strlen($text)) {
        $remainingText = substr($text, $position);
        
        $remainingText = preg_replace_callback('/(https?:\/\/[^\s<>"]+)/', function($matches) {
            return '<a href="' . htmlspecialchars($matches[1]) . '" target="_blank" rel="noopener noreferrer">' . htmlspecialchars($matches[1]) . '</a>';
        }, $remainingText);
        
        $processed .= htmlspecialchars($remainingText);
    }

    return $processed;
}

function safeNotificationDisplay($message) {
    $escaped = htmlspecialchars($message);
    
    $withLinks = processLinks($escaped);
    
    return $withLinks;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>الصفحة الرئيسية — مساعد الطالب</title>
    <meta name="description" content="منصتك الشاملة للموارد والأدوات الأكاديمية لطلاب كلية الحاسبات والمعلومات" />
    <link href="<?php echo auto_version('static/css/main.css'); ?>" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="static/img/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="static/img/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="static/img/favicon-16x16.png" />
    <link rel="manifest" href="static/img/site.webmanifest" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
</head>

<body>
    <nav class="nav-container">
        <div class="nav-content">
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>


            <div class="nav-links" id="nav-links">
                <a href="/study/" class="nav-button">
                    <i class="fas fa-book" aria-hidden="true"></i>
                    <span>بوابة الطلاب</span>
                </a>
                <a href="/studentcode/" class="nav-button">
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
                <a href="/admin/" class="nav-button">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    <span>بوابة المسؤول</span>
                </a>
            </div>
        </div>
    </nav>

    <header>
        <div class="header-content">
            <h1>مساعد الطالب</h1>
            <p>منصتك الشاملة للموارد والأدوات الأكاديمية لطلاب كلية الحاسبات والمعلومات</p>
        </div>
    </header>

    <main>
        <div class="container">
            <section id="system-notifications">
                <h2>
                    <i class="fas fa-bell"></i>
                    رسائل المشرفين
                </h2>
                <?php if(count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                <div class="notification">
                    <div class="notification-header">
                        <?php if(isset($notification['admin_name']) && !empty($notification['admin_name'])): ?>
                        <span class="admin-name"><?php echo htmlspecialchars($notification['admin_name']); ?></span>
                        <?php else: ?>
                        <span class="admin-name">النظام</span>
                        <?php endif; ?>
                        <span class="time">
                            <i class="far fa-clock"></i>
                            <?php echo date("Y-m-d H:i", strtotime($notification['created_at'])); ?>
                        </span>
                    </div>
                    <p class="notification-message">
                        <?php 
                        $message = $notification['message'];
                        $message = safeNotificationDisplay($message);
                        echo $message;
                        ?>
                    </p>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="empty-state">
                    <i class="far fa-comment-alt"></i>
                    <p>لا توجد إشعارات حالياً</p>
                </div>
                <?php endif; ?>
            </section>

            <section class="info-section">
                <h2>
                    <i class="fas fa-star"></i>
                    مميزات البوابة
                </h2>
                <div class="features-grid">
                    <a class="feature-card" href="/study/">
                        <i class="fas fa-book"></i>
                        <h3>المواد الدراسية</h3>
                        <p>الوصول إلى ملخصات المواد والمواد الدراسية التي أنشأها قادة الطلاب</p>
                    </a>
                    <a class="feature-card" href="/studentcode/">
                        <i class="fas fa-search"></i>
                        <h3>البحث عن طالب</h3>
                        <p>ابحث عن كود الطالب الخاص بك بسهولة</p>
                    </a>
                    <a class="feature-card" href="/results/">
                        <i class="fas fa-graduation-cap"></i>
                        <h3>نتائج الطلاب</h3>
                        <p>تحقق من نتائجك الدراسية ودرجاتك</p>
                    </a>
                    <a class="feature-card" href="/links/">
                        <i class="fas fa-users"></i>
                        <h3>روابط الجروبات</h3>
                        <p>الوصول إلى جميع روابط مجموعات الطلاب الرسمية</p>
                    </a>
                </div>
            </section>

            <section class="info-section">
                <h2>
                    <i class="fas fa-question-circle"></i>
                    تحتاج مساعدة؟
                </h2>
                <div class="help-card">
                    <p>إذا واجهت أي مشاكل أثناء استخدام البوابة، يرجى التحقق من حالة النظام أو التواصل معنا:</p>
                    <div class="contact-info">
                        <a href="/status/" class="contact-link">
                            <i class="fas fa-server"></i>
                            <span>تحقق من حالة النظام</span>
                        </a>
                        <a href="https://wa.me/201152961437" target="_blank" class="contact-link">
                            <i class="fab fa-whatsapp"></i>
                            <span>تواصل عبر واتساب</span>
                        </a>
                        <a href="https://mail.google.com/mail/u/0/?view=cm&fs=1&to=contact@ichiwi.me&su=Issue:+Portal"
                            target="_blank" class="contact-link">
                            <i class="fas fa-envelope"></i>
                            <span>أرسل بريد إلكتروني</span>
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer class="copyright-footer">
        <p style="direction: ltr;">&copy; <?php echo date("Y"); ?> ichiwi.me</p>
    </footer>

    <script src="<?php echo auto_version('static/js/main.js'); ?>"></script>
</body>

</html>
