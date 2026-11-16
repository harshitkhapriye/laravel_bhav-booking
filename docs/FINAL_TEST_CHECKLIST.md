# Bhav Booking — Handover Acceptance & Manual QA Checklist

This document provides a comprehensive, step-by-step browser testing checklist for the complete **Bhav Booking & Bullion Management System**. Every test case specifies exact user actions (**Steps**) and the expected system response (**Expected Result**).

---

## 1. Customer Search, Selection & Recents

### 1.1 Customer Dropdown & Search
- **Steps**:
  1. Open the Bhav Booking main page (`/bhav-bookings`).
  2. Click on the customer search input box in the booking card.
  3. Type 2 or 3 letters of an existing party's name or mobile number.
- **Expected Result**:
  - A debounced search runs (`GET /customers/search?q=...`).
  - Dropdown displays matching parties with their Name, Customer ID, Mobile number, and status badge.
  - Matches highlight the search term.

### 1.2 Recent Customer Selection
- **Steps**:
  1. Click the customer search input with an empty query.
- **Expected Result**:
  - Dropdown lists recently used/booked customers.
  - Selecting a customer populates their details immediately into the form.

### 1.3 Customer Insight Badge & Balance Preview
- **Steps**:
  1. Select a party that has past transaction balance or pending bookings.
- **Expected Result**:
  - The customer badge displays with their Customer ID, Name, and Mobile.
  - An info tooltip or insight chip displays pending bookings and balance overview (`/customers/{id}/insight`).
  - "Change" / remove button is visible next to the customer name.

### 1.4 Changing / Removing Customer in New Booking
- **Steps**:
  1. In a new unsaved booking with an active customer selected, click the remove / "Change" icon.
- **Expected Result**:
  - If entries or payments were entered, a confirmation modal appears: *"Changing the customer will keep the entries but assign them to the new party. Proceed?"*.
  - Confirming clears the selected party and focuses the search input.

---

## 2. New Party Modal & Party Master Management

### 2.1 Quick Add Customer Modal
- **Steps**:
  1. In the customer dropdown, click **"+ Add New Party"** (or press the Add button).
  2. Enter Party Name (required) and Mobile Number (10 digits).
- **Expected Result**:
  - Modal opens cleanly without page scroll issues.
  - Form validation enforces required fields and numeric 10-digit mobile number.

### 2.2 KYC & Additional Information Toggle
- **Steps**:
  1. In the Add New Party modal, click the **"Additional Info"** collapsible toggle.
  2. Enter Aadhaar Number (12 digits), PAN Number (10 characters: `[A-Z]{5}[0-9]{4}[A-Z]{1}`), Address, City, State, and Pincode.
  3. Attach a profile / document image (PNG or JPG under 2MB).
  4. Submit the form.
- **Expected Result**:
  - Aadhaar and PAN inputs format cleanly and pass format validation.
  - On save, Aadhaar and PAN are encrypted via AES-256 before storing in the database.
  - Image is uploaded to `storage/app/public/customers` and the path is linked.
  - Modal closes, success toast shows, and the new customer is automatically selected in the booking form.

### 2.3 Family Sub-Profile Toggle
- **Steps**:
  1. Open Add New Party modal.
  2. Toggle **"Add as Family Member / Sub-profile"**.
  3. Select an existing primary party from the dropdown, enter relationship (e.g., "Spouse", "Son"), and save.
- **Expected Result**:
  - Sub-profile links to `parent_id` in database.
  - Insight and party records reflect the family linkage.

### 2.4 Party Master List (`/parties`)
- **Steps**:
  1. Navigate to Party Master (`/parties`).
  2. Filter by Search (name/mobile), City, or Status.
- **Expected Result**:
  - Table loads parties with pagination.
  - Aadhaar and PAN are NEVER rendered in raw plaintext in JSON responses or DOM (masked or hidden).
  - Hovering a row highlights it with `#eef4ff` smoothly.

