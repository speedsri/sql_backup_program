# MySQL Database Backup System

A PHP-based web application for creating and managing MySQL database backups with a user-friendly interface.

## Features

- Web-based interface for easy database backup
- Automatic compression of backup files
- Batch processing for large databases
- Progress tracking and status updates
- Download functionality for backup files
- Support for all MySQL table types
- Foreign key handling
- UTF-8 character set support

## Requirements

- PHP 7.0 or higher
- MySQL/MariaDB server
- Apache/Nginx web server
- PHP Extensions:
  - mysqli
  - zlib (for compression)

## Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/mysql-backup-system.git
   ```

2. Set up the backup directory:
   - Create a directory for storing backups
   - Ensure the web server has write permissions to this directory
   - Default path is `C:\backups` (Windows) - modify as needed
   ```bash
   mkdir /path/to/backups
   chmod 755 /path/to/backups
   ```

3. Configure web server:
   - Place the files in your web server's document root or a subdirectory
   - Ensure PHP has permission to execute the scripts

4. Security considerations:
   - Place the backup directory outside the web root
   - Set appropriate file permissions
   - Consider adding HTTP authentication
   - Update your `php.ini` to allow for large backup files:
     ```ini
     max_execution_time = 300
     memory_limit = 512M
     post_max_size = 64M
     ```

## Usage

1. Access the application through your web browser:
   ```
   http://yourdomain.com/path/to/backup-system/
   ```

2. Enter the following details in the form:
   - Database Host (usually 'localhost')
   - Username (MySQL user with SELECT privileges)
   - Password
   - Database Name
   - Backup Directory (full path)

3. Click "Start Backup" to begin the backup process

4. Once complete, use the "Download Backup" button to retrieve your backup file

## File Structure

```
mysql-backup-system/
│
├── index.php          # Main application file
├── README.md         # This documentation
└── backups/          # Default backup directory (create this)
```

## Customization

### Changing Default Settings

Modify the following variables in the `Enhanced_Backup_Database` class:

```php
private $charset = 'utf8';      // Default character set
private $batchSize = 1000;      // Number of rows per batch
```

### Styling

The application includes a built-in CSS stylesheet. Modify the styles in the `<style>` section of `index.php` to match your preferences.

## Error Handling

The system includes comprehensive error handling:
- Database connection issues
- Backup directory permissions
- File writing errors
- Memory limitations

Common error messages and solutions:
- "ERROR connecting database": Check credentials and database server status
- "Failed to create backup directory": Check directory permissions
- "Could not write to backup file": Check disk space and permissions

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Author

Kevin Digital Developers

## Support

For support, please open an issue in the GitHub repository.
