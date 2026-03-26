# AstonCV – CV Management Web Application

## Overview
AstonCV is a database-driven web application that allows users to register, log in, create, edit, and search CVs. The system was developed using PHP, MySQL, HTML, and CSS as part of the DG1IAD Internet Applications and Databases module.

## Features
- View all CVs
- View individual CV details
- Search CVs by name or programming language
- User registration and login system
- Edit and update CV
- Change password
- Secure authentication system

## Technologies Used
- PHP (server-side)
- MySQL (database)
- HTML5
- CSS3
- PDO (prepared statements)
- Apache (.htaccess security)

## Security Features
- Password hashing using bcrypt
- SQL injection prevention using prepared statements
- Cross-Site Scripting (XSS) protection using htmlspecialchars
- Cross-Site Request Forgery (CSRF) protection using tokens
- Session security with HttpOnly and SameSite cookies
- Access control for authenticated users only
- URL validation to prevent unsafe links
- .htaccess file to protect sensitive files

## Database
The system uses a MySQL database with a table called `cvs` which stores:
- Name
- Email
- Password (hashed)
- Key programming languages
- Profile
- Education
- URL links

## Live Website
http://250204760.cs2410-web01pvm.aston.ac.uk

## Test Account
Email: 250204760@aston.ac.uk  
Password: Test1234

## Author
Zakir Mohammed  
Aston University – BSc Computer Science
