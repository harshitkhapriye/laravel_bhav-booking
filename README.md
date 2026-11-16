# Bhav Booking & Bullion Management System

A high-performance bullion booking, trade accounting, and party master management system designed for Indian jewellery and bullion merchants. It supports real-time multi-entry rate booking, gold & silver rate calculations (per 10 grams), advance payment splits, customer insight, thermal receipt generation, and live market rate comparisons.

---

> [!CAUTION]
> **CRITICAL PRODUCTION WARNING — SECURE BACKUP OF `APP_KEY`**
>
> Customer **Aadhaar** and **PAN** numbers are encrypted at rest in the database using Laravel's AES-256 encryption via Eloquent attribute casting (`'encrypted'`). The encryption key is derived directly from `APP_KEY` in your `.env` file.
>
> **You MUST securely back up your `APP_KEY` before deploying to production.**
> If `APP_KEY` is lost, regenerated, or altered, **all existing encrypted customer Aadhaar and PAN numbers will become permanently unrecoverable**.

---

## 1. Project Overview & Key Features

- **Multi-Entry Bullion Booking**: Book single or multi-row entries for Gold and Silver with independent weights, rates, and amounts.
- **Auto-Split Multi-Metal Bookings**: Seamlessly mix Gold and Silver rows in a single form; the system automatically creates two grouped bookings linked by `group_id`.
- **Indian Bullion Standard Math**: Calculations standard to Indian bullion markets: `Amount = (Weight in GM × Rate per 10GM) / 10`.
- **Advance & Payment Tracking**: Supports Cash, Online (UPI/NEFT/RTGS with ref code), and split payments with strict over-payment validation.
- **Trade View Customization**: Dedicated BUY and SELL views, plus an ALL view with responsive column widths and zero horizontal scroll on desktop (≥1366px).
- **Party Master & KYC**: Customer directory with mobile validation, encrypted Aadhaar/PAN, family sub-profiles, document uploads, and balance overview.
- **Thermal & A4 Receipts**: One-click "Save & Print" (popup-blocker safe) supporting both standard A4 and compact 80mm POS thermal formats (`?format=thermal`).
- **Live Market Rate vs Today**: Real-time gain/loss calculation on pending bookings compared to today's live rate from the shop's point of view.
- **Spotlight & Interactive UX**: Active row spotlight on 3-dot kebab menu and "Multiple" popovers, smooth `#eef4ff` row hover, and mobile tabbed interface (<992px).
- **Browser Draft Auto-Save**: Auto-saves form input to `localStorage` (debounced 500ms) with one-click restore banner and 24-hour expiration.
- **Robust Exception Suppression**: Sanitized JSON error responses (`500 {"message": "Server error. Please try again."}`) when `APP_DEBUG=false` preventing raw SQL/PDO leak.

---

## 2. System Requirements

- **PHP**: 8.2 or higher
- **Extensions**: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`, `json`, `fileinfo`
- **Database**: MySQL 8.0+ or MariaDB 10.4+
- **Dependency Managers**: Composer 2.x and Node.js 18+ (with npm)
- **Web Server**: Nginx or Apache (or Laravel built-in server for development)

---

## 3. Setup Instructions (From Zero)

Follow these steps to set up the project on a new development or production server:

```bash
# 1. Clone repository
git clone <repository-url> myapp10
cd myapp10

# 2. Install PHP dependencies
composer install --no-dev --optimize-autoloader # (use composer install in dev)

# 3. Create and configure environment file
cp .env.example .env

# Configure your database credentials in .env:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=myapp10_db
# DB_USERNAME=root
# DB_PASSWORD=your_password

# 4. Generate Application Key (SAVE THIS SECURELY!)
php artisan key:generate

# 5. Run Database Migrations
php artisan migrate --force

# 6. Create Storage Symlink (for customer profile/document images)
php artisan storage:link

# 7. Install Frontend Dependencies & Build Assets
npm install
npm run build # (or npm run dev for local development)

# 8. Start Local Development Server
php artisan serve
```

---

## 4. Automated Testing & Safety Guards

The project includes unit and feature tests covering calculation formulas, split-metal booking logic, canModify lifecycle guards, and raw exception suppression.

### Dedicated Test Database Guard
Automated tests **must run on a dedicated test database** (`myapp10_test`). `tests/TestCase.php` contains a safety guard:

```php
if (config('database.connections.mysql.database') !== 'myapp10_test') {
    throw new \RuntimeException('Refusing to run tests on a non-test database');
}
```

### Running Tests:
```bash
# 1. Ensure myapp10_test database exists in MySQL:
# CREATE DATABASE IF NOT EXISTS myapp10_test;

