document.addEventListener("DOMContentLoaded", function () {
  // Cache DOM elements
  const calculatorForm = document.getElementById("gpa-calculator-form");
  const semesterContainers = document.querySelectorAll(".semester-container");

  // Constants for GPA calculation
  const MAX_GPA = 4.0;
  const CREDIT_LIMIT_GOOD = 18;
  const CREDIT_LIMIT_BAD = 12;
  const GPA_THRESHOLD = 2.0;

  // Default subject data structure
  const collegeSubjects = {
    first: {
      first: [
        { name: "التفكير الحسابي", creditHours: 3, prerequisites: [] },
        { name: "رياضيات I", creditHours: 3, prerequisites: [] },
        { name: "فيزياء الحاسب", creditHours: 3, prerequisites: [] },
        { name: "برمجة I", creditHours: 3, prerequisites: [] },
        { name: "الاحصاء والاحتمالات", creditHours: 3, prerequisites: [] },
        { name: "لغة إنجليزية", creditHours: 2, prerequisites: [] },
      ],
      second: [
        {
          name: "تراكيب محددة",
          creditHours: 3,
          prerequisites: ["رياضيات I"],
        },
        { name: "رياضيات 2", creditHours: 3, prerequisites: ["رياضيات I"] },
        { name: "بحوث العمليات", creditHours: 3, prerequisites: [] },
        {
          name: "برمجة 2",
          creditHours: 3,
          prerequisites: ["برمجة I"],
        },
        { name: "التصميم المنطقي", creditHours: 3, prerequisites: [] },
        { name: "الاخلاقيات", creditHours: 2, prerequisites: [] },
        { name: "حقوق الانسان", creditHours: 0, prerequisites: [] },
      ],
    },
    second: {
      first: [],
      second: [],
    },
    third: {
      first: [],
      second: [],
    },
    fourth: {
      first: [],
      second: [],
    },
  };

  // Initialize the calculator
  initializeCalculator();

  // Set up event listeners
  setupEventListeners();

  // Initialize the calculator with default data
  function initializeCalculator() {
    // Set up initial state
    loadSavedData();
    calculateAllGPAs();
    updateSubjectStatuses();
  }

  // Set up event listeners for interaction
  function setupEventListeners() {
    document.querySelectorAll(".subject-grade").forEach((input) => {
      input.addEventListener("input", function () {
        validateGPAInput(input);

        calculateAllGPAs();
        updateSubjectStatuses();
      });

      input.addEventListener("blur", function () {
        if (input.value !== "") {
          input.value = parseFloat(input.value).toFixed(1);
        }
      });
    });

    const recommendFailedButton = document.getElementById("recommend-failed");
    if (recommendFailedButton) {
      recommendFailedButton.addEventListener("click", function () {
        const overallGPAElement = document.getElementById("overall-gpa");
        let creditLimit = CREDIT_LIMIT_BAD;

        if (overallGPAElement) {
          const overallGPA = parseFloat(overallGPAElement.textContent);
          creditLimit =
            !isNaN(overallGPA) && overallGPA >= GPA_THRESHOLD
              ? CREDIT_LIMIT_GOOD // 18 hours if GPA >= 2.0
              : CREDIT_LIMIT_BAD; // 12 hours if GPA < 2.0
        }

        document.querySelectorAll(".subject-row").forEach((row) => {
          row.dataset.recommended = "0";
        });

        const failedSubjectNames = new Set();

        document.querySelectorAll(".subject-row").forEach((row) => {
          const gradeInput = row.querySelector(".subject-grade");
          if (gradeInput && gradeInput.value.trim() !== "") {
            const gpaValue = parseFloat(gradeInput.value);
            if (gpaValue < 2.0) {
              failedSubjectNames.add(row.dataset.subjectName);
            }
          }
        });

        const availableFailedSubjects = [];

        document.querySelectorAll(".subject-row").forEach((row) => {
          const subjectName = row.dataset.subjectName;
          const gradeInput = row.querySelector(".subject-grade");
          const creditHours = parseInt(row.dataset.creditHours) || 0;
          const prerequisites = JSON.parse(row.dataset.prerequisites || "[]");

          if (
            failedSubjectNames.has(subjectName) &&
            !gradeInput.value.trim() &&
            creditHours > 0
          ) {
            let prerequisitesMet = true;
            for (const prereq of prerequisites) {
              let prereqPassed = false;
              document.querySelectorAll(".subject-row").forEach((otherRow) => {
                if (otherRow.dataset.subjectName === prereq) {
                  const prereqInput = otherRow.querySelector(".subject-grade");
                  if (prereqInput && prereqInput.value.trim() !== "") {
                    const prereqGPA = parseFloat(prereqInput.value);
                    if (prereqGPA >= 2.0) {
                      prereqPassed = true;
                    }
                  }
                }
              });

              if (!prereqPassed) {
                prerequisitesMet = false;
                break;
              }
            }

            if (prerequisitesMet) {
              availableFailedSubjects.push({
                row: row,
                creditHours: creditHours,
              });
            }
          }
        });

        availableFailedSubjects.sort((a, b) => a.creditHours - b.creditHours);

        let totalRecommendedHours = 0;
        let recommendedCount = 0;

        for (const subject of availableFailedSubjects) {
          if (totalRecommendedHours + subject.creditHours <= creditLimit) {
            subject.row.dataset.recommended = "1";
            totalRecommendedHours += subject.creditHours;
            recommendedCount++;
          }
        }

        updateSubjectStatuses();

        if (recommendedCount > 0) {
          showMessage(
            `تم توصية ${recommendedCount} مادة راسبة للتسجيل (${totalRecommendedHours} ساعة معتمدة)`
          );
        } else {
          showMessage("لم يتم العثور على مواد راسبة للتوصية");
        }
      });
    }

    const resetButton = document.getElementById("reset-calculator");
    if (resetButton) {
      resetButton.addEventListener("click", function () {
        if (
          confirm(
            "هل أنت متأكد من رغبتك في إعادة تعيين الحاسبة؟ سيتم فقدان جميع البيانات."
          )
        ) {
          resetCalculator();
        }
      });
    }
  }

  // Validate GPA input to ensure it's between 0.0 and 4.0
  function validateGPAInput(input) {
    let value = parseFloat(input.value);

    if (isNaN(value)) {
      input.value = "";
      return;
    }

    if (value < 0) {
      input.value = "0.0";
    } else if (value > 4.0) {
      input.value = "4.0";
    }
  }

  // Calculate GPA for all semesters
  function calculateAllGPAs() {
    let totalCreditHours = 0;
    let totalPoints = 0;

    // Calculate each semester GPA
    semesterContainers.forEach((container) => {
      const semesterId = container.dataset.semesterId;
      const { semesterCreditHours, semesterPoints } =
        calculateSemesterGPA(semesterId);

      totalCreditHours += semesterCreditHours;
      totalPoints += semesterPoints;

      // Update semester GPA display
      const semesterGPAElement = container.querySelector(".semester-gpa");
      if (semesterGPAElement && semesterCreditHours > 0) {
        const semesterGPA = semesterPoints / semesterCreditHours;
        semesterGPAElement.textContent = semesterGPA.toFixed(2);
        updateGPAClass(semesterGPAElement, semesterGPA);
      }
    });

    // Calculate and update overall GPA
    const overallGPAElement = document.getElementById("overall-gpa");
    if (overallGPAElement && totalCreditHours > 0) {
      const overallGPA = totalPoints / totalCreditHours;
      overallGPAElement.textContent = overallGPA.toFixed(2);
      updateGPAClass(overallGPAElement, overallGPA);

      // Update credit hour limit info
      const creditLimitElement = document.getElementById("credit-limit");
      if (creditLimitElement) {
        const limit =
          overallGPA >= GPA_THRESHOLD ? CREDIT_LIMIT_GOOD : CREDIT_LIMIT_BAD;
        creditLimitElement.textContent = limit;
      }
    }
  }

  // Calculate GPA for a specific semester
  function calculateSemesterGPA(semesterId) {
    const container = document.querySelector(
      `[data-semester-id="${semesterId}"]`
    );
    let semesterCreditHours = 0;
    let semesterPoints = 0;

    const gradeInputs = container.querySelectorAll(".subject-grade");

    gradeInputs.forEach((input) => {
      const gpaValue = parseFloat(input.value);
      if (!isNaN(gpaValue) && input.value.trim() !== "") {
        const row = input.closest("tr");
        const creditHours = parseInt(row.dataset.creditHours) || 0;

        semesterCreditHours += creditHours;
        semesterPoints += creditHours * gpaValue;

        // Determine pass/fail status based on GPA value
        if (gpaValue < 2.0) {
          row.dataset.status = "failed";
        } else {
          row.dataset.status = "passed";
        }
      }
    });

    return { semesterCreditHours, semesterPoints };
  }

  // Update subject statuses based on prerequisites and recommendations
  function updateSubjectStatuses() {
    // Get all subjects that have been attempted
    const attemptedSubjects = {};
    const passedSubjects = {};
    const failedSubjects = {};

    document.querySelectorAll(".subject-row").forEach((row) => {
      const subjectName = row.dataset.subjectName;
      const gradeInput = row.querySelector(".subject-grade");

      if (gradeInput && gradeInput.value.trim() !== "") {
        const gpaValue = parseFloat(gradeInput.value);
        attemptedSubjects[subjectName] = gpaValue;

        if (gpaValue >= 2.0) {
          passedSubjects[subjectName] = true;
        } else {
          failedSubjects[subjectName] = true;
        }
      }
    });

    // Check prerequisites and update statuses
    document.querySelectorAll(".subject-row").forEach((row) => {
      const subjectName = row.dataset.subjectName;
      const prerequisites = JSON.parse(row.dataset.prerequisites || "[]");
      const gradeInput = row.querySelector(".subject-grade");
      const isRecommended = row.dataset.recommended === "1";

      // Remove any existing status classes
      row.classList.remove(
        "subject-locked",
        "subject-recommended",
        "subject-passed"
      );

      // If this subject has a grade, mark it as passed or failed
      if (gradeInput && gradeInput.value.trim() !== "") {
        const gpaValue = parseFloat(gradeInput.value);
        if (gpaValue >= 2.0) {
          row.classList.add("subject-passed");
        } else {
          // Failed subjects have no special highlight beyond the grade text color
        }
      }

      // Check if any prerequisites have been failed
      const hasFailedPrerequisite = prerequisites.some(
        (prereq) =>
          prereq in attemptedSubjects && attemptedSubjects[prereq] < 2.0
      );

      // Check if all prerequisites have been passed
      const allPrerequisitesPassed =
        prerequisites.length > 0 &&
        prerequisites.every((prereq) => passedSubjects[prereq]);

      if (hasFailedPrerequisite) {
        row.classList.remove("subject-passed");
        row.classList.add("subject-locked");
      } else if (
        (allPrerequisitesPassed || isRecommended) &&
        !row.classList.contains("subject-passed")
      ) {
        row.classList.add("subject-recommended");
      }
    });
  }

  function updateGPAClass(element, gpa) {
    element.classList.remove("good", "warning", "danger");

    if (gpa >= 3.0) {
      element.classList.add("good");
    } else if (gpa >= 2.0) {
      element.classList.add("warning");
    } else {
      element.classList.add("danger");
    }
  }

  // Reset the calculator
  function resetCalculator() {
    document.querySelectorAll(".subject-grade").forEach((input) => {
      input.value = "";
    });

    // Clear recommendations
    document.querySelectorAll(".subject-row").forEach((row) => {
      row.dataset.recommended = "0";
    });

    calculateAllGPAs();
    updateSubjectStatuses();
    localStorage.removeItem("gpaCalculatorData");
    showMessage("تم إعادة تعيين الحاسبة بنجاح");
  }

  // Show a message to the user
  function showMessage(message) {
    const messageElement = document.createElement("div");
    messageElement.classList.add("message");
    messageElement.textContent = message;

    document.body.appendChild(messageElement);

    setTimeout(() => {
      messageElement.classList.add("show");

      setTimeout(() => {
        messageElement.classList.remove("show");
        setTimeout(() => {
          messageElement.remove();
        }, 300);
      }, 2000);
    }, 100);
  }
});
