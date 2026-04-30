# Entity Relationship Model (ERM) - LibroTech

This diagram represents the visual structure of the **LibroTech** database, showing how users, books, and borrowing records interact.

```mermaid
erDiagram
    USERS ||--o{ BORROWING_RECORDS : "places"
    BOOKS ||--o{ BORROWING_RECORDS : "involved in"
    CATEGORIES ||--o{ BOOKS : "classifies"
    BOOKS ||--|{ BOOK_AUTHORS : "has"
    AUTHORS ||--|{ BOOK_AUTHORS : "writes"

    USERS {
        int user_id PK
        string first_name
        string last_name
        string email UK
        string phone_number
        enum gender
        enum civil_status
        string username UK
        string password
        enum role
        timestamp created_at
    }

    BOOKS {
        int book_id PK
        string title
        string isbn UK
        int category_id FK
        int quantity
        int available_quantity
        enum status
        text description
        timestamp created_at
    }

    CATEGORIES {
        int category_id PK
        string category_name UK
        timestamp created_at
    }

    AUTHORS {
        int author_id PK
        string author_name UK
        text biography
        timestamp created_at
    }

    BOOK_AUTHORS {
        int book_id PK, FK
        int author_id PK, FK
    }

    BORROWING_RECORDS {
        int record_id PK
        int user_id FK
        int book_id FK
        date borrow_date
        date due_date
        date return_date
        enum status
        decimal fine_amount
    }
```

### Key Relationship Definitions:
*   **One-to-Many (1:N)**: 
    *   One **Category** can contain many **Books**.
    *   One **User** can have multiple **Borrowing Records**.
    *   One **Book** can appear in multiple **Borrowing Records** (over time).
*   **Many-to-Many (M:N)**:
    *   **Books** and **Authors** share a many-to-many relationship, resolved via the `book_authors` junction table. This allows one book to have multiple authors and one author to write multiple books.
