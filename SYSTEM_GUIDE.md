# System Operation Guide: Credit, EMI & Notifications

This guide provides detailed instructions for Admins and Users regarding the Credit, EMI, and Notification systems.

---

## 🛡️ FOR ADMINS

### 1. Global System Configuration
Manage the core rules of the system from **Admin > Settings > Advanced**.

*   **EMI Configuration:**
    *   **Default EMI Amount:** The fixed amount for every installment (e.g., ₹500).
    *   **EMI Frequency:** Days between payments (e.g., 7 for weekly).
    *   **Late Penalty:** The one-time fee (e.g., ₹80) added if a user misses a deadline.
*   **Push Notifications (FCM V1):**
    *   **FCM Project ID:** Your Firebase project identifier.
    *   **Service Account JSON:** Upload the private key file from your Firebase console to enable secure push notifications.

### 2. User Credit Approval
Users cannot use credit until you authorize them.
1.  Go to **Admin > Users**.
2.  Edit the desired user.
3.  Set their **Credit Limit** (e.g., ₹5000).
4.  Set status to **Approved**.

### 3. Order Management & Commission Reversal
You are responsible for tracking the lifecycle of an order.
*   **New Statuses:** Pending, Processing, Shipped, Delivered, Completed, Cancelled, Returned, Failed.
*   **Commission Reversal (Critical):**
    *   If you change an order status to **Cancelled** or **Returned**, the system will **automatically reverse** all commissions paid to uplines for that order.
    *   The reversal is logged in the uplines' transaction history as a "Debit."

### 4. Automated Maintenance
The system runs these tasks automatically every day:
*   **EMI Reminders:** Notifies users 2 days before an EMI is due.
*   **Penalty Application:** Levies fees on any EMI that is "Pending" past its due date.

---

## 👤 FOR USERS

### 1. Purchasing on Credit
If you have an approved credit limit, you can shop without immediate payment:
1.  Select products in the **Shop**.
2.  At checkout, choose **"Credit Wallet"** as your payment method.
3.  The system will automatically split your total into smaller weekly EMIs.

### 2. Managing Your EMIs
You can track and pay your installments from the **"EMI Schedule"** page in your sidebar.
*   **Payment:** Click **"Pay Now"** on any pending EMI. The amount is deducted from your **Main Wallet**.
*   **Credit Recovery:** Every time you pay an EMI, your **Available Credit** increases, allowing you to shop again.

### 3. Understanding Penalties
*   If an EMI is not paid by the due date, it becomes **Overdue**.
*   A **Late Penalty fee** (as set by the admin) will be added to your account.
*   Penalties must be paid separately on the EMI Schedule page.

### 4. Stay Notified
The system will send you **Push Notifications** and **In-App Alerts** for:
*   **Order Updates:** When your order moves from Processing to Shipped or Delivered.
*   **Reminders:** 2 days before an EMI is due.
*   **Financials:** When a penalty is applied or a wallet top-up is approved.

---

## 🛠️ TECHNICAL NOTES (For Admins)
If you need to trigger tasks manually, use these terminal commands:
*   `php artisan emi:apply-penalties` - Run penalty logic now.
*   `php artisan emi:send-reminders` - Send upcoming due alerts now.
*   `php artisan emi:test-push {user_id}` - Verify your Firebase V1 setup.