### 2.5 Party Master View & Edit
- **Steps**:
  1. Click **View** on a party row.
  2. Inspect details, family sub-profiles, and uploaded documents.
  3. Click **Edit**, update the mobile or city, and click Save.
- **Expected Result**:
  - View modal displays clean customer profile without raw Aadhaar/PAN leak.
  - Edit updates party record immediately with success toast.

### 2.6 Party Master Safe Delete Check
- **Steps**:
  1. Attempt to delete a party that has linked Bhav Bookings.
  2. Attempt to delete a party with NO linked bookings.
- **Expected Result**:
  - Party with bookings: Delete is rejected with a clear error: *"Cannot delete party with existing bookings"*.
  - Party without bookings: Delete confirmation prompt appears; on confirm, soft-deletes or removes party and updates the list.

---

## 3. Booking Form & Calculation Engine

### 3.1 BUY Mode vs SELL Mode Toggle
- **Steps**:
  1. On `/bhav-bookings`, switch between the **BUY** and **SELL** toggle buttons.
- **Expected Result**:
  - BUY mode sets theme accents to green (`#10b981`), sets default rate to today's Gold/Silver BUY rate, and labels payment field as **"PAID"** (Shop pays customer).
  - SELL mode sets theme accents to red/coral (`#ef4444`), sets default rate to today's Gold/Silver SELL rate, and labels payment field as **"RECEIVED"** (Shop receives from customer).

### 3.2 Single Entry Row Calculation
- **Steps**:
  1. Select Category: **Gold**.
  2. Enter Weight: `15.500` GM.
  3. Enter Rate: `1,65,000` (per 10 GM).
- **Expected Result**:
  - Amount automatically calculates: `(15.500 * 165000) / 10 = ₹2,55,750.00`.
  - Amount in words updates dynamically below the amount field: *"Rupees Two Lakh Fifty-Five Thousand Seven Hundred Fifty Only"*.

### 3.3 Multi-Row Entries
- **Steps**:
  1. Click the **"+"** button on the entry row to add a second row.
  2. Row 1: Gold, 10.000 GM @ 1,65,000/10g = ₹1,65,000.
  3. Row 2: Gold, 5.000 GM @ 1,66,000/10g = ₹83,000.
- **Expected Result**:
  - Total Weight displays `15.000 GM`.
  - Total Amount displays `₹2,48,000.00`.
  - Weighted Average Rate displays `₹1,65,333.33 / 10g`.

### 3.4 Multi-Metal Split (Gold + Silver in One Form)
- **Steps**:
  1. In Row 1, select **Gold**, 10.000 GM @ 1,65,000/10g = ₹1,65,000.
  2. In Row 2, select **Silver**, 500.000 GM @ 1,800/10g = ₹90,000.
  3. Fill payment: Cash ₹55,000.
  4. Click **SAVE**.
- **Expected Result**:
  - Backend splits the transaction into **two distinct bookings** sharing the same `group_id`.
  - Booking 1 is created for Gold (`total_weight = 10.000`, `total_amount = 165000`).
  - Booking 2 is created for Silver (`total_weight = 500.000`, `total_amount = 90000`).
  - Payment is assigned to the primary booking or split proportionately.
  - Success popup displays both booking numbers with group print option.

### 3.5 Payments (Cash, Online, Split)
- **Steps**:
  1. In the Received/Paid section, enter Cash: `50,000`.
  2. Click **"+"** or add Online payment: `25,000` with reference `UPI-987654321`.
- **Expected Result**:
  - Total Paid/Received displays `₹75,000.00`.
  - Balance calculates accurately: `Total Amount - Total Paid = Balance`.
  - Validation blocks payment exceeding `total_amount`.

