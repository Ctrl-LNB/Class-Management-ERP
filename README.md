# CSE 60F — Class ERP & Management Dashboard

A lightweight, high-performance PHP and JavaScript web application for tracking class routines, exam schedules, faculty contacts, task deadlines, and student attendance for CSE 60F[cite: 16, 17, 19].

🌐 Live Website: [https://classerp.page.gd](https://classerp.page.gd)

---

## ✨ Features

* 📅 Class Timetable: Dynamic weekly schedule view with automated live countdowns to upcoming periods
* 📝 Exam Routine: Scheduled class tests, quizzes, midterms, and finals with pinned status indicators
* 👩‍🏫 Faculty Directory: Instructor cards complete with contact details, Google Classroom codes, and designated batch advisor tags
* ✅ Task Management: Assign and keep track of deadlines and instructions for coursework, labs, and projects
* ✅ Notice Management: Assign notices and keep track for coursework, labs, and projects
* 📋 Attendance Tracking: Interactive visual student roster with tap-to-mark attendance and copyable report formatting
* 🌙 Dark/Light Mode: Full theme toggle support with local storage persistence
* 🔒 Admin Control Panel: Secure session-based authentication to modify data, upload photos, and import/export database backups

---

## 🛠 Tech Stack

* Backend: PHP 8+ (Zero external dependencies)[cite: 15]
* Frontend: Vanilla JavaScript (ES6+), HTML5, CSS3[cite: 19]
* Storage: File-based JSON database (database.json) with atomic write locking[cite: 16, 17]

---

## 🛠 File Directories

├── api.php         
├── config.php      
├── data.php        
├── database.json    
├── index.php        
└── uploads/
