# ERP_S3 Demo Workflow

## 1. Start the application

Open PowerShell and run:

```powershell
cd E:\ERP_S2\ERP_S3
php artisan migrate
php artisan db:seed
php artisan serve
```

Open `http://127.0.0.1:8000` in a browser.

The seeded demo owner account is:

- Email: `owner@prms.test`
- Password: `password`

Seeded sales engineer accounts are:

- `engineer1@prms.test` / `password` with customer `CUST-DEMO-001` and a `10000000` monthly target.
- `engineer2@prms.test` / `password` with customer `CUST-DEMO-002` and a `10000000` monthly target.
- `customer@prms.test` / `password`, linked to customer `CUST-DEMO-001`.

The seeder also creates two RFQs, two quotations, daily KPI activity, one payment, and one open complaint so the frontend is populated immediately.

## Dashboard meanings

- `/dashboard`: welcome message, current date, and date-wise to-do list.
- `/sales/dashboard`: owner-only review with date range, sales-engineer filter, record counts, and RFQ status chart.
- `/sales/kpis`: Sales Engineer-only personal KPI dashboard using only that engineer's records and target.
- `/customer/dashboard`: customer-only portal showing the linked customer's RFQs and complaints.

Use a local database configured in `.env`. Run `php artisan migrate:fresh --seed` only when it is safe to reset demo data.

## 2. Sign in and show the dashboard

1. Open `/login`.
2. Sign in with the owner account.
3. Show the dashboard at `/dashboard`.
4. Point out the monthly target, orders booked, pipeline, outstanding collections, daily activity, critical opportunities, and risks.
5. Explain that dashboard figures are calculated from RFQs, quotations, payments, customers, and daily KPI logs.

Useful direct URL: `/sales/dashboard`

## 3. Create a sales engineer

1. Open **Admin > Employees**, or `/admin/users`.
2. Create an account with:
   - Name: `Demo Sales Engineer`
   - Email: `engineer@prms.test`
   - Password: `password`
   - Role: `Sales Engineer`
   - Department: `Sales`
   - Monthly order-booking target: `10000000`
3. Log out and sign in as the new sales engineer.
4. Explain that the target field appears only when `Sales Engineer` is selected. It is stored as that engineer's monthly KPI target and is used by the dashboard achievement percentage.
5. Show that sales users can access the operational sales screens but cannot access owner-only menu administration.

To demonstrate isolation, sign in as `engineer1@prms.test`, open `/sales/rfqs`, and confirm that `RFQ-DEMO-002` is not visible. Sign in as `engineer2@prms.test` to see the opposite record.

## 4A. Demonstrate the customer portal

1. Sign out and sign in with `customer@prms.test` / `password`.
2. Confirm that login redirects to `/customer/dashboard`.
3. Show that only `CUST-DEMO-001` RFQs and complaints are visible.
4. Confirm that owner and sales-engineer administration pages are unavailable.

### Supported roles

Only these roles are supported:

- `Owner`: full visibility, user creation, menu management, and all sales records.
- `Sales Engineer`: access to sales work and only their assigned RFQs, customers, complaints, and KPI activity.
- `User`: standard account with no sales or administration menu access unless the owner grants a menu entry.

Legacy roles are converted during migration: `admin` and `sales_manager` become `owner`; `employee` and `department_user` become `user`.

## 4. Create a customer

1. Open **Customers**, or `/customers`.
2. Use the customer form available from the RFQ screen to create a customer with:
   - Customer code: `CUST-DEMO-001`
   - Company: `Demo Engineering Works`
   - Contact: `Asha Kumar`
   - Email: `asha@example.test`
   - Phone: `9876543210`
   - Type: `New`
3. Return to `/customers` and show the customer register.
4. Open the customer detail page and show the owner, customer details, RFQs, and complaints sections.

## 5. Register an RFQ

1. Open **RFQ Register**, or `/sales/rfqs`.
2. Create an RFQ using:
   - RFQ number: `RFQ-DEMO-001`
   - Customer: `Demo Engineering Works`
   - Received date: today
   - Description: `Automated assembly line requirement`
   - Quantity: `10`
   - Lead time: `45` days
   - Quotation target date: seven days from today
   - Status: `Follow up`
   - Quoted price: `2500000`
3. Save the RFQ.
4. Show that it appears in the RFQ register and contributes to the active pipeline.

