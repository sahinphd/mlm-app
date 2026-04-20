# System Operation Guide: Credit, EMI & Notifications

This guide provides detailed instructions for Admins and Users regarding the Credit, EMI, and Push Notification systems.

---

## 🛡️ FOR ADMINS

### 1. Global System Configuration
Manage the core rules of the system from **Admin > Settings > Advanced**.

*   **EMI Configuration:**
    *   **Default EMI Amount:** The fixed amount for every installment (e.g., ₹500). If an order is ₹1500, the system creates 3 EMIs of ₹500.
    *   **EMI Frequency:** Number of days between installments (e.g., 7 for weekly).
    *   **Late Penalty:** A separate fee (e.g., ₹80) added to the user's account if an EMI is not paid by the due date.
*   **Push Notifications (FCM V1):**
    *   **FCM Project ID:** Your Firebase project ID (e.g., `my-mlm-app-123`).
    *   **Service Account JSON:** Upload the private key JSON file generated from your Firebase Console. This is mandatory for the V1 API to function.

### 2. User Credit Management
1.  **Approval:** Go to **Admin > Users**, edit a user, and set a **Credit Limit**. Change their credit status to **Approved**.
2.  **Monitoring:** Use the **"User EMIs"** sidebar link to see a global table of every installment in the system.
3.  **Manual Reminders:** On the "User EMIs" page, you can click **"Send Reminder"** to manually push a notification to a specific user's device if they are nearing a deadline.

### 3. Order Lifecycle & Tracking
Maintain clear records by updating order statuses in **Admin > My Orders**:
*   **Processing:** For orders currently being packed.
*   **Shipped:** When the item is with the courier.
*   **Delivered:** When the user receives the item.
*   **Completed:** The final successful state.
*   **Returned/Cancelled:** Triggers the reversal logic (see below).

### 4. Commission Reversal Logic (Critical)
The system protects your revenue by handling cancellations automatically:
*   **The Trigger:** Changing an order status to **Cancelled** or **Returned**.
*   **The Action:** The system identifies all uplines who received commissions from this order and **deducts** those amounts from their `earning_balance`.
*   **Audit Trail:** A "Debit" transaction is created in the upline's history, clearly stating: *"Commission reversal for Order #X"*.

### 5. Automated Background Tasks
Ensure your server's Cron Job is running. The system automatically:
*   Sends reminders 2 days before an EMI is due.
*   Applies the ₹80 penalty (or your custom amount) at midnight on the day an EMI becomes overdue.

---

## 👤 FOR USERS

### 1. Purchasing with Credit
1.  Browse the **Shop** and add items to your cart.
2.  At checkout, select **"Credit Wallet"**.
3.  The system will instantly calculate your weekly installments based on the total price.

### 2. The EMI Schedule Page
Find this link in your sidebar. It is your central hub for:
*   **Tracking Due Dates:** See exactly when your next payment is due.
*   **Making Payments:** Click **"Pay Now"** to use your **Main Wallet** balance to clear an installment.
*   **Paying Penalties:** If you miss a deadline, the penalty will appear here and must be paid to keep your account in good standing.

### 3. Recovering Your Credit Limit
Every time you pay an EMI:
1.  Your **Used Credit** goes down.
2.  Your **Available Credit** goes up.
3.  You can then use that recovered limit to make new purchases in the Shop.

### 4. Smart Notifications
You will receive real-time push notifications on your device for:
*   **Order Status Changes:** (e.g., "Your order has been Shipped!")
*   **EMI Reminders:** 2 days before a deadline.
*   **Financial Updates:** When a top-up is approved or a penalty is applied.

---

## 🛠️ ADMIN TROUBLESHOOTING & COMMANDS
Run these via terminal if you need immediate results:
*   `php artisan emi:apply-penalties` - Manually trigger the late fee logic.
*   `php artisan emi:send-reminders` - Manually trigger the 2-day reminder notifications.
*   `php artisan emi:test-push {user_id}` - Verify if your Firebase Project ID and JSON file are working correctly.
