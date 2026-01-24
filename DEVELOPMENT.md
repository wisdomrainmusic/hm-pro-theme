# HM Pro Theme — Development Rules (Stability Guide)
## 2026-01-24 – Woo Variation Gallery Operational Rule (Variation Image Required)

Decision:
To ensure stable variation image switching and avoid edge-case blank gallery states,
we enforce an operational rule: every product variation must have its own featured
image set (variation image is required).

Context:
A gallery “blank/empty” state was observed only when a variation had no image and
relied on WooCommerce fallback behavior. When all variations have images, the
frontend behavior remains stable across products and does not require additional
fallback hacks.

Rationale:
This approach minimizes regression risk and keeps the system predictable for clients
using the theme/importer in production.

Action:
Update product data entry/import guidelines to require a featured image for each
variation. Custom variation galleries remain optional.

## 2026-01-24 – Woo Variation Gallery Race Condition Fix

Issue:
Variation change caused a brief correct image display followed by
an immediate revert to the parent product image.

Root Cause:
Custom frontend script (woo-variation-gallery.js) was restoring the
parent gallery whenever a variation had no custom hmpro_gallery,
overriding WooCommerce’s native variation image handling and causing
a race condition.

Resolution:
Introduced state-based logic in the frontend:
- Parent gallery is restored only if a custom variation gallery
  was previously applied.
- Variations without a custom gallery now rely entirely on
  WooCommerce default behavior.

Architectural Decision:
- Variation images must be set at import time (_thumbnail_id).
- Frontend must not compensate for missing variation images.
- WooCommerce default fallback is trusted.

Result:
Stable variation image switching without visual glitches.
This theme is designed to be updated frequently. To prevent regressions during updates, follow the rules below.
These rules are based on real production incidents (double-load, PHP 8.1+ deprecations, admin callback fatals).

---

## 1) One Entry Point for Tools

All internal tools must be loaded ONLY via:

- `inc/tools/tools-loader.php`

Do not include tool files from multiple places (functions.php, admin files, block files, etc.).

**Rule**
- If you add a new tool, register it in `tools-loader.php` (single source of truth).
- Avoid additional `require/include` calls elsewhere.

---

## 2) Never `return;` from a Tool File

Tool files often define:
- Classes
- Admin page callbacks (render functions)
- Helper functions used by admin menus

If you `return;` early, helper callbacks will not exist, and WordPress will fatal when the menu tries to call them.

**BAD**
```php
if ( class_exists( 'My_Tool_Class', false ) ) {
  return; // breaks helper functions defined later
}
```

**GOOD**
```php
if ( ! class_exists( 'My_Tool_Class', false ) ) {
  class My_Tool_Class { /* ... */ }
}
// helper functions remain available below
```

---

## 3) Make Loaders Idempotent

Some hosts/bootstrap orders can evaluate loader files more than once.
All loader files should be safe to run multiple times.

**Rule**

Add a single "loaded" constant guard at the top of loaders:

```php
if ( defined( 'HMPRO_SOMETHING_LOADED' ) ) {
  return;
}

define( 'HMPRO_SOMETHING_LOADED', true );
```

And

Wrap any `define()` calls with `if ( ! defined( ... ) )`.

---

## 4) Admin Menus: Do Not Pass `null` parent_slug

PHP 8.1+ can emit deprecations through WP core when `add_submenu_page()` gets null as parent slug.
This can cascade into `plugin_basename()` / `wp_normalize_path()` warnings/deprecations.

**BAD**
```php
add_submenu_page( null, 'Edit', 'Edit', 'manage_options', 'hm-edit', 'render_cb' );
```

**GOOD (Hidden page pattern)**
```php
add_submenu_page( 'hmpro-theme', 'Edit', 'Edit', 'manage_options', 'hm-edit', 'render_cb' );
remove_submenu_page( 'hmpro-theme', 'hm-edit' ); // still accessible via URL
```

---

## 5) Callback Safety (Admin/AJAX/REST)

Any callback referenced by WordPress must exist at the time WP calls it:

- Admin menu page callbacks
- AJAX handlers
- REST endpoints

**Rule**

Ensure callbacks are defined unconditionally (not behind early returns).

Optionally add defensive checks before registering:

