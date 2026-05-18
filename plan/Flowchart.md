# LibroTech Library Management System - Core Flowchart

This flowchart visualizes the primary user journeys within the application, encompassing Registration, Login (with OTP), and the respective Admin, Librarian, and Student workflows.

```mermaid
flowchart TD
    %% Define clean, readable pastel styles (Black text to avoid highlight issues)
    classDef action fill:#d4e6f1,stroke:#2980b9,stroke-width:2px,color:#000
    classDef decision fill:#fdebd0,stroke:#d35400,stroke-width:2px,color:#000
    classDef db fill:#d5f5e3,stroke:#27ae60,stroke-width:2px,color:#000
    classDef user fill:#ebdef0,stroke:#8e44ad,stroke-width:2px,color:#000

    Start([Start]):::action --> Auth{Has Account?}:::decision

    subgraph Registration Phase
        Auth -- No --> Register[Fill Registration Form]:::action
        Register --> SavePending[(DB: Save as 'Pending')]:::db
        SavePending --> WaitAdmin[Account Requires Admin Approval]:::user
    end

    subgraph Login & Authentication Phase
        Auth -- Yes --> Login[Enter Credentials]:::action
        Login --> CheckStatus{Is Approved?}:::decision
        CheckStatus -- No --> Reject[Access Denied]:::db
        CheckStatus -- Yes --> SendOTP["System Sends OTP (SMS / Email)"]:::db
        SendOTP --> InputOTP[Enter OTP]:::action
        InputOTP --> VerifyOTP{OTP Valid?}:::decision
        VerifyOTP -- No --> InputOTP
        VerifyOTP -- Yes --> RoleCheck{User Role?}:::decision
    end

    subgraph Admin Workflow
        RoleCheck -- Admin --> AdminDash[Admin Dashboard]:::user
        AdminDash --> MngSys[Manage System Settings]:::action
        AdminDash --> MngAllUsers[Manage All Users & Librarians]:::action
        MngAllUsers -.-> ApproveActAdmin[(DB: Set Pending Users to Approved)]:::db
    end

    subgraph Librarian Workflow
        RoleCheck -- Librarian --> LibDash[Librarian Dashboard]:::user
        LibDash --> MngBooks[Manage Books]:::action
        MngBooks -.-> dbAdd[(DB: Add/Edit/Delete)]:::db
        
        LibDash --> MngUsers[Manage Registrations]:::action
        MngUsers -.-> ApproveAct[(DB: Set Pending Users to Approved)]:::db
        
        LibDash --> ViewBorrow[View Borrowing Records]:::action
        LibDash --> SendNotif[Send Manual Email Reminders]:::db
    end

    subgraph Student Workflow
        RoleCheck -- Student --> StuDash[Student Dashboard]:::user
        StuDash --> Browse[Search Books]:::action
        Browse --> AvailCheck{Available?}:::decision
        AvailCheck -- No --> Browse
        AvailCheck -- Yes --> BorrowAction[Request to Borrow]:::action
        BorrowAction --> SysUpdateDB[(DB: Update Borrowings)]:::db
        SysUpdateDB --> BorrowSuccess[Borrowing Successful]:::db
    end

    subgraph Returns & Automation Phase
        BorrowSuccess --> NotifWait([Time Passes...]):::user
        NotifWait --> OverdueCheck{Due Date Near/Past?}:::decision
        OverdueCheck -- Yes --> AutoEmail[System Sends Email Reminder]:::db
        OverdueCheck -- No --> Return[Student Returns Book]:::action
        AutoEmail --> Return
        Return --> UpdateReturn[(DB: Update Book Status)]:::db
        UpdateReturn --> End([Cycle Complete]):::action
    end
```