### 3.6 Edit Existing Booking
- **Steps**:
  1. Click **Edit** on a PENDING booking from the table.
  2. Form loads in edit mode: URL updates to `?edit={id}` (or populates state).
  3. Notice the Customer field: customer is **locked** with a lock icon.
  4. Click the lock/customer box.
- **Expected Result**:
  - Alert/popup warns: *"Customer cannot be changed while editing an existing booking. To change the party, cancel this booking and create a new one."*
  - Entry weights, rates, narration, and payments are editable.
  - Saving updates the booking without altering customer identity.

### 3.7 Auto-Save Draft
- **Steps**:
  1. In a fresh booking form (not edit mode), select a customer and type a weight and rate.
  2. Wait 1 second (debounced 500ms auto-save).
  3. Refresh the browser page (`F5`).
- **Expected Result**:
  - A draft banner appears at the top of the booking form: *"Unsaved booking from a few seconds ago — [Restore] [Discard]"*.
  - Clicking **[Restore]** repopulates customer, metal, rows, rates, weights, and payments exactly as left.
  - Clicking **[Discard]** deletes `localStorage.bhavBookingDraft` and clears the banner.
  - Drafts older than 24 hours are cleared silently on load.

---

## 4. Save, Print & Thermal Output

### 4.1 Standard "SAVE" Button
- **Steps**:
  1. Fill a valid booking form.
  2. Click **SAVE** (or press `Ctrl+S`).
- **Expected Result**:
  - Validation runs; button shows spinner.
  - Booking saves via `POST /bhav-bookings`.
  - Success popup appears with:
    - Booking Number & Customer Name.
    - **"Open Print"** button.
    - **"Close"** button.
  - Current Bookings table automatically refreshes in the background.

### 4.2 "SAVE & PRINT" Combined Action (Popup Blocker Safe)
- **Steps**:
  1. Fill a booking form.
  2. Click the dropdown arrow on the save button and choose **SAVE & PRINT** (or press `Ctrl+P`).
- **Expected Result**:
  - A blank tab immediately opens with *"Preparing print..."* (satisfying browser popup-blocker security context).
  - API call completes successfully.
  - The opened tab redirects to `/bhav-bookings/{id}/print`.
  - If a browser blocks the tab, a toast appears with an explicit clickable *"Print now"* link.

### 4.3 A4 Format Print
- **Steps**:
  1. Open print preview: `/bhav-bookings/{id}/print`.
- **Expected Result**:
  - Full A4 receipt renders with shop header, booking ID, date/time, customer details, items table (metal, weight, rate per 10g, amount), payment breakdown, balance, and signature zones.
  - Clean CSS `@media print` layout without navigation bars or buttons.

### 4.4 Thermal 80mm Print
- **Steps**:
  1. Open `/bhav-bookings/{id}/print?format=thermal`.
- **Expected Result**:
  - Page sets `@page { size: 80mm auto; margin: 0 }`.
  - Content width is 72mm, monospaced/compact font 11–12px, high-contrast black on white.
  - Dashed separators, compact table, QR code (if enabled), and shop footer.
  - Prints cleanly on standard 80mm POS receipt printers.

---

## 5. Current Bookings Table (`/bhav-bookings`)

### 5.1 Filters Bar
- **Steps**:
  1. Filter by **Trade View**: Click `ALL`, `BUY`, and `SELL`.
  2. Filter by **Metal**: Select `Gold` or `Silver`.
  3. Filter by **Type**: Select `Only Entry` vs `Rate Cut`.
  4. Filter by **Date Range**: Select Today, Yesterday, This Week, or Custom Range.
  5. Filter by **Party**: Search and pick a specific party.
  6. Change **Page Size**: Switch between 10, 25, 50, 100 per page.
- **Expected Result**:
  - Table reloads without full page reload.
  - Table footer updates with exact sum of filtered records (Total Gold WT, Total Silver WT, Total Amount, Total Paid/Received).

