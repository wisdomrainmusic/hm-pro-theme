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

  

// When drawer is aria-hidden=true, make sure nothing inside is focusable.
// This prevents Lighthouse warning: aria-hidden elements contain focusable descendants.
function setDrawerFocusables(drawer, isOpen) {
  if (!drawer) return;
  var focusables = drawer.querySelectorAll('a, button, input, select, textarea, [tabindex]');
  for (var i = 0; i < focusables.length; i++) {
    var el = focusables[i];
    if (!el) continue;

    if (!isOpen) {
      // Store previous tabindex (once)
      if (!el.hasAttribute('data-hmpro-prev-tabindex')) {
        var prev = el.getAttribute('tabindex');
        el.setAttribute('data-hmpro-prev-tabindex', prev === null ? '' : prev);
      }
      el.setAttribute('tabindex', '-1');
    } else {
      var prev2 = el.getAttribute('data-hmpro-prev-tabindex');
      if (prev2 !== null) {
        el.removeAttribute('data-hmpro-prev-tabindex');
        if (prev2 === '') el.removeAttribute('tabindex');
        else el.setAttribute('tabindex', prev2);
      } else {
        if (el.getAttribute('tabindex') === '-1') el.removeAttribute('tabindex');
      }
    }
  }
}
function lockBody(lock) {
    document.documentElement.classList.toggle("hmpro-mobile-drawer-open", !!lock);
  }

  function openDrawer(drawer, btn) {
    if (!drawer) return;
    drawer.classList.add("is-open");
    drawer.setAttribute("aria-hidden", "false");
    setAriaExpanded(btn, true);
    setDrawerFocusables(drawer, true);
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
    setDrawerFocusables(drawer, false);
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

      // Close can be triggered by overlay, the close button, or any child (e.g. SVG icon).
      // Use closest() so clicks on the inner icon still close the drawer.
      var closeEl = null;
      if (t && t.closest) {
        closeEl = t.closest('[data-hmpro-close="1"]');
      } else if (t && t.getAttribute && t.getAttribute("data-hmpro-close") === "1") {
        closeEl = t;
      }

      if (closeEl) {
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
