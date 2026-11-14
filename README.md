# 🗳️ Online Voting System

A web-based **Student Union Election Management Portal** built with **PHP, MySQL, HTML, CSS and JavaScript**.
It allows a college or university to run elections online: students register, departments approve them, the admin creates elections and candidates, students vote once per position, and results are calculated automatically.

---

## 👨‍🎓 Project Information

| | |
|---|---|
| **Project Title** | Online Voting System |
| **Developed by** | Aminul Islam |
| **Registration No.** | 20220007157 |
| **Department** | Computer Science and Application |
| **University** | Assam University, Silchar |

---

## ✨ Features

### 👑 Admin (Presiding Officer)
- Dashboard with total departments, candidates and votes
- Create, edit and delete elections (title, description, start and end date)
- Add, edit and delete candidates (photo, position, department, percentage, manifesto)
- Approve, reject or delete department accounts
- Calculate results and **show / hide** results for each election
- Send notifications to all users, a department or students

### 🏫 Department (HOD)
- Register and log in with e-mail or mobile number
- Approve or reject students of its own department
- View student list and manage candidates
- View election results and edit profile

### 🎓 Student (Voter)
- Register with registration number, class roll, department, session, e-mail and mobile
- Log in after the HOD approves the account
- Cast **one vote per position** in an active election
- View results (when the admin publishes them), notifications and profile

---

## 🧰 Technologies Used

| Layer | Technology |
|---|---|
| Front end | HTML5, CSS3, JavaScript, Font Awesome, Google Fonts |
| Back end | PHP 8.1 (MySQLi prepared statements) |
| Database | MySQL / MariaDB 10.4 |
| Server | Apache (XAMPP) + phpMyAdmin |
| Library | PHPMailer 6.8 (Composer) |

---

## ⚙️ Installation (Step by Step)

1. **Install XAMPP** and start **Apache** and **MySQL** from the XAMPP Control Panel.
2. **Copy the project folder** `ovs` into:
   - Windows: `C:\xampp\htdocs\`
   - macOS / Linux: `/opt/lampp/htdocs/`
3. Open **phpMyAdmin** → `http://localhost/phpmyadmin`
4. Create a new database named **`voting_system`**.
5. Select that database → **Import** → choose `ovs/database/voting_system.sql` → **Go**.
6. Check the database settings in `config.php`:
   ```php
   $db_host = 'localhost';
   $db_user = 'root';
   $db_pass = '';
   $db_name = 'voting_system';
   ```
7. Open the project in your browser:
   **`http://localhost/ovs/`**

---

## 🔑 Demo Login Details

Choose the correct **user type** on the login page, then enter the details.

| Role | Username | Password |
|---|---|---|
| 👑 **Admin** | `1234567890` | `admin123` |
| 🏫 **Department (HOD)** | `8888888888` | `888888` |
| 🎓 **Student** | `9999999999` | `999999` |

> **Notes**
> - Admin logs in with **mobile number**, Department with **HOD e-mail or mobile**, Student with **registration number or mobile**.
> - These accounts are for **demo / testing only**. Make sure they exist in your database (create them from the registration pages or phpMyAdmin if needed).
> - The student account must be **approved by the HOD** before it can log in and vote.
> - **Change all default passwords** before using the system for a real election.

---

## 🔄 How to Use (Quick Demo Flow)

1. **Admin** logs in → creates an **Election** (start date must be in the future, end date after start).
2. **Admin** adds **Candidates** for positions (President, Vice President, Secretary, etc.).
3. **Department** registers → **Admin** approves the department.
4. **Student** registers → **HOD** approves the student.
5. When the election is active, **Student** logs in → **Cast Vote**.
6. **Admin** → **Manage Results** → calculate and make results **visible**.
7. **Student / Department** can now view the results.

---

## 🗄️ Database Tables

`admins` · `departments` · `students` · `elections` · `positions` · `candidates` · `votes` · `results` · `notifications` · `notification_reads` · `settings`

Key rules:
- `students.registration_no`, `email` and `mobile` are **unique**.
- A student can vote **only once per position in an election** (checked before every insert, inside a transaction).

---

## 📁 Folder Structure

```
ovs/
├── index.php                 # Common login page
├── config.php                # Database connection & session helpers
├── process_login.php         # Login processing
├── student_register.php      # Student registration
├── department_register.php   # Department registration
├── logout.php
├── database/
│   └── voting_system.sql     # Database dump
├── admin/                    # Admin module
├── department/               # Department (HOD) module
├── student/                  # Student module (cast_vote, results, profile)
├── includes/  css/           # Shared header, footer, styles
└── uploads/candidates/       # Candidate photos
```

---

## 🔐 Security Notes

- Uses **prepared statements** to prevent SQL injection.
- Student and department passwords are stored **hashed** (`password_hash`).
- Sample admin password is plain text — **hash it before real use**.
- Delete development helper scripts (`setup_*.php`, `fix_*.php`, `create_*.php`, `check_*.php`, `debug.log`) from a production server.
- Turn off `display_errors` in `config.php` on a live server.

---

## 🚀 Future Enhancements

- OTP verification by SMS / e-mail
- Biometric or face verification
- Result charts and PDF / Excel export
- Secret-ballot design (separate voter identity from vote)
- Audit logs and mobile app

---

## 📄 License

Developed as an academic project for **Assam University, Department of Computer Science and Application**.
Free to use for learning and educational purposes.
