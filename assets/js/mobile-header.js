/* HM Pro Theme - Mobile Header Drawer (Right Side)
 * - Provides overlay close, ESC close, body scroll lock
 * - Keeps CTA /hesabim visible in drawer head
 */
(function () {
  // Emit events for lazy loaders (e.g., translate) when drawer opens/closes.
  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function setAriaExpanded(btn, isOpen) {
    if (!btn) return;
    btn.setAttribute("aria-expanded", isOpen ? "true" : "false");
  }

  function lockBody(lock) {
    document.documentElement.classList.toggle("hmpro-mobile-drawer-open", !!lock);
  }

  function openDrawer(drawer, btn) {
    if (!drawer) return;
    drawer.classList.add("is-open");
    drawer.setAttribute("aria-hidden", "false");
    setAriaExpanded(btn, true);
    lockBody(true);

    try {
      window.dispatchEvent(new CustomEvent("hmpro:drawer:open"));
    } catch (e) {}
  }

  function closeDrawer(drawer, btn) {
    if (!drawer) return;
    drawer.classList.remove("is-open");
    drawer.setAttribute("aria-hidden", "true");
    setAriaExpanded(btn, false);
    lockBody(false);

    try {
      window.dispatchEvent(new CustomEvent("hmpro:drawer:close"));
    } catch (e) {}
  }

  document.addEventListener("DOMContentLoaded", function () {
    var drawer = qs("#hmpro-mobile-drawer");
    var toggle = qs(".hmpro-mobile-menu-toggle");
    if (!drawer || !toggle) return;

    toggle.addEventListener("click", function () {
      var isOpen = drawer.classList.contains("is-open");
      if (isOpen) closeDrawer(drawer, toggle);
      else openDrawer(drawer, toggle);
    });

    drawer.addEventListener("click", function (e) {
      var t = e.target;
      if (!t) return;
      if (t && t.getAttribute && t.getAttribute("data-hmpro-close") === "1") {
        e.preventDefault();
        closeDrawer(drawer, toggle);
      }
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && drawer.classList.contains("is-open")) {
        closeDrawer(drawer, toggle);
      }
    });
  });
})();
