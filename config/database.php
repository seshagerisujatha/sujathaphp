
<?php
/**
 * Database Configuration
 * Secure database connection with error handling
 */

class Database {
    private $host = "localhost";
    private $database_name = "leavedb";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database_name,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            return null;
        }
        
        return $this->conn;
    }
}

/**
 * CORS Configuration
 * Enable Cross-Origin Resource Sharing for Angular frontend
 */
function enableCORS() {
    // Allow requests from Angular development server
    $allowed_origins = [
        'http://localhost:4200',
        'http://127.0.0.1:4200',
        'http://localhost:3000'
    ];
    
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    
    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

/**
 * Standard API Response Format
 */
function sendResponse($status, $message, $data = null, $http_code = 200) {
    http_response_code($http_code);
    header('Content-Type: application/json');
    
    $response = [
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('c')
    ];
    
    echo json_encode($response);
    exit;
}

/**
 * Error Handler
 */
function sendError($message, $http_code = 400, $data = null) {
    sendResponse('error', $message, $data, $http_code);
}

/**
 * Success Handler
 */
function sendSuccess($message, $data = null, $http_code = 200) {
    sendResponse('success', $message, $data, $http_code);
}

// Enable CORS for all API requests
enableCORS();
?>
