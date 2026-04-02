# CLEANUP: Payment Gateways & Transactions

**Phase:** 1 (independent — no prerequisites)  
**Status:** ✅ COMPLETE

---

## SCOPE

Remove all payment gateway integrations and the transaction system entirely.  
Medicine billing (`medicine_bills.payment_type`) is **KEPT** — it's a separate system.

---

## CONTROLLERS TO DELETE

| File                                                  | Status     |
| ----------------------------------------------------- | ---------- |
| `app/Http/Controllers/PaypalController.php`           | ✅ Deleted |
| `app/Http/Controllers/AuthorizePaymentController.php` | ✅ Deleted |
| `app/Http/Controllers/PayTMController.php`            | ✅ Deleted |
| `app/Http/Controllers/TransactionController.php`      | ✅ Deleted |

---

## MODELS TO DELETE

| File                            | Status     |
| ------------------------------- | ---------- |
| `app/Models/Transaction.php`    | ✅ Deleted |
| `app/Models/PaymentGateway.php` | ✅ Deleted |
| `app/Models/Currency.php`       | ✅ Deleted |

---

## LIVEWIRE COMPONENTS TO DELETE

| File                                       | Status     |
| ------------------------------------------ | ---------- |
| `app/Livewire/TransactionTable.php`        | ✅ Deleted |
| `app/Livewire/DoctorsTransactionTable.php` | ✅ Deleted |
| `app/Livewire/PatientTransactionTable.php` | ✅ Deleted |

---

## REPOSITORIES TO DELETE

| File                                         | Status     |
| -------------------------------------------- | ---------- |
| `app/Repositories/TransactionRepository.php` | ✅ Deleted |

---

## REQUEST CLASSES TO DELETE

| File                                                         | Status     |
| ------------------------------------------------------------ | ---------- |
| `app/Http/Requests/CreateTransactionRequest.php` (if exists) | ✅ Deleted |
| `app/Http/Requests/CreatePaytmDetailRequest.php`             | ✅ Deleted |

---

## VIEW DIRECTORIES TO DELETE

| Directory / File                                    | Status                                                 |
| --------------------------------------------------- | ------------------------------------------------------ |
| `resources/views/transactions/` (entire directory)  | ✅ Deleted                                             |
| `resources/views/appointment_pdf/invoice.blade.php` | ⏭️ Deferred to Phase 2 (used by AppointmentController) |

---

## CONFIG FILES TO DELETE

| File                  | Status     |
| --------------------- | ---------- |
| `config/payments.php` | ✅ Deleted |
| `config/paypal.php`   | ✅ Deleted |

---

## ROUTE BLOCKS TO REMOVE

### `routes/web.php`

Remove all blocks with:

- `TransactionController`
- `PaypalController`
- `AuthorizePaymentController`
- `PayTMController`
- Route patterns: `transactions`, `paypal-*`, `authorize-payment-*`, `paytm-*`

### `routes/doctor.php`

- Remove `manage_transactions` permission group

### `routes/staff.php`

- Remove `manage_transactions` permission group

### `routes/patient.php`

- Remove `manage_transactions` permission group

---

## HELPERS TO REMOVE

In `app/helpers.php`, remove these functions:

| Function                       | Approx. Lines | Status     |
| ------------------------------ | ------------- | ---------- |
| `setStripeApiKey()`            | ~10 lines     | ✅ Removed |
| `getAllPaymentStatus()`        | ~10 lines     | ✅ Removed |
| `paymentMethodLangChange()`    | ~15 lines     | ✅ Removed |
| `paypalCurrencySupports()`     | ~15 lines     | ✅ Removed |
| `authorizedCurrencySupports()` | ~10 lines     | ✅ Removed |
| `paytmCurrencySupports()`      | ~10 lines     | ✅ Removed |
| `zeroDecimalCurrencies()`      | ~15 lines     | ✅ Removed |

---

## SEEDER CLEANUP

### `database/seeders/DefaultPermissionSeeder.php`

- Remove: `manage_transactions` (line 85)

### `database/seeders/RolePermissionsSeeder.php`

- Remove `manage_transactions` from doctor and staff role assignments

