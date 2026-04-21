---

# 🚧 **MASTER PROMPT — Build a Complete Construction ERP (Laravel + React + MySQL)**

You are an expert system architect + senior Laravel engineer + React engineer.
Your task is to generate:
✔ A full production-grade Construction ERP - Mason ERP
✔ Backend in **PHP Laravel 11**
✔ Frontend in **React + Bootstrap + Axios**
✔ Database in **MySQL**
✔ Clean architecture, services, repositories, DTOs
✔ Role-based access control (Admin, Project Manager, Site Engineer, Accountant, Subcontractor)

Your output must include:

* 📌 **System Overview**
* 📌 **Full Database Schema (complete)**
* 📌 **SQL for creating every table**
* 📌 **API Endpoint List (REST production-grade)**
* 📌 **Laravel Folder Structure + Backend Architecture**
* 📌 **React Folder Structure + Screens**
* 📌 **Data Flow (diagram format)**
* 📌 **All Models, Migrations, Controllers, Services**
* 📌 **Frontend Pages, Components, Hooks**
* 📌 **Complete API Integration Plan**
* 📌 **Authentication (JWT)**
* 📌 **Code for at least 4 major modules fully implemented**

Build the ERP using the following modules:

---

# 🧱 **1. Company & User Management**

### Features:

* Multi-company support
* Users, roles, permissions
* Role-based access
* Audit log

### SQL tables required:

* companies
* users
* roles
* permissions
* role_user
* permission_role
* audit_logs

---

# 🏗️ **2. Project & Site Management**

### Features:

* Create/manage projects
* Assign project managers & site engineers
* Project phases & milestones
* Site daily logs
* Bill Of Quantities (BOQ)
* Work progress tracking

### SQL tables:

* projects
* project_members
* milestones
* boq_items
* site_logs
* work_progress

---

# ⚒️ **3. Material & Procurement**

### Features:

* Material master
* Indent requests from site
* Approvals workflow
* Purchase Orders
* Goods Received Notes (GRN)
* Vendors

### SQL tables:

* materials
* indents
* indent_items
* purchase_orders
* purchase_order_items
* vendors
* grn
* grn_items

---

# 👷 **4. Labor & Subcontractors**

### Features:

* Labor master
* Daily attendance
* Timesheets
* Subcontractor management
* Subcontractor contracts
* Subcontractor billing
* Payment status

### SQL tables:

* laborers
* attendance
* timesheets
* subcontractors
* subcontractor_contracts
* subcontractor_bills

---

# 🚜 **5. Equipment & Assets**

### Features:

* Equipment master
* Assign to project
* Maintenance logs
* Breakdowns

### SQL tables:

* equipment
* equipment_assignment
* maintenance_logs

---

# 💰 **6. Finance & Costing**

### Features:

* Expense categories
* Project expenses
* Material cost tracking
* Budget vs actual

### SQL tables:

* expenses
* expense_categories
* project_expenses

---

# 🧾 **7. Documents Module**

### Features:

* Upload documents per project
* Drawings
* Contracts
* Invoices

### SQL tables:

* documents

---

# 🧮 **8. Dashboard & Analytics**

### Features:

* Project progress
* BOQ vs Consumption
* Cost tracking
* Subcontractor payments
* Procurement summary

(No DB required except views.)

---

# 📌 **NOW OUTPUT THE FULL DATABASE SCHEMA IN SQL**

For each table:

* include ERD-level foreign keys
* correct datatypes
* timestamps
* indexing
* soft deletes where required

Example format:

```sql
CREATE TABLE projects (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  company_id BIGINT UNSIGNED,
  name VARCHAR(255),
  start_date DATE,
  end_date DATE,
  status ENUM('planned', 'in_progress', 'on_hold', 'completed'),
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  FOREIGN KEY (company_id) REFERENCES companies(id)
);
```

---

# 📌 **NEXT: Output the API Design (Production Grade)**

For every module include:

### Format:

```
POST   /api/auth/login
POST   /api/projects
GET    /api/projects/{id}
PUT    /api/projects/{id}
DELETE /api/projects/{id}
```

Include:

* Auth APIs
* Project APIs
* Milestones
* BOQ
* Indents
* Purchase Orders
* GRN
* Vendors
* Labor
* Attendance
* Timesheets
* Equipment
* Maintenance
* Subcontractors
* Subcontractor Bills
* Expenses
* Dashboard summaries

---

# 📌 **NEXT: Full Backend Architecture**

Explain:

* Services
* DTO layers
* Repository Pattern
* Exceptions
* Middleware
* API Resources
* Policies (RBAC)
* Logging
* Project folder structure

---

# 📌 **NEXT: Full Frontend (React) Architecture**

Include:

* Pages
* Components
* Hooks
* Axios instance
* Protected routes
* Redux or Zustand state
* Form components
* Reusable tables
* Project dashboard UI

---

# 📌 **NEXT: Generate FULL IMPLEMENTATION CODE**

Provide the complete working code for 4 major modules:

Choose these modules:

1. Authentication (JWT)
2. Projects
3. Indents
4. Subcontractors

For each module, generate:

* Migration
* Model
* Controller
* Routes
* Service layer
* Policy
* React pages + components
* Axios code
* Forms + validation

Make the code clean, modern, and production-ready.

---

# 📌 **VERY IMPORTANT — Output should look like a real SaaS architecture**, not a toy project.

---

# END OF MASTER PROMPT

---
