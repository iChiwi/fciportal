<?php
require_once '../config.php';

function auto_version($file) {
    if (file_exists($file)) {
        return $file . '?v=' . filemtime($file);
    }
    return $file;
}

// Define the subject data structure
$collegeYears = [
    'first' => 'السنة الأولى',
    'second' => 'السنة الثانية',
    'third' => 'السنة الثالثة',
    'fourth' => 'السنة الرابعة'
];

$semesters = [
    'first' => 'الفصل الأول',
    'second' => 'الفصل الثاني'
];

// Define the subjects with prerequisites
$collegeSubjects = [
    'first' => [
        'first' => [
            ['name' => 'التفكير الحسابي', 'creditHours' => 3, 'prerequisites' => [], 'recommended' => 0],
            ['name' => 'رياضيات I', 'creditHours' => 3, 'prerequisites' => [], 'recommended' => 1],
            ['name' => 'فيزياء الحاسب', 'creditHours' => 3, 'prerequisites' => [], 'recommended' => 0],
            ['name' => 'برمجة I', 'creditHours' => 3, 'prerequisites' => [], 'recommended' => 1],
            ['name' => 'الاحصاء والاحتمالات', 'creditHours' => 3, 'prerequisites' => [], 'recommended' => 0],
            ['name' => 'لغة إنجليزية', 'creditHours' => 2, 'prerequisites' => [], 'recommended' => 0]
        ],
        'second' => [
            ['name' => 'تراكيب محددة', 'creditHours' => 3, 'prerequisites' => ['رياضيات I'], 'recommended' => 0],
            ['name' => 'رياضيات 2', 'creditHours' => 3, 'prerequisites' => ['رياضيات I'], 'recommended' => 1],
            ['name' => 'بحوث العمليات', 'creditHours' => 3, 'prerequisites' => [], 'recommended' => 1],
            ['name' => 'برمجة 2', 'creditHours' => 3, 'prerequisites' => ['برمجة I'], 'recommended' => 1],
            ['name' => 'التصميم المنطقي', 'creditHours' => 3, 'prerequisites' => [], 'recommended' => 1],
            ['name' => 'الاخلاقيات', 'creditHours' => 2, 'prerequisites' => [], 'recommended' => 0],
            ['name' => 'حقوق الانسان', 'creditHours' => 0, 'prerequisites' => [], 'recommended' => 1]
        ]
    ],
    'second' => [
        'first' => [],
        'second' => [] // Left empty as per requirements
    ],
    'third' => [
        'first' => [],
        'second' => [] // Left empty as per requirements
    ],
    'fourth' => [
        'first' => [], // Left empty as per requirements
        'second' => []  // Left empty as per requirements
    ]
];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>حاسبة المعدل التراكمي — مساعد الطالب</title>
    <meta name="description" content="حاسبة المعدل التراكمي لطلاب كلية الحاسبات والمعلومات" />
    <link href="<?php echo auto_version('../static/css/main.css'); ?>" rel="stylesheet">
    <link rel="apple-touch-icon" sizes="180x180" href="../static/img/apple-touch-icon.png" />
    <link rel="icon" type="image/png" sizes="32x32" href="../static/img/favicon-32x32.png" />
    <link rel="icon" type="image/png" sizes="16x16" href="../static/img/favicon-16x16.png" />
    <link rel="manifest" href="../static/img/site.webmanifest" />
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
                <a href="/" class="nav-button">
                    <i class="fas fa-home" aria-hidden="true"></i>
                    <span>الرئيسية</span>
                </a>
                <a href="/student/" class="nav-button">
                    <i class="fas fa-book" aria-hidden="true"></i>
                    <span>بوابة الطلاب</span>
                </a>
                <a href="/lookup/" class="nav-button">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>البحث عن طالب</span>
                </a>
                <a href="/links/" class="nav-button">
                    <i class="fas fa-link" aria-hidden="true"></i>
                    <span>روابط الجروبات</span>
                </a>
            </div>
        </div>
    </nav>

    <header>
        <div class="header-content">
            <h1>حاسبة المعدل التراكمي</h1>
            <p>احسب معدلك التراكمي وتعرف على المواد المناسبة للتسجيل</p>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- GPA Results Section -->
            <section class="gpa-calculator">
                <div class="gpa-results">
                    <div class="gpa-row">
                        <div class="gpa-label">المعدل التراكمي الكلي:</div>
                        <div class="gpa-value" id="overall-gpa">0.00</div>
                    </div>
                    <div class="gpa-row">
                        <div class="gpa-label">الحد الأقصى للساعات المعتمدة:</div>
                        <div class="gpa-value" id="credit-limit">12</div>
                    </div>
                </div>

                <div class="calculator-controls" style="margin-bottom: 1rem;">
                    <div class="control-group">
                        <button type="button" id="reset-calculator" class="btn danger">
                            <i class="fas fa-trash"></i>
                            إعادة تعيين
                        </button>
                    </div>
                </div>

                <div class="gpa-info">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        يتم احتساب المعدل التراكمي بناءً على مجموع (الساعات المعتمدة × درجة المادة) ÷ مجموع الساعات
                        المعتمدة.
                        إذا كان معدلك التراكمي 2.0 أو أعلى، يمكنك تسجيل حتى 18 ساعة معتمدة، وإلا فالحد الأقصى هو 12 ساعة
                        معتمدة.
                    </p>
                </div>

                <form id="gpa-calculator-form">
                    <?php
                    // Only show the first two years and third year first semester as per requirements
                    foreach ($collegeYears as $yearKey => $yearName):
                        if ($yearKey == 'fourth') continue;
                    foreach ($semesters as $semesterKey => $semesterName):
                            // Skip the second semester of third year
                            if ($yearKey == 'third' && $semesterKey == 'second') continue;

                            // Skip if there are no subjects
                            if (empty($collegeSubjects[$yearKey][$semesterKey])) continue;

                            $semesterId = "{$yearKey}-{$semesterKey}";
                            ?>
                    <div class="semester-container" data-semester-id="<?php echo $semesterId; ?>">
                        <div class="semester-header">
                            <h3><?php echo "{$yearName} - {$semesterName}"; ?></h3>
                            <div class="semester-gpa-container">
                                المعدل: <span class="semester-gpa">0.00</span>
                            </div>
                        </div>

                        <table class="subject-list">
                            <thead>
                                <tr>
                                    <th class="subject-name">اسم المادة</th>
                                    <th class="credit-hours">الساعات المعتمدة</th>
                                    <th class="prerequisites">المتطلبات السابقة</th>
                                    <th class="grade">التقدير</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($collegeSubjects[$yearKey][$semesterKey] as $subjectIndex => $subject): 
                                            $subjectId = "{$yearKey}-{$semesterKey}-{$subjectIndex}";
                                            $prerequisitesJson = json_encode($subject['prerequisites']);
                                            $recommended = isset($subject['recommended']) ? $subject['recommended'] : 0;
                                        ?>
                                <tr class="subject-row" data-subject-id="<?php echo $subjectId; ?>"
                                    data-subject-name="<?php echo $subject['name']; ?>"
                                    data-credit-hours="<?php echo $subject['creditHours']; ?>"
                                    data-prerequisites='<?php echo $prerequisitesJson; ?>'
                                    data-recommended="<?php echo $recommended; ?>">
                                    <td><?php echo $subject['name']; ?></td>
                                    <td><?php echo $subject['creditHours']; ?></td>
                                    <td>
                                        <?php 
                                                if (!empty($subject['prerequisites'])) {
                                                    echo implode(', ', $subject['prerequisites']);
                                                } else {
                                                    echo "—";
                                                }
                                                ?>
                                    </td>
                                    <td>
                                        <input type="number" class="subject-grade"
                                            data-subject-id="<?php echo $subjectId; ?>" min="0" max="4" step="0.1"
                                            placeholder="0.0-4.0">
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php 
                        endforeach;
                    endforeach; 
                    ?>

                    <div class="calculator-controls" style="display: none;">
                        <!-- Hide the bottom controls -->
                        <div class="control-group">
                            <button type="button" id="save-data" class="btn primary">
                                <i class="fas fa-save"></i>
                                حفظ البيانات
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Updated Legend -->
                <div class="gpa-info" style="margin-top: 1.5rem;">
                    <p>
                        <span
                            style="display: inline-block; width: 20px; height: 20px; background: var(--success-light); margin-left: 5px;"></span>
                        المواد المجتازة: حصلت فيها على تقدير 2.0 أو أعلى.
                    </p>
                    <p>
                        <span
                            style="display: inline-block; width: 20px; height: 20px; background: var(--error-light); margin-left: 5px;"></span>
                        المواد المغلقة: رسبت في المتطلبات السابقة لهذه المادة.
                    </p>
                    <p>
                        <span
                            style="display: inline-block; width: 20px; height: 20px; background: var(--primary-light); margin-left: 5px;"></span>
                        المواد الموصى بها: اجتزت جميع المتطلبات السابقة لهذه المادة.
                    </p>
                    <p>
                        <span
                            style="display: inline-block; width: 20px; height: 20px; background: white; border: 1px solid #ccc; margin-left: 5px;"></span>
                        المواد المحايدة: لا توجد متطلبات سابقة أو لم تكتمل جميع المتطلبات بعد.
                    </p>
                </div>
            </section>
        </div>
    </main>

    <footer class="copyright-footer">
        <p style="direction: ltr;">&copy; <?php echo date("Y"); ?> ichiwi.me</p>
    </footer>

    <script src="<?php echo auto_version('../static/js/gpa-calculator.js'); ?>"></script>
</body>

</html>
