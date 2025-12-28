# ⛽ Gasoline Station Management System

A comprehensive web-based platform designed to automate and manage fuel station operations. This system integrates real-time inventory tracking, multi-role user management, and a dynamic loyalty program.

## 🚀 Core Modules

### 👥 User & Access Management
- **Role-Based Access Control (RBAC):** Distinct dashboards and permissions for **Admins**, **Operators**, and **Customers**.
- **Secure Authentication:** Robust login/logout system with profile management and password recovery.

### ⛽ Fuel & Pump Operations
- **Real-time Monitoring:** Tracking fuel tank levels with automated low-stock alerts.
- **Service Hub:** Integrated scheduling for car washes, oil changes, and tire maintenance.
- **Service Logs:** Complete traceability of performed services and employee assignments.

### 📦 Inventory & Logistics
- **Smart Stock Control:** Management of shop products with automated reordering triggers.
- **Sensor Simulation:** Logic implemented to handle notification alerts for fuel levels and equipment maintenance.

### 💳 Loyalty & Retention
- **Gamified Rewards:** A points-based system (1 point per €1) where customers can redeem discounts and free services.
- **Customer Portal:** Dedicated area for users to track their balance and history.

## 🛠️ Technical Stack

- **Backend:** PHP (Procedural/Modular logic)
- **Database:** MySQL (Relational schema with foreign key constraints)
- **Frontend:** HTML5, CSS3 (Custom UI), and JavaScript
- **Dynamic UX:** **jQuery** and **AJAX** for real-time alerts and data updates without page refreshes.

## 🏗️ Highlights
- **Notification Engine:** Implementation of a logic-based alert system for critical station events.
- **Scalability:** Designed with a clear separation between business logic and presentation (UI/UX).

## 🔧 Installation & Setup

1. **Environment:** Best run on **XAMPP**, WAMP, or any Apache server.
2. **Database:** Import the provided `.sql` file to your MySQL instance.
3. **Configuration:** Update `db_connection.php` with your local credentials.
4. **Access:** Navigate to `http://localhost/GasolineStation_Project/Projeto/login.php`.