### 5.2 Responsive Layout & Zero Desktop Horizontal Scroll
- **Steps**:
  1. Resize browser window to `1366px` and `1920px` in `SELL` view and `BUY` view.
  2. Switch to `ALL` view.
- **Expected Result**:
  - At ≥1366px in BUY-only or SELL-only views: Table fits 100% card width with **NO horizontal scrollbar**.
  - Customer name truncates cleanly with ellipsis; hover shows full name tooltip.
  - In `ALL` view: Combined BUY + SELL columns fit cleanly with compact padding (~4px) and min-width ~1120px. Horizontal scrollbar only appears if viewport width is insufficient.

### 5.3 "Multiple" Entries Popover
- **Steps**:
  1. Locate a booking that has multiple entry rows.
  2. In the table, observe the **"Multiple"** badge in the Rate/Weight column.
  3. Click or hover on the "Multiple" badge.
- **Expected Result**:
  - Popover opens displaying the breakdown of all individual entries (Row #, Weight, Rate/10g, Amount).
  - Background table dims/blurs slightly (spotlight mode).
  - Clicking outside or pressing Esc closes the popover and restores table clarity.

### 5.4 Row Hover & Spotlight Effects
- **Steps**:
  1. Move mouse cursor over any table row.
  2. Click the 3-dot kebab menu button on row #3.
- **Expected Result**:
  - Hover: Row lights up with `#eef4ff` across all cells smoothly (overriding zebra and column tints).
  - 3-dot Menu open:
    - Active row #3 gets orange border and `#fff7ed` background.
    - Table receives `.has-active-row` class; all other rows blur (`filter: blur(1.5px); opacity: 0.45`).
    - The 3-dot dropdown menu opens with 10px rounded corners and elevated shadow (`0 16px 36px rgba(15,23,42,0.30)`).
    - Closing the menu (click outside or Esc) removes the spotlight blur immediately.

### 5.5 Action Menu (3-Dot Kebab) Options
- **Steps**:
  1. Click 3-dot menu on a PENDING booking.
  2. Verify all menu items:
     - **View**: Opens View modal.
     - **Print**: Opens print window.
     - **Edit**: Loads booking into form in edit mode.
     - **Cancel**: Prompts for reason; marks status as CANCELLED.
     - **Delete**: Soft-deletes booking with confirmation.
     - **Repeat Booking**: Copies details into form as a new booking with fresh timestamp.
     - **Share on WhatsApp**: Opens `https://wa.me/{mobile}?text=...` with formatted receipt text.
- **Expected Result**:
  - Every action operates smoothly and triggers proper state update.

### 5.6 Undo Delete Notification
- **Steps**:
  1. Delete a booking from the 3-dot menu.
- **Expected Result**:
  - Booking disappears from Current list.
  - A floating snackbar/toast appears: *"Booking #{id} deleted — [Undo]"* (stays for 8 seconds).
  - Clicking **[Undo]** calls `/bhav-bookings/{id}/restore`, restores the booking, and places it back in the table.

---

## 6. View Modal & Today's Rate Comparison

### 6.1 View Modal Details
- **Steps**:
  1. Click **View** on any booking row.
- **Expected Result**:
  - Modal opens showing:
    - Header: Booking #, Trade (BUY/SELL badge), Metal, Booking Date & Time.
    - Customer Info card.
    - Detailed Entries breakdown table.
    - Payment records with modes, dates, references.
    - Audit log trail (Created at, Updated at by User).
    - **"vs Today" chip**: Shows gain/loss compared to today's bullion rate.

### 6.2 "vs Today" Gain/Loss Logic
- **Steps**:
  1. Check a PENDING SELL booking for Gold @ 1,60,000/10g when today's SELL rate is 1,65,000/10g.
- **Expected Result**:
  - Shop point of view formula: `(Booked Rate - Today Rate) * Weight / 10`.
  - Negative difference renders in red (Shop loss if customer booked cheaper rate).
  - Positive difference renders in green (Shop gain).
  - If today's rate is not set, chip shows *"Rate not set today"*.

---

## 7. Today's Rate Widget & Live Update

### 7.1 Today's Rate Bar
- **Steps**:
  1. Look at the top bar / rate widget on the Bhav Booking page.
- **Expected Result**:
  - Displays Gold (24K / 10g) and Silver (10g / 1kg) BUY and SELL rates.
  - Displays trend pill: Green (▲ increased from yesterday) or Red (▼ decreased from yesterday).

### 7.2 Update Daily Rate Modal
- **Steps**:
  1. Click the **"Edit Rates"** button or icon next to today's rates.
  2. Change Gold Sell Rate to `1,68,000` and save.
- **Expected Result**:
  - Rate updates via `POST /daily-rates`.
  - Top bar rate widget updates immediately.
  - "vs Today" calculations on open modals or recalculation triggers refresh automatically.

---

## 8. Old Bookings Page (`/bhav-bookings/old`)

### 8.1 Delivered & Cancelled History
- **Steps**:
  1. Navigate to Old Bookings (`/bhav-bookings/old`).
- **Expected Result**:
  - Table loads only DELIVERED and CANCELLED bookings.
  - Filter bar supports Date Range, Trade, Metal, and Party search.
  - Delivered bookings show green check badge; Cancelled show grey/red badge.

### 8.2 Restore Booking to Current
- **Steps**:
  1. On a CANCELLED booking in Old Bookings, open the 3-dot menu and select **Restore**.
- **Expected Result**:
  - Confirmation modal appears: *"Restore booking #{id} back to PENDING status?"*.
  - Confirming updates status to `PENDING`.
  - Row is removed from Old Bookings and now appears in Current Bookings.

---

## 9. Mobile Experience (<992px)

### 9.1 Sticky Mobile Tabs
- **Steps**:
  1. Open DevTools, switch to responsive view (iPhone 12 / 390px, iPad / 768px).
  2. Navigate to `/bhav-bookings`.
- **Expected Result**:
  - Top of page renders sticky tab bar: **[ Booking ]** | **[ List (N) ]** (where N = count of active bookings).
  - Desktop two-column layout switches to single active card view.
  - Selecting **List** hides form and shows table/cards.
  - Selecting **Booking** displays the entry form.
  - Choice is remembered in `localStorage` across page refreshes.

### 9.2 Mobile Action Flow
- **Steps**:
  1. In Mobile List tab, click **Edit** on a booking.
- **Expected Result**:
  - View automatically switches to the **Booking** tab.
  - Form populates with booking data in edit mode.
  - Sticky bottom Save bar remains fully accessible and does not overlap virtual keyboard inputs.

---

## 10. Security & Error Handling

### 10.1 Raw SQL & Exception Suppression
- **Steps**:
  1. Trigger an invalid backend condition (or verify automated test `RawSqlExceptionHandlingTest`).
  2. Inspect response with `APP_DEBUG=false`.
- **Expected Result**:
  - Response is HTTP 500 JSON: `{"message": "Server error. Please try again."}`.
  - **No SQLSTATE**, database column names, table names, or PDO traces are leaked in response.

### 10.2 Table / API Error Recovery & Retry
- **Steps**:
  1. Simulate network disconnect or API failure while fetching party list or booking list.
- **Expected Result**:
  - Table displays friendly error banner: *"Could not load records. Please check your connection."*.
  - A visible **"Retry"** button is present. Clicking Retry re-fires the fetch request.

### 10.3 CSV Export
- **Steps**:
  1. On `/bhav-bookings`, apply a filter (e.g. `Sell`, `Gold`, this month).
  2. Click the **Export CSV** button.
- **Expected Result**:
  - A CSV file downloads: `bhav-bookings-export-YYYY-MM-DD.csv`.
  - File contains filtered records with proper headers, clean numeric values, and total summary.
