# Database ERM & Role Permissions

This document outlines the database structure and the role-based access control (RBAC) permissions for the **LibroTech Library Management System**.

## 1. Entity Relationship Diagram (ERM)

```mermaid
erDiagram
    USERS ||--o{ OTP : "has"
    USERS ||--o{ BORROWING_RECORDS : "makes"
    BOOKS ||--o{ BORROWING_RECORDS : "is involved in"

    USERS {
        int user_id PK
        string first_name "Encrypted"
        string last_name "Encrypted"
        string email "Encrypted"
        string username "Encrypted"
        string password "Hashed"
        enum role "Admin, Librarian, Student"
        enum approval_status "Pending, Approved, Rejected, Inactive"
        timestamp created_at
    }

    OTP {
        int id PK
        int user_id FK
        string otp_code
        timestamp otp_expiration
    }

    BOOKS {
        int book_id PK
        string title
        string author
        string category
        int available_copies
        enum status "Available, Borrowed, Not Available"
        timestamp created_at
    }

    BORROWING_RECORDS {
        int id PK
        int user_id FK
        int book_id FK
        date borrow_date
        date due_date
        date return_date
        string status "Borrowed, Returned, Overdue"
    }
```

## 2. Role Permissions Matrix

| Feature | Admin | Librarian | Student |
| :--- | :---: | :---: | :---: |
| **Manage Users** (Approve/Reject/Delete) | ✅ | ❌ | ❌ |
| **Manage Books** (Add/Edit/Delete) | ✅ | ✅ | ❌ |
| **Search & Browse Books** | ✅ | ✅ | ✅ |
| **Borrow/Return Books** | ✅ | ✅ | ❌ |
| **View Own History** | ✅ | ✅ | ✅ |
| **System-wide Reports** | ✅ | ✅ | ❌ |
| **System Settings** | ✅ | ❌ | ❌ |
| **Audit Logs/Activity** | ✅ | ❌ | ❌ |

---

## 3. Permission Details

### 🛡️ Administrator (Admin)
The Admin is the "Superuser" with absolute control over the system.
- **Account Governance**: Only the Admin can approve or reject registration requests.
- **System Security**: Can deactivate or permanently delete accounts.
- **Global Visibility**: Full access to all system-wide statistics and activity logs.

### 📚 Librarian
The Librarian focuses on day-to-day library operations.
- **Inventory Management**: Full CRUD (Create, Read, Update, Delete) permissions for the book collection.
- **Circulation Control**: Manages the borrowing and returning process for students.
- **Operational Reports**: Can view reports related to book popularity and overdue items.

### 🎓 Student
The Student is a consumer of the library services.
- **Resource Access**: Can search and browse the entire book catalog.
- **Personal Dashboard**: Can view their own borrowing history and the status of their current loans.
- **Account Management**: Limited to viewing their own profile and registration status.
