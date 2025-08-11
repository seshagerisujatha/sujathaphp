
<?php
/**
 * Leave API Endpoints
 * Handles CRUD operations for leave requests
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

// Get leave ID from URL if present
$leave_id = isset($path_parts[2]) ? intval($path_parts[2]) : null;

switch ($method) {
    case 'GET':
        if ($leave_id) {
            getLeave($db, $leave_id);
        } else {
            getAllLeaves($db);
        }
        break;
        
    case 'POST':
        createLeave($db);
        break;
        
    case 'PUT':
        if ($leave_id) {
            updateLeave($db, $leave_id);
        } else {
            sendError("Leave ID is required for update");
        }
        break;
        
    case 'DELETE':
        if ($leave_id) {
            deleteLeave($db, $leave_id);
        } else {
            sendError("Leave ID is required for delete");
        }
        break;
        
    default:
        sendError("Method not allowed", 405);
}

/**
 * Get all leave requests with employee and leave type info
 */
function getAllLeaves($db) {
    try {
        $query = "SELECT l.*, 
                         CONCAT(e.FNAME, ' ', e.LNAME) as EMPLOYEE_NAME,
                         e.EMPLOYID,
                         lt.LEAVETYPE,
                         CONCAT(a.FNAME, ' ', a.LNAME) as APPROVED_BY_NAME
                  FROM tblleave l
                  LEFT JOIN tblemployee e ON l.EMPID = e.EMPID
                  LEFT JOIN tblleavetype lt ON l.LEAVETYPEID = lt.LEAVETYPEID
                  LEFT JOIN tblemployee a ON l.APPROVED_BY = a.EMPID
                  ORDER BY l.LEAVEID DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $leaves = $stmt->fetchAll();
        
        sendSuccess("Leave requests retrieved successfully", $leaves);
        
    } catch (PDOException $e) {
        error_log("Get leaves error: " . $e->getMessage());
        sendError("Failed to retrieve leave requests", 500);
    }
}

/**
 * Get single leave request by ID
 */
function getLeave($db, $leave_id) {
    try {
        $query = "SELECT l.*, 
                         CONCAT(e.FNAME, ' ', e.LNAME) as EMPLOYEE_NAME,
                         e.EMPLOYID,
                         lt.LEAVETYPE,
                         CONCAT(a.FNAME, ' ', a.LNAME) as APPROVED_BY_NAME
                  FROM tblleave l
                  LEFT JOIN tblemployee e ON l.EMPID = e.EMPID
                  LEFT JOIN tblleavetype lt ON l.LEAVETYPEID = lt.LEAVETYPEID
                  LEFT JOIN tblemployee a ON l.APPROVED_BY = a.EMPID
                  WHERE l.LEAVEID = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$leave_id]);
        
        $leave = $stmt->fetch();
        
        if ($leave) {
            sendSuccess("Leave request retrieved successfully", $leave);
        } else {
            sendError("Leave request not found", 404);
        }
        
    } catch (PDOException $e) {
        error_log("Get leave error: " . $e->getMessage());
        sendError("Failed to retrieve leave request", 500);
    }
}

/**
 * Create new leave request
 */
function createLeave($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required_fields = ['EMPID', 'LEAVETYPEID', 'STARTDATE', 'ENDDATE', 'REASON'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                sendError("$field is required");
            }
        }
        
        // Calculate days
        $start_date = new DateTime($input['STARTDATE']);
        $end_date = new DateTime($input['ENDDATE']);
        $interval = $start_date->diff($end_date);
        $days = $interval->days + 1;
        
        // Validate dates
        if ($start_date > $end_date) {
            sendError("Start date cannot be after end date");
        }
        
        $query = "INSERT INTO tblleave (EMPID, LEAVETYPEID, STARTDATE, ENDDATE, DAYS, REASON, STATUS) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        
        $result = $stmt->execute([
            $input['EMPID'],
            $input['LEAVETYPEID'],
            $input['STARTDATE'],
            $input['ENDDATE'],
            $days,
            $input['REASON'],
            $input['STATUS'] ?? 'Pending'
        ]);
        
        if ($result) {
            $new_id = $db->lastInsertId();
            sendSuccess("Leave request created successfully", ['LEAVEID' => $new_id], 201);
        } else {
            sendError("Failed to create leave request", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Create leave error: " . $e->getMessage());
        sendError("Failed to create leave request", 500);
    }
}

/**
 * Update leave request
 */
function updateLeave($db, $leave_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Check if leave exists
        $check_query = "SELECT LEAVEID FROM tblleave WHERE LEAVEID = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$leave_id]);
        
        if (!$check_stmt->fetch()) {
            sendError("Leave request not found", 404);
        }
        
        $fields = [];
        $values = [];
        
        $allowed_fields = ['EMPID', 'LEAVETYPEID', 'STARTDATE', 'ENDDATE', 'REASON', 'STATUS', 'APPROVED_BY', 'COMMENTS'];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $values[] = $input[$field];
            }
        }
        
        // Recalculate days if dates are updated
        if (isset($input['STARTDATE']) || isset($input['ENDDATE'])) {
            // Get current record to get missing date
            $current_query = "SELECT STARTDATE, ENDDATE FROM tblleave WHERE LEAVEID = ?";
            $current_stmt = $db->prepare($current_query);
            $current_stmt->execute([$leave_id]);
            $current = $current_stmt->fetch();
            
            $start_date = new DateTime($input['STARTDATE'] ?? $current['STARTDATE']);
            $end_date = new DateTime($input['ENDDATE'] ?? $current['ENDDATE']);
            $interval = $start_date->diff($end_date);
            $days = $interval->days + 1;
            
            $fields[] = "DAYS = ?";
            $values[] = $days;
        }
        
        // Set approved date if status is being changed to approved
        if (isset($input['STATUS']) && $input['STATUS'] === 'Approved') {
            $fields[] = "APPROVED_DATE = NOW()";
        }
        
        if (empty($fields)) {
            sendError("No valid fields to update");
        }
        
        $values[] = $leave_id;
        
        $query = "UPDATE tblleave SET " . implode(', ', $fields) . " WHERE LEAVEID = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute($values)) {
            sendSuccess("Leave request updated successfully");
        } else {
            sendError("Failed to update leave request", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Update leave error: " . $e->getMessage());
        sendError("Failed to update leave request", 500);
    }
}

/**
 * Delete leave request
 */
function deleteLeave($db, $leave_id) {
    try {
        // Check if leave exists
        $check_query = "SELECT LEAVEID FROM tblleave WHERE LEAVEID = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$leave_id]);
        
        if (!$check_stmt->fetch()) {
            sendError("Leave request not found", 404);
        }
        
        $query = "DELETE FROM tblleave WHERE LEAVEID = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$leave_id])) {
            sendSuccess("Leave request deleted successfully");
        } else {
            sendError("Failed to delete leave request", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Delete leave error: " . $e->getMessage());
        sendError("Failed to delete leave request", 500);
    }
}
?>