```php
if ( function_exists( 'hmpro_render_page' ) ) {
  add_submenu_page( ..., 'hmpro_render_page' );
}
```

---

## 6) PHP 8.1+ Null-Safety Rules

Avoid passing null into string functions:

- `strpos()`, `str_replace()`, `trim()`, etc.

Use `?? ''` or cast to string:

```php
$value = (string) ( $value ?? '' );
```

When reading from options/meta:

```php
$x = get_option( 'my_option', '' );
$x = is_string( $x ) ? $x : '';
```

---

## 7) Textdomain Loading (WP 6.7+)

WP 6.7+ can show `_load_textdomain_just_in_time` warnings if translations are triggered too early.

**Rule**

Load theme textdomain on `after_setup_theme` with early priority (0).

Avoid calling `__()` / `_e()` before `after_setup_theme` when possible.

---

## 8) Additive Changes Only (Regression-Proof)

When fixing bugs:

- Do not relocate or remove working functions unless necessary.
- Prefer additive patches (guards, additional conditionals, wrappers).
- Keep function names and existing hooks stable.

---

## 9) Admin Menu Callback Guarantee (Critical)

Admin menu callbacks must be safe even if tool files are not loaded yet due to host/bootstrap order.
On some environments, WordPress can attempt to execute menu callbacks while a tool file failed to load
or was skipped. If a callback is missing, WP will fatal.

**Mandatory Rules**
1. Ensure tools are loaded before registering admin menu pages:

   In the admin menu registration file (e.g. `inc/admin/admin-menu.php`), require_once the tools loader.

   The tools loader must be idempotent (Rule #3), so requiring it is safe.

2. Never bind `add_submenu_page()` to a function that might not exist:

   Wrap callback binding with `function_exists()`.

   Provide a fallback renderer to prevent fatal crashes.

**Recommended Pattern**
```php
// 1) Ensure tool callbacks are available (idempotent loader).
require_once HMPRO_PATH . '/inc/tools/tools-loader.php';

// 2) Fallback to prevent fatal crashes.
function hmpro_render_missing_tool_page( $title, $details = '' ) {
  echo '<div class="wrap">';
  echo '<h1>' . esc_html( $title ) . '</h1>';
  echo '<div class="notice notice-error"><p><strong>Tool page callback is missing.</strong></p>';

  if ( $details ) {
    echo '<p>' . esc_html( $details ) . '</p>';
  }

  echo '</div></div>';
}

// 3) Safe callback binding.
$cb = function_exists( 'hmpro_render_category_importer_page' )
  ? 'hmpro_render_category_importer_page'
  : function () {
    hmpro_render_missing_tool_page(
      'Category Importer',
      'Missing: hmpro_render_category_importer_page'
    );
  };

add_submenu_page(
  'hmpro-theme',
  'Category Importer',
  'Category Importer',
  'manage_options',
  'hmpro-category-importer',
  $cb
);
```

**Goal**
- Admin must never crash.
- Missing tool callbacks should show an error notice instead of a fatal error.

---

## Mini Smoke Test Checklist (3–10 minutes)

Run this after every update/commit (especially on a clean WP install).

### A) Clean Install / Activation

Install a clean WordPress instance.

Enable:

- `WP_DEBUG = true`
- `WP_DEBUG_LOG = true`
- `WP_DEBUG_DISPLAY = true` (optional)

Activate HM Pro Theme.

Confirm:

- No fatal errors
- Admin loads

### B) Admin Pages

Go to: Appearance → Themes

Open theme menus (HM Pro Theme, HM Mega Menu, Tools pages).

Verify tool pages load without callback fatals.

### C) Frontend

Visit homepage.

Confirm header/footer render.

Confirm no console errors (basic check).

### D) Logs

Check `/wp-content/debug.log`

Must NOT contain:

- Fatal error
- Deprecated
- Warning
- Notice (theme-caused)

If the log contains only plugin/host notices (not theme paths), note and ignore.

### Quick Rule of Thumb

If a log line contains:

- `wp-content/themes/hm-pro-theme-main/` → OUR issue (must fix)
- `wp-content/plugins/` → plugin issue
- `wp-content/mu-plugins/` → hosting/mandatory plugin issue
