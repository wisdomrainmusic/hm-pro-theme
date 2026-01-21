(function () {
  function init(root) {
    const tabs = root.querySelectorAll(".hmpro-pft__tab");
    const panes = root.querySelectorAll(".hmpro-pft__pane");
    if (!tabs.length || !panes.length) return;

    function activate(index) {
      tabs.forEach((t) => {
        const i = parseInt(t.getAttribute("data-tab-index") || "0", 10);
        const active = i === index;
        t.classList.toggle("is-active", active);
        t.setAttribute("aria-selected", active ? "true" : "false");
      });
      panes.forEach((p) => {
        const i = parseInt(p.getAttribute("data-tab-index") || "0", 10);
        p.classList.toggle("is-active", i === index);
      });
    }

    tabs.forEach((btn) => {
      btn.addEventListener("click", () => {
        const index = parseInt(btn.getAttribute("data-tab-index") || "0", 10);
        activate(index);
      });
    });
  }

  function boot() {
    document.querySelectorAll(".hmpro-pft").forEach(init);
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();