document.addEventListener("DOMContentLoaded", function () {
  // Handle error messages auto-disappear
  const errorMessages = document.querySelectorAll(".error-message");
  if (errorMessages.length > 0) {
    setTimeout(function () {
      errorMessages.forEach(function (message) {
        message.classList.add("fade-out");

        setTimeout(function () {
          message.style.display = "none";
        }, 500);
      });
    }, 5000); // 5 seconds delay before hiding

    // Reset form fields when error is shown
    const loginForm = document.querySelector(".login-form");
    if (loginForm) {
      setTimeout(function () {
        const usernameField = loginForm.querySelector('input[type="text"]');
        if (usernameField) {
          usernameField.value = "";
        }

        const passwordField = loginForm.querySelector('input[type="password"]');
        if (passwordField) {
          passwordField.value = "";
        }

        if (usernameField) {
          usernameField.focus();
        }
      }, 6000);
    }
  }

  // Allow users to dismiss error messages by clicking on them
  errorMessages.forEach(function (message) {
    message.addEventListener("click", function () {
      message.classList.add("fade-out");
      setTimeout(function () {
        message.style.display = "none";
      }, 500);
    });
  });
});
