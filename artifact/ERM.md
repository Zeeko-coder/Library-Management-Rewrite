# Entity-Relationship Model (ERM) - Libro Tech Database

This diagram represents the structure and relationships of the `libro_tech_db` database.

## ER Diagram

```mermaid
erDiagram
    USERS ||--o{ OTP : "receives"
    USERS ||--o{ NOTIFICATIONS : "receives"
    USERS ||--o{ BORROWINGS : "makes"
    BOOKS ||--o{ BORROWINGS : "is borrowed in"
    
    USERS {
        int user_id PK
        string first_name
        string last_name
        string email
        string phone_number
        enum gender
        enum civil_status
        string username
        string password
        enum role
        enum approval_status
        timestamp created_at
        timestamp last_notif_view
    }
    
    OTP {
        int otp_id PK
        int user_id FK
        string otp_code
        datetime otp_expiration
        timestamp created_at
    }
    
    SYSTEM_SETTINGS {
        string setting_key PK
        text setting_value
        timestamp updated_at
    }
    
    NOTIFICATIONS {
        int id PK
        int user_id FK
        string type
        string title
        text message
        tinyint is_read
        timestamp created_at
    }
    
    BOOKS {
        int book_id PK
        string title
        string author
        string category
        int available_copies
        enum status
        int added_by
        timestamp created_at
    }
    
    BORROWINGS {
        int id PK
        int user_id FK
        int book_id FK
        int quantity
        datetime borrow_date
        datetime due_date
        datetime return_date
        string status
        timestamp created_at
    }
```

## Table Descriptions

### USERS
Stores information about librarians and students. Sensitive data (names, emails) is stored in an encrypted format.

### BOOKS
The library catalog containing book details, categories, and availability status.

### BORROWINGS
Tracks book transactions. It links users and books, storing dates for borrowing, due dates, and actual returns.

### NOTIFICATIONS
A system for sending reminders and news alerts to users.

### OTP
Stores one-time passwords for user verification and security.

### SYSTEM_SETTINGS
A key-value store for global application configurations (e.g., library name, borrow limits).
