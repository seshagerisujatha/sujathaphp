
<?php
/**
 * Company API Endpoints
 * Handles CRUD operations for companies
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

// Get company ID from URL if present
$company_id = isset($path_parts[2]) ? intval($path_parts[2]) : null;

switch ($method) {
    case 'GET':
        if ($company_id) {
            getCompany($db, $company_id);
        } else {
            getAllCompanies($db);
        }
        break;
        
    case 'POST':
        createCompany($db);
        break;
        
    case 'PUT':
        if ($company_id) {
            updateCompany($db, $company_id);
        } else {
            sendError("Company ID is required for update");
        }
        break;
        
    case 'DELETE':
        if ($company_id) {
            deleteCompany($db, $company_id);
        } else {
            sendError("Company ID is required for delete");
        }
        break;
        
    default:
        sendError("Method not allowed", 405);
}

/**
 * Get all companies
 */
function getAllCompanies($db) {
    try {
        $query = "SELECT * FROM tblcompany ORDER BY COMPANYID DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        $companies = $stmt->fetchAll();
        
        sendSuccess("Companies retrieved successfully", $companies);
        
    } catch (PDOException $e) {
        error_log("Get companies error: " . $e->getMessage());
        sendError("Failed to retrieve companies", 500);
    }
}

/**
 * Get single company by ID
 */
function getCompany($db, $company_id) {
    try {
        $query = "SELECT * FROM tblcompany WHERE COMPANYID = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$company_id]);
        
        $company = $stmt->fetch();
        
        if ($company) {
            sendSuccess("Company retrieved successfully", $company);
        } else {
            sendError("Company not found", 404);
        }
        
    } catch (PDOException $e) {
        error_log("Get company error: " . $e->getMessage());
        sendError("Failed to retrieve company", 500);
    }
}

/**
 * Create new company
 */
function createCompany($db) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validate required fields
        $required_fields = ['COMPANYNAME', 'COMPANYADDRESS', 'COMPANYCONTACTNO'];
        foreach ($required_fields as $field) {
            if (empty($input[$field])) {
                sendError("$field is required");
            }
        }
        
        $query = "INSERT INTO tblcompany (COMPANYNAME, COMPANYADDRESS, COMPANYCONTACTNO) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        
        $result = $stmt->execute([
            $input['COMPANYNAME'],
            $input['COMPANYADDRESS'],
            $input['COMPANYCONTACTNO']
        ]);
        
        if ($result) {
            $new_id = $db->lastInsertId();
            sendSuccess("Company created successfully", ['COMPANYID' => $new_id], 201);
        } else {
            sendError("Failed to create company", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Create company error: " . $e->getMessage());
        sendError("Failed to create company", 500);
    }
}

/**
 * Update company
 */
function updateCompany($db, $company_id) {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Check if company exists
        $check_query = "SELECT COMPANYID FROM tblcompany WHERE COMPANYID = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$company_id]);
        
        if (!$check_stmt->fetch()) {
            sendError("Company not found", 404);
        }
        
        $fields = [];
        $values = [];
        
        $allowed_fields = ['COMPANYNAME', 'COMPANYADDRESS', 'COMPANYCONTACTNO'];
        
        foreach ($allowed_fields as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = ?";
                $values[] = $input[$field];
            }
        }
        
        if (empty($fields)) {
            sendError("No valid fields to update");
        }
        
        $values[] = $company_id;
        
        $query = "UPDATE tblcompany SET " . implode(', ', $fields) . " WHERE COMPANYID = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute($values)) {
            sendSuccess("Company updated successfully");
        } else {
            sendError("Failed to update company", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Update company error: " . $e->getMessage());
        sendError("Failed to update company", 500);
    }
}

/**
 * Delete company
 */
function deleteCompany($db, $company_id) {
    try {
        // Check if company exists
        $check_query = "SELECT COMPANYID FROM tblcompany WHERE COMPANYID = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$company_id]);
        
        if (!$check_stmt->fetch()) {
            sendError("Company not found", 404);
        }
        
        // Check if company has employees
        $employee_check = "SELECT COUNT(*) FROM tblemployee WHERE COMPANYID = ?";
        $employee_stmt = $db->prepare($employee_check);
        $employee_stmt->execute([$company_id]);
        
        if ($employee_stmt->fetchColumn() > 0) {
            sendError("Cannot delete company with existing employees", 400);
        }
        
        $query = "DELETE FROM tblcompany WHERE COMPANYID = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$company_id])) {
            sendSuccess("Company deleted successfully");
        } else {
            sendError("Failed to delete company", 500);
        }
        
    } catch (PDOException $e) {
        error_log("Delete company error: " . $e->getMessage());
        sendError("Failed to delete company", 500);
    }
}
?>
