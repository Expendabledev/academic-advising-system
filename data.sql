-- Create the database
CREATE DATABASE IF NOT EXISTS academic_advising;
USE academic_advising;

-- Users table (for authentication)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'supervisor', 'student') NOT NULL,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Supervisors table (extends users)
CREATE TABLE supervisors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(20) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    office_location VARCHAR(100),
    office_hours VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Students table (extends users)
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    program VARCHAR(100) NOT NULL,
    academic_year ENUM('1st Year', '2nd Year', '3rd Year', '4th Year', 'Graduate') NOT NULL,
    gpa DECIMAL(3,2),
    status ENUM('active', 'inactive', 'graduated') DEFAULT 'active',
    supervisor_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES supervisors(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    credits INT NOT NULL,
    description TEXT,
    semester ENUM('Spring', 'Summer', 'Fall') NOT NULL,
    year INT NOT NULL,
    status ENUM('active', 'completed', 'upcoming') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY (course_code, semester, year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Student-Course enrollment
CREATE TABLE student_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    completion_status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
    grade VARCHAR(2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY (student_id, course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Advising meetings
CREATE TABLE meetings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    supervisor_id INT NOT NULL,
    meeting_date DATE NOT NULL,
    meeting_time TIME NOT NULL,
    duration INT NOT NULL COMMENT 'Duration in minutes',
    type ENUM('in_person', 'video_call', 'phone_call') NOT NULL,
    purpose TEXT,
    status ENUM('scheduled', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (supervisor_id) REFERENCES supervisors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Messages/communication
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reports
CREATE TABLE reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supervisor_id INT NOT NULL,
    report_type VARCHAR(50) NOT NULL,
    time_period VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supervisor_id) REFERENCES supervisors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System activities log
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create initial admin user
-- Default password: admin123 (will be hashed)
INSERT INTO users (username, password, email, role) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@university.edu', 'admin');

-- Create initial supervisor user
-- Default password: supervisor123
INSERT INTO users (username, password, email, role) 
VALUES ('supervisor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor@university.edu', 'supervisor');

-- Add supervisor profile
INSERT INTO supervisors (user_id, title, full_name, department, office_location, office_hours, phone)
VALUES (2, 'Dr.', 'Oyelakun T.A.', 'Computer Science', 'ICT Building, Room 205', 'Monday-Friday, 10am-4pm', '+2348012345678');

-- Create initial student user
-- Default password: student123
INSERT INTO users (username, password, email, role) 
VALUES ('student', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student@university.edu', 'student');

-- Add student profile
INSERT INTO students (user_id, student_id, full_name, email, program, academic_year, gpa, supervisor_id)
VALUES (3, 'CS2023001', 'John Smith', 'john.smith@student.university.edu', 'Computer Science', '3rd Year', 3.75, 1);