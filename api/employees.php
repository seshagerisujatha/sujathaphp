
<?php
/**
 * Employee API Endpoints
 * Handles CRUD operations for employees
 */

require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    sendError("Database connection failed", 500);
}

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path_parts = explode('/', trim(parse_url($request_uri, PHP_URL_PATH), '/'));

// Get employee ID from URL if present
$employee_id = isset($path_parts[2]) ? intval($path_parts[2]) : null;

switch ($method) {
    case 'GET':
        if ($employee_id) {
            getEmployee($db, $employee_id);
        } else {
            getAllEmployees($db);
        }
        break;
        
    case 'POST':
        createEmployee($db);
        break;
        
    case 'PUT':
        if ($employee_id) {
            updateEmployee($db, $employee_id);
        } else {
            sendError("Employee ID is required for update");
        }
        break;
        
    case 'DELETE':
        if ($employee_id) {
            deleteEmployee($db, $employee_id);
        } else {
            sendError("Employee ID is required for delete");
        }
        break;
        
    default:
        sendError("Method not allowed", 405);
}

/**
 * Get all employees with company and department info
 */
function getAllEmployees($db) {
    try {
        $query = "SELECT e.*, c.COMPANYNAME, d.DEPARTMENT 
                  FROM tblemployee e 
                  LEFT JOIN tblcompany c ON e.COMPANYID = c.COMPANYID 
                  LEFT JOIN tbldepartment d ON e.DEPARTMENTID = d.DEPARTMENTID 
                  ORDER BY e.EMPID DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $employees = $stmt->fetchAll();
        
        // Remove password from response
        foreach ($employees as &$employee) {
            unset($employee['PASS']);
        }
        
        sendSuccess("Employees retrieved successfully", $employees);
        
    } catch (PDOException $e) {
        error_log("Get employees error: " . $e->getMessage());
        sendError("Failed to retrieve employees", 500);
    }
}

/**
 * Get single employee by ID
 */
function getEmployee($db, $employee_id) {
    try {
        $query = "SELECT e.*, c.COMPANYNAME, d.DEPARTMENT 
                  FROM tblemployee e 
                  LEFT JOIN tblcompany c ON e.COMPANYID = c.COMPANYID 
                  LEFT JOIN tbldepartment d ON e.DEPARTMENTID = d.DEPARTMENTID 
                  WHERE e.EMPID = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$employee_id]);
        
        $employee = $stmt->fetch();
        
        if ($employee) {
            unset($employee['PASS']);
            sendSuccess("Employee retrieved successfully", $employee);
        } else {
            sendError("Employee not found", 404);
        }
        
    } catch (PDOException $e) {
        error_log("Get employee error: " . $e->getMessage());
        sendError("Failed to retrieve employee", 500);
    }
}

/**
 * Create new employee
 */
function createEmployee($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required_fields = ['EMPLOYID', 'FNAME', 'LNAME', 'EMAIL', 'COMPANYID', 'DEPARTMENTID', 'USERNAME'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                sendError("$field is required");
            }
        }
        
        // Check if employee ID, email, or username already exists
        $check_query = "SELECT COUNT(*) FROM tblemployee WHERE EMPLOYID = ? OR EMAIL = ? OR USERNAME = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$input['EMPLOYID'], $input['EMAIL'], $input['USERNAME']]);
        
        if ($check_stmt->fetchColumn() > 0) {
            sendError("Employee ID, email, or username already exists");
        }
        
        $query = "INSERT INTO tblemployee (EMPLOYID, FNAME, LNAME, MNAME, ADDRESS, EMAIL, PHONE, 
                  COMPANYID, DEPARTMENTID, POSITION, DATEHIRED, USERNAME, PASS, TYPE, STATUS) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        
        // Hash password
        $password = !empty($input['PASS']) ? password_hash($input['PASS'], PASSWORD_DEFAULT) : password_hash('password123', PASSWORD_DEFAULT);
        
        $result = $stmt->execute([
            $input['EMPLOYID'],
            $input['FNAME'],
            $input['LNAME'],
            $input['MNAME'] ?? '',
            $input['ADDRESS'] ?? '',
            $input['EMAIL'],
            $input['PHONE'] ?? '',
            $input['COMPANYID'],
            $input['DEPARTMENTID'],
            $input['POSITION'] ?? '',
            $input['DATEHIRED'] ?? null,
            $input['USERNAME'],
            $password,
            $input['TYPE'] ?? 'Employee',
            $input['STATUS'] ?? 'Active'
        ]);
        
        if ($result) {
            $new_id = $db->lastInsertId();
            sendSuccess("Employee created successfully", ['EMPID' => $new_id], 201);
        } else {
            sendError("Failed to create employee", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Create employee error: " . $e->getMessage());
        sendError("Failed to create employee", 500);
    }
}

/**
 * Update employee
 */
function updateEmployee($db, $employee_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Check if employee exists
        $check_query = "SELECT EMPID FROM tblemployee WHERE EMPID = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$employee_id]);
        
        if (!$check_stmt->fetch()) {
            sendError("Employee not found", 404);
        }
        
        $fields = [];
        $values = [];
        
        $allowed_fields = ['EMPLOYID', 'FNAME', 'LNAME', 'MNAME', 'ADDRESS', 'EMAIL', 'PHONE', 
                          'COMPANYID', 'DEPARTMENTID', 'POSITION', 'DATEHIRED', 'USERNAME', 'TYPE', 'STATUS'];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $values[] = $input[$field];
            }
        }
        
        // Handle password update separately
        if (!empty($input['PASS'])) {
            $fields[] = "PASS = ?";
            $values[] = password_hash($input['PASS'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            sendError("No valid fields to update");
        }
        
        $values[] = $employee_id;
        
        $query = "UPDATE tblemployee SET " . implode(', ', $fields) . " WHERE EMPID = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute($values)) {
            sendSuccess("Employee updated successfully");
        } else {
            sendError("Failed to update employee", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Update employee error: " . $e->getMessage());
        sendError("Failed to update employee", 500);
    }
}

/**
 * Delete employee
 */
function deleteEmployee($db, $employee_id) {
    try {
        // Check if employee exists
        $check_query = "SELECT EMPID FROM tblemployee WHERE EMPID = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$employee_id]);
        
        if (!$check_stmt->fetch()) {
            sendError("Employee not found", 404);
        }
        
        // Check if employee has leave records
        $leave_check = "SELECT COUNT(*) FROM tblleave WHERE EMPID = ?";
        $leave_stmt = $db->prepare($leave_check);
        $leave_stmt->execute([$employee_id]);
        
        if ($leave_stmt->fetchColumn() > 0) {
            // Soft delete - update status instead of deleting
            $query = "UPDATE tblemployee SET STATUS = 'Inactive' WHERE EMPID = ?";
        } else {
            // Hard delete if no leave records
            $query = "DELETE FROM tblemployee WHERE EMPID = ?";
        }
        
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$employee_id])) {
            sendSuccess("Employee deleted successfully");
        } else {
            sendError("Failed to delete employee", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Delete employee error: " . $e->getMessage());
        sendError("Failed to delete employee", 500);
    }
}
?>
