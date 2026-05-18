# LibroTech: Library Management System Documentation

## 1. Introduction
The LibroTech Library Management System is a comprehensive, web-based application designed to streamline and automate the daily operations of a library. The system caters to three primary user roles: Administrators, Librarians, and Students. It aims to eliminate manual record-keeping by providing a centralized platform for cataloging books, managing user memberships, tracking circulation (borrowing and returning), and generating analytical reports. A key objective of this project is to integrate modern communication technologies, specifically SMS notifications, to enhance user engagement and ensure timely return of borrowed materials.

## 2. System Architecture (SIA)
LibroTech is built upon a robust **3-Tier Modular Architecture** to ensure separation of concerns, scalability, and ease of maintenance:

*   **Presentation Tier (UI):** Developed using standard HTML5, CSS3, and JavaScript. It features a responsive, modern design tailored for different user roles with dedicated interactive dashboards.
*   **Logic Tier (Application):** Powered by PHP, this tier handles all business logic, session management, secure user authentication (including OTP verification), and acts as the intermediary between the user interface and the database.
*   **Data Tier (Database):** Utilizes a MySQL database for secure, persistent storage of all library data and state management.
*   **Integration Feature:** The system integrates the **UniSMS API** to facilitate automated SMS notifications. This integration fulfills the requirement for an external API/Integration feature, utilizing it for Two-Factor Authentication (OTP) during registration/login and for sending alerts.

## 3. Database Design (IM)
The database (`libro_tech_db`) has been strictly designed following normalization rules (1NF, 2NF, and 3NF) to guarantee data integrity, eliminate redundancy, and support efficient relational queries (JOINs).

*   **Users Table:** Stores normalized user profiles (Admin, Librarian, Student) with securely hashed passwords.
*   **Books Table:** Contains core inventory details. Transitive dependencies were eliminated by linking to a separate `categories` table via Foreign Keys.
*   **Categories & Authors Tables:** Distinct tables normalized to 3NF to allow for efficient categorization and many-to-many relationships (handled via a `book_authors` junction table).
*   **Borrowings/Transactions Table:** Tracks the lifecycle of a borrowed book, linking `users` and `books` via Foreign Keys, and storing critical timeline states (borrow date, due date, return status).
*   **OTP & Notifications Tables:** Supports the integrated SMS authentication workflows and internal system alerts.

*Data relationships are strongly enforced using Foreign Keys with `ON DELETE CASCADE` and `ON UPDATE CASCADE` constraints to maintain strict referential integrity.*

## 4. System Features (IPT2)
The application provides a fully functional, interactive web-based experience boasting the following core features:

*   **Comprehensive CRUD Operations:** 
    *   **Book Management:** Librarians and Admins can Create, Read, Update, and Delete book records, controlling the library's physical inventory.
    *   **User Management:** Admins have full control over user registrations, with the ability to approve, reject, or suspend accounts.
*   **Advanced Search and Filtering:** Students and staff can search the dynamic catalog using complex queries (utilizing SQL `JOIN` and `LIKE` operators) to filter inventory by title, author, or category in real-time.
*   **Circulation Logic:** An automated system tracks real-time book availability, logs borrowing transactions, calculates due dates, and automatically flags overdue items based on the current date.
*   **Interactive UI/UX:** The system features intuitive sidebar navigation, distinct role-based dashboards, data visualization, and responsive forms equipped with strict client-side and server-side validation to ensure clean data entry.
*   **Feedback & Notifications:** Users receive immediate visual feedback on their actions (via success/error toasts) alongside the integrated SMS alerts for critical account actions.

## 5. Screenshots of Outputs (System Flow)
*(Please insert the actual screenshot images under each respective step)*

### Phase 1: Onboarding and Authentication Flow
*   **Step 1: Landing Page** - The initial public-facing interface of the LibroTech system.
*   **Step 2: User Registration** - A student or librarian filling out the registration form.
*   **Step 3: Admin Approval Process** - The Admin dashboard showing the pending user being approved.
*   **Step 4: Login & OTP Verification** - The login screen followed by the SMS OTP verification prompt.
*   **Step 5: SMS OTP Received** - A mobile device screenshot proving the OTP SMS was successfully delivered via UniSMS.

### Phase 2: Core Library Operations Flow
*   **Step 6: Librarian Adding a Book (CRUD)** - The Librarian dashboard showing the process of adding a new book to the inventory.
*   **Step 7: Student Searching the Catalog** - The Student dashboard demonstrating the advanced search and filter functionality.
*   **Step 8: Borrowing a Book** - The student interacting with the system to request or borrow a book.
*   **Step 9: Student Dashboard (Active Loans)** - The student's view of their currently borrowed books and due dates.
*   **Step 10: Librarian Circulation Management** - The Librarian view showing the active borrowings and overdue statuses.
*   **Step 11: SMS Overdue Notification** - A mobile device screenshot showing an automated SMS reminder for an overdue book.
