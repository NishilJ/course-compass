<?php
require_once 'admin-functions.php';
require_once 'db.php';

requireAdminAuth();
handleLogout();

$message = '';
$message_type = '';

// CSV Import Function for Courses
function importCoursesFromCSV($conn, $csvFile) {
    $success = 0;
    $errors = 0;
    
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        $rowNum = 0;
        while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
            $rowNum++;
            
            // Skip header row
            if ($rowNum === 1) {
                continue;
            }
            
            if (count($data) >= 6) {
                $course_id = intval($data[0]);
                $course_prefix = trim($data[1]);
                $course_credits = intval($data[2]);
                $course_subject = trim($data[3]);
                $course_number = intval($data[4]);
                $course_title = trim($data[5]);
                $course_description = trim($data[6]);
                
                $stmt = $conn->prepare("INSERT IGNORE INTO course (course_id, course_prefix, course_credits, course_subject, course_number, course_title, course_description) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isissss', $course_id, $course_prefix, $course_credits, $course_subject, $course_number, $course_title, $course_description);
                
                if ($stmt->execute() && $conn->affected_rows > 0) {
                    $success++;
                } else {
                    $errors++;
                }
                $stmt->close();
            } else {
                $errors++;
            }
        }
        fclose($handle);
    }
    
    return ['success' => $success, 'errors' => $errors];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $prefix = strtoupper(trim($_POST['course_prefix']));
                $number = intval($_POST['course_number']);
                $title = trim($_POST['course_title']);
                $description = trim($_POST['course_description']);
                $subject = trim($_POST['course_subject']);
                $credits = intval($_POST['course_credits']);
                
                $errors = [];
                
                if (empty($prefix) || !preg_match('/^[A-Z]{2,4}$/', $prefix)) {
                    $errors[] = 'Course prefix must be 2 to 4 uppercase letters.';
                }
                
                if (empty($number) || $number < 1000 || $number > 9999) {
                    $errors[] = 'Course number must be exactly 4 digits (1000-9999).';
                }
                
                if (empty($title)) {
                    $errors[] = 'Course title is required.';
                }
                
                if (empty($description)) {
                    $errors[] = 'Course description is required.';
                }
                
                if ($credits < 1 || $credits > 6) {
                    $errors[] = 'Credits must be between 1 and 6.';
                }
                
                if (empty($errors)) {
                    $stmt = $conn->prepare("INSERT INTO course (course_prefix, course_number, course_title, course_description, course_subject, course_credits) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('sisssi', $prefix, $number, $title, $description, $subject, $credits);
                    
                    if ($stmt->execute()) {
                        $message = 'Course added successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error adding course.';
                        $message_type = 'error';
                    }
                    $stmt->close();
                } else {
                    $message = implode('<br>', $errors);
                    $message_type = 'error';
                }
                break;
                
            case 'import_csv':
                if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadedFile = $_FILES['csv_file'];
                    
                    if (strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION)) === 'csv') {
                        $import_results = importCoursesFromCSV($conn, $uploadedFile['tmp_name']);
                        $message = getCSVImportMessage($import_results['success'], $import_results['errors'], 'courses');
                        $message_type = 'success';
                    } else {
                        $message = 'Please upload a valid CSV file.';
                        $message_type = 'error';
                    }
                } else {
                    $message = 'Please select a CSV file to upload.';
                    $message_type = 'error';
                }
                break;
                
            case 'update':
                $course_id = intval($_POST['course_id']);
                $prefix = strtoupper(trim($_POST['course_prefix']));
                $number = intval($_POST['course_number']);
                $title = trim($_POST['course_title']);
                $description = trim($_POST['course_description']);
                $subject = trim($_POST['course_subject']);
                $credits = intval($_POST['course_credits']);
                
                $errors = [];
                
                if (empty($prefix) || !preg_match('/^[A-Z]{2,4}$/', $prefix)) {
                    $errors[] = 'Course prefix must be 2 to 4 uppercase letters.';
                }
                
                if (empty($number) || $number < 1000 || $number > 9999) {
                    $errors[] = 'Course number must be exactly 4 digits (1000-9999).';
                }
                
                if (empty($title)) {
                    $errors[] = 'Course title is required.';
                }
                
                if (empty($description)) {
                    $errors[] = 'Course description is required.';
                }
                
                if ($credits < 1 || $credits > 6) {
                    $errors[] = 'Credits must be between 1 and 6.';
                }
                
                if (empty($errors)) {
                    $stmt = $conn->prepare("UPDATE course SET course_prefix = ?, course_number = ?, course_title = ?, course_description = ?, course_subject = ?, course_credits = ? WHERE course_id = ?");
                    $stmt->bind_param('sisssis', $prefix, $number, $title, $description, $subject, $credits, $course_id);
                    
                    if ($stmt->execute()) {
                        $message = 'Course updated successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error updating course.';
                        $message_type = 'error';
                    }
                    $stmt->close();
                } else {
                    $message = implode('<br>', $errors);
                    $message_type = 'error';
                }
                break;
                
            case 'delete':
                $course_id = intval($_POST['course_id']);
                if ($course_id > 0) {
                    // Check if there are any sections using this course
                    $check_stmt = $conn->prepare("SELECT COUNT(*) as section_count FROM section WHERE course_id = ?");
                    $check_stmt->bind_param('i', $course_id);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    $section_count = $result->fetch_assoc()['section_count'];
                    $check_stmt->close();
                    
                    if ($section_count > 0) {
                        // Check if cascade delete is requested
                        if (isset($_POST['cascade_delete']) && $_POST['cascade_delete'] === 'yes') {
                            // Start transaction for safe cascade delete
                            $conn->autocommit(FALSE);
                            
                            try {
                                // Delete sections for this course
                                $section_delete = $conn->prepare("DELETE FROM section WHERE course_id = ?");
                                $section_delete->bind_param('i', $course_id);
                                $section_delete->execute();
                                $sections_deleted = $section_delete->affected_rows;
                                $section_delete->close();
                                
                                // Finally delete the course
                                $course_delete = $conn->prepare("DELETE FROM course WHERE course_id = ?");
                                $course_delete->bind_param('i', $course_id);
                                $course_delete->execute();
                                $course_delete->close();
                                
                                // Commit transaction
                                $conn->commit();
                                $conn->autocommit(TRUE);
                                
                                $message = "Course deleted successfully! Also deleted {$sections_deleted} section(s).";
                                $message_type = 'success';
                                
                            } catch (Exception $e) {
                                // Rollback on error
                                $conn->rollback();
                                $conn->autocommit(TRUE);
                                $message = 'Error during cascade delete: ' . $e->getMessage();
                                $message_type = 'error';
                            }
                        } else {
                            $message = "Cannot delete course: {$section_count} section(s) are still using this course. Check 'Force Delete' to delete the course and all its sections.";
                            $message_type = 'error';
                        }
                    } else {
                        // Safe to delete the course
                        $stmt = $conn->prepare("DELETE FROM course WHERE course_id = ?");
                        $stmt->bind_param('i', $course_id);
                        
                        if ($stmt->execute()) {
                            $message = 'Course deleted successfully!';
                            $message_type = 'success';
                        } else {
                            $message = 'Error deleting course: ' . $conn->error;
                            $message_type = 'error';
                        }
                        $stmt->close();
                    }
                }
                break;
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-login.php');
    exit();
}

