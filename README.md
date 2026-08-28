# PRMS ERP — Project Review & Sales Management System

A comprehensive, enterprise-ready ERP web application built with **Laravel**, **Bootstrap 5**, and **MySQL** for managing industrial and B2B sales pipelines, quotations, RFQs, engineer KPI scoring, incentive calculations, customer complaints, and dynamic role-based access control.

---

## 🚀 Key Features

### 1. 🔐 Role-Based Access Control & Menu Permissions
- **Supported Roles**: `Owner`, `Sales Engineer`, `Customer`.
- **Owner Role Restriction**: Guaranteed single-owner architecture.
- **Dynamic Menu Access Matrix**:
  - **Role-Based Default Access**: Configure which menus each role can access by default.
  - **User-Specific Overrides**: Granularly grant or revoke menu items for individual users.
  - **Auto-Restricted Registration**: Newly registered users get access only to the Dashboard and Profile until approved by the Owner.
- **Active/Inactive Status Guard**: Inactive accounts are blocked at login.

### 2. 📊 Timezone-Aware Analytics Dashboard
- **Local Timezone (IST - `Asia/Kolkata`)**: Calculates time-based dynamic greetings (*Good Morning*, *Good Afternoon*, *Good Evening*, *Good Night*).
- **Live Seconds Clock**: Real-time ticking digital clock displayed directly in the dashboard header badge.
- **Scoped Visibility**:
  - **Owner / Admin**: Full visibility into all company RFQs, quotations, targets, and employee metrics.
  - **Sales Engineer**: Isolation to view only their assigned customers, RFQs, quotes, and KPI targets.
  - **Customer**: Dedicated portal view for their own company RFQs and support complaints.

### 3. 💼 Sales Pipeline & RFQ Register
- **RFQ Tracking**: Track RFQ numbers, customer links, receipt dates, submission target dates, and statuses (*Follow Up*, *Follow Through*, *Won*, *Lost*, *Cancelled*).
- **Quotation Management**: Multi-quote tracking per RFQ with target vs. actual submission dates, quoted vs. awarded values, and commercial accuracy logs.
- **Critical Opportunities Watchlist**: Automatic alerts for high-value quotes and upcoming submission deadlines.

### 4. 🎯 KPI Scoring & Incentive Engine
- **Daily Activity Logging**: Track daily customer calls, follow-ups, physical visits, online meetings, and CRM updates.
- **Automated Incentive Slabs**:
  - `< 80%` Achievement: **0× (No Incentive)**
  - `80% – 99%` Achievement: **1× (Standard)**
  - `100% – 110%` Achievement: **1.5×**
  - `> 110%` Achievement: **2× (Accelerated)**

### 5. 🏢 Customer & Complaint Management
- Full customer directory with contact persons, company logos, and assigned sales engineers.
- Customer support complaint ticketing with lifecycle status updates (*Open* → *In Progress* → *Resolved*).

### 6. 📄 Universal Standardized Pagination
- Dynamic record selection on every data table: **`5, 10, 25, 50, 100, 200, 500`** entries per page.
- Clean Bootstrap 5 pagination controls with record counters and query string retention.

---

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+, Laravel 11
- **Database**: MySQL 8.0+
- **Frontend**: Blade, Bootstrap 5, Sneat / Materialize UI, Chart.js, ApexCharts
- **Build Tool**: Vite / Laravel Mix, NPM
- **Containerization**: Docker (Alpine + Nginx + PHP-FPM)

---

## 🔑 Default Credentials (Seeded)

| Role | Email | Password | Target / Access |
| :--- | :--- | :--- | :--- |
| **Owner** | `owner@prms.test` | `password` | Full System Access |
| **Sales Engineer 1** | `engineer1@prms.test` | `password` | ₹ 1.00 Cr Target, Linked to CUST-DEMO-001 |
| **Sales Engineer 2** | `engineer2@prms.test` | `password` | ₹ 1.00 Cr Target, Linked to CUST-DEMO-002 |
| **Customer** | `customer@prms.test` | `password` | Customer Portal for CUST-DEMO-001 |

---

## 💻 Local Setup & Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- MySQL

### Step-by-Step Instructions

1. **Clone the repository**:
   ```bash
   git clone <repository-url>
   cd ERP_S3
   ```

2. **Install Dependencies**:
   ```bash
   composer install
   npm install
   npm run build # or npm run prod
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Edit `.env` and configure your database credentials (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`) and `APP_TIMEZONE=Asia/Kolkata`.*

4. **Storage Symlink**:
   ```bash
   php artisan storage:link
   ```

5. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

6. **Start the Local Development Server**:
   ```bash
   php artisan serve
   ```
   Open `http://127.0.0.1:8000` in your web browser.

---

## 🐳 Docker Deployment

The project includes an optimized production [`Dockerfile`](./Dockerfile) using Alpine Linux, Nginx, and PHP-FPM.

### Build and Run with Docker

```bash
# 1. Build the Docker image
docker build -t prms-erp .

# 2. Run container
docker run -d -p 80:80 \
  -e DB_HOST=your-db-host \
  -e DB_DATABASE=prms_db \
  -e DB_USERNAME=your-user \
  -e DB_PASSWORD=your-password \
  --name prms-app prms-erp
```

### Automated Startup Sequence in Container:
The container automatically runs:
1. `php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear`
2. `php artisan storage:link --force`
3. `php artisan migrate --force && php artisan db:seed --force`
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
5. Starts PHP-FPM and Nginx web server concurrently.

---

## 🛡️ License

This project is proprietary enterprise software developed for PRMS. All rights reserved.
