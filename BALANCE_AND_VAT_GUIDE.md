# MITAS System: Balance Detection, Auto-Clearing & VAT Implementation Guide

## 🎯 Executive Summary

The MITAS system **automatically detects and clears balances with ZERO configuration needed**. It uses real-time database queries to calculate outstanding amounts and automatically updates payment status. VAT is now fully integrated into the system.

---

## Part 1: Balance Detection & Auto-Clearing (NO Configuration Needed)

### How It Works: The Complete Flow

#### **Step 1: Invoice/Order Created**
```
Invoice INV001-2024: Customer = "ABC Corp"
├─ Total Amount = TZS 1,000,000
├─ Item: Laptop
└─ Status: "Pending"
```

#### **Step 2: System Automatically Detects Balance**

When you view an invoice or recording payment, the system uses:

```php
function getInvoiceBalance(PDO $pdo, int $invoiceId): float {
    // QUERY 1: Get total amount from invoice
    SELECT final_total (or total_amount if no VAT) FROM invoices WHERE id = ?
    Result: TZS 1,000,000
    
    // QUERY 2: Get all payments ever made to this invoice
    SELECT SUM(amount) FROM payments WHERE invoice_id = ?
    Result: TZS 0 (no payments yet)
    
    // CALCULATION: Remaining balance
    Balance = 1,000,000 - 0 = TZS 1,000,000 (OUTSTANDING)
}
```

**No configuration needed** — the system queries the database on-demand!

#### **Step 3: Record Payment**
```
User enters: Payment Amount = TZS 500,000
System automatically:
1. Calls getInvoiceBalance() → Gets balance = TZS 1,000,000
2. Compares: 500,000 < 1,000,000?
   YES → Status = "Partially Paid" ⚠️
3. Inserts into payments table:
   - invoice_id: 1
   - amount: 500,000
   - date: now
```

#### **Step 4: Second Payment Clears Balance**
```
User enters: Payment Amount = TZS 500,000 (second payment)
System automatically:
1. Calls getInvoiceBalance() again
2. New query: total_amount - (500,000 + 500,000) = 1,000,000 - 1,000,000 = 0
3. Compares: 500,000 >= 0?
   YES → Status = "Paid" ✅
4. Balance Profile panel now shows: balance = 0 → AUTO-REMOVED ✨
```

### The Balance Profile Panel (Real-Time Auto-Update)

**Location:** `balance_profile` panel in index.php

**What it does:**
```sql
SELECT * FROM invoices 
WHERE (total_amount - SUM(payments)) > 0
ORDER BY balance DESC;
```

**Auto-clearing happens because:**
- Panel runs query EVERY TIME user views it (or every 10 seconds in live mode)
- If balance ≤ 0, document doesn't appear in results
- If balance > 0, shows "Pending" (red text)
- If balance = 0, shows "Cleared" (green text) or disappears

**No manual configuration** — just record payment → system automatically updates!

---

## Part 2: VAT Implementation (NEW)

### What Was Added

#### **1. Database Schema Updates**

**invoice_items table:**
```sql
vat_percent REAL DEFAULT 0      -- Store VAT percentage (e.g., 18)
vat_amount REAL DEFAULT 0       -- Auto-calculated VAT amount (e.g., 18,000)
```

**order_items table:**
```sql
vat_percent REAL DEFAULT 0
vat_amount REAL DEFAULT 0
```

**invoices table:**
```sql
vat_amount REAL DEFAULT 0       -- Total VAT for entire invoice
final_total REAL DEFAULT 0      -- Amount + VAT (used for balance calculation)
```

**orders table:**
```sql
vat_amount REAL DEFAULT 0       -- Total VAT for entire order
final_total REAL DEFAULT 0      -- Amount + VAT (used for balance calculation)
```

#### **2. Invoice Form Update**

**New field added:**
```html
<input type="number" step="0.01" name="invoice_vat[]" 
       min="0" max="30" value="0" placeholder="VAT % (Optional)">
```

**Per line item:**
```
Description: [Laptop]
Category: [Electronics]
Qty: [2]
Unit Price: [500,000 TZS]
Discount: [0%]
VAT %: [18%]  ← NEW FIELD
```

#### **3. VAT Calculation in Backend**

When invoice is submitted:

