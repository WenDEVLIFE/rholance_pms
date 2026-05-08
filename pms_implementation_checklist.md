# Rholance Trading PMS Implementation Checklist

This checklist tracks the progress of the Rholance Trading Project Management System modernization and feature implementation.

---

## 🌟 General & UI/UX
- [ ] **Modern UI/UX Design**
    - [x] Define global CSS variables (colors, shadows, glass tokens).
    - [x] Implement Glassmorphism `.glass-premium` + `.btn-modern` utility classes.
    - [ ] Ensure mobile responsiveness across all pages.
- [ ] **Landing Page Updates**
    - [x] Add "Customized Products" section & filter tab.
    - [x] Update Footer with **Laguna** and **Cavite** branch locations + maps.
    - [ ] Modernize Hero section with Glassmorphism.
    - [ ] Update FAQ answers to match real system data.

---

## 👤 Customer Features
- [x] **Product Customization** (`custom_orders` table)
    - [x] `customize.php` — form with Project Name, Category, Material, Dimensions, Description.
    - [x] Reference image upload (`image`/`reference_image` column).
    - [x] `api/store_custom_order.php` — backend insert.
- [x] **Appointment Management** (`appointments` table)
    - [x] Booking modal with **Branch selection** (Cavite / Laguna only).
    - [x] `request_appointment.php` — includes `branch_id`.
- [x] **Project Tracking** (`my_projects.php`)
    - [x] Filter tabs: **Ongoing** / **Finished** / **Old Transactions**.
    - [x] Progress % derived from status.
    - [x] Card grid with status badge and progress bar.
- [x] **Project Details** (`project_details.php`)
    - [x] Progress % and status.
    - [x] Estimated completion date.
    - [x] Assigned personnel (welders via `tasks` join).
    - [x] "Expectation vs Reality" image comparison.
    - [x] Material breakdown table (`order_items` → `items`).
- [x] **Payment** (`add_payment.php`)
    - [x] Choose Cash or GCash.
    - [x] GCash proof screenshot upload.
    - [x] `api/store_payment.php` — backend insert into `transactions`.
- [ ] **Digital Contract** — viewing / signing (not yet built).

---

## 👷 Staff (Cashier) Features
- [x] **Appointment Page** (`staff/appointment.php`) — rebuilt
    - [x] Date strip + date filter picker.
    - [x] Out-of-scope address warning badge.
    - [x] Customer info popover (Address, Email, Contact).
    - [x] Approve / Reject / Complete actions.
    - [x] Walk-in form + Add Slot form.
- [x] **Inventory** (`inventory/staff_inventory.php`) — rebuilt
    - [x] Items grouped by category with item names (not IDs).
    - [x] Price, stock count, color-coded status (In / Low / Out).
    - [x] Summary cards + live search.
- [x] **Project Management** (`staff/project_management.php`)
    - [x] Status filter tabs (All / Appointment / On-going / etc.).
    - [x] Card view with project image, status badge, welder badge.
    - [x] **Assign Welder** modal (from `users` where role=welder).
    - [x] **Add Materials** in assign modal (dynamic rows, all items).
    - [x] **Set Completion Date** in assign modal.
    - [x] **Update Status** modal.
    - [x] **Cancel Project** (one-click with confirmation).
    - [x] `api/assign_project.php` — inserts tasks + order_items.
    - [x] `api/update_project_status.php` — updates status.
- [ ] **Cancellation policy enforcement** — rule-based (e.g., who can approve cancellations).

---

## 🔨 Staff (Welder) Features
- [x] **Welder Dashboard** (`staff/welder_dashboard.php`)
    - [x] Shows only projects assigned to the logged-in welder.
    - [x] Card view with progress bar, status badge, due date.
    - [x] **Update Modal**: Status, Progress %, Estimated Date, Remarks.
    - [x] `api/welder_update.php` — updates status + logs remarks to `transactions`.
- [ ] **Update materials used** — welder-side material editing not yet implemented.
- [ ] **Contract update** — not yet implemented.

---

## 🔑 Admin Features
- [x] **User Management** (`admin/user_management.php`)
    - [x] Summary count cards (Admin / Staff / Welder / Customer).
    - [x] Searchable, filterable table (role, branch, name/email).
    - [x] User avatar, role pill, status pill.
    - [x] Block / Unblock / Archive actions.
- [x] **Sales Reports** (`admin/sales_reports.php`)
    - [x] Period tabs: Daily / Weekly / Monthly / Yearly.
    - [x] Chart.js revenue trend line chart.
    - [x] Summary cards (Revenue, Completed, Active, Staff count).
    - [x] **Staff & Welder Project Load** table with visual load bar.
    - [x] **Top Customers by Spending** table.
- [ ] **Admin inventory monitoring** (global, across branches) — not yet separate page.

---

## 🗄️ Database Notes
- Schema from `rholance_pms.sql` (MariaDB 10.4) — imported.
- Key tables: `appointments`, `appointment_slots`, `branches`, `custom_orders`, `inventory`, `items`, `order_items`, `sales`, `tasks`, `task_assignments`, `transactions`, `users`.
- Missing columns added via `tools/update_schema.php`: `transactions.payment_method`, `transactions.payment_proof`.
- Branches: `1` = Cavite (Bautista), `2` = Laguna.

---

## 🚧 Still To Do
- [ ] Landing page hero section modernization.
- [ ] Digital contract feature.
- [ ] Welder — edit materials used on a project.
- [ ] Admin — global inventory page (both branches).
- [ ] Mobile responsiveness pass.
- [ ] Cancellation policy / approval workflow.
- [ ] Sidebar navigation links updated to include new pages.

---
*Last updated: 2026-05-08*