# 2. Run the test suite:
php artisan test
```

---

## 5. Directory & Architecture Map

```
myapp10/
├── app/
│   ├── Exceptions/
│   │   └── Handler.php                     # Global exception handler (suppresses raw SQL/PDO in JSON)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── BhavBookingController.php   # Main booking CRUD, search, filter, export, print
│   │   │   ├── CustomerController.php      # Customer search, recents, insight
│   │   │   ├── DailyRateController.php     # Bullion daily rates retrieval & updates
│   │   │   └── PartyController.php          # Party Master list, CRUD, image upload handling
│   │   └── Requests/
│   │       ├── StoreBhavBookingRequest.php # Validation rules for booking creation
│   │       ├── UpdateBhavBookingRequest.php# Validation rules for booking updates
│   │       ├── StoreCustomerRequest.php    # Validation rules for party creation
│   │       └── UpdateCustomerRequest.php   # Validation rules for party updates
│   ├── Models/
│   │   ├── BhavBooking.php                 # Booking model with relationships & canModify logic
│   │   ├── BhavBookingItem.php             # Individual entry item row
│   │   ├── BhavBookingPayment.php          # Payment record
│   │   ├── Customer.php                    # Party model with AES-256 encrypted Aadhaar/PAN
│   │   └── DailyRate.php                   # Market rates history
│   └── Services/
│       └── BhavBookingService.php          # Domain logic: weighted avg, rate diff, multi-metal split
├── config/
│   ├── bhav.php                            # Default bullion rates, unit grams (10), limits
│   └── app.php                             # App locale, encryption cipher, debug flags
├── database/
│   └── migrations/                         # 16 database migrations for schema
├── docs/
│   └── FINAL_TEST_CHECKLIST.md             # Complete step-by-step browser testing checklist
├── public/
│   ├── css/
│   │   └── style.css                       # Application styles, table layouts, spotlight, prints
│   └── js/
│       ├── bhav-common.js                  # Shared formatting, toast, and spotlight helpers
│       ├── bhav-booking.js                 # Booking form, draft save, keyboard shortcuts, list engine
│       ├── bhav-booking-old.js             # Old bookings (Delivered/Cancelled) view & restore logic
│       └── parties.js                      # Party master list, filtering, KYC modal management
├── resources/views/
│   ├── bhav-bookings/
│   │   ├── index.blade.php                 # Main Bhav Booking workspace
│   │   ├── old.blade.php                   # Old / Archived bookings page
│   │   └── print.blade.php                 # A4 & 80mm thermal receipt template
│   └── parties/
│       └── index.blade.php                 # Party Master management view
└── routes/
    └── web.php                             # Application routes with throttle rate-limiting
