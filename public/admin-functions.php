<?php
/**
 * Shared Admin Functions
 * Common functionality for all manage pages
 */

// Common authentication check
function requireAdminAuth() {
    session_start();
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: admin-login.php');
        exit();
    }
}

// Handle logout #
function handleLogout() {
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: admin-login.php');
        exit();
    }
}

// Generate course dropdown options
function getCourseDropdown($conn, $selected_id = null) {
    static $courses_result = null;
    if ($courses_result === null) {
        $query = "SELECT course_id, CONCAT(course_prefix, ' ', course_number, ' - ', course_title) as course_display FROM course ORDER BY course_prefix, course_number";
        $courses_result = $conn->query($query);
    } else {
        $courses_result->data_seek(0);
    }
    
    $html = '<option value="">Select a course</option>';
    while ($course = $courses_result->fetch_assoc()) {
        $selected = ($selected_id == $course['course_id']) ? 'selected' : '';
        $html .= "<option value=\"{$course['course_id']}\" {$selected}>";
        $html .= htmlspecialchars($course['course_display']);
        $html .= "</option>";
    }
    return $html;
}

// Generate instructor dropdown options
function getInstructorDropdown($conn, $selected_id = null) {
    static $instructors_result = null;
    if ($instructors_result === null) {
        $query = "SELECT instructor_id, instructor_name FROM instructor ORDER BY instructor_name";
        $instructors_result = $conn->query($query);
    } else {
        $instructors_result->data_seek(0);
    }
    
    $html = '<option value="">Select an instructor</option>';
    while ($instructor = $instructors_result->fetch_assoc()) {
        $selected = ($selected_id == $instructor['instructor_id']) ? 'selected' : '';
        $html .= "<option value=\"{$instructor['instructor_id']}\" {$selected}>";
        $html .= htmlspecialchars($instructor['instructor_name']);
        $html .= "</option>";
    }
    return $html;
}

// Generate term dropdown options
function getTermDropdown($conn, $selected_term = null) {
    $current_year = date('Y');
    $terms_query = "SELECT DISTINCT term FROM section ORDER BY term";
    $terms_result = $conn->query($terms_query);
    
    $available_terms = [];
    if ($terms_result) {
        while ($term = $terms_result->fetch_assoc()) {
            $available_terms[] = $term['term'];
        }
    }
    
    $standard_terms = [
        "Spring $current_year",
        "Summer $current_year", 
        "Fall $current_year"
    ];
    
    foreach ($standard_terms as $std_term) {
        if (!in_array($std_term, $available_terms)) {
            $available_terms[] = $std_term;
        }
    }
    
    sort($available_terms);
    
    $html = '<option value="">Select term</option>';
    foreach ($available_terms as $term_option) {
        $selected = ($selected_term == $term_option) ? 'selected' : '';
        $html .= "<option value=\"" . htmlspecialchars($term_option) . "\" {$selected}>";
        $html .= htmlspecialchars($term_option);
        $html .= "</option>";
    }
    return $html;
}

// Common CSV import response
function getCSVImportMessage($success, $errors, $item_type) {
    $message = "CSV Import completed: {$success} {$item_type} added";
    if ($errors > 0) {
        $message .= ", {$errors} entries skipped";
    }
    return $message;
}

// Common error validation
function validateRequired($data, $field, $label) {
    if (empty($data[$field])) {
        return "{$label} is required.";
    }
    return null;
}

// Common success/error messages
function setMessage($message, $type = 'success') {
    global $message, $message_type;
    $message = $message;
    $message_type = $type;
}

// Generate rating dropdown options
function getRatingDropdown($selected_rating = null) {
    $html = '<option value="">Select rating</option>';
    for ($i = 1; $i <= 5; $i++) {
        $selected = ($selected_rating == $i) ? 'selected' : '';
        $html .= "<option value=\"{$i}\" {$selected}>{$i} Star" . ($i > 1 ? 's' : '') . "</option>";
    }
    return $html;
}
?>
