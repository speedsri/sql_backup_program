# Installation Guide - Job Management System

This guide provides detailed steps to install and configure the Job Management System on your server.

## System Requirements

### Minimum Requirements
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Apache 2.4+ or Nginx 1.18+
- 512MB RAM (minimum)
- 1GB free disk space

### Recommended PHP Extensions
- PDO
- PDO_MYSQL
- mysqli
- json
- session
- xml
- mbstring
- zip (for Excel export functionality)

## Step-by-Step Installation

### 1. Server Preparation

#### For Apache
```bash
# Install required packages on Ubuntu/Debian
sudo apt update
sudo apt install apache2 php mysql-server php-mysql php-mbstring php-xml php-zip

# Enable required PHP extensions
sudo phpenmod pdo_mysql
sudo phpenmod mbstring
sudo phpenmod xml
sudo phpenmod zip

# Restart Apache
sudo service apache2 restart
```

#### For Nginx
```bash
# Install required packages
sudo apt update
sudo apt install nginx php-fpm mysql-server php-mysql php-mbstring php-xml php-zip

# Enable PHP-FPM
sudo systemctl enable php-fpm
sudo systemctl start php-fpm
```

### 2. Database Setup

```sql
# Login to MySQL
mysql -u root -p

# Create database and user
CREATE DATABASE job_management_db;
CREATE USER 'job_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON job_management_db.* TO 'job_user'@'localhost';
FLUSH PRIVILEGES;

# Create required tables
CREATE TABLE jayantha_1500_table (
    sr_no INT AUTO_INCREMENT PRIMARY KEY,
    Year INT,
    Month VARCHAR(20),
    DTJobNumber VARCHAR(50),
    HOJobNumber VARCHAR(50),
    Client VARCHAR(100),
    DateOpened DATE,
    DescriptionOfWork TEXT,
    TargetDate DATE,
    CompletionDate DATE,
    DeliveredDate DATE,
    FileClosed TINYINT(1),
    LabourHours DECIMAL(10,2),
    MaterialCost DECIMAL(10,2),
    TypeOfWork VARCHAR(50),
    Remarks TEXT
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 3. Application Installation

```bash
# Navigate to web root directory
cd /var/www/html

# Clone the repository (if using Git)
git clone https://github.com/yourusername/job-management-system.git
cd job-management-system

# Set proper permissions
sudo chown -R www-data:www-data /var/www/html/job-management-system
sudo chmod -R 755 /var/www/html/job-management-system
```

### 4. Configuration Setup

Create a database configuration file `db_conn.php`:

```php
<?php
$host = 'localhost';
$dbname = 'job_management_db';
$username = 'job_user';
$password = 'your_secure_password';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

### 5. Web Server Configuration

#### Apache Configuration (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]

# PHP settings
php_value upload_max_filesize 10M
php_value post_max_size 10M
php_value max_execution_time 300
php_value max_input_time 300
```

#### Nginx Configuration
Create a new site configuration in `/etc/nginx/sites-available/job-management`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/job-management-system;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 6. Required Dependencies

Install JavaScript dependencies:

```bash
# Copy required CSS and JS files
cp css/tailwind.min.css /var/www/html/job-management-system/css/
cp css/select2.min.css /var/www/html/job-management-system/css/
cp css/jquery-3.6.0.min.js /var/www/html/job-management-system/css/
cp css/select2.min.js /var/www/html/job-management-system/css/
```

### 7. Create Admin User

Create an initial admin user by running this SQL:

```sql
INSERT INTO users (username, password) 
VALUES ('admin', '$2y$10$YOUR_HASHED_PASSWORD');
```

### 8. Final Steps

1. **Test the Installation**
   - Navigate to `http://your-domain/index.php`
   - Try logging in with the admin credentials
   - Test the job management features

2. **Security Checklist**
   - Update all default passwords
   - Secure the config files
   - Set proper file permissions
   - Enable H