# CMMS - Course Material Management System

A comprehensive web-based platform for university students to manage their academic life - courses, schedules, assessments, and study materials - all in one place.

## 🎯 Overview

CMMS is a full-featured PHP/MySQL application designed to help students stay organized throughout the semester. Track your class routine, manage assignments and quizzes, upload and browse study materials, and monitor your academic progress with an intuitive dashboard.

Built as a database-driven project using PHP and MySQL with a modern, responsive UI.

## ✨ Features

### 📚 Course Management
- **Course Enrollment** - Enroll in courses with course code and title
- **Course Details** - Centralized view for each enrolled course
- **Semester Organization** - Organize courses by semester

### 📅 Class Scheduling
- **Weekly Routine** - Add class sessions with day, time, room, and mode (online/offline)
- **Schedule View** - View your complete weekly schedule ordered from Saturday
- **PDF Download** - Download your class routine as a formatted PDF

### 📝 Assessment Tracking
- **Multiple Types** - Track quizzes, assignments, and exams
- **Due Dates** - Set due dates and times (AM/PM format)
- **Mark Complete** - Track completion status for each assessment
- **Mandatory Flags** - Mark assessments as mandatory

### 📂 Study Materials
- **File Upload** - Upload any file type (PDF, DOC, images, videos, etc.)
- **Link Sharing** - Share external links and resources
- **Browse Materials** - View all materials by course and student
- **Easy Access** - Direct download/view links for all materials

### 📊 Dashboard
- **Personalized View** - See all your enrolled courses at a glance
- **Quick Actions** - Enroll in courses, view upcoming assessments
- **Course Cards** - Visual cards showing course code, title, and semester

### 📆 Additional Features
- **Calendar Integration** - Monthly calendar view
- **Upcoming Assessments** - Dedicated page for upcoming quizzes and assignments
- **Student & Admin Roles** - Separate interfaces for students and administrators

## 🛠️ Tech Stack

| Layer           | Technology              |
|-----------------|-------------------------|
| **Backend**     | PHP 8.x                 |
| **Frontend**    | HTML5, CSS3, JavaScript |
| **Database**    | MySQL                   |
| **Server**      | Apache (via XAMPP)      |
| **Storage**     | File system (uploads/)  |

## 📋 Prerequisites

- [XAMPP](https://www.apachefriends.org/) (includes Apache, MySQL, PHP)
- Modern web browser (Chrome, Firefox, Edge, Safari)
- Git (for cloning the repository)

## 🚀 Installation & Setup

### 1. Install XAMPP
Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)

### 2. Clone the Repository
```bash
cd C:\xampp\htdocs
git clone https://github.com/yourusername/CMM-master.git
```

### 3. Create Database
1. Start Apache and MySQL from XAMPP Control Panel
2. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Create a new database named `cmms`
4. Import the schema:
   - Click on the `cmms` database
   - Go to "Import" tab
   - Choose file: `CMM-master/sql/schema.sql`
   - Click "Go"

### 4. Configure Database Connection
Edit `includes/db.php` if needed (default settings work with XAMPP):
```php
$host = 'localhost';
$dbname = 'cmms';
$username = 'root';
$password = '';
```

### 5. Access the Application
Open your browser and navigate to:
```
http://localhost/CMM-master/
```

## 📁 Project Structure

```
CMM-master/
├── Admin/                  # Admin management pages
├── assets/                 # CSS, images, and static files
│   ├── logo.svg
│   └── style.css
├── backend/               # Backend processing scripts
│   ├── add-assessment.php
│   ├── add-material.php
│   ├── add-session.php
│   ├── db.php
│   ├── download-routine.php
│   ├── enroll.php
│   ├── login.php
│   ├── logout.php
│   ├── mark-complete.php
│   └── register.php
├── includes/              # Reusable components
│   ├── db.php            # Database connection
│   ├── header.php        # Common header
│   └── footer.php        # Common footer
├── sql/                   # Database schema
│   └── schema.sql        # Complete database structure
├── uploads/              # User-uploaded files
├── admin-dashboard.php   # Admin interface
├── calendar.php          # Calendar view
├── course-details.php    # Course management page
├── dashboard.php         # Role router
├── enroll.php           # Course enrollment
├── login.php            # Login page
├── register.php         # User registration
├── routine-download.php # Class schedule view
├── student-dashboard.php # Student dashboard
├── study-material.php   # Browse study materials
└── upcoming-assessments.php # Upcoming deadlines
```

## 🗄️ Database Schema

The application uses 7 main tables:

- **UNIVERSITY** - University information
- **STUDENT** - Student accounts
- **ADMIN** - Admin accounts
- **COURSE** - Course catalog
- **ENROLLMENT** - Student-course relationships
- **ASSESSMENT** - Quizzes, assignments, and exams
- **CLASS_SESSION** - Weekly class schedule
- **STUDY_MATERIAL** - Uploaded files and links

## 👤 User Roles

### Student
- Register and login
- Enroll in courses
- Add class sessions
- Track assessments
- Upload and browse study materials
- Download class routine
- View calendar and upcoming deadlines

### Admin
- Manage universities
- Manage students
- System administration

## 🔒 Security Features

- Password hashing for secure storage
- Session-based authentication
- SQL injection prevention using prepared statements
- File upload validation
- Role-based access control

## 📱 Browser Compatibility

- ✅ Chrome (Recommended)
- ✅ Firefox
- ✅ Edge
- ✅ Safari
- ✅ Opera

## 🤝 Contributing

This is an academic project. If you'd like to contribute:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is created for educational purposes as part of CSE370: Database Systems course.

## 👨‍💻 Authors

Built with ❤️ by university students learning database systems.

## 🐛 Known Issues

None currently. Report issues on the GitHub repository.

## 📞 Support

For support, please open an issue on GitHub or contact the repository maintainer.

---

**Note:** This application is designed for local development with XAMPP. For production deployment, additional security measures and configurations are recommended.
