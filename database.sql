-- Database: libro_tech_db
-- Applied Normalization: 1NF, 2NF, 3NF

CREATE DATABASE IF NOT EXISTS libro_tech_db;
USE libro_tech_db;

-- 1. Categories Table (3NF: Eliminates transitive dependency from books table)
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Authors Table (3NF: Allows M:M relationship and avoids redundant author data)
CREATE TABLE authors (
    author_id INT AUTO_INCREMENT PRIMARY KEY,
    author_name VARCHAR(255) NOT NULL UNIQUE,
    biography TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Users Table (1NF: Atomic values; 2NF/3NF: Role normalization)
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone_number VARCHAR(20),
    gender ENUM('Male', 'Female', 'Other') NOT NULL,
    civil_status ENUM('Single', 'Married', 'Divorced', 'Widowed') NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Librarian', 'Student',
    approval_status ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Books Table (2NF: All non-key fields depend on book_id; 3NF: category_id reference)
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    category_id INT,
    quantity INT DEFAULT 0,
    available_quantity INT DEFAULT 0,
    status ENUM('Available', 'Out of Stock', 'Discontinued') DEFAULT 'Available',
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- 5. Book_Authors Junction Table (2NF: Handles Many-to-Many between Books and Authors)
CREATE TABLE book_authors (
    book_id INT,
    author_id INT,
    PRIMARY KEY (book_id, author_id),
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES authors(author_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Borrowing Records Table (Transaction Tracking)
CREATE TABLE borrowing_records (
    record_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status ENUM('Borrowed', 'Returned', 'Overdue') DEFAULT 'Borrowed',
    fine_amount DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Initial Seed Data for Testing
INSERT INTO categories (category_name) VALUES ('Programming'), ('Science Fiction'), ('History'), ('Mathematics');
INSERT INTO authors (author_name) VALUES ('Robert C. Martin'), ('Isaac Asimov'), ('J.K. Rowling');

-- Sample Admin Account (Password: admin123 - hashed version should be used in production)
INSERT INTO users (first_name, last_name, email, phone_number, gender, civil_status, username, password, role) 
VALUES ('System', 'Admin', 'admin@librotech.com', '09123456789', 'Male', 'Single', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin');
