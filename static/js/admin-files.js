/**
 * Admin File Manager JavaScript
 * Provides functionality for browsing, creating folders, uploading, and managing files
 */
document.addEventListener("DOMContentLoaded", function () {
  // Elements
  const subjectSelect = document.getElementById("subject");
  const filesContainer = document.getElementById("files-container");
  const backButton = document.getElementById("back-button");
  const newFolderBtn = document.getElementById("new-folder-btn");
  const newFolderForm = document.getElementById("new-folder-form");
  const folderNameInput = document.getElementById("new-folder-name");
  const createFolderBtn = document.getElementById("create-folder-btn");
  const cancelFolderBtn = document.getElementById("cancel-folder-btn");
  const toggleUploadFormBtn = document.getElementById("toggle-upload-form");
  const uploadForm = document.getElementById("inline-upload-form");
  const fileInput = document.getElementById("summary-file");
  const customFileLabel = document.getElementById("custom-file-label");
  const uploadButton = document.getElementById("upload-button");
  const cancelUploadBtn = document.getElementById("cancel-upload-btn");
  const uploadStatus = document.getElementById("upload-status");
  const folderPathInput = document.getElementById("folder-path");
  const currentFolderDisplay = document.getElementById("current-folder");

  // State variables
  let currentSubject = "";
  let currentPath = "";
  let isDebugMode = false;

  // Enable debug mode with double Shift key press
  let shiftCount = 0;
  document.addEventListener("keydown", function (e) {
    if (e.key === "Shift") {
      shiftCount++;
      if (shiftCount === 2) {
        isDebugMode = !isDebugMode;
        if (isDebugMode) {
          showNotification("تم تفعيل وضع التصحيح");
        } else {
          showNotification("تم إيقاف وضع التصحيح");
          // Remove any debug info elements
          document.querySelectorAll(".debug-info").forEach((el) => el.remove());
        }
        shiftCount = 0;
      }
      setTimeout(() => {
        shiftCount = 0;
      }, 500);
    }
  });

  // Event Listeners
  if (subjectSelect) {
    subjectSelect.addEventListener("change", loadSubjectFiles);
  }
  if (backButton) {
    backButton.addEventListener("click", navigateBack);
  }
  if (newFolderBtn) {
    newFolderBtn.addEventListener("click", showNewFolderForm);
  }
  if (createFolderBtn) {
    createFolderBtn.addEventListener("click", createFolder);
  }
  if (cancelFolderBtn) {
    cancelFolderBtn.addEventListener("click", hideNewFolderForm);
  }
  if (toggleUploadFormBtn) {
    toggleUploadFormBtn.addEventListener("click", toggleUploadForm);
  }
  if (fileInput) {
    fileInput.addEventListener("change", updateFileLabel);
  }
  if (uploadButton) {
    uploadButton.addEventListener("click", uploadFile);
  }
  if (cancelUploadBtn) {
    cancelUploadBtn.addEventListener("click", hideUploadForm);
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
    newFolderBtn.disabled = false;

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
    if (isDebugMode && data.debug) {
      console.log("Server response:", data);
    }

    if (data.error) {
      filesContainer.innerHTML = `<div class="error-message"><i class="fas fa-exclamation-triangle"></i> ${data.error}</div>`;
      if (isDebugMode && data.debug) {
        addDebugInfo(data.debug);
      }
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

        if (isFolder) {
          // Folder actions
          html += `
            <button class="btn btn-danger delete-folder-btn" title="حذف المجلد" data-path="${item.path}">
              <i class="fas fa-trash-alt"></i>
            </button>
          `;
        } else {
          // File actions
          html += `
            <a href="${item.url}" class="btn download-action" title="تحميل الملف" download>
              <i class="fas fa-download"></i>
            </a>
            <a href="${item.url}" class="btn view-action" title="عرض الملف" target="_blank">
              <i class="fas fa-eye"></i>
            </a>
            <button class="btn btn-secondary reorder-file-btn" title="إعادة ترتيب الملف" data-name="${item.name}">
              <i class="fas fa-sort-numeric-down"></i>
            </button>
            <button class="btn btn-danger delete-file-btn" title="حذف الملف" data-name="${item.name}" data-path="${item.path}">
              <i class="fas fa-trash-alt"></i>
            </button>
          `;
        }

        html += `</div>
        </div>`;
      });

      html += "</div>";
    }

    if (isDebugMode && data.debug) {
      addDebugInfo(data.debug);
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

    // Delete folder button events
    document.querySelectorAll(".delete-folder-btn").forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.stopPropagation(); // Prevent folder navigation
        const folderPath = this.getAttribute("data-path");
        if (
          confirm("هل أنت متأكد من أنك تريد حذف هذا المجلد وجميع محتوياته؟")
        ) {
          deleteFolder(folderPath);
        }
      });
    });

    // Delete file button events
    document.querySelectorAll(".delete-file-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        const filePath = this.getAttribute("data-path");
        if (confirm("هل أنت متأكد من أنك تريد حذف هذا الملف؟")) {
          deleteFile(filePath);
        }
      });
    });

    // Reorder file button events
    document.querySelectorAll(".reorder-file-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        const filename = this.getAttribute("data-name");
        const newOrder = prompt("أدخل الترتيب الجديد للملف (1-99):", "");

        if (newOrder !== null) {
          const orderNum = parseInt(newOrder);
          if (!isNaN(orderNum) && orderNum > 0 && orderNum < 100) {
            reorderFile(filename, orderNum);
          } else {
            alert("الرجاء إدخال رقم صحيح بين 1 و 99.");
          }
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
   * Show the new folder form
   */
  function showNewFolderForm() {
    newFolderForm.style.display = "block";
    folderNameInput.value = "";
    folderNameInput.focus();

    // Hide the upload form if visible
    uploadForm.style.display = "none";
  }

  /**
   * Hide the new folder form
   */
  function hideNewFolderForm() {
    newFolderForm.style.display = "none";
  }

  /**
   * Create a new folder
   */
  function createFolder() {
    const folderName = folderNameInput.value.trim();

    if (!folderName) {
      alert("الرجاء إدخال اسم المجلد");
      return;
    }

    if (!/^[a-zA-Z0-9_\-\s]+$/.test(folderName)) {
      alert(
        "اسم المجلد يجب أن يحتوي على أحرف إنجليزية، أرقام، مسافات، أو شرطات فقط"
      );
      return;
    }

    const formData = new FormData();
    formData.append("create_folder", "true");
    formData.append("subject", currentSubject);
    formData.append("folderPath", currentPath);
    formData.append("folderName", folderName);

    // Show loading state
    createFolderBtn.disabled = true;
    createFolderBtn.innerHTML =
      '<i class="fas fa-sync fa-spin"></i> جاري الإنشاء...';

    fetch("upload_handler.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          hideNewFolderForm();
          showNotification("تم إنشاء المجلد بنجاح");
          navigateToFolder(data.folderPath);
        } else {
          alert("فشل في إنشاء المجلد: " + (data.error || "خطأ غير معروف"));
          if (isDebugMode && data.debug) {
            addDebugInfo(data.debug);
          }
        }
      })
      .catch((error) => {
        console.error("خطأ في إنشاء المجلد:", error);
        alert("حدث خطأ أثناء إنشاء المجلد");
      })
      .finally(() => {
        // Reset button state
        createFolderBtn.disabled = false;
        createFolderBtn.innerHTML = "إنشاء";
      });
  }

  /**
   * Toggle the upload form visibility
   */
  function toggleUploadForm() {
    if (uploadForm.style.display === "none" || !uploadForm.style.display) {
      uploadForm.style.display = "block";
      customFileLabel.innerHTML = "اختر ملف للتحميل";
      fileInput.value = "";
      document.getElementById("file-order").value = "";
    } else {
      uploadForm.style.display = "none";
    }
  }

  /**
   * Hide the upload form
   */
  function hideUploadForm() {
    uploadForm.style.display = "none";
  }

  /**
   * Update the custom file input label with the selected filename
   */
  function updateFileLabel() {
    if (fileInput.files.length > 0) {
      customFileLabel.innerHTML = fileInput.files[0].name;
    } else {
      customFileLabel.innerHTML = "اختر ملف للتحميل";
    }
  }

  /**
   * Upload a file
   */
  function uploadFile() {
    if (!fileInput.files.length) {
      alert("الرجاء اختيار ملف للتحميل");
      return;
    }

    const fileSize = fileInput.files[0].size;
    const maxSize = 50 * 1024 * 1024; // 50MB

    if (fileSize > maxSize) {
      alert("حجم الملف كبير جدًا. الحد الأقصى هو 50 ميجابايت.");
      return;
    }

    const formData = new FormData();
    formData.append("file", fileInput.files[0]);
    formData.append("subject", currentSubject);
    formData.append("folderPath", currentPath);

    const orderInput = document.getElementById("file-order");
    if (orderInput && orderInput.value) {
      formData.append("order", orderInput.value);
    }

    // Show loading state
    uploadButton.disabled = true;
    uploadButton.innerHTML =
      '<i class="fas fa-sync fa-spin"></i> جاري التحميل...';
    uploadStatus.innerHTML = "جاري تحميل الملف...";
    uploadStatus.style.display = "block";

    fetch("upload_handler.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          uploadStatus.innerHTML = "تم تحميل الملف بنجاح";
          uploadStatus.style.display = "block";

          // Reset form
          fileInput.value = "";
          customFileLabel.innerHTML = "اختر ملف للتحميل";
          document.getElementById("file-order").value = "";

          // Refresh the file list
          navigateToFolder(currentPath);

          // Hide the upload form after a delay
          setTimeout(() => {
            hideUploadForm();
            uploadStatus.style.display = "none";
          }, 2000);
        } else {
          uploadStatus.innerHTML =
            "فشل في تحميل الملف: " + (data.error || "خطأ غير معروف");
          uploadStatus.style.display = "block";
          uploadStatus.className = "error-message";

          if (isDebugMode && data.debug) {
            addDebugInfo(data.debug);
          }
        }
      })
      .catch((error) => {
        console.error("خطأ في تحميل الملف:", error);
        uploadStatus.innerHTML = "حدث خطأ أثناء تحميل الملف";
        uploadStatus.style.display = "block";
        uploadStatus.className = "error-message";
      })
      .finally(() => {
        // Reset button state
        uploadButton.disabled = false;
        uploadButton.innerHTML = "تحميل الملف";
      });
  }

  /**
   * Delete a file
   */
  function deleteFile(filePath) {
    const formData = new FormData();
    formData.append("delete_file", "true");
    formData.append("subject", currentSubject);
    formData.append("filePath", filePath);

    fetch("upload_handler.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification("تم حذف الملف بنجاح");
          // Refresh the file list
          navigateToFolder(currentPath);
        } else {
          alert("فشل في حذف الملف: " + (data.error || "خطأ غير معروف"));
          if (isDebugMode && data.debug) {
            addDebugInfo(data.debug);
          }
        }
      })
      .catch((error) => {
        console.error("خطأ في حذف الملف:", error);
        alert("حدث خطأ أثناء حذف الملف");
      });
  }

  /**
   * Delete a folder
   */
  function deleteFolder(folderPath) {
    const formData = new FormData();
    formData.append("delete_folder", "true");
    formData.append("subject", currentSubject);
    formData.append("folderPath", folderPath);

    fetch("upload_handler.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification("تم حذف المجلد بنجاح");
          // Refresh the file list - go back to parent folder if needed
          if (currentPath === folderPath) {
            navigateBack();
          } else {
            navigateToFolder(currentPath);
          }
        } else {
          alert("فشل في حذف المجلد: " + (data.error || "خطأ غير معروف"));
          if (isDebugMode && data.debug) {
            addDebugInfo(data.debug);
          }
        }
      })
      .catch((error) => {
        console.error("خطأ في حذف المجلد:", error);
        alert("حدث خطأ أثناء حذف المجلد");
      });
  }

  /**
   * Reorder a file
   */
  function reorderFile(filename, newOrder) {
    const formData = new FormData();
    formData.append("reorder_file", "true");
    formData.append("subject", currentSubject);
    formData.append("folderPath", currentPath);
    formData.append("filename", filename);
    formData.append("newOrder", newOrder);

    fetch("upload_handler.php", {
      method: "POST",
      body: formData,
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          showNotification("تم إعادة ترتيب الملف بنجاح");
          // Refresh the file list
          navigateToFolder(currentPath);
        } else {
          alert("فشل في إعادة ترتيب الملف: " + (data.error || "خطأ غير معروف"));
          if (isDebugMode && data.debug) {
            addDebugInfo(data.debug);
          }
        }
      })
      .catch((error) => {
        console.error("خطأ في إعادة ترتيب الملف:", error);
        alert("حدث خطأ أثناء إعادة ترتيب الملف");
      });
  }

  /**
   * Show a notification
   */
  function showNotification(message) {
    // Create notification element
    const notification = document.createElement("div");
    notification.className = "notification-toast";
    notification.innerHTML = `<div class="notification-content">${message}</div>`;

    // Add to DOM
    document.body.appendChild(notification);

    // Trigger animation
    setTimeout(() => {
      notification.classList.add("show");
    }, 10);

    // Remove after delay
    setTimeout(() => {
      notification.classList.remove("show");
      setTimeout(() => {
        notification.remove();
      }, 300);
    }, 3000);
  }

  /**
   * Add debug information
   */
  function addDebugInfo(debugData) {
    if (!isDebugMode) return;

    const debugContainer = document.createElement("div");
    debugContainer.className = "debug-info";
    debugContainer.innerHTML = `
            <h3>معلومات التصحيح</h3>
            <pre>${JSON.stringify(debugData, null, 2)}</pre>
        `;

    filesContainer.insertAdjacentElement("afterend", debugContainer);
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

      currentFolderDisplay.classList.add("highlight-animation");
      setTimeout(() => {
        currentFolderDisplay.classList.remove("highlight-animation");
      }, 1500);
    }
  }
});
