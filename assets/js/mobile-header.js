/* HM Pro Theme - Mobile Header Drawer (Right Side)
 * - Clones existing .hmpro-primary-nav into the drawer
 * - Provides overlay close, ESC close, body scroll lock
 * - Keeps CTA /hesabim visible in drawer head
 */
(function () {
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
  }

  function closeDrawer(drawer, btn) {
    if (!drawer) return;
    drawer.classList.remove("is-open");
    drawer.setAttribute("aria-hidden", "true");
    setAriaExpanded(btn, false);
    lockBody(false);
  }

  function ensureMobileNav(drawer) {
    var nav = qs(".hmpro-mobile-nav", drawer);
    if (!nav) return;
    if (nav.getAttribute("data-hmpro-ready") === "1") return;

    var desktopNav = qs("#site-header .hmpro-primary-nav");
    if (!desktopNav) {
      nav.innerHTML = "";
      nav.setAttribute("data-hmpro-ready", "1");
      return;
    }

    // Clone only the menu markup; keep it simple/vertical for mobile.
    nav.innerHTML = desktopNav.innerHTML;
    nav.setAttribute("data-hmpro-ready", "1");
  }

  document.addEventListener("DOMContentLoaded", function () {
    var drawer = qs("#hmpro-mobile-drawer");
    var toggle = qs(".hmpro-mobile-menu-toggle");
    if (!drawer || !toggle) return;

    ensureMobileNav(drawer);

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

    // If menu gets re-rendered by builder changes, allow manual refresh on resize
    window.addEventListener("resize", function () {
      // Refresh clone once if needed
      if (drawer && drawer.classList.contains("is-open")) return;
      var nav = qs(".hmpro-mobile-nav", drawer);
      if (!nav) return;
      nav.removeAttribute("data-hmpro-ready");
      ensureMobileNav(drawer);
    });
  });
})();
