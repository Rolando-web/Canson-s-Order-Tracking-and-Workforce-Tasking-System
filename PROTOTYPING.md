# System Prototyping Documentation
## Canson's School & Office Supplies – HCPLAY Computer Trading
### Order Management, Inventory & Workforce Tracking System

---

## Figure 1: Login Page

This page is the main entry point of the system where you sign in with your username and password. It features a split-screen layout with company branding on the left and the login form on the right. Use the "Remember Me" option to stay logged in, and upon successful authentication, you are automatically redirected to the Dashboard based on your assigned role.

---

## Figure 2: Dashboard Page (Super Admin View)

This page gives you an immediate snapshot of your entire business the moment you log in. Quickly check Today's Sales, Total Orders, Pending Orders, and Monthly Revenue at a glance. Review the Weekly Sales chart, Top-Selling Products, Recent Sales, Order Status breakdown, and Inventory Stock Alerts to monitor overall business health and catch issues early without navigating to other pages.

---

## Figure 3: Dashboard Page (Employee View)

This page gives employees a focused view of their personal workload. Quickly see how many tasks are Pending, In Progress, Completed, and the Total count. Use the Quick Action cards to jump straight to Assignments, Notifications, or Schedule, and browse the All Orders table to check order progress and delivery urgency at a glance.

---

## Figure 4: Orders Page (Super Admin Only)

This page lets you create, edit, and manage all customer orders as the sole authority in the system. Enter customer information, select products from inventory with real-time stock validation, set delivery dates and priority levels, and let the system automatically split large orders into multiple delivery phases. Cover pending damage claims from previous deliveries during order creation to keep customer accounts in good standing.

---

## Figure 5: Assignments Page – Admin View

This page lets you distribute work by assigning specific employees to specific order items. View employee availability, see which orders are still unassigned, and track active orders with per-phase progress. Inventory is automatically deducted the moment an assignment is created, keeping stock levels accurate and aligned with committed orders.

---

## Figure 6: Assignments Page – Employee View

This page lets employees view and manage their assigned tasks in one place. Start working on assignments, submit completed quantities per item through a simple progress update form, and review progress history. Phase locking is enforced to ensure employees complete phases in the correct order, keeping production organized and on track.

---

## Figure 7: Order Progress Page

This page lets you monitor real-time production progress for all active orders, phase by phase. View completion percentages, item-level progress bars, and damage carry-forward tracking between phases. Record damage when it occurs and watch it automatically propagate to subsequent phases, so you always know the true status of every order.

---

## Figure 8: Dispatch / Delivery Page (Admin and Above)

This page lets you handle the final step of order fulfillment by marking completed orders as delivered. Optionally report any delivery damages, which automatically creates return item claims, deducts damaged stock from inventory, and carries the damage forward to the next phase for rework. Keep delivery records clean and ensure every damaged item is accounted for.

---

## Figure 9: Inventory List Page (Admin and Above)

This page lets you see all your products in one organized view with stock levels, categories, and pricing. Check the KPI cards for Total Items and Low Stock Alerts at a glance. Use the Reports tab to review Stock Valuation by Category, Low/Out of Stock item listings, and recent Stock In/Out transaction history to stay on top of your inventory health.

---

## Figure 10: Products Page (Admin and Above)

This page lets you manage all your products in one organized table. Easily view each item's name, price, current quantity, category, and supplier. Add new products with auto-generated item codes, images, and reorder thresholds, or update existing details quickly to maintain accurate inventory. Stock adjustments are restricted to Stock In/Out transactions only, ensuring every change is properly tracked.

---

## Figure 11: Stock In Page (Admin and Above)

This page lets you record incoming inventory from suppliers into the system. Add multiple items per batch, link them to a supplier with a unique reference number, and keep a clear audit trail of every unit received. Browse the Movement History tab for past transactions and the Suppliers tab to manage your supplier records, ensuring full traceability of all incoming stock.

---

## Figure 12: Stock Out Page (Admin and Above)

This page lets you view a complete, read-only history of every inventory deduction the system has made. Stock is automatically deducted when orders are assigned to employees or when delivery damage is recorded. Each entry is linked back to its originating order, giving you full transparency into why stock levels changed and where every unit went.

---

## Figure 13: Sales Page (Admin and Above)

This page lets you track revenue from all completed orders in one place. Review summary KPI cards, analyze the Sales Trend chart with daily, weekly, or monthly views, and browse a paginated, filterable sales table. Export your data to CSV or print formatted sales reports for financial record-keeping and business planning.

---

## Figure 14: Analytics Page (Admin and Above)

This page lets you dive deep into business performance with configurable period filters. Analyze Revenue Trends, Sales by Category, Top Products, Top Customers, Order Status Distribution, Weekly Production Output, and Worker Efficiency metrics. Export any dataset to CSV for further analysis, helping you make data-driven decisions about operations, staffing, and product strategy.

---

## Figure 15: Reports Page (Admin and Above)

This page lets you view all your key business metrics in a single, print-ready summary. See Total Revenue, Total Orders, a 12-Month Revenue chart, Order Status Distribution, Top Products ranking, and a Recent Orders table. Use the quick-access links to jump to detailed Sales and Analytics reports when you need to dig deeper.

---

## Figure 16: Cover Items / Returns Page (Admin and Above)

This page lets you track and resolve damage claims from delivery issues. Damage claims are automatically created when delivery damage is reported and stay in "Pending" status until you cover them as free replacement items in a future order for the same customer. Keep every claim accounted for and ensure customer satisfaction by addressing damaged deliveries promptly.

---

## Figure 17: Schedule Page (All Roles)

This page lets you and your entire team view upcoming order deadlines and planned events on a shared calendar. Switch between Month and Week views, create and manage Schedule Notes for important activities, and see auto-populated Order Deadlines so everyone stays aware of production timelines and upcoming commitments.

---

## Figure 18: Employees Page (Admin and Above)

This page lets you manage your entire workforce in one place. Add new employees, edit profiles, assign roles (Employee, Admin, Super Admin), and remove accounts as needed. Each role determines the user's system-wide access level, ensuring the right people have the right permissions to keep operations running smoothly.

---

## Figure 19: Notifications Page (All Roles)

This page keeps you informed of all important system events without having to check each page manually. Receive auto-generated alerts for new orders, work assignments, phase completions, deliveries, damage carry-forwards, and low stock warnings. Mark notifications as read individually or all at once, with an unread count badge always visible in the sidebar.

---

## Figure 20: Settings Page (Admin and Above)

This page lets you manage your account by updating your profile information and changing your password. Current password verification is required before accepting a new password to keep your account secure. Your assigned role is displayed as read-only for reference.

---

## End-to-End Major Transaction Flow

1. **Order Creation** — Create customer orders with product selection, delivery scheduling, priority levels, and automatic phase splitting. Cover pending damage claims during order creation.
2. **Work Assignment** — Assign employees to order items; inventory is auto-deducted upon assignment.
3. **Production Tracking** — Employees update their progress; phases auto-complete when all items are done. Monitor progress in real time.
4. **Dispatch / Delivery** — Mark completed orders as delivered and report any delivery damages, which automatically generate return claims.
5. **Damage Recovery** — Damage claims stay pending until covered as free replacements in a future order for the same customer.
6. **Business Intelligence** — All transactions feed into Sales, Analytics, and Reports for full visibility into revenue, performance, and efficiency.
