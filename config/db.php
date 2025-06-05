<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'gaming_controllers_tz';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                                $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}

// Session configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Site configuration
define('SITE_URL', 'http://localhost/gaming-controllers-tz');
define('UPLOAD_PATH', 'assets/uploads/');

// Payment gateway configurations
define('CLICKPESA_API_KEY', 'your_clickpesa_api_key');
define('CLICKPESA_SECRET', 'your_clickpesa_secret');
define('LIPA_NAMBA_API_KEY', 'your_lipa_namba_api_key');
define('LIPA_NAMBA_SECRET', 'your_lipa_namba_secret');
?>