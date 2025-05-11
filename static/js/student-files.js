/**
 * Student File Browser JavaScript
 * Provides functionality for browsing, viewing, and downloading files
 */
document.addEventListener("DOMContentLoaded", function () {
  // Elements
  const subjectSelect = document.getElementById("subject-select");
  const filesContainer = document.getElementById("files-container");
  const backButton = document.getElementById("back-button");
  const currentFolderDisplay = document.getElementById("current-folder");
  const folderPathInput = document.getElementById("folder-path");

  // State variables
  let currentSubject = "";
  let currentPath = "";

  // Event Listeners
  if (subjectSelect) {
    subjectSelect.addEventListener("change", loadSubjectFiles);
  }
  if (backButton) {
    backButton.addEventListener("click", navigateBack);
  }

  /**
   * Load files for the selected subject
   */
  function loadSubjectFiles() {
    currentSubject = subjectSelect.value;
    currentPath = "";
    folderPathInput.value = "";
    updateCurrentFolder();

    if (!currentSubject) return;

    filesContainer.innerHTML =
      '<div class="loading-spinner"><i class="fas fa-sync fa-spin"></i> جاري التحميل...</div>';
    backButton.disabled = true;

    fetch(`get_files.php?subject=${currentSubject}&path=${currentPath}`)
      .then((response) => response.json())
      .then((data) => {
        displayFiles(data);
      })
      .catch((error) => {
        console.error("خطأ في تحميل الملفات:", error);
        filesContainer.innerHTML =
          '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> فشل في تحميل الملفات.</div>';
      });
  }

  /**
   * Display files and folders in the container
   */
  function displayFiles(data) {
    if (data.error) {
      filesContainer.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ${data.error}</div>`;
      return;
    }

    // Create breadcrumbs
    let html = "";
    html += renderBreadcrumb(data.subject, data.currentPath);

    if (!data.items || data.items.length === 0) {
      html +=
        '<div class="empty-message"><i class="fas fa-folder-open"></i> لا توجد ملفات أو مجلدات في هذا المجلد.</div>';
    } else {
      // Sort items - folders first, then files
      const items = [...data.items];
      items.sort((a, b) => {
        // Type comparison (folders first)
        if (a.type !== b.type) {
          return a.type === "folder" ? -1 : 1;
        }
        // For files, consider order
        if (a.type === "file" && a.order !== b.order) {
          return a.order - b.order;
        }
        // Alphabetical sort
        return a.name.localeCompare(b.name);
      });

      // Create the file list view
      html += '<div class="file-list-view">';

      items.forEach((item) => {
        const isFolder = item.type === "folder";
        const itemIcon = isFolder ? "fa-folder" : item.icon;
        const itemName = item.name;
        const orderBadge =
          !isFolder && item.order > 0
            ? `<span class="order-badge">${item.order}</span>`
            : "";

        html += `<div class="file-list-item ${
          isFolder ? "folder-item" : "file-item"
        }">
          <div class="file-list-icon">
            <i class="fas ${itemIcon}"></i>
          </div>
          <div class="file-list-details" ${
            isFolder ? 'data-path="' + item.path + '"' : ""
          }>
            <div class="file-list-name">
              ${orderBadge} ${itemName}
            </div>
          </div>
          <div class="file-list-actions">`;

        if (!isFolder) {
          // File actions for students (download and view only)
          html += `
            <a href="${item.url}" class="btn download-action" title="تحميل الملف" download>
              <i class="fas fa-download"></i>
            </a>
            <a href="${item.url}" class="btn view-action" title="عرض الملف" target="_blank">
              <i class="fas fa-eye"></i>
            </a>
          `;
        }

        html += `</div>
        </div>`;
      });

      html += "</div>";
    }

    filesContainer.innerHTML = html;

    // Add event listeners to the dynamically created elements
    addEventListeners();

    // Update UI state based on current path
    backButton.disabled = !data.currentPath;
  }

  /**
   * Render breadcrumb navigation
   */
  function renderBreadcrumb(subject, path) {
    const subjectNames = {
      or: "بحوث العمليات",
      discrete: "التراكيب المحددة",
      ethics: "الاخلاقيات",
      dld: "التصميم المنطقي",
      maths: "رياضيات II",
      hr: "حقوق الانسان",
    };

    const subjectName = subjectNames[subject] || subject;

    let html = '<div class="breadcrumb">';
    html += `<span class="breadcrumb-item root" data-path=""><i class="fas fa-home"></i> ${subjectName}</span>`;

    if (path) {
      const parts = path.split("/");
      let currentPath = "";

      parts.forEach((part, index) => {
        currentPath += (index > 0 ? "/" : "") + part;
        const isLast = index === parts.length - 1;

        html += '<span class="breadcrumb-separator">/</span>';

        if (isLast) {
          html += `<span class="breadcrumb-item active">${part}</span>`;
        } else {
          html += `<span class="breadcrumb-item" data-path="${currentPath}">${part}</span>`;
        }
      });
    }

    html += "</div>";
    return html;
  }

  /**
   * Add event listeners to dynamically created elements
   */
  function addEventListeners() {
    // Folder click events in list view
    document
      .querySelectorAll(".file-list-item.folder-item .file-list-details")
      .forEach((item) => {
        item.addEventListener("click", function (e) {
          const folderPath = this.getAttribute("data-path");
          navigateToFolder(folderPath);
        });
      });

    // Breadcrumb navigation
    document.querySelectorAll(".breadcrumb-item").forEach((item) => {
      item.addEventListener("click", function () {
        const path = this.getAttribute("data-path");
        if (path !== undefined) {
          // Check if data-path exists
          navigateToFolder(path);
        }
      });
    });
  }

  /**
   * Navigate to a specific folder
   */
  function navigateToFolder(folderPath) {
    currentPath = folderPath;
    folderPathInput.value = folderPath;
    updateCurrentFolder();

    filesContainer.innerHTML =
      '<div class="loading-spinner"><i class="fas fa-sync fa-spin"></i> جاري التحميل...</div>';

    fetch(`get_files.php?subject=${currentSubject}&path=${currentPath}`)
      .then((response) => response.json())
      .then((data) => {
        displayFiles(data);
      })
      .catch((error) => {
        console.error("خطأ في تحميل الملفات:", error);
        filesContainer.innerHTML =
          '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> فشل في تحميل الملفات.</div>';
      });
  }

  /**
   * Navigate back to the parent folder
   */
  function navigateBack() {
    if (!currentPath) return;

    const pathParts = currentPath.split("/");
    // Remove the last part to go up one level
    pathParts.pop();
    currentPath = pathParts.join("/");
    folderPathInput.value = currentPath;
    updateCurrentFolder();

    filesContainer.innerHTML =
      '<div class="loading-spinner"><i class="fas fa-sync fa-spin"></i> جاري التحميل...</div>';

    fetch(`get_files.php?subject=${currentSubject}&path=${currentPath}`)
      .then((response) => response.json())
      .then((data) => {
        displayFiles(data);
      })
      .catch((error) => {
        console.error("خطأ في التنقل للخلف:", error);
        filesContainer.innerHTML =
          '<div class="error-message"><i class="fas fa-exclamation-triangle"></i> فشل في التنقل للخلف.</div>';
      });
  }

  /**
   * Update the current folder display
   */
  function updateCurrentFolder() {
    if (currentFolderDisplay) {
      const subjectNames = {
        or: "بحوث العمليات",
        discrete: "التراكيب المحددة",
        ethics: "الاخلاقيات",
        dld: "التصميم المنطقي",
        maths: "رياضيات II",
        hr: "حقوق الانسان",
      };

      const subjectName = subjectNames[currentSubject] || currentSubject;

      if (currentPath) {
        currentFolderDisplay.value = `${subjectName}/${currentPath}/`;
      } else {
        currentFolderDisplay.value = `${subjectName}/`;
      }

      // Add animation class if supported
      if (currentFolderDisplay.classList) {
        currentFolderDisplay.classList.add("highlight-animation");
        setTimeout(() => {
          currentFolderDisplay.classList.remove("highlight-animation");
        }, 1500);
      }
    }
  }

  // Populate subject select on page load
  function populateSubjectSelect() {
    if (subjectSelect) {
      // Add options to the subject select
      const subjects = [
        { value: "or", label: "بحوث العمليات" },
        { value: "discrete", label: "التراكيب المحددة" },
        { value: "ethics", label: "الاخلاقيات" },
        { value: "dld", label: "التصميم المنطقي" },
        { value: "maths", label: "رياضيات II" },
        { value: "hr", label: "حقوق الانسان" },
      ];

      subjects.forEach((subject) => {
        const option = document.createElement("option");
        option.value = subject.value;
        option.textContent = subject.label;
        subjectSelect.appendChild(option);
      });
    }
  }

  // Initialize
  populateSubjectSelect();
});
