<?php
session_start();

// Database connection | Ensure you have a config.php file with the correct database connection settings
require '../config.php';

// Check if the user is already logged in and verifying the session
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT password FROM admins WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin'] = $username;
        header("Location: home.php");
        exit();
    } else {
        $error = "كلمة المرور أو اسم المستخدم غير صحيح.";
    }
}

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
    <title>تسجيل الدخول — بوابة الإدارة</title>
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
            </div>
        </div>
    </nav>

    <!-- Header Container -->
    <header>
        <div class="header-content">
            <h1>تسجيل دخول الإدارة</h1>
            <p>متاح فقط لقادة الطلاب</p>
        </div>
    </header>

    <!-- Main Content -->
    <main>
        <div class="container">
            <!-- Login Section -->
            <section class="info-section">
                <h2>
                    <i class="fas fa-sign-in-alt"></i>
                    تسجيل الدخول
                </h2>
                <form method="post" action="login.php" class="login-form">
                    <?php if(isset($error)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="username"><strong>اسم المستخدم</strong></label>
                        <input type="text" name="username" id="username" dir="ltr" required />
                    </div>
                    <div class="form-group">
                        <label for="password"><strong>كلمة المرور</strong></label>
                        <input type="password" name="password" id="password" required />
                    </div>
                    <button type="submit" class="btn">تسجيل الدخول</button>
                </form>
            </section>
        </div>
    </main>

    <!-- Footer Message -->
    <footer class="copyright-footer">
        <p style="direction: ltr;">&copy; <?php echo date("Y"); ?> ichiwi.me</p>
    </footer>

    <script src="<?php echo auto_version('../static/js/main.js'); ?>"></script>
    <script src="<?php echo auto_version('../static/js/forms.js'); ?>"></script>
</body>

</html>