## 6. Add a quotation

1. Open **Quotations**, or `/sales/quotations`.
2. Create a quotation for `RFQ-DEMO-001`:
   - Quotation number: `QUO-DEMO-001`
   - Quotation date: today
   - Quoted date: today
   - Quoted price: `2500000`
   - Status: `Submitted`
3. Save it.
4. Show the quotation in the quotation list and confirm the RFQ quoted total is updated.

## 7. Record daily activity

1. Open **Daily KPI Log**, or `/sales/daily-log`.
2. Add today’s activity:
   - Customer calls: `15`
   - Follow-up calls: `20`
   - Customer visits: `2`
   - Online meetings: `2`
   - RFQs received: `1`
   - Quotations submitted: `1`
   - CRM updated: enabled
   - Notes: `Demo activity completed`
3. Save the log.
4. Return to the dashboard and show the updated daily activity totals.

## 8. Review sales performance

1. Open `/sales/dashboard` as owner or sales manager.
2. Show the RFQ pipeline, quotation count, customer count, conversion rate, collection balance, critical opportunities, and risks.
3. Open `/sales/kpis` to demonstrate the KPI review entry point.
4. Explain that sales engineers see their own scoped data and their configured target, while owners see every sales engineer's RFQs, quotations, customers, collections, and aggregate daily activity.

## 9. Log and resolve a complaint

1. Open **Customer Complaints**, or `/sales/complaints`.
2. Log a complaint for `Demo Engineering Works`:
   - Reported date: today
   - Subject: `Delivery schedule clarification`
   - Description: `Customer requested confirmation of the delivery milestone.`
3. Save it with the default `Open` status.
4. Change the status to `In progress` and add a resolution note.
5. Save again, then change it to `Resolved` after demonstrating the follow-up.
6. Open the customer detail page and show the complaint in the customer history.

## 10. Demonstrate permissions

Use two browser sessions or an incognito window:

1. Sign in as `owner@prms.test` and show **Employees** and **Menu management**.
2. In **Menu management**, assign a menu item to a role, user, or department. Access is granted when the logged-in account matches any active access record.
3. Sign in as the sales engineer and show the sales menu items. The sidebar is built from active menu records visible to that user.
4. Try `/admin/menus` as the sales engineer. It should return HTTP 403.
5. Confirm that a sales engineer only sees assigned customer, RFQ, activity, and complaint data, while the owner sees all records.

### How menu access works

1. Every protected route has a named route, such as `sales.rfqs` or `customers.index`.
2. The `menu_items` table stores the label, route name, icon, and active state.
3. The `menu_accesses` table stores access by `role`, `user`, or `department`.
4. On each authenticated request, inactive or unauthorized menu routes return HTTP 403.
5. The sidebar loads only active menu items visible to the logged-in user.
6. Owners bypass menu restrictions so they can administer the system.

## 11. Create a customer portal account

1. As Owner, open **Customers** and select **Add customer**.
2. Fill the customer company and contact fields.
3. Fill **Portal email** and **Portal password** for the customer login.
4. Use **Edit** later to change customer details or reset the portal password.
5. Sign in with those credentials and confirm only that customer's dashboard data is visible.

## 12. Audit trail and search

1. Open an RFQ, customer, or complaint using **View**.
2. Review who raised or viewed the record and when.
3. For complaints, status changes show the actor and transition from open to in progress to resolved.
4. Use the navbar search box for customer company/code or RFQ number/description. Results follow the logged-in user's scope.

## 13. Menu administration workflow

1. As Owner, open **Master menus** at `/admin/menus`.
2. Create a menu using a label and Laravel route name, such as `customers.index`.
3. Use **Edit** to change label, icon, order, or Active/Inactive status.
4. Open **Employee menu access** at `/admin/menu-access`.
5. Select an employee, tick the menus they should see, and save.
6. Unticked or inactive menus are hidden and unauthorized direct URLs return HTTP 403.

## Demo completion checklist

- Owner can sign in.
- Dashboard loads without errors.
- Customer can be created and viewed.
- RFQ can be created and appears in the pipeline.
- Quotation can be created and linked to an RFQ.
- Daily KPI activity can be saved and shown on the dashboard.
- Complaint can be logged, updated, and resolved.
- Owner-only administration is protected.
- Sidebar entries match the authenticated user’s permissions.
