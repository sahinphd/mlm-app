# Admin Guide: EMI, Credit & Notification System

This document explains how to manage the integrated Credit, EMI, and Push Notification system.

## 1. System Configuration
All global settings for the EMI system are managed in the Admin Panel.
- **Location:** `Admin > Settings > Advanced`
- **Default EMI Amount:** The fixed amount for each installment (e.g., ₹500). The system will split any credit purchase into installments of this size until the total is covered.
- **EMI Frequency:** How many days between installments (e.g., 7 for weekly, 30 for monthly).
- **Late Penalty Amount:** The fee levied automatically if a user misses their due date (e.g., ₹80).
- **FCM Project ID & JSON:** Required for push notifications. Upload your Firebase Service Account JSON file here.

## 2. Managing User Credit
Users cannot use the Credit Wallet until an admin approves them and sets a limit.
1. Go to `Admin > Users`.
2. Edit a user or view their Credit details.
3. Set the **Credit Limit** (e.g., ₹5000) and change the status to **Approved**.
4. The user can now select "Credit Wallet" during checkout.

## 3. Global EMI Management
Admins can monitor and manage all user installments from the **"User EMIs"** page in the sidebar.
- **Global Table:** View every EMI across the entire system in a real-time data table.
- **Filtering:** Filter the view by **Status** (Pending, Paid, Overdue) to focus on problematic accounts.
- **Search:** Quickly find EMIs by **User Name**, **Email**, **Order ID**, or **EMI ID**.
- **Manual Reminders:** Click the **"Send Reminder"** button to manually trigger a push notification and dashboard alert for any user regarding a specific installment.

## 4. How the EMI Logic Works
- **Auto-Generation:** When a user buys something via credit, the system calculates how many installments are needed based on your "Default EMI Amount".
- **Example:** If a user buys a product for ₹1200 and your default EMI is ₹500:
    - Installment 1: ₹500 (Due in 7 days)
    - Installment 2: ₹500 (Due in 14 days)
    - Installment 3: ₹200 (Due in 21 days)
- **Repayment:** Users pay their EMIs from their **Main Wallet** via the "EMI Schedule" page in their dashboard.

## 5. Automated Tasks (Cron Jobs)
The system is designed to be "hands-off." The following tasks are scheduled to run daily:
- **EMI Reminders:** Sends a Push Notification to users 2 days before an EMI is due.
- **Penalty Application:** Automatically identifies overdue EMIs, changes status to "Overdue," and adds the late fee to the user's account.

### Manual Commands
If you need to run these tasks manually via terminal:
```bash
# Apply penalties to all overdue EMIs
php artisan emi:apply-penalties

# Send reminders for upcoming EMIs (due in 2 days)
php artisan emi:send-reminders

# Test push notification for a specific user
php artisan emi:test-push {user_id}
```

## 6. Push Notifications (FCM V1)
The system uses the latest **Firebase Cloud Messaging V1 API**.
- **Events Notified:**
    - Upcoming EMI (2-day warning)
    - Late Penalty Applied
    - Order Status Updates (Processing, Completed, etc.)
    - Wallet Top-up Approvals/Rejections
- **Troubleshooting:** If notifications aren't arriving, check `storage/logs/laravel.log` for error responses from Google.

---
*Generated on April 20, 2026*
