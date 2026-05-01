# Guide: Commission Payouts & BV Conversions

This update introduces a robust, multi-wallet system designed to manage MLM earnings, lock-in periods, and tax deductions (TDS).

---

## 🛡️ FOR ADMINS

### 1. Configuration (Admin > Settings > Commission)
Manage the payout rules from the **Commission** tab in settings.

*   **Commission Lock Period:**
    *   Number of days (e.g., 30) before a commission becomes "Withdrawable".
    *   This prevents loss if an order is cancelled/returned after a few days.
*   **Payout Deductions:**
    *   **TDS Deduction (%):** Set the tax percentage (e.g., 5%) to be deducted from every payout.
    *   **Service Charge (%):** Set the processing fee (e.g., 5%) for the platform.
*   **BV Conversion Factor:**
    *   **Conversion Rate:** Set how much ₹1 is worth in BV (e.g., 1.0 means 100 BV = ₹100).
    *   **Min. BV for Conversion:** Minimum points required before a user can convert them to balance.

### 2. Wallet Distribution Logic
Earnings are now routed to specific buckets:
1.  **Commission Wallet:** Joining and Repurchase commissions go here first. They stay "Locked" until the Lock Period expires.
2.  **BV Earning Wallet:** BV earnings (from orders) go here. They can be converted to Main Balance at any time once the minimum threshold is met.
3.  **Main Balance:** This is the only balance used for making purchases or transferring to other users.

### 3. Reversal Management
If an order is **Cancelled** or **Returned**:
*   The system checks the status of the commissions.
*   If the commission is still in the **Commission Wallet**, it is deducted from there.
*   If the user has already **withdrawn** the commission to their **Main Balance**, the deduction is taken from the Main Balance instead.

---

## 👤 FOR USERS

### 1. Payouts & Conversions Page
Find the new **"Payouts & Conversions"** link in your sidebar.

### 2. Withdrawing Commissions
1.  View your **Commission Wallet** balance.
2.  The page shows two amounts:
    *   **Withdrawable:** Earnings that have passed the lock-in period.
    *   **Locked:** Recent earnings that are still in the verification period.
3.  Click **"Transfer Withdrawable to Main Balance"**.
4.  The system will process the payout and create separate line items in your history:
    *   **Net Payout Debit**: The actual amount moved to your main wallet.
    *   **TDS Deduction**: The tax portion withheld.
    *   **Service Charge Deduction**: The platform fee withheld.
    *   **Main Wallet Credit**: The net amount received in your main balance.

### 3. Converting BV Points
1.  View your **BV Point Balance**.
2.  Check the current **Conversion Rate** set by the admin.
3.  If you meet the minimum BV requirement, click **"Convert BV to Main Balance"**.
4.  The points will be deducted, and the equivalent ₹ value will be added to your Main Balance instantly.

---

## 📊 DASHBOARD REPORTS
Your **Dashboard** now features 4 real-time report cards:
*   **Total Commission**: All-time gross earnings.
*   **Withdrawable**: Current available funds in your Commission Wallet.
*   **Total Payouts**: Sum of all successful transfers to your Main Balance.
*   **Total Deductions**: Total tax and service fees paid.

---

## 🛠️ TECHNICAL COMMANDS (FOR DEVELOPERS)

### Data Migration & Sync
If you have just installed this update, you must sync your existing data so old commissions are not double-paid.

*   **`php artisan mlm:sync-commissions`**
    *   Marks all commissions created before today as "Withdrawn".
    *   Populates missing timestamps in the database.
*   **`php artisan mlm:sync-commissions --reset`**
    *   *Dev use only*: Moves all Main Balance back to the Commission Wallet to test the withdrawal workflow.
