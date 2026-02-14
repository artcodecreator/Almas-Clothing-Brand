

## 🛠 Setup Instructions

1.  **Database Setup**:
    - Import `almas_clothing.sql` into your MySQL database (e.g., via phpMyAdmin).
    - Ensure your database name matches the configuration in `includes/db.php` (Default: `almas_clothing`).

2.  **Configuration**:
    - Verify database credentials in `includes/db.php`:
      ```php
      $host = "localhost";
      $user = "root";
      $pass = "";
      $db_name = "almas_clothing";
      ```

3.  **Run the Application**:
    - Start Apache and MySQL in XAMPP.
    - Access the site at `http://localhost/ACB/` (or your specific folder path).

## 🔑 Default Credentials

**Admin Account**:
- Email: `admin@almas.com`
- Password: `Almas@2025`

**Demo User Accounts**:
- Email: `alice@almas.com` / Password: `User@2025`
- Email: `bob@almas.com` / Password: `User@2025`
- john@example.com / Almas@2025
- jane@example.com / Almas@2025
- mike@example.com / Almas@2025
- sarah@example.com / Almas@2025
- david@example.com / Almas@2025