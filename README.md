# Event Management System (Core PHP + MySQL)

A professional, web-based platform built with **PHP 8 + MySQL (PDO)** designed for university event coordination. This system provides a clean, framework-free architecture that manages the entire lifecycle of an event from creation to student enrollment.



## Requirements
* XAMPP (Apache + MySQL) or any PHP 8.0+ environment.
* PHP Extensions: `PDO`, `pdo_mysql`.

## Setup (XAMPP)
1. **Move Project**: Copy the `Event_Management-_system` folder to your `C:\xampp\htdocs\` directory.
2. **Start Services**: Open the XAMPP Control Panel and start **Apache** and **MySQL**.
3. **Database Setup**:
   - Open **phpMyAdmin**: `http://localhost/phpmyadmin`
   - Create a new database named: `event_management_db`
   - Import the SQL schema from: `/setup/database.sql`
4. **Run Project**: Access the site at `http://localhost/Event_Management-_system/`

## Default Credentials
* **Administrator**
    * **Email**: `admin@university.edu`
    * **Password**: `Admin@123`
* **Student User**
    * **Email**: `student1@university.edu`
    * **Password**: `Student@123`

## Features
### Student Portal (`/students`)
* **Event Discovery**: Browse through upcoming campus events with categorized views.
* **One-Click Enrollment**: Register for events instantly with session-tracked user data.
* **Personal Dashboard**: View joined events and check registration statuses.
* **Secure Authentication**: Registration and login system using industry-standard `password_hash`.

### Admin Dashboard (`/admin`)
* **Live Analytics**: Real-time overview of total events and student participation.
* **Full Event CRUD**: Complete control to Create, Read, Update, and Delete event listings.
* **Attendee Tracking**: View lists of students registered for specific events.
* **System Control**: Instantly toggle event visibility or archive past events.

## Security & Architecture
* **PDO Prepared Statements**: Secure database interactions to prevent SQL Injection.
* **Role-Based Access (RBAC)**: Strict server-side separation between Student and Admin sessions.
* **Clean File Structure**: Logic is separated into `/auth`, `/config`, and `/includes` for easy maintenance.
* **Input Sanitization**: Built-in protection against Cross-Site Scripting (XSS).

## Known Issues / Notes
* This project is built for educational portfolios and university environments.
* Ensure your database credentials in `/config/` match your local server settings.

---
**Developed by Niroj**
