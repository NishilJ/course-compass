<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit();
}

include('db.php');

$message = '';
$message_type = '';

// CSV Import Function for Courses
function importCoursesFromCSV($conn, $csvFile) {
    $success = 0;
    $errors = 0;
    
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
            if (count($data) >= 6) {
                $course_id = intval($data[0]);
                $course_prefix = trim($data[1]);
                $course_credits = intval($data[2]);
                $course_subject = trim($data[3]);
                $course_number = intval($data[4]);
                $course_description = trim($data[5]);
                
                $stmt = $conn->prepare("INSERT IGNORE INTO course (course_id, course_prefix, course_credits, course_subject, course_number, course_title) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isisis', $course_id, $course_prefix, $course_credits, $course_subject, $course_number, $course_description);
                
                if ($stmt->execute() && $conn->affected_rows > 0) {
                    $success++;
                } else {
                    $errors++;
                }
                $stmt->close();
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
                $description = trim($_POST['course_title']);
                $subject = trim($_POST['course_subject']);
                $credits = intval($_POST['course_credits']);
                
                $errors = [];
                
                if (empty($prefix) || !preg_match('/^[A-Z]{2,4}$/', $prefix)) {
                    $errors[] = 'Course prefix must be 2 to 4 uppercase letters.';
                }
                
                if (empty($number) || $number < 1000 || $number > 9999) {
                    $errors[] = 'Course number must be exactly 4 digits (1000-9999).';
                }
                
                if (empty($description)) {
                    $errors[] = 'Course description is required.';
                }
                
                if ($credits < 1 || $credits > 6) {
                    $errors[] = 'Credits must be between 1 and 6.';
                }
                
                if (empty($errors)) {
                    $stmt = $conn->prepare("INSERT INTO course (course_prefix, course_number, course_title, course_subject, course_credits) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param('sissi', $prefix, $number, $description, $subject, $credits);
                    
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
                        $message = "CSV Import completed: {$import_results['success']} courses added";
                        if ($import_results['errors'] > 0) {
                            $message .= ", {$import_results['errors']} entries skipped";
                        }
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
                
            case 'delete':
                $course_id = intval($_POST['course_id']);
                if ($course_id > 0) {
                    $stmt = $conn->prepare("DELETE FROM course WHERE course_id = ?");
                    $stmt->bind_param('i', $course_id);
                    
                    if ($stmt->execute()) {
                        $message = 'Course deleted successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error deleting course.';
                        $message_type = 'error';
                    }
                    $stmt->close();
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
            <p style="color: #666; margin-bottom: 15px;">Upload a CSV file with course data (format: course_id, course_prefix, course_credits, course_subject, course_number, course_description)</p>
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
                                    <td><?php echo htmlspecialchars($course['course_subject'] ?? '-'); ?></td>
                                    <td><?php echo $course['course_credits']; ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this course?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="material-icons" style="vertical-align: middle;">delete</i>
                                            </button>
                                        </form>
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
    
    <script>
        // Real-time validation for course prefix
        document.getElementById('course_prefix').addEventListener('input', function(e) {
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
    </script>
</body>
</html>
