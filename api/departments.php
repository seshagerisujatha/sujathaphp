
<?php
/**
 * Department API Endpoints
 * Handles CRUD operations for departments
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

// Get department ID from URL if present
$department_id = isset($path_parts[2]) ? intval($path_parts[2]) : null;

switch ($method) {
    case 'GET':
        if ($department_id) {
            getDepartment($db, $department_id);
        } else {
            getAllDepartments($db);
        }
        break;
        
    case 'POST':
        createDepartment($db);
        break;
        
    case 'PUT':
        if ($department_id) {
            updateDepartment($db, $department_id);
        } else {
            sendError("Department ID is required for update");
        }
        break;
        
    case 'DELETE':
        if ($department_id) {
            deleteDepartment($db, $department_id);
        } else {
            sendError("Department ID is required for delete");
        }
        break;
        
    default:
        sendError("Method not allowed", 405);
}

/**
 * Get all departments with company info
 */
function getAllDepartments($db) {
    try {
        $query = "SELECT d.*, c.COMPANYNAME 
                  FROM tbldepartment d 
                  LEFT JOIN tblcompany c ON d.COMPANYID = c.COMPANYID 
                  ORDER BY d.DEPARTMENTID DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $departments = $stmt->fetchAll();
        
        sendSuccess("Departments retrieved successfully", $departments);
        
    } catch (PDOException $e) {
        error_log("Get departments error: " . $e->getMessage());
        sendError("Failed to retrieve departments", 500);
    }
}

/**
 * Get single department by ID
 */
function getDepartment($db, $department_id) {
    try {
        $query = "SELECT d.*, c.COMPANYNAME 
                  FROM tbldepartment d 
                  LEFT JOIN tblcompany c ON d.COMPANYID = c.COMPANYID 
                  WHERE d.DEPARTMENTID = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$department_id]);
        
        $department = $stmt->fetch();
        
        if ($department) {
            sendSuccess("Department retrieved successfully", $department);
        } else {
            sendError("Department not found", 404);
        }
        
    } catch (PDOException $e) {
        error_log("Get department error: " . $e->getMessage());
        sendError("Failed to retrieve department", 500);
    }
}

/**
 * Create new department
 */
function createDepartment($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required_fields = ['DEPARTMENT', 'COMPANYID'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                sendError("$field is required");
            }
        }
        
        $query = "INSERT INTO tbldepartment (DEPARTMENT, COMPANYID) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        
        $result = $stmt->execute([
            $input['DEPARTMENT'],
            $input['COMPANYID']
        ]);
        
        if ($result) {
            $new_id = $db->lastInsertId();
            sendSuccess("Department created successfully", ['DEPARTMENTID' => $new_id], 201);
        } else {
            sendError("Failed to create department", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Create department error: " . $e->getMessage());
        sendError("Failed to create department", 500);
    }
}

/**
 * Update department
 */
function updateDepartment($db, $department_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Check if department exists
        $check_query = "SELECT DEPARTMENTID FROM tbldepartment WHERE DEPARTMENTID = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$department_id]);
        
        if (!$check_stmt->fetch()) {
            sendError("Department not found", 404);
        }
        
        $fields = [];
        $values = [];
        
        $allowed_fields = ['DEPARTMENT', 'COMPANYID'];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $values[] = $input[$field];
            }
        }
        
        if (empty($fields)) {
            sendError("No valid fields to update");
        }
        
        $values[] = $department_id;
        
        $query = "UPDATE tbldepartment SET " . implode(', ', $fields) . " WHERE DEPARTMENTID = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute($values)) {
            sendSuccess("Department updated successfully");
        } else {
            sendError("Failed to update department", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Update department error: " . $e->getMessage());
        sendError("Failed to update department", 500);
    }
}

/**
 * Delete department
 */
function deleteDepartment($db, $department_id) {
    try {
        // Check if department exists
        $check_query = "SELECT DEPARTMENTID FROM tbldepartment WHERE DEPARTMENTID = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$department_id]);
        
        if (!$check_stmt->fetch()) {
            sendError("Department not found", 404);
        }
        
        // Check if department has employees
        $employee_check = "SELECT COUNT(*) FROM tblemployee WHERE DEPARTMENTID = ?";
        $employee_stmt = $db->prepare($employee_check);
        $employee_stmt->execute([$department_id]);
        
        if ($employee_stmt->fetchColumn() > 0) {
            sendError("Cannot delete department with existing employees", 400);
        }
        
        $query = "DELETE FROM tbldepartment WHERE DEPARTMENTID = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$department_id])) {
            sendSuccess("Department deleted successfully");
        } else {
            sendError("Failed to delete department", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Delete department error: " . $e->getMessage());
        sendError("Failed to delete department", 500);
    }
}
?>
