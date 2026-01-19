✅ CHECKPOINT – Header & Hero Banner Refactor (Jan 2026)
Summary

This checkpoint finalizes a major refactor around the header, transparent header logic, and hero banner behavior.
The previous scroll-based transparent header approach was intentionally removed in favor of a clean, Customizer-driven Hero Banner system that works independently of Elementor.

What Was Tried

Astra-like transparent header with menu overlay on hero

Scroll-based state switching (transparent → opaque)

Header overlay logic dependent on page structure

Result

These approaches introduced unnecessary complexity and unclear UX behavior in a custom theme context.

Final Decision

The transparent header scroll logic was fully removed.

Instead, the theme now uses:

A Customizer-controlled Hero Banner (image or video)

Fixed, predictable header layout

No scroll-based header state changes

This results in a simpler, more stable, and more maintainable architecture.

Current System (Active)

Hero Banner is rendered above page content

Configurable from Customizer

Elementor is not required

Header remains consistent across scroll states

Customizer Features

Hero background image

Optional background video (mp4 / webm)

Adjustable hero height (px)

Overlay darkness control

Optional title & description fields

Mobile-safe behavior (video muted / optional fallback image)

Removed / Deprecated

Scroll-to-opaque header option

Scroll offset / threshold controls

Header scroll JS logic

Transparent header color state switching

These options were removed because they produced no meaningful or reliable UX benefit in the current theme architecture.

Why This Matters

Reduces CSS/JS complexity

Prevents layout edge cases

Makes demos more predictable

Keeps the theme flexible for Elementor and non-Elementor users

Establishes a solid base for future hero + CTA enhancements

Next Possible Enhancements

Hero content alignment (center / left)

CTA button support in Hero

Per-page hero override (optional)

