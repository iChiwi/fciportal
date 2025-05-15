<?php
require_once '../config.php';

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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>نتائج الطلاب — مساعد الطالب</title>
    <meta name="description" content="اعرف نتائجك الدراسية بسهولة من موقع الجامعة" />
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
                    <span>الصفحة الرئيسية</span>
                </a>
                <a href="/study/" class="nav-button">
                    <i class="fas fa-book" aria-hidden="true"></i>
                    <span>بوابة الطلاب</span>
                </a>
                <a href="/studentcode/" class="nav-button">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <span>البحث عن طالب</span>
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
            <h1>نتائج الطلاب</h1>
            <p>استعلم عن نتائجك الدراسية مباشرة من موقع الجامعة</p>
        </div>
    </header>

    <main>
        <div class="container">
            <section id="search-section">
                <h2>
                    <i class="fas fa-id-card"></i>
                    الرقم القومي
                </h2>
                <form id="search-form" class="standard-form" novalidate>
                    <div id="national-search">
                        <input type="text" id="national-number" name="national-number" minlength="14" maxlength="14"
                            required placeholder="ادخل الرقم القومي المكون من 14 رقم" />
                    </div>
                    <button type="submit" class="btn">عرض النتائج</button>
                </form>
                <div id="search-result"></div>
            </section>

            <section id="results-section" class="info-section gpa-calculator" style="display: none;">
                <h2>
                    <i class="fas fa-clipboard-list"></i>
                    النتائج الدراسية
                </h2>

                <div class="gpa-results">
                    <div class="gpa-row">
                        <div class="gpa-label">الاسم:</div>
                        <div class="gpa-value" id="student-name">-</div>
                    </div>
                    <div class="gpa-row">
                        <div class="gpa-label">الكود:</div>
                        <div class="gpa-value" id="student-code">-</div>
                    </div>
                    <div class="gpa-row">
                        <div class="gpa-label">الفرقة:</div>
                        <div class="gpa-value" id="student-phase">-</div>
                    </div>
                    <div class="gpa-row">
                        <div class="gpa-label">المعدل التراكمي:</div>
                        <div class="gpa-value" id="overall-gpa">0.0</div>
                    </div>
                    <div class="gpa-row">
                        <div class="gpa-label">مجموع الساعات المعتمدة:</div>
                        <div class="gpa-value" id="total-credit-hours">0</div>
                    </div>
                </div>

                <div id="semesters-container">

                </div>


                <div class="gpa-info" style="margin-top: 1.5rem;">
                    <p>
                        <i class="fas fa-info-circle"></i>
                        يتم احتساب المعدل التراكمي بناءً على مجموع (الساعات المعتمدة × درجة المادة) ÷ مجموع الساعات
                        المعتمدة.
                    </p>
                    <p>
                        <span
                            style="display: inline-block; width: 20px; height: 20px; background: var(--success-light); margin-left: 5px;"></span>
                        المواد المجتازة: حصلت فيها على تقدير 2.0 أو أعلى.
                    </p>
                    <p>
                        <span
                            style="display: inline-block; width: 20px; height: 20px; background: var(--error-light); margin-left: 5px;"></span>
                        المواد الغير مجتازة: حصلت فيها على تقدير أقل من 2.0.
                    </p>
                </div>
            </section>
        </div>
    </main>

    <footer class="copyright-footer">
        <p style="direction: ltr;">&copy; <?php echo date("Y"); ?> ichiwi.me</p>
    </footer>

    <script src="<?php echo auto_version('../static/js/main.js'); ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('search-form');
        const resultsSection = document.getElementById('results-section');
        const nationalInput = document.getElementById('national-number');
        const searchSection = document.getElementById('search-section');
        const searchResult = document.getElementById('search-result');

        nationalInput.addEventListener("input", function() {
            this.value = this.value.replace(/[^0-9]/g, "");
            if (this.value.length === 14) {
                this.classList.add("valid");
            } else {
                this.classList.remove("valid");
            }
        });

        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            const nationalNumber = nationalInput.value.trim();
            if (nationalNumber.length !== 14) {
                showError("يجب أن يكون الرقم القومي 14 رقمًا.");
                return;
            }

            searchResult.innerHTML = `
                <div class="loading-spinner" role="status">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>جارِ البحث...</span>
                </div>`;

            resultsSection.style.display = 'none';

            try {
                const response = await fetch('/api/studentresults', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        national_number: nationalNumber
                    })
                });

                // Check for non-JSON responses
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error(
                        "الخادم أرجع استجابة غير صالحة. يرجى المحاولة مرة أخرى لاحقًا.");
                }

                let data;
                try {
                    data = await response.json();
                } catch (jsonError) {
                    throw new Error(
                        "غير قادر على معالجة استجابة الخادم. يرجى المحاولة مرة أخرى لاحقًا.");
                }

                if (!response.ok) {
                    throw new Error(data.error || 'حدث خطأ في جلب النتائج');
                }

                // Clear loading spinner by hiding the search result div completely
                searchResult.style.display = 'none';
                searchResult.innerHTML = '';

                document.getElementById('student-name').textContent = data.student_name || '-';
                document.getElementById('student-code').textContent = data.student_code || '-';
                document.getElementById('student-phase').textContent = data.phase_name ||
                    'غير متوفر';

                displayResults(data.results);

                resultsSection.style.display = 'block';

            } catch (error) {
                showError(error.message || 'حدث خطأ في جلب النتائج، يرجى المحاولة مرة أخرى');
            }
        });

        function showError(message) {
            // Reset display property when showing errors
            searchResult.style.display = 'block';
            searchResult.innerHTML = `
                <div class="result-error" role="alert">
                    <span>${message}</span>
                </div>`;
        }

        function displayResults(results) {
            const semestersContainer = document.getElementById('semesters-container');

            if (!results || (Object.keys(results).length === 0)) {
                semestersContainer.innerHTML = `
                    <div class="result-error" role="alert">
                        <span>لم يتم العثور على الطالب. يرجى التحقق من المعلومات والمحاولة مرة أخرى.</span>
                    </div>`;

                document.getElementById('student-name').textContent = '-';
                document.getElementById('student-code').textContent = '-';
                document.getElementById('student-phase').textContent = 'غير متوفر';
                document.getElementById('overall-gpa').textContent = '0.0';
                document.getElementById('overall-gpa').className = 'gpa-value';
                document.getElementById('total-credit-hours').textContent = '0';
                return;
            }

            let totalCreditHours = 0;
            let totalPoints = 0;

            Object.entries(results).forEach(([semesterName, courses], index) => {

                if (!courses || courses.length === 0) {
                    return;
                }

                const semesterContainer = document.createElement('div');
                semesterContainer.className = 'semester-container';
                semesterContainer.setAttribute('data-semester-id', `semester-${index}`);

                const semesterHeader = document.createElement('div');
                semesterHeader.className = 'semester-header';
                semesterHeader.innerHTML = `<h3>${semesterName}</h3>`;

                let semesterCreditHours = 0;
                let semesterPoints = 0;

                let tableHTML = `
                    <div class="results-table-wrapper">
                        <table class="subject-list results-table">
                            <thead>
                                <tr>
                                    <th class="subject-code">كود المادة</th>
                                    <th class="subject-name">اسم المادة</th>
                                    <th class="grade">الدرجة</th>
                                    <th class="grade-letter">التقدير</th>
                                    <th class="status">الحالة</th>
                                    <th class="credit-hours">الساعات المعتمدة</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                courses.forEach(course => {
                    const creditHours = parseFloat(course.credit_hours) || 0;
                    const marks = parseFloat(course.marks) || 0;
                    const gpa = parseFloat(course.gpa) || 0;
                    const roundedGpa = gpa.toFixed(1);

                    totalCreditHours += creditHours;
                    totalPoints += (creditHours * gpa);

                    semesterCreditHours += creditHours;
                    semesterPoints += (creditHours * gpa);

                    const rowClass = gpa >= 2.0 ? 'passed' : 'failed';

                    tableHTML += `
                        <tr class="${rowClass}">
                            <td data-label="كود المادة">${course.subject_code}</td>
                            <td data-label="اسم المادة">${course.subject_name}</td>
                            <td data-label="الدرجة">${marks}</td>
                            <td data-label="التقدير">${roundedGpa}</td>
                            <td data-label="الحالة" class="status">${course.status}</td>
                            <td data-label="الساعات المعتمدة">${creditHours}</td>
                        </tr>
                    `;
                });

                tableHTML += `
                            </tbody>
                        </table>
                    </div>
                `;

                if (semesterCreditHours > 0) {
                    const semesterGPA = (semesterPoints / semesterCreditHours).toFixed(1);
                    const gpaClass = semesterGPA >= 3.0 ? 'good' : (semesterGPA >= 2.0 ? 'warning' :
                        'danger');

                    const gpaContainer = document.createElement('div');
                    gpaContainer.className = 'semester-gpa-container';
                    gpaContainer.innerHTML = `
                        <div class="semester-gpa-label">المعدل الفصلي:</div>
                        <div class="semester-gpa-value ${gpaClass}">${semesterGPA}</div>
                    `;
                    semesterHeader.appendChild(gpaContainer);
                }

                semesterContainer.appendChild(semesterHeader);
                semesterContainer.innerHTML += tableHTML;

                semestersContainer.appendChild(semesterContainer);
            });

            const overallGpa = totalCreditHours > 0 ? (totalPoints / totalCreditHours).toFixed(1) : 0;
            const gpaElement = document.getElementById('overall-gpa');
            gpaElement.textContent = overallGpa;

            if (overallGpa >= 3.0) {
                gpaElement.className = 'gpa-value good';
            } else if (overallGpa >= 2.0) {
                gpaElement.className = 'gpa-value warning';
            } else {
                gpaElement.className = 'gpa-value danger';
            }

            document.getElementById('total-credit-hours').textContent = totalCreditHours;
        }
    });
    </script>
</body>

</html>
