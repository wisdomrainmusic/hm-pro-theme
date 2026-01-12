# HM Pro Theme

## Checkpoint — Presets + Typography Engine (Phase 1)

Status: Completed ✅

What’s working:
- Preset system (CRUD): create/edit/delete (active preset protected)
- Active preset switch (Set Active) with notice feedback
- CSV import + template download (robust delimiter + admin routing fix)
- Palette preview dots in preset list (premium UI)
- CSS variable engine (base + WooCommerce friendly, no !important)
- Typography engine:
  - Font tokens → Google Fonts loader (loads only selected fonts)
  - :root font variables (body + heading)
  - Typography Presets (1-click apply):
    - Modern Store (inter / poppins)
    - Editorial / Fashion (inter / playfair_display)
    - Soft Elegant (lato / poppins)
    - Signature Brand (inter / dancing_script)
  - “Aa” mini preview on preset buttons

Screens:
- Admin presets UI: assets/img/admin-presets-ui.png
- Frontend signature typography: assets/img/frontend-signature-typography.png

Next planned:
- Phase 2: Header/Footer Builder expansion + WooCommerce components styling pack
- Phase 3: Demo Engine integration into theme (category/menu importer, starter demos)

Premium WooCommerce-focused WordPress theme with built-in preset & palette engine.

## Theme Screenshot

The WordPress theme preview image (`screenshot.png`) is intentionally excluded
from version control.

Please add `screenshot.png` manually to the theme root directory before
uploading the theme to WordPress.

Recommended size: 1200x900px (PNG)

## Status
- Commit 002: base theme + token-ready CSS

- ## Project Status — Checkpoint

### Current State
HM Pro Theme is successfully installed and activated as a valid WordPress theme.

Completed:
- Valid WordPress theme structure (style.css, index.php, header.php, footer.php)
- Token-based CSS foundation using CSS variables (--hm-*)
- Admin menu: HM Pro Theme → Presets
- Presets admin page UI shell (no logic yet)
- WooCommerce theme support enabled
- Clean activation with no PHP errors

### What Exists Now
- Theme scaffold and frontend rendering
- Admin UI for future preset & palette system
- CSS tokens ready to be driven by presets

### What Is NOT Implemented Yet
- Preset CRUD (add/edit/delete)
- Active preset logic
- CSS engine to output preset variables
- CSV import/export
- WooCommerce detailed styling
- Elementor widget integrations

### Next Milestones
1. Preset data model stored in wp_options
2. Active preset selection + runtime CSS output
3. Preset editor (admin UI)
4. CSV import/export for presets
5. WooCommerce & Elementor token binding

Last updated: Commit 003 (Admin Presets UI shell)

---

## Development Checkpoint — Preset System Online

### Current Status
The HM Pro Theme preset system is now functional at the data and admin level.

### What Works
- Theme installs and activates correctly as a valid WordPress theme
- Admin panel: HM Pro Theme → Presets
- Preset list renders correctly in admin
- Sample presets can be seeded for testing
- Presets are stored in wp_options
- Active preset state changes correctly via admin actions
- Admin notices confirm preset activation
- Active preset persists across page reloads

### Expected Behavior (Confirmed)
- Changing the active preset updates the stored state
- UI correctly reflects the active preset (Active / Set Active)
- No PHP errors or admin warnings

### Known Limitations (Planned)
- Frontend visual changes are minimal because CSS Engine is not yet implemented
- Preset values are not yet injected into :root CSS variables
- WooCommerce and Elementor components are not yet bound to preset tokens

### Next Steps
1. Implement CSS Engine to output active preset as CSS variables (:root)
2. Bind theme base styles to preset tokens for visible frontend changes
3. Extend preset editor (add/edit/delete)
4. Add CSV import/export for presets
5. Integrate WooCommerce & Elementor styling

Checkpoint reached at: Commit 005 (Preset activation working)