// Get all courses
$courses_query = "SELECT * FROM course ORDER BY course_prefix, course_number";
$courses_result = $conn->query($courses_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - Course Compass Admin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <div class="page-header">
            <h1>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">book</i>
                Manage Courses
            </h1>
            <a href="admin-dashboard.php" class="back-btn">
                <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">arrow_back</i>
                Back to Dashboard
            </a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">
                    <?php echo $message_type === 'success' ? 'check_circle' : 'error'; ?>
                </i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h3>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">add</i>
                Add New Course
            </h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="course_prefix">Course Prefix *</label>
                        <input type="text" id="course_prefix" name="course_prefix" required placeholder="e.g., CS, EE, MATH" maxlength="4" minlength="2" pattern="[A-Z]{2,4}" title="Please enter 2 to 4 uppercase letters">
                    </div>
                    <div class="form-group">
                        <label for="course_number">Course Number *</label>
                        <input type="number" id="course_number" name="course_number" required placeholder="e.g., 1337, 2336" min="1000" max="9999" title="Please enter a 4-digit course number (1000-9999)">
                    </div>
                    <div class="form-group">
                        <label for="course_credits">Credits</label>
                        <input type="number" id="course_credits" name="course_credits" min="1" max="6" value="3" placeholder="3" title="Credits must be between 1 and 6">
                    </div>
                    <div class="form-group">
                        <label for="course_title">Course Title *</label>
                        <input type="text" id="course_title" name="course_title" required placeholder="e.g., Introduction to Java" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="course_subject">Course Subject</label>
                        <input type="text" id="course_subject" name="course_subject" placeholder="e.g., Computer Science" maxlength="100">
                    </div>
                    <div class="form-group full-width">
                        <label for="course_description">Course Description *</label>
                        <textarea id="course_description" name="course_description" required placeholder="Enter course description..." maxlength="500" rows="3"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">add</i>
                    Add Course
                </button>
            </form>
        </div>

        <div class="form-section">
            <h3>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">file_upload</i>
                Import from CSV
            </h3>
            <p style="color: #666; margin-bottom: 15px;">Upload a CSV file with course data (format: course_id, course_prefix, course_credits, course_subject, course_number, course_description, course_title)</p>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import_csv">
                <div class="form-group">
                    <label for="csv_file">Select CSV File</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">upload_file</i>
                    Import CSV File
                </button>
            </form>
        </div>

        <div class="data-table">
            <h3>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">list</i>
                Existing Courses
            </h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Course Title</th>
                            <th>Description</th>
                            <th>Subject</th>
                            <th>Credits</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($courses_result && $courses_result->num_rows > 0): ?>
                            <?php while ($course = $courses_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($course['course_prefix'] . ' ' . $course['course_number']); ?></td>
                                    <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                                    <td><?php echo htmlspecialchars($course['course_description']); ?></td>
                                    <td><?php echo htmlspecialchars($course['course_subject'] ?? '-'); ?></td>
                                    <td><?php echo $course['course_credits']; ?></td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" onclick="openEditModal(<?php echo $course['course_id']; ?>, '<?php echo addslashes($course['course_prefix']); ?>', <?php echo $course['course_number']; ?>, '<?php echo addslashes($course['course_title']); ?>', '<?php echo addslashes($course['course_description']); ?>', '<?php echo addslashes($course['course_subject'] ?? ''); ?>', <?php echo $course['course_credits']; ?>)">
                                            <i class="material-icons" style="vertical-align: middle;">edit</i>
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="openDeleteModal(<?php echo $course['course_id']; ?>, '<?php echo addslashes($course['course_prefix'] . ' ' . $course['course_number']); ?>', '<?php echo addslashes($course['course_title']); ?>')">
                                            <i class="material-icons" style="vertical-align: middle;">delete</i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #666; padding: 30px;">
                                    No courses found. Add some courses to get started.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">edit</i>
                    Edit Course
                </h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="course_id" id="edit_course_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_course_prefix">Course Prefix *</label>
                        <input type="text" id="edit_course_prefix" name="course_prefix" required placeholder="e.g., CS, EE, MATH" maxlength="4" minlength="2" pattern="[A-Z]{2,4}" title="Please enter 2 to 4 uppercase letters">
                    </div>
                    <div class="form-group">
                        <label for="edit_course_number">Course Number *</label>
                        <input type="number" id="edit_course_number" name="course_number" required placeholder="e.g., 1337, 2336" min="1000" max="9999" title="Please enter a 4-digit course number (1000-9999)">
                    </div>
                    <div class="form-group">
                        <label for="edit_course_credits">Credits</label>
                        <input type="number" id="edit_course_credits" name="course_credits" min="1" max="6" value="3" placeholder="3" title="Credits must be between 1 and 6">
                    </div>
                    <div class="form-group">
                        <label for="edit_course_title">Course Title *</label>
                        <input type="text" id="edit_course_title" name="course_title" required placeholder="e.g., Introduction to Java" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="edit_course_subject">Course Subject</label>
                        <input type="text" id="edit_course_subject" name="course_subject" placeholder="e.g., Computer Science" maxlength="100">
                    </div>
                    <div class="form-group full-width">
                        <label for="edit_course_description">Course Description *</label>
                        <textarea id="edit_course_description" name="course_description" required placeholder="Enter course description..." maxlength="500" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">close</i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">save</i>
                        Update Course
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Delete</h3>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this course?</p>
                <div class="section-info">
                    <strong>Course:</strong> <span id="delete_course_code"></span><br>
                    <strong>Title:</strong> <span id="delete_course_title"></span>
                </div>
                <p><strong>This action cannot be undone.</strong></p>
            </div>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="course_id" id="delete_course_id">
                
                <div style="margin: 15px 20px; padding: 10px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                    <label style="display: flex; align-items: center; font-size: 14px; color: #856404;">
                        <input type="checkbox" name="cascade_delete" value="yes" style="margin-right: 8px;">
                        <strong>Force Delete:</strong> Also delete all sections associated with this course
                    </label>
                    <small style="color: #6c757d; margin-left: 24px;">Use this if the course has sections that you want to remove as well.</small>
                </div>
                
                <div class="modal-footer">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openEditModal(courseId, prefix, number, title, description, subject, credits) {
            document.getElementById('edit_course_id').value = courseId;
            document.getElementById('edit_course_prefix').value = prefix;
            document.getElementById('edit_course_number').value = number;
            document.getElementById('edit_course_title').value = title;
            document.getElementById('edit_course_description').value = description;
            document.getElementById('edit_course_subject').value = subject;
            document.getElementById('edit_course_credits').value = credits;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Delete modal functions
        function openDeleteModal(courseId, courseCode, courseTitle) {
            document.getElementById('delete_course_id').value = courseId;
            document.getElementById('delete_course_code').textContent = courseCode;
            document.getElementById('delete_course_title').textContent = courseTitle;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target === editModal) {
                closeEditModal();
            } else if (event.target === deleteModal) {
                closeDeleteModal();
            }
        }
        
        // Real-time validation for course prefix
        document.getElementById('course_prefix').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
            if (this.value.length > 4) {
                this.value = this.value.slice(0, 4);
            }
        });
        
        document.getElementById('edit_course_prefix').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
            if (this.value.length > 4) {
                this.value = this.value.slice(0, 4);
            }
        });
        
        // Real-time validation for course number (4 digits only)
        document.getElementById('course_number').addEventListener('input', function(e) {
            let value = this.value;
            if (value.length > 4) {
                this.value = value.slice(0, 4);
            }
        });
        
        document.getElementById('edit_course_number').addEventListener('input', function(e) {
            let value = this.value;
            if (value.length > 4) {
                this.value = value.slice(0, 4);
            }
        });
        
        // Real-time validation for credits (1 digit only)
        document.getElementById('course_credits').addEventListener('input', function(e) {
            let value = this.value;
            if (value.length > 1) {
                this.value = value.slice(0, 1);
            }
            // Ensure it's within 1-6 range
            let num = parseInt(this.value);
            if (num > 6) {
                this.value = '6';
            } else if (num < 1 && this.value !== '') {
                this.value = '1';
            }
        });
        
        document.getElementById('edit_course_credits').addEventListener('input', function(e) {
            let value = this.value;
            if (value.length > 1) {
                this.value = value.slice(0, 1);
            }
            // Ensure it's within 1-6 range
            let num = parseInt(this.value);
            if (num > 6) {
                this.value = '6';
            } else if (num < 1 && this.value !== '') {
                this.value = '1';
            }
        });
    </script>
</body>
</html>
