<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

class Enhanced_Backup_Database {
    private $host;
    private $username;
    private $passwd;
    private $dbName;
    private $charset;
    private $conn;
    private $backupDir;
    private $backupFile;
    private $gzipBackupFile;
    private $output;
    private $batchSize;

    public function __construct($host, $username, $passwd, $dbName, $charset = 'utf8') {
        $this->host = $host;
        $this->username = $username;
        $this->passwd = $passwd;
        $this->dbName = $dbName;
        $this->charset = $charset;
        $this->conn = $this->initializeDatabase();
        $this->batchSize = 1000;
        $this->output = '';
    }

    protected function initializeDatabase() {
        try {
            $conn = mysqli_connect($this->host, $this->username, $this->passwd, $this->dbName);
            if (mysqli_connect_errno()) {
                throw new Exception('ERROR connecting database: ' . mysqli_connect_error());
            }
            if (!mysqli_set_charset($conn, $this->charset)) {
                mysqli_query($conn, 'SET NAMES '.$this->charset);
            }
            return $conn;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function setBackupDirectory($dir) {
        $this->backupDir = $dir;
        if (!file_exists($this->backupDir)) {
            if (!mkdir($this->backupDir, 0777, true)) {
                throw new Exception("Failed to create backup directory");
            }
        }
        if (!is_writable($this->backupDir)) {
            throw new Exception("Backup directory is not writable");
        }
    }

    public function backupDatabase($compress = true) {
        try {
            $this->backupFile = 'backup-'.$this->dbName.'-'.date("Y-m-d-H-i-s").'.sql';
            $this->gzipBackupFile = $compress;

            $tables = [];
            $result = mysqli_query($this->conn, 'SHOW TABLES');
            while($row = mysqli_fetch_row($result)) {
                $tables[] = $row[0];
            }

            $sql = "SET foreign_key_checks = 0;\n\n";
            
            foreach($tables as $table) {
                $this->addOutput("Backing up table: " . $table);
                
                // Get create table statement
                $row = mysqli_fetch_row(mysqli_query($this->conn, 'SHOW CREATE TABLE `'.$table.'`'));
                $sql .= "DROP TABLE IF EXISTS `$table`;\n" . $row[1] . ";\n\n";
                
                // Get table data
                $row = mysqli_fetch_row(mysqli_query($this->conn, "SELECT COUNT(*) FROM `$table`"));
                $numRows = $row[0];
                
                if ($numRows > 0) {
                    $sql .= $this->getTableData($table, $numRows);
                }
            }

            $sql .= "SET foreign_key_checks = 1;";
            
            $this->saveBackup($sql);
            
            if ($this->gzipBackupFile) {
                $this->gzipBackup();
            }
            
            return true;
            
        } catch (Exception $e) {
            throw new Exception("Backup failed: " . $e->getMessage());
        }
    }

    protected function getTableData($table, $numRows) {
        $sql = '';
        $numBatches = ceil($numRows / $this->batchSize);
        
        for ($b = 0; $b < $numBatches; $b++) {
            $query = "SELECT * FROM `$table` LIMIT " . ($b * $this->batchSize) . "," . $this->batchSize;
            $result = mysqli_query($this->conn, $query);
            
            if (mysqli_num_rows($result) > 0) {
                $sql .= "INSERT INTO `$table` VALUES ";
                $first = true;
                
                while ($row = mysqli_fetch_row($result)) {
                    $sql .= $first ? '' : ",\n";
                    $sql .= "(";
                    foreach ($row as $value) {
                        if (is_null($value)) {
                            $sql .= "NULL,";
                        } else {
                            $value = mysqli_real_escape_string($this->conn, (string)$value);
                            $sql .= "'$value',";
                        }
                    }
                    $sql = rtrim($sql, ',') . ")";
                    $first = false;
                }
                $sql .= ";\n\n";
            }
        }
        return $sql;
    }

    protected function saveBackup($sql) {
        if (!file_put_contents($this->backupDir . '/' . $this->backupFile, $sql)) {
            throw new Exception("Could not write to backup file");
        }
    }

    protected function gzipBackup() {
        $source = $this->backupDir . '/' . $this->backupFile;
        $dest = $source . '.gz';
        
        $this->addOutput("Compressing backup file...");
        
        $mode = 'wb9';
        if ($fpOut = gzopen($dest, $mode)) {
            if ($fpIn = fopen($source, 'rb')) {
                while (!feof($fpIn)) {
                    gzwrite($fpOut, fread($fpIn, 1024 * 256));
                }
                fclose($fpIn);
                unlink($source);
            }
            gzclose($fpOut);
        }
        
        $this->addOutput("Compression complete");
    }

    protected function addOutput($message) {
        $this->output .= date('Y-m-d H:i:s') . ' - ' . $message . "\n";
    }

    public function getOutput() {
        return $this->output;
    }

    public function getBackupFileName() {
        return $this->gzipBackupFile ? $this->backupFile . '.gz' : $this->backupFile;
    }
}

// Handle download request
if (isset($_GET['download']) && $_GET['download'] == 'true' && isset($_GET['file'])) {
    $file = $_GET['file'];
    $filepath = 'C:/backups/' . basename($file);
    
    if (file_exists($filepath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySQL Database Backup</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px; 
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2196F3;
            text-align: center;
            margin-bottom: 30px;
        }
        .status { 
            padding: 15px; 
            margin: 15px 0; 
            border-radius: 4px; 
        }
        .success { 
            background-color: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .error { 
            background-color: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }
        .form-group { 
            margin-bottom: 20px; 
        }
        .form-group label { 
            display: block; 
            margin-bottom: 5px;
            color: #333;
            font-weight: bold;
        }
        .form-group input { 
            width: 100%; 
            padding: 8px;
            border: 1px solid #ddd; 
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background: #2196F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #1976D2;
        }
        .download-btn {
            background: #4CAF50;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 10px;
        }
        .download-btn:hover {
            background: #45a049;
        }
        .output {
            font-family: monospace;
            white-space: pre-wrap;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
        }
        footer {
            text-align: center;
            margin-top: 30px;
            color: #666;
        }
    </style>
</head>
<body>
    <nav class="bg-green shadow-md">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="instructions.html" target="_blank" class="text-blue-800 hover:text-blue-300 font-bold">Instructions to Use</a>
        </div>
    </nav>

    <div class="container">
        <h1>MySQL Database Backup</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $backup = new Enhanced_Backup_Database(
                    $_POST['host'],
                    $_POST['username'],
                    $_POST['password'],
                    $_POST['database']
                );
                
                $backup->setBackupDirectory($_POST['backup_dir']);
                $compress = true;  // Always compress for better download size
                
                if ($backup->backupDatabase($compress)) {
                    $fileName = $backup->getBackupFileName();
                    echo '<div class="status success">Backup completed successfully!</div>';
                    echo '<div class="output">' . nl2br($backup->getOutput()) . '</div>';
                    echo '<a href="?download=true&file=' . urlencode($fileName) . '" class="download-btn">Download Backup</a>';
                }
                
            } catch (Exception $e) {
                echo '<div class="status error">Error: ' . $e->getMessage() . '</div>';
            }
        }
        ?>

        <form method="post">
            <div class="form-group">
                <label for="host">Database Host:</label>
                <input type="text" id="host" name="host" value="localhost" required>
            </div>
            
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password">
            </div>
            
            <div class="form-group">
                <label for="database">Database Name:</label>
                <input type="text" id="database" name="database" required>
            </div>
            
            <div class="form-group">
                <label for="backup_dir">Backup Directory:</label>
                <input type="text" id="backup_dir" name="backup_dir" value="C:\backups" required>
            </div>
            
            <button type="submit" class="btn">Start Backup</button>
        </form>
    </div>
    <footer>
        <p>&copy; Kevin Digital Developers</p>
    </footer>
</body>
</html>