### `database/seeders/DefaultAssignPermissionSeeder.php`

- Remove `manage_transactions` from patient and doctor assignments

### `database/seeders/DefaultPaymentGatewaySeeder.php`

- **Entire file** — delete or leave commented (already commented out in DatabaseSeeder.php)
- `database/seeders/DefaultCurrenciesSeeder.php` — already commented out

---

## COMPOSER PACKAGES TO REMOVE

```bash
composer remove srmklive/paypal stripe/stripe-php anandsiddharth/laravel-paytm-wallet authorizenet/authorizenet gerardojbaez/money
```

> ⚠️ Run this AFTER all class references are removed from PHP files, or `composer remove` will fail on autoload generation.

---

## ENV VARIABLES TO REMOVE

Remove from `.env` and `.env.example` (if present):

```
STRIPE_KEY=
STRIPE_SECRET=
PAYPAL_MODE=
PAYPAL_SANDBOX_API_USERNAME=
PAYPAL_SANDBOX_API_PASSWORD=
PAYPAL_SANDBOX_API_SECRET=
PAYPAL_LIVE_API_USERNAME=
PAYPAL_LIVE_API_PASSWORD=
PAYPAL_LIVE_API_SECRET=
PAYTM_ENVIRONMENT=
PAYTM_MERCHANT_ID=
PAYTM_MERCHANT_KEY=
PAYTM_MERCHANT_WEBSITE=
PAYTM_CHANNEL=
PAYTM_INDUSTRY_TYPE=
AUTHORIZE_NET_API_LOGIN_ID=
AUTHORIZE_NET_TRANSACTION_KEY=
AUTHORIZE_NET_SANDBOX=
```

---

## NAVIGATION CLEANUP (handled in CLEANUP_NAVIGATION.md)

- `resources/views/layouts/menu.blade.php` — Remove 3 Transaction nav items (doctor/patient/admin)
- `resources/views/layouts/sub_menu.blade.php` — Remove Doctor Transactions (L29), Patient Transactions (L47), Admin Transactions (L262)

---

## DATABASE TABLES TO DROP (handled in CLEANUP_DATABASE.md)

- `transactions`
- `payment_gateways`
- `currencies`

---

## COMPLETION CHECKLIST

- [x] Delete 4 controllers — `PaypalController`, `AuthorizePaymentController`, `PayTMController`, `TransactionController`
- [x] Delete 3 models — `Transaction.php` (`PaymentGateway.php`, `Currency.php` not found — likely never created)
- [x] Delete 3 Livewire table components — `TransactionTable`, `DoctorsTransactionTable`, `PatientTransactionTable`
- [x] Delete TransactionRepository
- [x] Delete view directories — `resources/views/transactions/` (entire directory)
- [x] Delete config files: `config/payments.php`, `config/paypal.php`
- [x] Delete request classes: `CreatePaytmDetailRequest` (`CreateTransactionRequest` not found)
- [x] Delete seeders: `DefaultPaymentGatewaySeeder.php`, `DefaultCurrenciesSeeder.php`
- [x] Remove route blocks in `web.php`, `doctor.php`, `staff.php`, `patient.php`
- [x] Remove 7 helper functions from `helpers.php` + `use Stripe\Stripe` import
- [x] Update `DefaultPermissionSeeder` — removed `manage_transactions`
- [x] Update `DefaultAssignPermissionSeeder` — removed `manage_transactions`
- [x] `RolePermissionsSeeder` — already clean (no `manage_transactions`)
- [x] Remove 3 Transaction nav blocks from `menu.blade.php`
- [x] Remove 3 Transaction nav items from `sub_menu.blade.php`
- [x] Run `composer remove` — 5 payment packages removed successfully
- [x] Gate 1 passed — `php artisan view:cache` clean, `route:list` has no payment routes
- [ ] Clean `.env` of payment credentials (manual step — see ENV section above)
- [ ] Database: drop `transactions`, `payment_gateways`, `currencies` tables — **Phase 7**
- [ ] `resources/views/appointment_pdf/invoice.blade.php` — **deferred to Phase 2** (used by AppointmentController)

---

_Phase 1 — No prerequisites. Can start immediately._