```php
// For each invoice line item:
$subtotal = unit_price * quantity * (1 - discount/100)
            = 500,000 * 2 * (1 - 0/100)
            = 1,000,000

$vat_amount = subtotal * (vat_percent / 100)
            = 1,000,000 * (18 / 100)
            = 180,000

$amount = subtotal + vat_amount
        = 1,000,000 + 180,000
        = 1,180,000

// Total invoice:
invoiceTotal = sum of all subtotals = 1,000,000
invoiceVatTotal = sum of all VAT = 180,000
final_total (for balance) = 1,000,000 + 180,000 = 1,180,000 ✅
```

#### **4. Balance Calculation Now Includes VAT**

**BEFORE:**
```php
Balance = total_amount - payments
```

**AFTER:**
```php
Balance = final_total - payments
        = (total_amount + vat_amount) - payments
        = ACCURATE INCLUDING VAT
```

**Example:**
- Invoice total: TZS 1,000,000
- VAT (18%): TZS 180,000
- **Final Amount Due: TZS 1,180,000** ← This is what balance calculation uses
- Payment: TZS 1,000,000
- **Remaining Balance: TZS 180,000** (just the VAT) ✅

---

## Part 3: How to Use

### Recording Invoices with VAT

1. **Click "Create Invoice" panel**
2. **Enter invoice details:**
   - Invoice Number (auto-generated if left blank)
   - Customer Name
3. **For each line item:**
   ```
   Description: "Dell Laptop"
   Category: "Electronics"
   Quantity: 1
   Unit Price: 500,000
   Discount: 0
   VAT %: 18          ← Enter VAT percentage
   Manufacturer: Dell (optional)
   Grade: A (optional)
   Condition: New (optional)
   ```
4. **Click "Add item" for multiple lines**
5. **Click "Save Invoice"**
6. **System automatically:**
   - Calculates subtotal: 500,000 TZS
   - Calculates VAT: 500,000 × 18% = 90,000 TZS
   - Calculates final total: 590,000 TZS
   - Stores all values in database

### Recording Sales Orders with VAT

Same process for "Save Sale" panel — VAT works the same way.

### Recording Payments

1. **Click "Record Payment"**
2. **Select document type:** Invoice or Order
3. **Select invoice/order:** Shows balance INCLUDING VAT
   ```
   Example: "INV001 - ABC Corp - Balance TZS 1,180,000"
   ```
4. **Enter payment amount:** TZS 1,180,000
5. **System automatically:**
   - Deducts from final_total (includes VAT)
   - If payment = final_total → Status = "Paid" ✅
   - Balance Profile disappears (auto-cleared)

### Viewing Balance Profile

**Panel:** "Balance Profile - Outstanding Balances"

**Shows:**
- Customer Name
- Document Number (INV-001 or ORD-1)
- Details (item + total)
- **Balance Amount (including VAT)** ← Final amount due
- Status (Pending/Cleared)

**Auto-updates:**
- Every time you view it
- Every 10 seconds in live mode
- Disappears when balance ≤ 0 ✨

---

## Part 4: Key Features

### ✅ Automatic Balance Detection
- **No configuration needed**
- Real-time database queries
- Queries: `total_amount - SUM(payments)`

### ✅ Automatic Balance Clearing
- **No configuration needed**
- Happens when payment ≥ balance
- Auto-updates document status
- Auto-removes from Balance Profile when paid

### ✅ VAT Support
- Optional per line item
- Auto-calculates VAT amount
- Included in final balance
- Displayed on invoices/orders

### ✅ Real-Time Updates
- API refreshes every 10 seconds
- Balance Profile updates live
- Payment status changes immediately

### ✅ No Manual Configuration Required
- System detects balance automatically
- System clears balance automatically
- System calculates VAT automatically

---

## Part 5: Data Flow Diagram

```
CREATE INVOICE
    ↓
Capture items + VAT %
    ↓
Calculate: subtotal, VAT, amount
    ↓
INSERT INTO invoices (total_amount=subtotal, vat_amount=VAT, final_total=amount)
INSERT INTO invoice_items (vat_percent, vat_amount, amount)
    ↓
    ↓
VIEW BALANCE PROFILE
    ↓
Query: SELECT balance = final_total - SUM(payments) FROM invoices
    ↓
Display: balance (includes VAT)
    ↓
    ↓
RECORD PAYMENT
    ↓
Insert: INSERT INTO payments (invoice_id, amount)
    ↓
Query: getInvoiceBalance() → new balance
    ↓
If payment ≥ balance: UPDATE invoices SET status = "Paid"
    ↓
Balance Profile auto-updates (shows balance = 0, auto-clears)
    ↓
    ↓
INVOICE FULLY PAID ✅
All payments recorded
Balance = 0
Status = "Paid"
Auto-removed from Balance Profile
```

