<?php
require_once 'admin-functions.php';
require_once 'db.php';

requireAdminAuth();
handleLogout();

$message = '';
$message_type = '';

// CSV Import Function for Sections
function importSectionsFromCSV($conn, $csvFile) {
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

            // Skip empty or malformed rows
            if (count($data) < 11) {
                $errors++;
                continue;
            }

            $section_id = intval($data[0]);
            $instructor_id = intval($data[1]);
            $course_id = intval($data[2]);
            $location = trim($data[3]);
            $capacity = intval($data[4]);
            $term = trim($data[5]);
            $start_time = trim($data[6]);
            $end_time = trim($data[7]);
            $days = trim($data[8]);
            $start_date = trim($data[9]);
            $end_date = trim($data[10]);

            $stmt = $conn->prepare("INSERT IGNORE INTO section 
                (section_id, course_id, instructor_id, location, capacity, term, start_time, end_time, days, start_date, end_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if (!$stmt) {
                $errors++;
                continue;
            }

            $stmt->bind_param(
                'iiissssssss',
                $section_id, $course_id, $instructor_id, $location, $capacity, $term,
                $start_time, $end_time, $days, $start_date, $end_date
            );

            if ($stmt->execute() && $conn->affected_rows > 0) {
                $success++;
            } else {
                $errors++;
            }
            $stmt->close();
        }
        fclose($handle);
    }
    
    return ['success' => $success, 'errors' => $errors];
}

// Generate time options in 15-minute intervals
function getTimeOptions($selectedTime = '') {
    $options = '<option value="">Select Time</option>';
    
    // Generate times from 8:00 AM to 10:00 PM in 15-minute intervals
    for ($hour = 8; $hour <= 22; $hour++) {
        for ($minute = 0; $minute < 60; $minute += 15) {
            $time24 = sprintf('%02d:%02d', $hour, $minute);
            
            // Convert to 12-hour format for display
            $time12 = date('g:i A', strtotime($time24));
            
            $selected = ($selectedTime === $time24) ? 'selected' : '';
            $options .= "<option value=\"{$time24}\" {$selected}>{$time12}</option>";
        }
    }
    
    return $options;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $course_id = intval($_POST['course_id']);
                $instructor_id = intval($_POST['instructor_id']);
                $term = trim($_POST['term']);
                $location = trim($_POST['location']) ?: 'ECSW 1.365, etc.';
                $capacity = intval($_POST['capacity']) ?: NULL;
                $start_time = trim($_POST['start_time']) ?: NULL;
                $end_time = trim($_POST['end_time']) ?: NULL;
                $days = trim($_POST['days']) ?: NULL;
                $start_date = trim($_POST['start_date']) ?: NULL;
                $end_date = trim($_POST['end_date']) ?: NULL;
                
                $errors = [];
                
                if (empty($course_id)) {
                    $errors[] = 'Please select a course.';
                }
                
                if (empty($instructor_id)) {
                    $errors[] = 'Please select an instructor.';
                }
                
                if (empty($term)) {
                    $errors[] = 'Term is required.';
                }
                
                if (empty($errors)) {
                    $stmt = $conn->prepare("INSERT INTO section (course_id, instructor_id, term, location, capacity, start_time, end_time, days, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('iississsss', $course_id, $instructor_id, $term, $location, $capacity, $start_time, $end_time, $days, $start_date, $end_date);
                    
                    if ($stmt->execute()) {
                        $message = 'Section added successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error adding section.';
                        $message_type = 'error';
                    }
                    $stmt->close();
                } else {
                    $message = implode('<br>', $errors);
                    $message_type = 'error';
                }
                break;
                
            case 'update':
                $section_id = intval($_POST['section_id']);
                $course_id = intval($_POST['course_id']);
                $instructor_id = intval($_POST['instructor_id']);
                $term = trim($_POST['term']);
                $location = trim($_POST['location']) ?: 'ECSW 1.365, etc.';
                $capacity = intval($_POST['capacity']) ?: NULL;
                $start_time = trim($_POST['start_time']) ?: NULL;
                $end_time = trim($_POST['end_time']) ?: NULL;
                $days = trim($_POST['days']) ?: NULL;
                $start_date = trim($_POST['start_date']) ?: NULL;
                $end_date = trim($_POST['end_date']) ?: NULL;
                
                $errors = [];
                
                if (empty($course_id)) {
                    $errors[] = 'Please select a course.';
                }
                
                if (empty($instructor_id)) {
                    $errors[] = 'Please select an instructor.';
                }
                
                if (empty($term)) {
                    $errors[] = 'Term is required.';
                }
                
                if (empty($errors)) {
                    $stmt = $conn->prepare("UPDATE section SET course_id = ?, instructor_id = ?, term = ?, location = ?, capacity = ?, start_time = ?, end_time = ?, days = ?, start_date = ?, end_date = ? WHERE section_id = ?");
                    $stmt->bind_param('iississsssi', $course_id, $instructor_id, $term, $location, $capacity, $start_time, $end_time, $days, $start_date, $end_date, $section_id);
                    
                    if ($stmt->execute()) {
                        $message = 'Section updated successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error updating section.';
                        $message_type = 'error';
                    }
                    $stmt->close();
                } else {
                    $message = implode('<br>', $errors);
                    $message_type = 'error';
                }
                break;
                
            case 'delete':
                $section_id = intval($_POST['section_id']);
                if ($section_id > 0) {
                    // Sections can be deleted directly as they don't have dependent records
                    $stmt = $conn->prepare("DELETE FROM section WHERE section_id = ?");
                    $stmt->bind_param('i', $section_id);
                    
                    if ($stmt->execute()) {
                        $message = 'Section deleted successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error deleting section: ' . $conn->error;
                        $message_type = 'error';
                    }
                    $stmt->close();
                }
                break;
                
            case 'import_csv':
                if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadedFile = $_FILES['csv_file'];
                    
                    if (strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION)) === 'csv') {
                        $import_results = importSectionsFromCSV($conn, $uploadedFile['tmp_name']);
                        $message = getCSVImportMessage($import_results['success'], $import_results['errors'], 'sections');
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
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-login.php');
    exit();
}