```

---

## 6. Route Summary

| Method | URI | Description | Throttle |
| :--- | :--- | :--- | :--- |
| `GET` | `/bhav-bookings` | Main Bhav Booking workspace | — |
| `GET` | `/bhav-bookings/list` | JSON list of bookings (filters, pagination, totals) | 120 / min |
| `POST` | `/bhav-bookings` | Create new booking (or split multi-metal) | 30 / min |
| `GET` | `/bhav-bookings/{id}` | Show single booking with items, payments, vs Today | 120 / min |
| `PUT` | `/bhav-bookings/{id}` | Update existing booking | 30 / min |
| `DELETE` | `/bhav-bookings/{id}` | Soft-delete booking | 30 / min |
| `POST` | `/bhav-bookings/{id}/restore` | Restore soft-deleted booking (undo delete) | 30 / min |
| `POST` | `/bhav-bookings/{id}/cancel` | Cancel booking (marks CANCELLED) | 30 / min |
| `GET` | `/bhav-bookings/{id}/print` | Print single receipt (A4 or thermal) | — |
| `GET` | `/bhav-bookings/group/{groupId}/print` | Group print for multi-metal split bookings | — |
| `GET` | `/bhav-bookings/export` | Download filtered bookings CSV | 10 / min |
| `GET` | `/bhav-bookings/old` | Old bookings view (DELIVERED & CANCELLED) | — |
| `GET` | `/customers/search` | Search customers by name, phone, or customer_no | 120 / min |
| `GET` | `/customers/recent` | Fetch recently transacted customers | 120 / min |
| `GET` | `/customers/{id}/insight` | Customer summary, balance, pending bookings | 120 / min |
| `POST` | `/customers` | Create party with encrypted Aadhaar/PAN & KYC | 30 / min |
| `GET` | `/parties` | Party Master page | — |
| `GET` | `/parties/list` | Party directory JSON list with filters | 120 / min |
| `GET` | `/parties/{customer}` | View customer profile | 120 / min |
| `PUT` | `/parties/{customer}` | Update customer profile & KYC | 30 / min |
| `DELETE` | `/parties/{customer}` | Safe delete party (blocked if bookings exist) | 30 / min |
| `GET` | `/daily-rates/today` | Fetch today's gold & silver rates & trend | — |
| `POST` | `/daily-rates` | Update today's gold & silver rates | 30 / min |

---

## 7. Business Rules & Calculation Formulas

### 7.1 Unit of Rate & Amount Formula
In the Indian bullion market, Gold and Silver rates are quoted **per 10 grams** (1 tola standard):
$$\text{Amount} = \frac{\text{Weight (gm)} \times \text{Rate per 10gm}}{10}$$

### 7.2 Weighted Average Rate
When a booking has multiple entry items, the combined average rate is weighted by weight:
$$\text{Booked Average Rate} = \frac{\sum \text{Amount}_i \times 10}{\sum \text{Weight}_i}$$

### 7.3 "vs Today" Difference (Shop Point of View)
Calculated only for `PENDING` or `PARTIAL` bookings when today's daily rate for that metal is configured:
- **Pending Weight**: $\text{Total Weight} - \text{Delivered Weight}$
- **SELL Booking**:
  $$\text{Gain/Loss} = \frac{(\text{Booked Rate} - \text{Today Rate}) \times \text{Pending Weight}}{10}$$
  *(Positive = Shop sold at higher rate than today; Gain for shop. Negative = Shop sold cheaper; Loss for shop)*
- **BUY Booking**:
  $$\text{Gain/Loss} = \frac{(\text{Today Rate} - \text{Booked Rate}) \times \text{Pending Weight}}{10}$$
  *(Positive = Market rate increased after buying; Gain for shop)*

### 7.4 `canModify` Lifecycle Guard
A booking may be edited or deleted **ONLY** if:
1. Status is `PENDING`.
2. Delivered weight is zero (`delivered_weight == 0`).
3. No settlement / buy-sell conversion payments exist.
4. Booking is not `CANCELLED` or soft-deleted.

This rule is enforced identically in the backend Eloquent model (`BhavBooking::canModify()`) and frontend JavaScript.

### 7.5 Multi-Metal Split Rule
If a single booking submission includes both **Gold** and **Silver** rows:
- The system generates two distinct bookings sharing a single UUID `group_id`.
- The customer, trade mode, and remarks are linked.
- The group print URL (`/bhav-bookings/group/{groupId}/print`) renders both bookings on a consolidated receipt.

---

## 8. Client Review & Production Handover Notes

Before launching into high-volume live production, the following items should be reviewed by the client:

1. **Daily Rate Automation / Cron Schedule**:
   - Currently, daily bullion rates are updated manually via the "Edit Rates" modal or API (`POST /daily-rates`).
   - If the merchant subscribes to an external bullion live API (e.g. IBJA, MCX), an artisan command can be scheduled via `routes/console.php` to fetch and update rates automatically every morning.
2. **Thermal Printer Configuration**:
   - The thermal receipt layout is optimized for standard **80mm (3-inch)** receipt printers (72mm printable area).
   - In the browser print dialog, set Paper Size to `80mm` (or `Receipt`), Margins to `None`, and disable Headers/Footers.
3. **WhatsApp Web vs WhatsApp Business API**:
   - The current WhatsApp integration formats a rich receipt summary and redirects to `https://wa.me/{mobile}?text={encoded_message}` for instant 1-click customer notification via WhatsApp Web or mobile app without ongoing API costs.
   - If automated background WhatsApp delivery without manual user click is required, an official provider (e.g., Twilio, Gupshup, Aisensy) can be hooked into `BhavBookingController::store()`.
4. **Data Backup Policy**:
   - Ensure automated daily database dumps (`mysqldump`) and backup of the `storage/app/public` directory.
   - Re-verify that `APP_KEY` is documented in an encrypted password vault.
