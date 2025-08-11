
<?php
/**
 * Leave Types API Endpoints
 * Handles CRUD operations for leave types
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

// Get leave type ID from URL if present
$leave_type_id = isset($path_parts[2]) ? intval($path_parts[2]) : null;

switch ($method) {
    case 'GET':
        if ($leave_type_id) {
            getLeaveType($db, $leave_type_id);
        } else {
            getAllLeaveTypes($db);
        }
        break;
        
    case 'POST':
        createLeaveType($db);
        break;
        
    case 'PUT':
        if ($leave_type_id) {
            updateLeaveType($db, $leave_type_id);
        } else {
            sendError("Leave type ID is required for update");
        }
        break;
        
    case 'DELETE':
        if ($leave_type_id) {
            deleteLeaveType($db, $leave_type_id);
        } else {
            sendError("Leave type ID is required for delete");
        }
        break;
        
    default:
        sendError("Method not allowed", 405);
}

/**
 * Get all leave types
 */
function getAllLeaveTypes($db) {
    try {
        $query = "SELECT * FROM tblleavetype ORDER BY LEAVETYPEID DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $leave_types = $stmt->fetchAll();
        
        sendSuccess("Leave types retrieved successfully", $leave_types);
        
    } catch (PDOException $e) {
        error_log("Get leave types error: " . $e->getMessage());
        sendError("Failed to retrieve leave types", 500);
    }
}

/**
 * Get single leave type by ID
 */
function getLeaveType($db, $leave_type_id) {
    try {
        $query = "SELECT * FROM tblleavetype WHERE LEAVETYPEID = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$leave_type_id]);
        
        $leave_type = $stmt->fetch();
        
        if ($leave_type) {
            sendSuccess("Leave type retrieved successfully", $leave_type);
        } else {
            sendError("Leave type not found", 404);
        }
        
    } catch (PDOException $e) {
        error_log("Get leave type error: " . $e->getMessage());
        sendError("Failed to retrieve leave type", 500);
    }
}

/**
 * Create new leave type
 */
function createLeaveType($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required_fields = ['LEAVETYPE', 'LEAVEDAYS'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                sendError("$field is required");
            }
        }
        
        $query = "INSERT INTO tblleavetype (LEAVETYPE, LEAVEDAYS, DESCRIPTION) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        
        $result = $stmt->execute([
            $input['LEAVETYPE'],
            $input['LEAVEDAYS'],
            $input['DESCRIPTION'] ?? ''
        ]);
        
        if ($result) {
            $new_id = $db->lastInsertId();
            sendSuccess("Leave type created successfully", ['LEAVETYPEID' => $new_id], 201);
        } else {
            sendError("Failed to create leave type", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Create leave type error: " . $e->getMessage());
        sendError("Failed to create leave type", 500);
    }
}

/**
 * Update leave type
 */
function updateLeaveType($db, $leave_type_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Check if leave type exists
        $check_query = "SELECT LEAVETYPEID FROM tblleavetype WHERE LEAVETYPEID = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$leave_type_id]);
        
        if (!$check_stmt->fetch()) {
            sendError("Leave type not found", 404);
        }
        
        $fields = [];
        $values = [];
        
        $allowed_fields = ['LEAVETYPE', 'LEAVEDAYS', 'DESCRIPTION'];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $values[] = $input[$field];
            }
        }
        
        if (empty($fields)) {
            sendError("No valid fields to update");
        }
        
        $values[] = $leave_type_id;
        
        $query = "UPDATE tblleavetype SET " . implode(', ', $fields) . " WHERE LEAVETYPEID = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute($values)) {
            sendSuccess("Leave type updated successfully");
        } else {
            sendError("Failed to update leave type", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Update leave type error: " . $e->getMessage());
        sendError("Failed to update leave type", 500);
    }
}

/**
 * Delete leave type
 */
function deleteLeaveType($db, $leave_type_id) {
    try {
        // Check if leave type exists
        $check_query = "SELECT LEAVETYPEID FROM tblleavetype WHERE LEAVETYPEID = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$leave_type_id]);
        
        if (!$check_stmt->fetch()) {
            sendError("Leave type not found", 404);
        }
        
        // Check if leave type is used in leave requests
        $leave_check = "SELECT COUNT(*) FROM tblleave WHERE LEAVETYPEID = ?";
        $leave_stmt = $db->prepare($leave_check);
        $leave_stmt->execute([$leave_type_id]);
        
        if ($leave_stmt->fetchColumn() > 0) {
            sendError("Cannot delete leave type with existing leave requests", 400);
        }
        
        $query = "DELETE FROM tblleavetype WHERE LEAVETYPEID = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$leave_type_id])) {
            sendSuccess("Leave type deleted successfully");
        } else {
            sendError("Failed to delete leave type", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Delete leave type error: " . $e->getMessage());
        sendError("Failed to delete leave type", 500);
    }
}
?>