---

## Part 6: Technical Details

### Functions That Handle Balance

**`getInvoiceBalance($pdo, $invoiceId)`**
```php
Balance = final_total - SUM(payments WHERE invoice_id = ?)
Returns: max(0, balance)
```

**`getOrderBalance($pdo, $orderId)`**
```php
Balance = final_total - SUM(payments WHERE order_id = ?)
Returns: max(0, balance)
```

### Database Queries

**Calculate Invoice Balance:**
```sql
SELECT 
  COALESCE(final_total, total_amount) AS due,
  COALESCE((SELECT SUM(amount) FROM payments WHERE invoice_id = invoices.id), 0) AS paid
FROM invoices WHERE id = ?;

Balance = due - paid
```

**Calculate VAT:**
```sql
-- Stored in invoice_items.vat_amount
-- Stored in invoices.vat_amount
-- Included in final_total used for balance calculation
```

**View Outstanding:**
```sql
SELECT * FROM invoices 
WHERE (final_total - COALESCE(SUM(payments), 0)) > 0
ORDER BY created_at DESC;
```

---

## Part 7: Common Scenarios

### Scenario 1: Invoice with 18% VAT
```
Item: Laptop
Unit Price: 1,000,000 TZS
Quantity: 1
Discount: 0%
VAT: 18%

System calculates:
Subtotal = 1,000,000
VAT Amount = 1,000,000 × 0.18 = 180,000
Final Total = 1,180,000

Invoice shows:
- Subtotal: 1,000,000 TZS
- VAT (18%): 180,000 TZS
- Total: 1,180,000 TZS

Balance for payment: 1,180,000 TZS ✅
```

### Scenario 2: Partial Payment with VAT
```
Invoice Total (with VAT): 1,180,000 TZS
Payment 1: 500,000 TZS

System calculates:
Remaining Balance = 1,180,000 - 500,000 = 680,000 TZS
Status = "Partially Paid"
Balance Profile shows: 680,000 TZS remaining ⚠️

Payment 2: 680,000 TZS
Remaining Balance = 1,180,000 - 500,000 - 680,000 = 0
Status = "Paid"
Balance Profile: AUTO-CLEARS ✨
```

### Scenario 3: Multiple Items with Different VAT Rates
```
Item 1: Desktop Computer - 1,000,000 TZS @ 18% VAT = 180,000 VAT
Item 2: Mouse - 100,000 TZS @ 0% VAT = 0 VAT

Invoice Total:
Subtotal = 1,100,000 TZS
Total VAT = 180,000 TZS
Final Total = 1,280,000 TZS

Balance for payment: 1,280,000 TZS ✅
```

---

## Part 8: FAQ

**Q: Do I need to configure anything for balance detection?**
A: NO. The system automatically queries the database to calculate remaining balance in real-time.

**Q: How does the system know when a balance is fully paid?**
A: When the payment amount ≥ remaining balance, the system automatically sets status to "Paid" and removes it from Balance Profile.

**Q: Do I need to manually clear balances?**
A: NO. Balances automatically clear when fully paid. No configuration needed.

**Q: Is VAT required?**
A: NO. VAT is optional. Leave the field at 0 or empty if not needed.

**Q: How is VAT included in the balance calculation?**
A: The balance calculation uses `final_total = total_amount + vat_amount` instead of just `total_amount`. So if invoice is 1,000,000 + 180,000 VAT, the balance is calculated from 1,180,000.

**Q: Can I set different VAT rates per item?**
A: YES. Each line item has its own VAT % field. Different items can have different rates.

**Q: What if I don't enter a VAT percentage?**
A: VAT defaults to 0%. The item is added without VAT, and final_total = total_amount.

**Q: Does the system show VAT on printed invoices/orders?**
A: YES. The invoice and order detail pages display manufacturer, grade, condition, and now also show VAT calculations.

---

## Part 9: System Status

✅ **Balance Detection:** AUTOMATIC (no config)
✅ **Auto-Clearing:** AUTOMATIC (no config)
✅ **VAT Calculation:** AUTOMATIC per item
✅ **Final Total Calculation:** AUTOMATIC (includes VAT)
✅ **Payment Tracking:** REAL-TIME queries
✅ **Balance Profile:** AUTO-UPDATES every 10 seconds
✅ **PHP Syntax:** VALIDATED (no errors)

---

**Last Updated:** July 12, 2026
**System Version:** MITAS v2.0 (with VAT)
**Status:** Production Ready ✅
