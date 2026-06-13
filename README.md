# AAWEB Wholesale Delayed Payments

AAWEB Wholesale Delayed Payments adds a delayed payment workflow for WooCommerce wholesale customers.

## Features

- Wholesale-only checkout workflow
- Preferred payment method selection
- No immediate payment required
- Automatic order placement in On Hold
- Administrator approval before payment
- WooCommerce Order Pay support
- HPOS compatible
- Lightweight and fast
- No tracking or external services

## How It Works

1. Wholesale customer places an order.
2. Customer selects a preferred payment method:
   - Card Payment
   - Bank Transfer
   - Cash on Delivery
3. Order is created with status **On Hold**.
4. No payment is collected during checkout.
5. Administrator reviews availability and stock.
6. Administrator changes order status to **Pending Payment**.
7. Customer completes payment through normal WooCommerce payment gateways.

## Supported User Role

```text
wholesale_customer
```

## Requirements

- WordPress 6.0+
- WooCommerce
- PHP 7.4+

## Compatibility

- WooCommerce HPOS
- WooCommerce Checkout Blocks
- Classic Checkout
- Most payment gateways

## Author

AAWEB – Apostolou Antonios

https://antoapweb.gr

## License

GPLv2 or later