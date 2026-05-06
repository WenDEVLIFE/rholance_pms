# Rholance PMS: Modern UI & Functional Implementation (Tailwind CSS)

## 🎨 Design Philosophy
The Rholance Project Management System (PMS) leverages **Tailwind CSS** to create a high-performance, visually stunning interface. The aesthetic is **Industrial Modern** – dark, sleek, and professional, reflecting the welding industry while providing a premium software experience.

---

## 🏗️ 1. Module-Specific Features

### 🏠 Landing Page
- **Customized Products Showcase**: Dynamic grid/carousel highlighting bespoke work.
- **Location Integration**: High-visibility footer displaying the **Laguna** location and map.
- **Call to Action**: Quick links to the Customer Customization tool.

### 👤 Customer Module
- **Interactive Customization**: Tool to customize product parameters with real-time feedback.
- **Location-Aware Appointments**:
    - **Filter**: System restricts appointments to **Cavite and Laguna** only.
    - **Schedule Visibility**: Dashboard shows **Available Dates** for easy booking.
- **Project Tracking**:
    - **Transaction History**: Filtered tabs for `Ongoing`, `Finished`, and `Old` transactions.
    - **Expectation vs Reality**: Side-by-side visual comparison (Design vs. Actual).
    - **Granular Progress**: Milestone-based tracker (e.g., "20% Foundation/Framing").
    - **Transparency**: Estimated completion date and assigned personnel (Welder/Staff).
- **Financial Details**:
    - **Material Breakdown**: Full list of materials needed.
    - **Price Ranges**: Cost ranges for each material category.
- **Payment & Legal**:
    - **Dual Payment**: Choose between Cash or GCash (with proof-of-payment upload).
    - **Contracts**: Digital access to project contracts and terms.

### 🧑‍💼 Staff Module (Cashier)
- **Appointment Management**:
    - **Daily Overview**: View all appointments for a specific date.
    - **Constraint**: No cancellation authority for initial consultations.
- **Scope Validation**:
    - Automated flagging for out-of-scope addresses (outside Cavite/Laguna).
    - Customer-side visibility of scope restrictions.
- **Shop Visibility**: Easy access to shop info (Address, Phone, Email) for customer support.
- **Inventory & Projects**:
    - **Table-Based Inventory**: Focused on project materials used.
    - **Sourcing Logic**: Project acceptance based on material origin (Preference for local Philippines sourcing vs international).
    - **Resource Allocation**: Assign materials and staff (Welders) to specific projects.
- **Governance**: Defined rules for project cancellation authority.

### 👨‍🏭 Staff Module (Welder)
- **Production Control**:
    - Update project details based on appointment outcomes.
    - Manually update **Progress Percentage** and **Materials Used**.
    - Adjust **Timeline** and update **Contract** status in real-time.

### 👑 Admin Module
- **User Governance**: Overview of all accounts and roles.
- **Performance Intelligence**:
    - Staff workload tracking (who handles which projects).
    - Identification of high-performing staff members.
- **Operations & Sales**:
    - Global inventory oversight.
    - **Sales Analytics**: Daily, Weekly, Monthly, and Yearly reporting.

---

## 🛠️ 2. Core Design System (Tailwind)
- **Theme**: Dark Mode focus with `slate-900` backgrounds and `amber-500` accents.
- **Typography**: `Outfit` (Headings) and `Inter` (Body).
- **Glassmorphism**: Use of `backdrop-blur-md` for cards and modals.
- **Interactions**: Subtle `hover:scale-[1.02]` and `fade-in` animations for a premium feel.
