-- EduPool Database Schema
-- Create database and tables for the educational portal

CREATE DATABASE IF NOT EXISTS edu_pool;
USE edu_pool;

-- Admin table for admin authentication
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Universities table
CREATE TABLE universities (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    established_year YEAR,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Colleges table
CREATE TABLE colleges (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    location VARCHAR(500),
    map_link VARCHAR(500),
    image VARCHAR(255),
    university_id INT,
    website_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE SET NULL
);

-- Courses table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    duration VARCHAR(50),
    syllabus TEXT,
    eligibility TEXT,
    career_paths TEXT,
    required_documents TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Junction table for university-course relationships
CREATE TABLE university_courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    university_id INT NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (university_id) REFERENCES universities(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_university_course (university_id, course_id)
);

-- Junction table for college-course relationships
CREATE TABLE college_courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    college_id INT NOT NULL,
    course_id INT NOT NULL,
    program_level ENUM('Diploma', 'Bachelor', 'Master', 'PhD') NOT NULL,
    FOREIGN KEY (college_id) REFERENCES colleges(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_college_course (college_id, course_id)
);

-- Users table for student registration
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create indexes for better performance
CREATE INDEX idx_universities_name ON universities(name);
CREATE INDEX idx_colleges_name ON colleges(name);
CREATE INDEX idx_colleges_university ON colleges(university_id);
CREATE INDEX idx_courses_name ON courses(name);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);

-- Insert default admin user (password: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample data for testing
INSERT INTO universities (name, description, established_year, image) VALUES
('Tribhuvan University', 'The oldest and largest university in Nepal, established in 1959. It offers a wide range of undergraduate and graduate programs.', 1959, 'tu_banner.jpg'),
('Kathmandu University', 'A modern autonomous university established in 1991, known for its quality education and research programs.', 1991, 'ku_banner.jpg'),
('Pokhara University', 'Established in 1997, focusing on science, technology, and management education with practical approach.', 1997, 'pu_banner.jpg');

INSERT INTO colleges (name, description, location, map_link, image, university_id, website_url) VALUES
('Pulchowk Campus', 'Institute of Engineering under Tribhuvan University, premier engineering college in Nepal.', 'Lalitpur, Nepal', 'https://maps.google.com?q=Pulchowk+Campus+Lalitpur', 'pulchowk.jpg', 1, 'https://pcampus.edu.np'),
('Kathmandu University School of Engineering', 'Leading engineering school with modern facilities and industry connections.', 'Dhulikhel, Nepal', 'https://maps.google.com?q=KU+School+Engineering+Dhulikhel', 'ku_soe.jpg', 2, 'https://soe.ku.edu.np'),
('Pokhara University School of Business', 'Business school offering MBA and BBA programs with practical approach.', 'Pokhara, Nepal', 'https://maps.google.com?q=PU+School+Business+Pokhara', 'pu_sob.jpg', 3, 'https://sob.pu.edu.np');

INSERT INTO courses (name, duration, syllabus, eligibility, career_paths, required_documents) VALUES
('Computer Engineering', '4 Years', 'Programming Fundamentals, Data Structures, Computer Networks, Database Systems, Software Engineering, Web Development, Mobile App Development, Artificial Intelligence, Machine Learning', '+2 Science with Physics and Mathematics, Minimum 60% marks', 'Software Developer, System Administrator, Network Engineer, Data Scientist, AI Engineer, Web Developer, Mobile App Developer', 'Academic Transcripts, Character Certificate, Citizenship Certificate, Passport Size Photos'),
('Business Administration (BBA)', '4 Years', 'Principles of Management, Marketing Management, Financial Management, Human Resource Management, Operations Management, Business Communication, Entrepreneurship, Strategic Management', '+2 in any stream with minimum 50% marks', 'Business Manager, Marketing Executive, HR Manager, Financial Analyst, Entrepreneur, Consultant, Project Manager', 'Academic Transcripts, Character Certificate, Citizenship Certificate, Passport Size Photos'),
('Master of Business Administration (MBA)', '2 Years', 'Advanced Management, Strategic Planning, Leadership, International Business, Digital Marketing, Financial Analysis, Operations Research, Business Analytics', "Bachelor's degree in any field with minimum 50% marks", 'CEO, General Manager, Business Consultant, Investment Banker, Management Consultant, Director, Senior Manager', "Bachelor's Degree Certificate, Academic Transcripts, Work Experience Certificate, Character Certificate");

INSERT INTO university_courses (university_id, course_id) VALUES
(1, 1), (1, 2), (2, 1), (2, 3), (3, 2), (3, 3);

INSERT INTO college_courses (college_id, course_id, program_level) VALUES
(1, 1, 'Bachelor'), (2, 1, 'Bachelor'), (3, 2, 'Bachelor'), (3, 3, 'Master');
