# ResumeForge — Resume Builder

A full-stack resume builder built with HTML, CSS, JavaScript, and PHP + MySQL.

---

## Project Structure

```
resume_builder/
├── index.php            → Redirects to login or dashboard
├── login.php            → Sign-in page
├── register.php         → Account creation page
├── dashboard.php        → User dashboard with progress stats
├── builder.php          → Resume input form (all sections)
├── preview.php          → Print-ready resume preview
├── logout.php           → Session destroy + redirect
│
├── css/
│   └── style.css        → All styles (dark theme, print, responsive)
│
├── js/
│   ├── builder.js       → Tabs, dynamic entries, skills, autosave
│   └── auth.js          → Client-side form validation
│
├── php/
│   └── save_resume.php  → POST endpoint: save/update resume in DB
│
└── includes/
    ├── db.php           → PDO database connection
    ├── auth.php         → Session helpers (requireLogin, isLoggedIn)
    └── schema.sql       → MySQL table definitions
```

---

## Setup Instructions

### 1. Install a local server
Download and install **XAMPP** (Windows/Mac/Linux):  
https://www.apachefriends.org/

Start **Apache** and **MySQL** from the XAMPP Control Panel.

### 2. Place project files
Copy the entire `resume_builder/` folder into:
```
C:\xampp\htdocs\resume_builder\    (Windows)
/Applications/XAMPP/htdocs/resume_builder/  (Mac)
```

### 3. Create the database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **New** in the left sidebar → name it `resume_builder` → click Create
3. Click the `resume_builder` database → click the **SQL** tab
4. Copy the contents of `includes/schema.sql` and paste → click **Go**

### 4. Configure DB credentials (if needed)
Open `includes/db.php` and update:
```php
define('DB_USER', 'root');   // your MySQL username
define('DB_PASS', '');       // your MySQL password (blank by default in XAMPP)
```

### 5. Open the app
Go to: `http://localhost/resume_builder/`

You'll be redirected to the login page. Click **Create one free** to register.

---

## Features

- Secure registration & login with password hashing (`password_hash`)
- PHP session-based authentication with page guards
- SQL injection prevention via PDO prepared statements
- Resume sections: Personal Info, Summary, Experience, Education, Skills, Links
- Dynamic add/remove entries for Experience and Education
- Skill tag UI with comma-separated input
- Autosave (2-second debounce) + manual save
- Professional print-ready resume preview
- Download as PDF via browser print dialog (`Ctrl+P` / `Cmd+P`)
- Fully responsive dark UI
- Word count on summary field

---

## Deploying Live (optional)

1. Upload all files to your hosting provider via FTP (e.g. Hostinger, InfinityFree)
2. Create a MySQL database in your hosting control panel (cPanel)
3. Import `includes/schema.sql` via phpMyAdmin on your host
4. Update `includes/db.php` with your live DB credentials
5. Visit your domain — done!
