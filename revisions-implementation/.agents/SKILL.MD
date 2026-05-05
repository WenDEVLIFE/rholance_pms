# Rholance PMS: Revision & Implementation Plan

## 🎯 Project Objective
Transform the Rholance Project Management System (PMS) into a modern, clean, and highly functional platform for both customers and staff. The focus is on product customization, precise project tracking, and efficient resource management centered around the Laguna and Cavite areas.

---

## 🎨 General UI/UX Strategy
- **Design Language**: Modern, Minimalist, and Professional.
- **Color Palette**: Sleek dark modes or clean "Industrial" light modes (grays, whites, and brand accent colors).
- **Responsiveness**: Ensure seamless experience across desktop and mobile for staff on-site and customers on-the-go.
- **Feedback**: Use micro-animations and clear progress indicators (e.g., progress bars for 20% completion).

---

## 🏠 1. Landing Page Enhancements
- **Customized Products Showcase**: Add a dedicated section or carousel featuring "Customized Products" to highlight the bespoke nature of the business.
- **Location Integration**: Explicitly display the **Laguna Location** at the bottom (Footer/Contact section) with a map or clear address details.
- **Call to Action**: Direct links to the Customer Customization tool.

---

## 👥 2. Customer Module
### 🛠️ Product Customization
- **Feature**: Interactive tool for customers to customize product parameters (dimensions, materials, design style).
- **Integration**: Customization results should link directly to the appointment/quote system.

### 📅 Appointment System
- **Location Filter**: Restrict appointment bookings to **Cavite and Laguna** only.
- **Dashboard Visibility**: Show a calendar or list of **Available Dates** for scheduling to prevent booking conflicts.
- **Info Visibility**: Display the shop's appointment contact info (Address, Phone, Email) clearly.

### 📊 Project Tracking & Dashboard
- **Transaction History**: Tabs or filters for **Ongoing**, **Finished**, and **Old Transactions**.
- **Project Details**:
    - **Expectation vs Reality**: Side-by-side comparison (Design/Mockup vs Actual Photos).
    - **Progress Tracking**: Real-time percentage completion (e.g., "20% Started - Foundation/Framing").
    - **Timeline**: Estimated completion date clearly displayed.
    - **Personnel**: Information on who is assigned to the project (Welder/Staff).
- **Financial Transparency**:
    - **Material Breakdown**: Detailed list of materials needed.
    - **Price Ranges**: Cost per material category and total estimated range.

### 💳 Payments & Legal
- **Add Payment**: Support for **Cash** and **GCash**.
- **Payment Verification**: Upload field for GCash proof of payment.
- **Contracts**: Digital access to project contracts and terms.

---

## 🧑‍💼 3. Staff Module (Cashier/Office)
### 📋 Appointment Management
- **Daily View**: See all appointments scheduled for a specific date.
- **Constraint**: Cashiers **cannot cancel** appointments (Initial stage is for consultation/quote only).
- **Scope Awareness**: System must highlight if a customer's address is within the Cavite/Laguna scope.

### 📦 Inventory & Project Logic
- **Table View**: Inventory must be displayed in a structured table format.
- **Material-Centric**: Inventory tracking should be focused on **Project Materials** used.
- **Project Acceptance Logic**:
    - Based on material availability: If materials are only available abroad (International), the system should prompt for a special acceptance process or flag the project.
    - Preference for locally sourced (Philippines) materials.

### ⚙️ Assignments & Workflow
- **Material Allocation**: Assign specific materials from inventory to a project.
- **Staff Assignment**: Assign Welders/Technicians to specific projects.
- **Cancellation Authority**: Define specific roles or conditions under which a project can be cancelled.

---

## 👨‍🏭 4. Staff Module (Welder/Production)
### 🏗️ Project Execution
- **Updates**: Provide granular updates based on appointments.
- **Progress Control**: Manually update the **Progress Percentage** (e.g., incrementing as milestones are met).
- **Timeline Management**: Adjust or confirm the estimated timeline as work progresses.
- **Material Tracking**: Update specific materials being used in real-time.
- **Contract Management**: Update contract status or details based on production changes.

---

## 👑 5. Admin Module
### 👥 User & Staff Management
- **Account Overview**: Full list of all registered users and their roles.
- **Performance Tracking**:
    - Monitor which staff members are handling which projects.
    - Identify high-performing staff (most projects completed/highest volume).

### 📈 Business Intelligence
- **Inventory Oversight**: Global view of all material stocks and usage trends.
- **Sales Analytics**:
    - Daily Sales
    - Weekly Sales
    - Monthly Sales
    - Yearly Sales

---

## 💻 Technical Considerations (Development Notes)
- **Database Schema Updates**:
    - `projects` table: Add `progress_percent`, `assigned_staff_id`, `estimated_completion`, `expectation_image`, `reality_image`.
    - `appointments` table: Add `location_scope` (Cavite/Laguna validation).
    - `payments` table: Add `payment_method` (Cash/GCash), `proof_image_path`.
    - `inventory` table: Add `source_origin` (Local/International).
- **Logic Implementation**:
    - Validation middleware for address scope during appointment booking.
    - Calculation engine for material price ranges and breakdowns.
