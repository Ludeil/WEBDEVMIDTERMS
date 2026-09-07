OBEDA DORMITORIES LOGIN SETUP

Files:
- login.php              Login page
- login_process.php      Verifies credentials and starts the session
- logout.php             Destroys the login session
- signup.php             Creates a dormitory account using password_hash()
- database/config.php    PDO connection for obeda_dormitory
- database/dormitory.sql Dormitory database and tables
- assets/css/login.css   Login/signup styling matching the charcoal + white site

DATABASE SETUP
1. Start Apache and MySQL/MariaDB in XAMPP.
2. Open phpMyAdmin.
3. Import database/dormitory.sql.
4. Make sure database/config.php matches your local MySQL settings.
5. Put the PHP files inside your XAMPP htdocs project folder and keep the assets/images folder from the existing website.
6. Open login.php in the browser.

ACCOUNT FLOW
- signup.php inserts accounts into users.
- Passwords are stored as password hashes, not plain text.
- login.php accepts either the email address or username.
- login_process.php verifies the hash with password_verify().
- A successful login stores the user's ID/name/role in $_SESSION.
- logout.php ends the session.

IMPORTANT
The existing student-registration files from the reference use a school_db database and a user table. They are intentionally not used by this version.
The new application uses obeda_dormitory and separates users, dormitories, rooms, viewing_requests, and room_applications.
