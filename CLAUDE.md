# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

WordPress plugin (`Printcart Integration`) that adds a "Start Design" / "Upload Design" button to WooCommerce product pages, loads the Printcart Designer SDK, stores designs on cart/order items, and pushes a matching project to the Printcart Cloud API on checkout.

- Entry point: `printcart-design.php` (bumps `PRINTCART_VERSION` and defines all global constants; must be updated in sync with `readme.txt`'s `Stable tag` and the `== ChangeLog ==` section when releasing).
- Requires WooCommerce to be active; activation hook kills the load with an error message otherwise.
- PHP ≥ 7.0, WordPress ≥ 5.0, WooCommerce ≥ 6.0.

## Build / test

There is no build system, no bundler, no test suite. Files are committed as-is and loaded directly by WordPress.

- `composer install` — only pulls `printcart/php-printcart-sdk`. The committed code does **not** currently use the SDK — all HTTP calls go through `PC_W2P_API` using `wp_remote_get`/`wp_remote_post` directly. Don't assume the SDK is wired up.
- `vendor/` and `*.zip` are gitignored; release artifacts are built by zipping the plugin directory.
- Translations: `languages/printcart-integration.pot` is the template; regenerate with WP-CLI (`wp i18n make-pot . languages/printcart-integration.pot`) if you add/change translatable strings.

## Architecture

Load order is fixed in `printcart-design.php`:

```
class-utilities.php        → PC_W2P_UTILITIES (HPOS-aware meta helpers, font subsets)
class-pc-api.php           → PC_W2P_API       (all calls to https://api.printcart.com/v1)
class-pc-admin-settings.php→ Printcart_Admin_Settings (wp-admin menu + pages)
class-pc-hook.php          → Printcart_Product_Hook   (WC front-end + order integration)
class-pc-custom-api.php    → Printcart_REST_Custom_Controller (/wp-json/wc/v3/printcart/api-key)
```

Product/order list-table classes in `includes/class-pc-product-table.php` and `includes/class-pc-order-table.php` are loaded lazily by `Printcart_Admin_Settings::printcart_products()` / `printcart_orders()`. **Both files declare a class named `Printcart_Options_List_Table`** — they are never loaded together, but because they share a name you cannot `require_once` both in the same request. Keep the lazy-load pattern.

### Credentials and auth flow

All credentials live in the single WP option `printcart_w2p_account` as `{ sid, secret, unauth_token }`. Legacy installs may have `printcart_account`; the activation hook in `printcart-design.php` migrates it.

Three auth modes depending on the caller:
- **Admin → Printcart API**: HTTP Basic with `sid:secret` (see `PC_W2P_API::get_basic_auth`).
- **Frontend → Printcart Designer SDK**: `X-PrintCart-Unauth-Token` header, token fetched from the store-details endpoint on save (`get_header_unauth_token`).
- **Setup wizard iframe**: JWT from `/account/render-jwt`, embedded into `PRINTCART_BACKOFFICE_URL/setup-wizard?pc-sid=…&pc-token=…`.

The "authorize" button on the settings page links out to `PRINTCART_BACKOFFICE_URL/authorize` with `callback_url=<home_url>/wp-json/wc/v3/printcart/api-key`. That REST route (in `class-pc-custom-api.php`, registered under the `wc/v3` namespace via the `woocommerce_rest_api_get_rest_namespaces` filter) **has `check_permission_unauth` returning `true`** — it is an open endpoint that writes `printcart_w2p_account`. Any change here is security-sensitive.

### Front-end design capture → order fulfillment

The pipeline is:
1. `Printcart_Product_Hook::printcart_add_sdk()` renders `views/start-and-upload-design.php` on the single-product page. The hook is attached to one of four WC action hooks depending on `printcart_w2p_button_posititon` (1 = before add-to-cart button, 2 = before form, 3 = after button, 4 = after single product / sticky).
2. `printcart_get_product_integration()` calls `/integration/woocommerce/products/{wc_product_id}` to discover the linked Printcart product (and `enable_design` / `enable_upload` flags). Variable products re-resolve via the `printcart_get_product_integration_by_variation` AJAX action.
3. The Designer SDK posts selected designs back into the form as `printcart_options_design` / `printcart_options_design_upload`. `printcart_add_cart_item_data` stashes them in the cart item under `printcart_options`.
4. `printcart_order_line_item` serializes them onto WC order items as `_printcart_designs` and `_printcart_design_upload` meta (both are also registered as hidden via `woocommerce_hidden_order_itemmeta`).
5. `printcart_create_project` runs on `woocommerce_thankyou`, collects the design IDs from the order, calls `PC_W2P_API::createOrder()` (`POST /projects`), and stores the returned project ID on the order as `_printcart_project_id`. Guarded by `$project_id` existence so it is idempotent on thank-you page reloads.

### HPOS (High-Performance Order Storage)

The plugin declares HPOS compatibility in `printcart-design.php` via `FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true )`. All order meta access MUST go through `PC_W2P_UTILITIES::{get,update,add,delete}_post_meta`, which branch on `check_hpos_enabled()` and use the WC CRUD API (`$order->get_meta` / `update_meta_data` / `save`) when HPOS is on, falling back to `get_post_meta`/`update_post_meta` otherwise. Don't call `get_post_meta` directly on order IDs.

The admin "Printcart Customer Design" meta box is registered against either `wc_get_page_screen_id('shop-order')` (HPOS) or the legacy `'shop_order'` screen; `printcart_add_design_box` handles both.

### Views

Everything under `views/` is plain PHP templates `include_once`'d from the admin class. They read WP options / API results from the variables the including method defines — if you add a variable they depend on, trace back to the caller in `class-pc-admin-settings.php`.

### AJAX endpoints

Registered in `Printcart_Product_Hook::printcart_ajax()`:
- `printcart_get_product_integration_by_variation` — variation lookup on the PDP.
- `printcart_generate_key` — creates a WooCommerce REST API key row directly in `{$wpdb->prefix}woocommerce_api_keys` after deleting previous rows with the same `description`. Requires `edit_user` cap.
- `printcart_w2p_check_connection_dashboard` — probes Printcart with a candidate sid/secret pair.

All three are registered with both `wp_ajax_` and `wp_ajax_nopriv_` prefixes (the `$nopriv = true` flag in the `$ajax_events` map). `printcart_generate_key` does its own `current_user_can` check, but the others do not — any new AJAX action here should decide deliberately whether unauthenticated access is intended.

## Gotchas

- Two classes share the name `Printcart_Options_List_Table` (products vs. orders table). Keep them in their own files, loaded lazily.
- Design metadata is stored as PHP-`serialize`'d arrays (not JSON) on order items; `unserialize` is used on the read path. Don't change serialization without a migration.
- `PRINTCART_DESIGNER_SDK_URL` currently points to `assets/js/printcart-designer.min.js` (bundled), while `PRINTCART_DESIGNTOOL` and `PRINTCART_BACKOFFICE_URL` point to `customizer.printcart.com` / `dashboard.printcart.com`. The SDK file is binary and not meant to be hand-edited.
- The option key is misspelled as `printcart_w2p_button_posititon` (extra `i`). Reuse the misspelling — there are stored values in the wild under that key.
- `readme.txt` is the WordPress.org plugin directory format, not a GitHub README. Update `Stable tag`, `Tested up to`, and `== ChangeLog ==` together with `PRINTCART_VERSION` on release.
