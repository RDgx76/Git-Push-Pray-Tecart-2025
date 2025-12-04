/* ui.js
   General UI interactions: sidebar, modal, theme toggle, helpers.
   Include this file on all pages.
*/

(function () {
  // Theme toggle: data-theme attribute on <html> or <body>
  const themeBtn = document.querySelector("#themeToggle");
  const rootEl = document.documentElement || document.body;

  function getStoredTheme() {
    try {
      return localStorage.getItem("bytemart_theme");
    } catch (e) {
      return null;
    }
  }
  function storeTheme(t) {
    try {
      localStorage.setItem("bytemart_theme", t);
    } catch (e) {}
  }

  function applyTheme(theme) {
    if (theme === "light") {
      rootEl.setAttribute("data-theme", "light");
    } else {
      rootEl.removeAttribute("data-theme");
    }
    storeTheme(theme);
  }

  // initialize theme
  (function initTheme() {
    const t =
      getStoredTheme() ||
      (window.matchMedia &&
      window.matchMedia("(prefers-color-scheme: light)").matches
        ? "light"
        : "dark");
    applyTheme(t);
    if (themeBtn) themeBtn.textContent = t === "light" ? "🌞" : "🌙";
  })();

  if (themeBtn) {
    themeBtn.addEventListener("click", () => {
      const current =
        rootEl.getAttribute("data-theme") === "light" ? "light" : "dark";
      const next = current === "light" ? "dark" : "light";
      applyTheme(next);
      themeBtn.textContent = next === "light" ? "🌞" : "🌙";
    });
  }

  // Sidebar toggle for responsive
  const sidebarToggle = document.querySelector("#sidebarToggle");
  const sidebar = document.querySelector(".sidebar");
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener("click", () =>
      sidebar.classList.toggle("collapsed")
    );
  }

  // Modal system: data-modal attribute points to modal id
  document.addEventListener("click", (e) => {
    const openBtn = e.target.closest("[data-open-modal]");
    if (openBtn) {
      const id = openBtn.getAttribute("data-open-modal");
      const modal = document.getElementById(id);
      if (modal) modal.classList.add("active");
    }

    const closeBtn = e.target.closest("[data-close-modal]");
    if (closeBtn) {
      const modal = closeBtn.closest(".modal");
      if (modal) modal.classList.remove("active");
    }

    // click outside modal-box to close
    if (e.target.classList && e.target.classList.contains("modal")) {
      e.target.classList.remove("active");
    }
  });

  // Simple helper: debounce
  window.bytemart = window.bytemart || {};
  window.bytemart.debounce = function (fn, wait = 200) {
    let t;
    return function (...args) {
      clearTimeout(t);
      t = setTimeout(() => fn.apply(this, args), wait);
    };
  };
})();
