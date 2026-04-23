document.addEventListener("DOMContentLoaded", function () {

    const faqButtons = document.querySelectorAll(".faqZone .faqButton");
    const plusSymbols = document.querySelectorAll(".plusSym");

    faqButtons.forEach((button, index) => {
        button.addEventListener("click", function (e) {
            e.preventDefault();
            document.querySelectorAll(".faq-answer").forEach(a => a.classList.remove("active"));
            faqButtons.forEach(btn => btn.classList.remove("active"));
            plusSymbols.forEach(s => s.classList.remove("active"));

            const target = document.getElementById(this.getAttribute("data-faq"));
            if (target) target.classList.add("active");
            this.classList.add("active");
            if (plusSymbols[index]) plusSymbols[index].classList.add("active");
        });
    });

    const togglePassword = document.getElementById("togglePassword");
    const passwordField = document.getElementById("passwordField");
    if (togglePassword && passwordField) {
        togglePassword.addEventListener("click", function () {
            const type = passwordField.type === "password" ? "text" : "password";
            passwordField.type = type;
            this.textContent = type === "password" ? "👁" : "👁‍🗨";
        });
    }

    const termsLink = document.getElementById("termsLink");
    const overlay = document.getElementById("termsOverlay");
    const closeButton = document.getElementById("closeOverlay");
    if (termsLink && overlay && closeButton) {
        termsLink.addEventListener("click", function (e) { e.preventDefault(); overlay.style.display = "flex"; });
        closeButton.addEventListener("click", function () { overlay.style.display = "none"; });
        overlay.addEventListener("click", function (e) { if (e.target === overlay) overlay.style.display = "none"; });
    }

});
