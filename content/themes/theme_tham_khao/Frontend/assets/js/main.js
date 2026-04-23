document.addEventListener("DOMContentLoaded", function () {
  const mobileMenuToggle = document.querySelector("#mobileMenuToggle");
  const mobileMenu = document.querySelector("#mobileMenu");
  const mobileMenuOverlay = document.querySelector("#mobileMenuOverlay");
  const closeMobileMenu = document.querySelector("#closeMobileMenu");

  if (mobileMenuToggle && mobileMenu && mobileMenuOverlay) {
    // Open menu
    mobileMenuToggle.addEventListener("click", function () {
      mobileMenu.classList.add("active");
      mobileMenu.classList.remove("hidden");
      mobileMenuOverlay.classList.remove("hidden");
    });

    // Close menu via overlay
    mobileMenuOverlay.addEventListener("click", function () {
      mobileMenu.classList.remove("active");
      mobileMenu.classList.add("hidden");
      mobileMenuOverlay.classList.add("hidden");
    });

    // Close menu via X button
    if (closeMobileMenu) {
      closeMobileMenu.addEventListener("click", function () {
        mobileMenu.classList.remove("active");
        mobileMenu.classList.add("hidden");
        mobileMenuOverlay.classList.add("hidden");
      });
    }
  }
});

