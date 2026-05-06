# Data Flow Diagrams: Staff Workflows

This document provides specialized Data Flow Diagrams (DFD) for the **Cashier** and **Welder** roles within the Rholance PMS.

---

## 🟢 Level 0: Context Diagram
The Context Diagram provides a macro view of how all actors interact with the system.

```mermaid
graph LR
    CU[Customer] <-->|Appointment Info, Payments, Progress Updates| SYS((Rholance PMS))
    CA[Cashier] <-->|Appointments, Inventory, Assignments, Payments| SYS
    WE[Welder] <-->|Work Logs, Progress updates, Material Usage| SYS
    AD[Admin] <-->|Reports, User Management, Analytics| SYS
```

---

## 🔵 Level 1: Cashier Workflow DFD
Focuses on administration, resource allocation, and financial verification.

```mermaid
flowchart TD
    %% Entities
    CU[Customer]
    CA["Cashier (Office/Staff)"]
    AD[Admin]

    %% Processes
    subgraph Processes
        P1["1.0 Appointment & <br/>Scope Validation"]
        P2["2.0 Sourcing & <br/>Material Stock Check"]
        P3["3.0 Project & <br/>Staff Assignment"]
        P5["5.0 Payment <br/>Verification"]
    end

    %% Data Stores
    subgraph "Data Stores"
        D1[("D1: Appointments")]
        D2[("D2: Inventory (Materials)")]
        D3[("D3: Projects & Contracts")]
        D4[("D4: Payments")]
    end

    %% --- Cashier Specific Flows ---
    CA ---|Monitor Daily Schedule| P1
    P1 ---|Check Cavite/Laguna Scope| D1
    
    CA ---|Update Stock Levels| P2
    P2 <-->|Check Local vs Int'l Availability| D2
    
    CA ---|Assign Welder & Allocate Stock| P3
    P3 ---|Initialize Project Record| D3
    P3 ---|Reserve Materials| D2
    
    CA ---|Verify GCash Proof/Cash| P5
    P5 ---|Update Payment History| D4
    P5 ---|Activate Contract| D3

    %% External Entity Interactions
    CU ---|Submit Appointment/Address| P1
    CU ---|Upload GCash Proof| P5
    AD ---|Inventory Oversight| D2
    AD ---|Sales Reports| D4

    %% Styling
    style CA fill:#e1f5fe,stroke:#01579b
    style Processes fill:#fff,stroke:#333,stroke-dasharray: 5 5
```

---

## 🟠 Level 1: Welder Workflow DFD
Focuses on production, real-time tracking, and material consumption.

```mermaid
flowchart TD
    %% Entities
    WE[Welder]
    CU[Customer]

    %% Processes
    subgraph Processes
        P4.1["4.1 Progress & <br/>Milestone Updates"]
        P4.2["4.2 Material <br/>Usage Logging"]
        P4.3["4.3 Timeline & <br/>Contract Adjustment"]
    end

    %% Data Stores
    subgraph "Data Stores"
        D2[("D2: Inventory (Materials)")]
        D3[("D3: Projects & Contracts")]
    end

    %% --- Welder Specific Flows ---
    D3 ---|Assigned Task Details| WE
    
    WE ---|Update Progress %| P4.1
    P4.1 ---|Store Completion Milestones| D3
    
    WE ---|Log Materials Consumed| P4.2
    P4.2 ---|Deduct from Inventory| D2
    
    WE ---|Update Estimated Completion| P4.3
    P4.3 ---|Update Contract/Timeline| D3

    %% External Entity Interactions
    D3 ---|Real-time % Tracker| CU
    D3 ---|Expectation vs Reality Photos| CU

    %% Styling
    style WE fill:#f3e5f5,stroke:#4a148c
    style Processes fill:#fff,stroke:#333,stroke-dasharray: 5 5
```

---

## 📖 Key Role Differences

| Feature | Cashier (Office) | Welder (Production) |
| :--- | :--- | :--- |
| **Primary Focus** | Scheduling & Resource Prep | Fabrication & Tracking |
| **Inventory Action** | Sourcing & Allocation (Reserving) | Consumption & Logging (Deducting) |
| **Project Interaction** | Activation & Assignment | Progress Updates & Timeline Adjustments |
| **Customer Touchpoint** | Scope Validation & Payments | Visual Progress (Photos) & % Updates |
