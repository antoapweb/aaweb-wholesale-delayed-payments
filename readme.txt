=== AAWEB Wholesale Delayed Payments ===
Contributors: antoapweb
Tags: woocommerce, wholesale, b2b, delayed-payment, payment-gateway
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allow wholesale customers to place WooCommerce orders without immediate payment. Admin approval is required before payment becomes available.

== Description ==

AAWEB Wholesale Delayed Payments adds a delayed payment workflow for WooCommerce wholesale customers.

Instead of paying immediately during checkout, wholesale customers can:

* Submit their order without payment.
* Select a preferred payment method.
* Wait for order review and stock confirmation.
* Complete payment only after the administrator approves the order.

This workflow is ideal for:

* B2B stores
* Wholesale suppliers
* Distributors
* Manufacturers
* Custom quotation workflows

= Features =

* Wholesale-only checkout workflow.
* Separate payment preference selection.
* No immediate payment required.
* Automatic order placement in "On Hold".
* Administrator reviews the order before payment.
* Supports WooCommerce Order Pay page.
* Compatible with WooCommerce HPOS.
* No external services required.
* Lightweight and fast.
* No tracking or data collection.

= How It Works =

1. A customer with the role `wholesale_customer` places an order.
2. During checkout they choose their preferred payment method:
   * Card Payment
   * Bank Transfer
   * Cash on Delivery
3. The order is created with status "On Hold".
4. No payment is collected during checkout.
5. The administrator reviews stock and availability.
6. The administrator changes the order status to "Pending Payment".
7. The customer can then access the payment page and complete payment using normal WooCommerce payment gateways.

= Wholesale Role =

The plugin works with the following user role:

`wholesale_customer`

You can create this role using:

* Wholesale Suite
* WooCommerce Wholesale Prices
* Any custom role management plugin
* Custom code

= Payment Gateways =

During wholesale checkout:

* Only the virtual "Wholesale Pending Payment" gateway is shown.

After administrator approval:

* All normal WooCommerce payment gateways become available.
* The virtual gateway is automatically hidden.

= HPOS Compatibility =

This plugin is compatible with WooCommerce High Performance Order Storage (HPOS).

== Installation ==

1. Upload the plugin files to:

`/wp-content/plugins/aaweb-wholesale-delayed-payments/`

2. Activate the plugin through the WordPress Plugins screen.
3. Ensure WooCommerce is installed and activated.
4. Assign the role `wholesale_customer` to wholesale users.

== Frequently Asked Questions ==

= Does this affect retail customers? =

No.

Retail customers continue using the standard WooCommerce checkout process.

= Can I use Stripe? =

Yes.

Any WooCommerce-compatible payment gateway can be used after administrator approval.

= Can I use Viva Wallet? =

Yes.

Any gateway available on the WooCommerce payment page can be used.

= Does the plugin create a new order status? =

No.

It uses WooCommerce's built-in statuses:

* On Hold
* Pending Payment

= Does it support HPOS? =

Yes.

HPOS compatibility is included.

= Is any customer data sent externally? =

No.

The plugin does not connect to external services.

== Screenshots ==

1. Wholesale payment preference field on checkout.
2. Wholesale order stored as On Hold.
3. Payment preference visible in WooCommerce admin.
4. Approved order ready for payment.

== Changelog ==

= 1.0.0 =

* Initial release.
* Wholesale delayed payment workflow.
* Preferred payment method selection.
* Virtual payment gateway.
* WooCommerce HPOS support.
* Order approval before payment.

== Upgrade Notice ==

= 1.0.0 =

Initial public release.