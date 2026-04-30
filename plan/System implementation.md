Implementation Plan: Library Management System (LibroTech)
This plan outlines the development roadmap for the Library Management System, aligning the existing UI Features with the Final Project Guidelines provided by the client.

1. Information Management (IM) - Database Design
The foundation of the system will be a normalized MySQL database to ensure data integrity and efficient querying.

Normalized Database Tables:
users: (id, username, password, role [Admin, Librarian, Student], profile_data)
books: (id, title, author, isbn, category_id, status, quantity, description)
categories: (id, category_name)
borrowing_records: (id, user_id, book_id, borrow_date, due_date, return_date, status [Borrowed, Returned, Overdue])
notifications: (id, user_id, message, type, created_at)
Key Functionalities:
CRUD Operations: Full Create, Read, Update, and Delete for Books and Members.
Advanced Queries: Implementation of JOIN queries to link borrowing records with user and book details.
Search & Filtering: Real-time search capabilities by Title, Author, or ISBN using SQL LIKE and filtering by Category.
2. System Integration & Architecture (SIA)
The system will follow a 3-tier modular architecture to ensure scalability and maintainability.

Architecture Layers:
Presentation Tier (UI): HTML5, CSS3 (Vanilla), and JavaScript for dynamic interactions.
Logic Tier (Application): PHP scripts handling authentication, business logic, and session management.
Data Tier (Database): MySQL database for persistent storage.
Integration Feature (Client Requirement):
We will implement two integration features to exceed requirements:

Dashboard/Report Integration: A visual dashboard for Librarians/Admins showing circulation statistics and popular books.
Email Notification Simulation: A logic-based system that flags overdue books and triggers reminders (Simulated for local environment).
3. Core Features Implementation (IPT2)
Based on the "Our Features" section of the landing page:

Book Cataloging: Implement the books CRUD interface with status tracking (Available/Borrowed).
Member Management: Secure registration and profile management for Students and Librarians.
Circulation System: Logic to calculate due dates, handle returns, and prevent borrowing if a user has overdue items.
UI/UX & Validation:
Form validation (Client-side JS and Server-side PHP).
Responsive design for mobile/tablet access.
Interactive feedback (Success/Error toasts).
4. Project Timeline (Strict Alignment)
Phase	Dates	Activities
Phase 1	April 20–23	Database Design, Normalization, and Schema Implementation.
Phase 2	April 24–28	Core Development: Authentication, CRUD for Books/Users, and Search.
Phase 3	April 29 – May 2	Integration: Circulation Logic, Dashboard Reports, and Notifications.
Phase 4	May 3–5	Debugging, UI Polish, and Documentation (PDF/SQL).
Phase 5	May 6–7	Final Presentation / Defense.
5. Verification Plan
Unit Testing: Validate each CRUD operation individually.
Integration Testing: Ensure borrowing a book correctly updates the book status and creates a record.
UI Audit: Check responsiveness and color palette consistency (Classic Library theme).
Documentation: Generate the required PDF containing Architecture diagrams and Database schema.