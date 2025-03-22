document.addEventListener("DOMContentLoaded", function () {
  // Dropdown functionality
  const dropdown = document.querySelector(".dropdown");
  const dropdownContent = document.querySelector(".dropdown-content");
  let timeoutId = null;

  if (dropdown && dropdownContent) {
    function showDropdown() {
      clearTimeout(timeoutId);
      dropdownContent.classList.add("show");
    }

    function hideDropdown() {
      timeoutId = setTimeout(() => {
        dropdownContent.classList.remove("show");
      }, 500);
    }

    dropdown.addEventListener("mouseenter", showDropdown);
    dropdown.addEventListener("mouseleave", hideDropdown);
    dropdownContent.addEventListener("mouseenter", showDropdown);
    dropdownContent.addEventListener("mouseleave", hideDropdown);

    const dropdownLinks = document.querySelectorAll(".dropdown-content a");
    dropdownLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault();
        const tool = this.textContent;
        console.log(`Selected tool: ${tool}`);
        dropdownContent.classList.remove("show");
      });
    });
  }

  // FAQ functionality
  const faqButtons = document.querySelectorAll(".faqZone .faqButton");
  const plusSymbols = document.querySelectorAll(".plusSym");

  faqButtons.forEach((button, index) => {
    button.addEventListener("click", function (e) {
      e.preventDefault();

      // Hide all answers
      document.querySelectorAll(".faq-answer").forEach((answer) => {
        answer.classList.remove("active");
      });

      // Remove active class from all buttons
      faqButtons.forEach((btn) => {
        btn.classList.remove("active");
      });

      // Reset all plus symbols
      plusSymbols.forEach((symbol) => {
        symbol.classList.remove("active");
      });

      // Show selected answer
      const faqId = this.getAttribute("data-faq");
      const targetAnswer = document.getElementById(faqId);
      if (targetAnswer) {
        targetAnswer.classList.add("active");
      }

      // Add active class to clicked button
      this.classList.add("active");

      // Add active class to clicked plus symbol
      if (plusSymbols[index]) {
        plusSymbols[index].classList.add("active");
      }
    });
  });

  // Scan type switching functionality
  const scanButtons = document.querySelectorAll(".choiceScansZone button");

  // Show initial scan zone
  const webTracking = document.getElementById("webTracking");
  if (webTracking) {
    webTracking.classList.add("active");
  }

  scanButtons.forEach((button) => {
    button.addEventListener("click", function () {
      scanButtons.forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      const scanType = this.getAttribute("data-scan");
      const targetZone = document.getElementById(scanType);

      document.querySelectorAll(".scanResultZone").forEach((zone) => {
        zone.style.opacity = "0";
      });

      setTimeout(() => {
        document.querySelectorAll(".scanResultZone").forEach((zone) => {
          zone.classList.remove("active");
        });
        if (targetZone) {
          targetZone.classList.add("active");
          void targetZone.offsetWidth;
          targetZone.style.opacity = "1";
        }
      }, 300);
    });
  });

  // Storage Information Update
  function updateStorageInfo() {
    const usedStorage = 650;
    const totalStorage = 1000;
    const percentage = (usedStorage / totalStorage) * 100;

    const storageBar = document.getElementById("storageBar");
    if (storageBar) {
      storageBar.style.width = `${percentage}%`;

      if (percentage > 90) {
        storageBar.style.backgroundColor = "#ff4444";
      } else if (percentage > 70) {
        storageBar.style.backgroundColor = "#ffaa00";
      } else {
        storageBar.style.backgroundColor = "white";
      }
    }

    const usedElement = document.getElementById("usedStorage");
    const totalElement = document.getElementById("totalStorage");

    if (usedElement && totalElement) {
      if (
        usedElement.textContent !== usedStorage.toString() ||
        totalElement.textContent !== totalStorage.toString()
      ) {
        usedElement.classList.add("updating");
        totalElement.classList.add("updating");

        usedElement.textContent = usedStorage;
        totalElement.textContent = totalStorage;

        setTimeout(() => {
          usedElement.classList.remove("updating");
          totalElement.classList.remove("updating");
        }, 300);
      }
    }
  }

  // Terms and Conditions overlay functionality
  const termsLink = document.getElementById("termsLink");
  const overlay = document.getElementById("termsOverlay");
  const closeButton = document.getElementById("closeOverlay");

  if (termsLink && overlay && closeButton) {
    termsLink.addEventListener("click", function (e) {
      e.preventDefault();
      overlay.style.display = "flex";
    });

    closeButton.addEventListener("click", function () {
      overlay.style.display = "none";
    });

    overlay.addEventListener("click", function (e) {
      if (e.target === overlay) {
        overlay.style.display = "none";
      }
    });
  }

  // Password toggle functionality
  const togglePassword = document.getElementById("togglePassword");
  const passwordField = document.getElementById("passwordField");

  if (togglePassword && passwordField) {
    togglePassword.addEventListener("click", function () {
      const type =
        passwordField.getAttribute("type") === "password" ? "text" : "password";
      passwordField.setAttribute("type", type);
      this.textContent = type === "password" ? "👁" : "👁‍🗨";
    });
  }

  // Warning and Delete button handlers
  const clearLogsButton = document.querySelector(".action-button.warning");
  const deleteLogsButton = document.querySelector(".action-button.delete");
  const deleteAccountButton = document.querySelectorAll(
    ".action-button.delete",
  )[1];

  if (clearLogsButton) {
    clearLogsButton.addEventListener("click", function () {
      if (
        confirm(
          "Are you sure you want to clear all logs? This action cannot be undone.",
        )
      ) {
        console.log("Clearing logs...");
      }
    });
  }

  if (deleteLogsButton) {
    deleteLogsButton.addEventListener("click", function () {
      if (
        confirm(
          "Are you sure you want to delete all logs? This action cannot be undone.",
        )
      ) {
        console.log("Deleting logs...");
      }
    });
  }

  if (deleteAccountButton) {
    deleteAccountButton.addEventListener("click", function () {
      if (
        confirm(
          "WARNING: Are you sure you want to delete your account? This action cannot be undone and you will lose all data.",
        )
      ) {
        console.log("Deleting account...");
      }
    });
  }

  // Form Submissions
  const settingsForm = document.querySelector(".settings-form");
  if (settingsForm) {
    settingsForm.addEventListener("submit", function (e) {
      e.preventDefault();
      console.log("Form submitted");
    });
  }

  // Log Entry Animation
  function animateLogEntries() {
    const logEntries = document.querySelectorAll(".log-entry");
    logEntries.forEach((entry, index) => {
      setTimeout(() => {
        entry.style.opacity = "1";
        entry.style.transform = "translateX(0)";
      }, index * 100);
    });
  }

  // Initialize functions
  updateStorageInfo();
  animateLogEntries();

  // Update storage info periodically
  setInterval(updateStorageInfo, 30000);
});