Preset hero styles for demos
HM Pro Theme — Surgical Checkpoint (Developer)
Repository snapshot
Theme root: hm-pro-theme-main/
Version constant: HMPRO_VERSION = 0.1.0 (functions.php)
Entry points:
functions.php loads core + engine + admin modules
header.php renders either Builder Header or Legacy Header fallback
footer.php (builder-driven)
High-level architecture
1) Core
inc/core/setup.php
Theme supports: custom-logo, title-tag, post-thumbnails, responsive-embeds, woocommerce, wc gallery features
Registers nav menu locations:
primary, topbar, footer, mobile_menu
legacy: hm_primary, hm_footer
Builder region helpers:
hmpro_get_builder_regions()
hmpro_has_builder_layout(), hmpro_header_builder_has_layout()
inc/core/enqueue.php
Enqueues style.css via get_stylesheet_uri() (hmpro-style)
NOTE: functions.php also enqueues base/header/footer/mega-menu/woo styles.
2) Admin UI
inc/admin/admin-menu.php
Adds parent: hmpro-theme
Subpages: Presets, Header Builder, Footer Builder, Mega Menu Builder, Importers, etc.
assets/admin-builder.js + assets/admin-builder.css
Builder admin UI (drag/drop, component settings, save)
inc/admin/title-visibility.php
Registers post meta: _hmpro_hide_title (REST enabled)
Classic editor metabox + Gutenberg document panel toggle
Adds body_class: hmpro-hide-title
CSS fallback in assets/css/base.css
3) Builder engine (Header/Footer)
inc/engine/builder-storage.php
Options:
hmpro_header_layout
hmpro_footer_layout
Schema:
schema_version: 1
regions: { region_key => [rows] }
Sanitization:
Allowlisted component types + settings keys
URL normalization for social URLs
SVG sanitization via wp_kses for custom icons
inc/engine/builder-renderer.php
Hooks used by templates:
do_action('hmpro/header/render_region', region_key)
do_action('hmpro/footer/render_region', region_key)
Component renderers:
hmpro_builder_comp_logo/menu/search/cart/button/html/spacer/footer_menu/footer_info/social/social_icon_button
HTML block uses do_shortcode() + custom kses allowlist (scripts blocked)
Social icon preset loader:
hmpro_load_social_svg_preset() searches assets/icon/social/ then assets/icons/social/
4) Mega Menu engine
inc/engine/mega-menu-library.php
Registers CPT: hm_mega_menu
Stores layout/meta:
_hmpro_mega_layout
_hmpro_mega_settings
inc/engine/mega-menu-menuitem-meta.php
Adds dropdown field to nav menu items
Saves binding meta: _hmpro_mega_menu_id (nav_menu_item post meta)
Frontend injection via walker_nav_menu_start_el
Adds li classes: hmpro-li-has-mega + hmpro-mega-id-{id}
assets/js/mega-menu.js
Interaction mode can be set via customizer:
theme_mod: hmpro_mega_menu_interaction (hover|click)
body_class adds hmpro-mega-click when click mode enabled
5) Presets + CSS engine
inc/engine/presets.php
Options:
hmpro_presets
hmpro_active_preset
inc/engine/css-engine.php
Prints CSS variables (accent, contrast, etc.)
inc/engine/typography.php
Typography utilities (variable application)
6) Embedded tools (inside theme)
inc/tools/tools-loader.php
Embeds modules:
category-importer
slug-menu-builder
product-importer
hm-menu-controller
hm-basic-ceviri-inline
Moves HM Basic Translate page under HM Pro Theme menu.
7) WooCommerce tweaks
inc/woocommerce/gallery-tweaks.php
inc/woocommerce/checkout-tweaks.php
Template behavior
Header (header.php)
If builder layout exists → renders builder regions + adds two extra UI elements:
Desktop persistent account CTA (absolute positioned)
Mobile hamburger toggle + right-side drawer
If builder layout missing → legacy header fallback (hm_primary menu location).
Mobile drawer
Markup is always present when builder header is active.
Visibility controlled by CSS media query (max-width: 768px) and JS (assets/js/mobile-header.js).
Critical issues / risks (actionable)
Text domain inconsistency
style.css declares Text Domain: hm-pro-theme
load_theme_textdomain() is called with 'hmpro'
Strings use both 'hmpro' and 'hm-pro-theme'
Impact: translation and string management becomes fragmented.
Fix: pick ONE domain (recommend: hm-pro-theme) and refactor.
Language consistency
Mixed Turkish/English strings across admin + frontend.
Impact: conflicts with “English-only site/admin” preference; also makes demo packaging harder.
Fix: normalize UI strings and defaults (placeholder, button labels, menu labels).
Hardcoded My Account path
header.php uses home_url('/hesabim/')
Impact: breaks if slug differs, or on non-TR installs.
Fix:
if WooCommerce active: get_permalink( wc_get_page_id('myaccount') )
else: fallback to wp_login_url() / wp_registration_url()
Missing preset asset
LinkedIn SVG is referenced in allowed presets but assets/icon/social/linkedin.svg is absent.
Impact: fallback badge appears (not fatal) but inconsistent.
Fix: add linkedin.svg or remove from preset list.
Duplicate conditional branch in builder-storage sanitization
builder-storage.php contains duplicated menu_id/depth/width/height handling.
Impact: not a security issue; increases maintenance risk.
Fix: clean up branches and add unit-ish tests (payload samples).
Duplicate style enqueues
inc/core/enqueue.php enqueues style.css; functions.php enqueues base/header/footer/mega-menu/woo styles.
Impact: not fatal; but can cause override confusion and extra requests.
Fix: either merge style.css into base.css or keep style.css minimal and document CSS layering.
Recommended “next commit” plan (surgical, low risk)
Normalize text domain + strings
Set load_theme_textdomain('hm-pro-theme')
Replace __('...', 'hmpro') usages
Convert Turkish strings to English (or wrap with translations consistently)
Fix My Account link resolution
Create helper:
hmpro_get_account_url()
Use WooCommerce lookup if available, fallback otherwise.
Add linkedin.svg or remove LinkedIn from presets
Refactor builder-storage sanitizer
Remove duplicate branches
Keep allowlists identical
Add sample payload tests (even as PHP arrays in a dev-only file or wp-cli command)
Export/import readiness notes
If you want a demo installer to fully reproduce a site, the following must be packaged and remapped:
Options:
hmpro_header_layout
hmpro_footer_layout
hmpro_presets
hmpro_active_preset
CPT + meta:
hm_mega_menu posts + _hmpro_mega_layout + _hmpro_mega_settings
Nav menu bindings:
nav_menu_item meta: _hmpro_mega_menu_id
IMPORTANT: mega menu IDs change after import; must remap old->new.
Best remap key: mega menu slug (post_name) or a stable custom GUID meta.
