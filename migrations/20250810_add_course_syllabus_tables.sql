-- Migration: Add course syllabus groups and items tables

CREATE TABLE IF NOT EXISTS course_syllabus_groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    university_course_id INTEGER NOT NULL,
    label TEXT NOT NULL,
    total_credit INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (university_course_id) REFERENCES university_courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS course_syllabus_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    group_id INTEGER NOT NULL,
    subject_name TEXT NOT NULL,
    credit_hours INTEGER NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES course_syllabus_groups(id) ON DELETE CASCADE
);
