```markdown
# EduPool Course Syllabus Enhancement & Management Plan

This plan outlines the detailed changes needed to support course syllabi that vary per university. Courses now may be offered by different universities with unique syllabus details. The syllabus will be structured by “year” (e.g. Year I, Year II, etc.) or “semester” (e.g. Year I Sem I, Year I Sem II, etc.) with subjects and corresponding credit hours. The implementation includes modifications to the database, admin management interfaces, and course detail display.

---

## 1. Database Changes

**File:** `edu_pool.sql`  
_Add new tables to store syllabus details per university-course association._

- **New Table: course_syllabus_groups**  
  - Columns:
    - `id INT PRIMARY KEY AUTO_INCREMENT`
    - `university_course_id INT NOT NULL` (FK referencing `university_courses(id)`)
    - `label VARCHAR(100) NOT NULL` (e.g. "Year I", "Year I Sem I")
    - `total_credit INT DEFAULT 0` (manually updated or auto-summed)
    - `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`
    - `updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`
  - Foreign key: `FOREIGN KEY (university_course_id) REFERENCES university_courses(id) ON DELETE CASCADE`

- **New Table: course_syllabus_items**  
  - Columns:
    - `id INT PRIMARY KEY AUTO_INCREMENT`
    - `group_id INT NOT NULL` (FK referencing `course_syllabus_groups(id)`)
    - `subject_name VARCHAR(255) NOT NULL`
    - `credit_hours INT NOT NULL`
    - `created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP`
  - Foreign key: `FOREIGN KEY (group_id) REFERENCES course_syllabus_groups(id) ON DELETE CASCADE`

---

## 2. Admin Manage Courses Update

**File:** `admin/manage_courses.php`  
_Update the admin UI to allow linking a course with a university and managing syllabus details._

- **Add University Dropdown:**
  - In the add/edit course form, add a dropdown that lists all universities.
  - Retrieve university list from the `universities` table.
  - On form submission, after adding/updating a course record in the `courses` table, create (or update) a record in the `university_courses` junction table for the selected university and course.
  - Ensure proper error handling for insertion into the junction table.

- **"Manage Syllabus" Link:**
  - In the courses list, show a “Manage Syllabus” button/link if a university association exists for the course.
  - This link points to `admin/manage_course_syllabus.php?uc_id=[university_course_id]`.

---

## 3. Admin Manage Course Syllabus Page

**File:** `admin/manage_course_syllabus.php`  
_Create a new page to manage structured syllabus details._

- **Functionality:**
  - Accept a GET parameter `uc_id` (the ID from `university_courses`) to identify the course-university association.
  - Display existing syllabus groups with their items.
  - Provide a form to add a new syllabus group:
    - Group “label” (e.g., "Year I", "Year I Sem I", etc.)
    - A sub-form to add multiple subjects with fields: `subject_name` and `credit_hours`.
  - Allow editing/deleting syllabus groups and their items.
  - Sum credit hours for each group and display the total.
  - Use modern form elements, clear typography, spacing and appropriate alerts for errors.

- **Error Handling:**
  - Validate inputs (non-empty labels, valid credit hours).
  - Use try/catch for database operations.
  - Display error or success messages.

---

## 4. Course Detail Page Update

**File:** `pages/courses/course_detail.php`  
_Update the course detail page to display the structured syllabus._

- **Functionality:**
  - Allow users to view a course’s detailed syllabus.
  - If a course is linked to multiple universities, allow selecting one (dropdown) to view its specific syllabus.
  - Fetch syllabus groups for the chosen university-course association (`university_courses`).
  - For each syllabus group, display the group label (e.g., "Year II" or "Year III Sem I") and list subjects under it with their credit hours.
  - Display total credit hours for each group.
  
- **UI Considerations:**
  - Use a card layout or CSS grid to group syllabus sections.
  - Use clear headings and typography.
  - Use placeholder images only if necessary (for banners, use: `<img src="https://placehold.co/1200x400?text=University+Campus+Aerial+View+with+clear+lighting+and+enhanced+clarity" alt="University campus aerial view with enhanced clarity" onerror="this.src='fallback.jpg'">`).

- **Error Handling:**
  - Validate GET parameters and handle missing data gracefully.
  - Show user-friendly messages if syllabus details aren’t available.

---

## 5. Additional UI/UX and Integration

- **Modern, Responsive Design:**
  - Maintain blue and white color theme.
  - Ensure mobile-friendly layout with proper spacing and breakpoints.
  - Use CSS animations (as implemented in `assets/css/animations.css`) for subtle transitions highlighting syllabus sections.

- **Best Practices:**
  - Use prepared statements, input sanitization, and CSRF tokens in forms.
  - Log errors and activities (via `logActivity` function in `includes/functions.php`).
  - Provide clear feedback to admins and users through alert messages.

- **Dependent Files Reviewed:**
  - `edu_pool.sql` (update with new tables)
  - `admin/manage_courses.php` (updated add/edit form and logic)
  - `admin/manage_course_syllabus.php` (new file for syllabus management)
  - `pages/courses/course_detail.php` (update syllabus display)
  - Others (header/footer, functions, session) already in place.

---

## Summary
- Updated database schema by adding two new tables: `course_syllabus_groups` and `course_syllabus_items` for granular syllabus management.
- Enhanced admin manage courses page to include a university dropdown and a “Manage Syllabus” link.
- Introduced a new admin page `admin/manage_course_syllabus.php` to add/edit syllabus groups and items (structured per year/semester with credit hours).
- Updated the course detail page to display syllabus details group-wise with total credit hours.
- Employed modern, responsive UI design with clear typography and alerts, ensuring all error handling and best practices.
- This update supports real-world use cases where the same course has different syllabi across universities.
  
This completes the detailed plan and design for the requested enhancements.
