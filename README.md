# OneStore - PHP E-commerce Project

A simple e-commerce web application built with PHP and MySQL featuring product management, categories, and an admin panel.

## Features

- Product catalog with categories
- Admin panel for managing products and categories
- Image slider for homepage
- File upload functionality
- Responsive design
- MVC architecture

## Prerequisites

Before running this project, make sure you have:

- **PHP 7.4 or higher**
- **MySQL/MariaDB**
- **Web server (Apache/Nginx)** or local development environment like XAMPP, WAMP, or Laragon
- **Web browser**

## Installation

### 1. Clone or Download the Project

```bash
git clone <repository-url>
# or download and extract the ZIP file
```

### 2. Database Setup

1. **Create Database:**
   - Open phpMyAdmin or your MySQL client
   - Create a new database named `onestore_db`

2. **Import Database:**
   - Import the `data.sql` file into the `onestore_db` database
   - This will create all necessary tables and insert sample data

3. **Create Admin Table:**
   - The admin table is referenced but not created in data.sql
   - Run the following SQL to create it:

```sql
CREATE TABLE `tbl_admin` (
  `adminID` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gmail` varchar(255) DEFAULT NULL,
  `img` longblob DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`adminID`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default admin user (password: admin123)
INSERT INTO `tbl_admin` (`username`, `password`, `gmail`) VALUES 
('admin', 'admin123', 'admin@onestore.com');
```

### 3. Configuration

1. **Database Configuration:**
   - Open `database.php`
   - Update database credentials if needed:
     - Host: `localhost` (default)
     - Database: `onestore_db`
     - Username: `root` (default)
     - Password: `` (empty by default)

### 4. File Permissions

Ensure the following directories have write permissions:
- `uploads/images/`
- `uploads/slider/`

### 5. Web Server Setup

#### Using Laragon (Recommended for Windows):
1. Place the project folder in `C:\laragon\www\`
2. Start Laragon
3. Access: `http://localhost/php-test` or `http://php-test.test`

#### Using XAMPP:
1. Place the project folder in `htdocs`
2. Start Apache and MySQL
3. Access: `http://localhost/php-test`

#### Using Local PHP Server:
```bash
cd /path/to/php-test
php -S localhost:8000
```

## Usage

### Frontend
- **Homepage:** `http://localhost/php-test` or `http://localhost:8000`
- **Shop:** `?page=shop`
- **Product Details:** `?page=productdetail&id=1`
- **About:** `?page=about`
- **Contact:** `?page=contact`

### Admin Panel
- **Access:** `http://localhost/php-test/admin`
- **Login:** Use the admin credentials created in the database setup
- **Features:**
  - Product management (CRUD)
  - Category management
  - Admin profile management

## File Structure

```
php-test/
├── admin/              # Admin panel
│   ├── auth/          # Admin authentication
│   ├── controllers/   # Admin controllers
│   ├── includes/      # Admin includes
│   └── view/          # Admin views
├── assets/
│   └── js/           # JavaScript files
├── controllers/       # Main controllers
├── includes/         # Common includes
├── models/           # Database models
├── uploads/          # File uploads
│   ├── images/       # Product images
│   └── slider/       # Slider images
├── views/            # Frontend views
├── data.sql          # Database schema and data
├── database.php      # Database connection
├── function.php      # Utility functions
├── index.php         # Main entry point
└── router.php        # URL routing
```

## Default Credentials

- **Admin Username:** admin
- **Admin Password:** admin123

## Development

### Adding New Pages
1. Create a controller in `controllers/`
2. Create a view in `views/`
3. The router will automatically detect new pages

### Database Operations
- Use the stored procedures defined in `data.sql`
- Database connection is handled in `database.php`

## Troubleshooting

### Common Issues:

1. **Database Connection Error:**
   - Check MySQL service is running
   - Verify database credentials in `database.php`
   - Ensure `onestore_db` database exists

2. **File Upload Issues:**
   - Check write permissions on `uploads/` directories
   - Verify PHP upload settings in `php.ini`

3. **404 Errors:**
   - Ensure web server is configured correctly
   - Check `.htaccess` rules if using Apache

4. **Admin Login Issues:**
   - Verify admin table exists and has data
   - Check admin credentials

## Support

For issues and questions, please check the troubleshooting section or create an issue in the project repository. 