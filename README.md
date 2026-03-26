# AstonCV – CV Database Website

## Project Overview
This project is a database-driven web application that allows users to create, edit, and search CV profiles for developers. Users can register an account, log in securely, update their CV information, and search for other developers by name or programming language.

This system was developed as part of the DG1IAD Portfolio 3 assignment at Aston University.

---

## Features
- User registration system
- User login and logout system
- Edit and update CV information
- View CV profiles
- Search developers by name or programming language
- Featured CV displayed on homepage
- Change password system
- Secure password hashing
- Session-based authentication
- SQL injection protection using prepared statements

---

## Technologies Used
- PHP
- MySQL
- HTML
- CSS
- XAMPP (Apache & MySQL)
- phpMyAdmin

---

## Database
The system uses a MySQL database called **astoncv** with a table called **cvs**.

The table stores:
- id
- name
- email
- password (hashed)
- key programming languages
- profile
- education
- URL links (e.g. GitHub)

The database structure is included in the file `cvs.sql`.

---

## How to Run the Project
1. Install XAMPP
2. Place the project folder into the `htdocs` folder
3. Start Apache and MySQL in XAMPP
4. Open phpMyAdmin
5. Create a database called `astoncv`
6. Import the file `cvs.sql`
7. Open the website in browser: http://localhost/astoncv/

---

## Security Features
- Passwords are hashed using bcrypt
- Prepared statements are used to prevent SQL injection
- Sessions are used for authentication
- Session ID is regenerated after login
- CSRF protection is implemented
- User input is validated and sanitised

---

## Author
Zakir Mohammed  
BSc Computer Science – Aston University  
DG1IAD Portfolio 3

GitHub Repository:  
https://github.com/zakirmvh/astoncv-website

---

## Future Improvements
- Add profile pictures
- Upload CV as PDF
- Messaging system between developers
- Advanced search filters
- Admin panel