// Get all sections with course and instructor details
$sections_query = "SELECT s.*, CONCAT(c.course_prefix, ' ', c.course_number) as course_code, c.course_title, c.course_prefix, c.course_number, i.instructor_name 
                   FROM section s 
                   JOIN course c ON s.course_id = c.course_id 
                   JOIN instructor i ON s.instructor_id = i.instructor_id 
                   ORDER BY s.term, course_code";
$sections_result = $conn->query($sections_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Sections - Course Compass Admin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <div class="page-header">
            <h1>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">class</i>
                Manage Sections
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
                Add New Section
            </h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="course_id">Course *</label>
                        <select id="course_id" name="course_id" required>
                            <?php echo getCourseDropdown($conn); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="instructor_id">Instructor *</label>
                        <select id="instructor_id" name="instructor_id" required>
                            <?php echo getInstructorDropdown($conn); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="term">Term *</label>
                        <select id="term" name="term" required>
                            <?php echo getTermDropdown($conn); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="location">Location</label>
                        <input type="text" id="location" name="location" placeholder="ECSW 1.365, etc." maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="capacity">Capacity</label>
                        <input type="number" id="capacity" name="capacity" placeholder="30" min="1" max="500">
                    </div>
                    <div class="form-group">
                        <label for="days">Days</label>
                        <input type="text" id="days" name="days" placeholder="MW, TTh, etc." maxlength="10">
                    </div>
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <select id="start_time" name="start_time">
                            <?php echo getTimeOptions(); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <select id="end_time" name="end_time">
                            <?php echo getTimeOptions(); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date">
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">add</i>
                    Add Section
                </button>
            </form>
        </div>

        <div class="form-section">
            <h3>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">file_upload</i>
                Import from CSV
            </h3>
            <p style="color: #666; margin-bottom: 15px;">Upload a CSV file with section data (format: section_id, instructor_id, course_id, location, capacity, term, start_time, end_time, days, start_date, end_date)</p>
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
                Existing Sections
            </h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Instructor</th>
                            <th>Term</th>
                            <th>Location</th>
                            <th>Capacity</th>
                            <th>Days</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($sections_result && $sections_result->num_rows > 0): ?>
                            <?php while ($section = $sections_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($section['course_code'] . ' - ' . $section['course_title']); ?></td>
                                    <td><?php echo htmlspecialchars($section['instructor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($section['term'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($section['location'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($section['capacity'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($section['days'] ?? '-'); ?></td>
                                    <td>
                                        <?php 
                                        $time_display = '';
                                        if ($section['start_time'] && $section['end_time']) {
                                            $time_display = date('g:i A', strtotime($section['start_time'])) . ' - ' . date('g:i A', strtotime($section['end_time']));
                                        }
                                        echo htmlspecialchars($time_display ?: '-');
                                        ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" onclick="openEditModal(<?php echo $section['section_id']; ?>, <?php echo $section['course_id']; ?>, <?php echo $section['instructor_id']; ?>, '<?php echo addslashes($section['term'] ?? ''); ?>', '<?php echo addslashes($section['location'] ?? ''); ?>', <?php echo $section['capacity'] ?? 'null'; ?>, '<?php echo addslashes($section['days'] ?? ''); ?>', '<?php echo $section['start_time'] ?? ''; ?>', '<?php echo $section['end_time'] ?? ''; ?>', '<?php echo $section['start_date'] ?? ''; ?>', '<?php echo $section['end_date'] ?? ''; ?>')">
                                            <i class="material-icons" style="vertical-align: middle;">edit</i>
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="openDeleteModal(<?php echo $section['section_id']; ?>, '<?php echo addslashes($section['course_prefix'] . ' ' . $section['course_number']); ?>', '<?php echo addslashes($section['term'] ?? 'Unknown term'); ?>')">
                                            <i class="material-icons" style="vertical-align: middle;">delete</i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #666; padding: 30px;">
                                    No sections found. Add some sections to get started.
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
                    Edit Section
                </h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="section_id" id="edit_section_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_course_id">Course *</label>
                        <select id="edit_course_id" name="course_id" required>
                            <?php echo getCourseDropdown($conn); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_instructor_id">Instructor *</label>
                        <select id="edit_instructor_id" name="instructor_id" required>
                            <?php echo getInstructorDropdown($conn); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_term">Term *</label>
                        <select id="edit_term" name="term" required>
                            <?php echo getTermDropdown($conn); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_location">Location</label>
                        <input type="text" id="edit_location" name="location" placeholder="ECSW 1.365, etc." maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="edit_capacity">Capacity</label>
                        <input type="number" id="edit_capacity" name="capacity" placeholder="30" min="1" max="500">
                    </div>
                    <div class="form-group">
                        <label for="edit_days">Days</label>
                        <input type="text" id="edit_days" name="days" placeholder="MW, TTh, etc." maxlength="10">
                    </div>
                    <div class="form-group">
                        <label for="edit_start_time">Start Time</label>
                        <select id="edit_start_time" name="start_time">
                            <?php echo getTimeOptions(); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_end_time">End Time</label>
                        <select id="edit_end_time" name="end_time">
                            <?php echo getTimeOptions(); ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_start_date">Start Date</label>
                        <input type="date" id="edit_start_date" name="start_date">
                    </div>
                    <div class="form-group">
                        <label for="edit_end_date">End Date</label>
                        <input type="date" id="edit_end_date" name="end_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">close</i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">save</i>
                        Update Section
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
                <p>Are you sure you want to delete this section?</p>
                <div class="section-info">
                    <strong>Course:</strong> <span id="delete_section_course"></span><br>
                    <strong>Term:</strong> <span id="delete_section_term"></span>
                </div>
                <p><strong>This action cannot be undone.</strong></p>
            </div>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="section_id" id="delete_section_id">
                
                <div class="modal-footer">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openEditModal(sectionId, courseId, instructorId, term, location, capacity, days, startTime, endTime, startDate, endDate) {
            document.getElementById('edit_section_id').value = sectionId;
            document.getElementById('edit_course_id').value = courseId;
            document.getElementById('edit_instructor_id').value = instructorId;
            document.getElementById('edit_term').value = term;
            document.getElementById('edit_location').value = location;
            document.getElementById('edit_capacity').value = capacity === 'null' ? '' : capacity;
            document.getElementById('edit_days').value = days;
            document.getElementById('edit_start_time').value = startTime;
            document.getElementById('edit_end_time').value = endTime;
            document.getElementById('edit_start_date').value = startDate;
            document.getElementById('edit_end_date').value = endDate;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Delete modal functions
        function openDeleteModal(sectionId, courseCode, term) {
            document.getElementById('delete_section_id').value = sectionId;
            document.getElementById('delete_section_course').textContent = courseCode;
            document.getElementById('delete_section_term').textContent = term;
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
    </script>
</body>
</html>
