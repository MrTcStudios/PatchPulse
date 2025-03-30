// script_mobile.js
document.addEventListener("DOMContentLoaded", function () {
  // Mobile Navigation
  const menuButton = document.querySelector(".menu-button");
  const mobileNav = document.querySelector(".mobile-nav");
  const closeNav = document.querySelector(".close-nav");

  // FAQ Functionality
  const faqItems = document.querySelectorAll(".faq-item");
  faqItems.forEach((item) => {
    const question = item.querySelector(".faq-question");
    question.addEventListener("click", () => {
      item.classList.toggle("active");
    });
  });

  // Dropdown functionality
  const dropdownHeader = document.querySelector(".dropdown-header");
  const dropdownItems = document.querySelector(".dropdown-items");

  dropdownHeader?.addEventListener("click", () => {
    dropdownItems.classList.toggle("active");
  });

  // Touch Feedback
  function addTouchFeedback(elements) {
    elements.forEach((element) => {
      element.addEventListener("touchstart", function (e) {
        this.classList.add("touch-feedback");
        const rect = this.getBoundingClientRect();
        const x = e.touches[0].clientX - rect.left;
        const y = e.touches[0].clientY - rect.top;

        const ripple = document.createElement("div");
        ripple.classList.add("ripple");
        ripple.style.left = `${x}px`;
        ripple.style.top = `${y}px`;

        this.appendChild(ripple);

        setTimeout(() => {
          ripple.remove();
          this.classList.remove("touch-feedback");
        }, 600);
      });
    });
  }

  function preventZoomOnDoubleTap() {
    let lastTouchEnd = 0;
    document.addEventListener(
      "touchend",
      function (event) {
        const now = Date.now();
        if (now - lastTouchEnd < 300) {
          event.preventDefault();
        }
        lastTouchEnd = now;
      },
      false,
    );
  }

  preventZoomOnDoubleTap();

  // Swipe Detection
  let touchStartX = 0;
  let touchEndX = 0;

  function handleSwipe() {
    if (touchStartX - touchEndX > 100) {
      // Swipe left
      mobileNav.classList.remove("active");
    }
    if (touchEndX - touchStartX > 100) {
      // Swipe right
      mobileNav.classList.add("active");
    }
  }

  document.addEventListener("touchstart", (e) => {
    touchStartX = e.changedTouches[0].screenX;
  });

  document.addEventListener("touchend", (e) => {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
  });

  // Menu Toggle
  menuButton?.addEventListener("click", () => {
    menuButton.classList.toggle("active");
    mobileNav.classList.toggle("active");
  });

  closeNav?.addEventListener("click", () => {
    menuButton.classList.remove("active");
    mobileNav.classList.remove("active");
  });

  // Close dropdown and menu when clicking outside
  document.addEventListener("click", (event) => {
    if (
      !mobileNav.contains(event.target) &&
      !menuButton.contains(event.target)
    ) {
      mobileNav.classList.remove("active");
      menuButton.classList.remove("active");
      dropdownItems?.classList.remove("active");
    }
  });

  // Scan Mode Selection
  const modeButtons = document.querySelectorAll(".mode-button");
  const resultSections = document.querySelectorAll(".result-section");

  modeButtons?.forEach((button) => {
    button.addEventListener("click", () => {
      const mode = button.dataset.mode;

      modeButtons.forEach((btn) => btn.classList.remove("active"));
      resultSections.forEach((section) => section.classList.remove("active"));

      button.classList.add("active");
      document.getElementById(mode)?.classList.add("active");
    });
  });

 // Form Handling
const forms = document.querySelectorAll("form");
forms.forEach((form) => {
  form.addEventListener("submit", function (e) {
    // e.preventDefault(); // Rimuovi questa riga
    // Add loading state
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.classList.add("loading");

    // Simulate form submission
    setTimeout(() => {
      submitButton.classList.remove("loading");
      // Puoi lasciare eventuali altre azioni, ma il modulo deve essere inviato correttamente.
    }, 2000);
  });
});


  // Password Toggle
  const togglePassword = document.querySelector(".toggle-password");
  const passwordInput = document.querySelector('input[type="password"]');

  togglePassword?.addEventListener("click", () => {
    const type =
      passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);
    togglePassword.textContent = type === "password" ? "👁" : "👁‍🗨";
  });

  // Initialize touch feedback
  addTouchFeedback(
    document.querySelectorAll("button, .action-button, .result-item"),
  );

  // Handle orientation change
  window.addEventListener("orientationchange", () => {
    // Adjust layout if needed
    setTimeout(() => {
      window.scrollTo(0, 0);
    }, 200);
  });

  // Prevent zoom on double tap
  document.addEventListener(
    "touchend",
    function (e) {
      const now = Date.now();
      const timeDiff = now - (this.lastTouch || now);

      this.lastTouch = now;

      if (timeDiff < 500 && timeDiff > 0) {
        e.preventDefault();
      }
    },
    false,
  );
});
