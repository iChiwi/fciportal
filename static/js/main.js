document.addEventListener("DOMContentLoaded", function () {
  // Navigation selectors
  const hamburger = document.querySelector(".hamburger");
  const navLinks = document.querySelector(".nav-content .nav-links");

  // UI component selectors
  const allAnchorLinks = document.querySelectorAll('a[href^="#"]');

  function initNavigation() {
    // Initialize hamburger menu
    if (hamburger && navLinks) {
      hamburger.addEventListener("click", function (e) {
        e.stopPropagation();
        e.preventDefault();
        hamburger.classList.toggle("active");
        navLinks.classList.toggle("active");

        document.body.style.overflow = navLinks.classList.contains("active")
          ? "hidden"
          : "";
      });

      document.addEventListener("click", function (e) {
        if (
          navLinks.classList.contains("active") &&
          !e.target.closest(".nav-content")
        ) {
          hamburger.classList.remove("active");
          navLinks.classList.remove("active");
          document.body.style.overflow = ""; // Re-enable scrolling
        }
      });

      navLinks.querySelectorAll(".nav-button").forEach((link) => {
        link.addEventListener("click", () => {
          hamburger.classList.remove("active");
          navLinks.classList.remove("active");
          document.body.style.overflow = ""; // Re-enable scrolling
        });
      });
    }

    const currentPage = window.location.pathname.split("/").pop();
    document.querySelectorAll(".nav-links a").forEach((link) => {
      const linkHref = link.getAttribute("href");
      if (linkHref === currentPage) {
        link.classList.add("active");
      }
    });

    allAnchorLinks.forEach(function (anchor) {
      anchor.addEventListener("click", function (e) {
        const href = this.getAttribute("href");
        if (href === "#") return;

        e.preventDefault();
        const target = document.querySelector(href);
        if (target) target.scrollIntoView({ behavior: "smooth" });
      });
    });
  }

  function initCursorEffect() {
    // Skip cursor effect on touch devices
    if ("ontouchstart" in window) return;

    const existingCursor = document.getElementById("cursor-effect");
    if (existingCursor) existingCursor.remove();

    const cursorEffect = document.createElement("div");
    cursorEffect.id = "cursor-effect";
    document.body.appendChild(cursorEffect);

    document.addEventListener("mousemove", function (e) {
      requestAnimationFrame(() => {
        cursorEffect.style.left = `${e.clientX}px`;
        cursorEffect.style.top = `${e.clientY}px`;
      });
    });

    document.addEventListener("mousedown", function () {
      cursorEffect.classList.add("clicking");
    });

    document.addEventListener("mouseup", function () {
      cursorEffect.classList.remove("clicking");
    });
  }
  // Initialize all UI components
  initNavigation();
  initCursorEffect();
});
