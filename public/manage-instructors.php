<?php
require_once 'admin-functions.php';
require_once 'db.php';

requireAdminAuth();
handleLogout();

$message = '';
$message_type = '';

// CSV Import Function for Instructors
function importInstructorsFromCSV($conn, $csvFile) {
    $success = 0;
    $errors = 0;
    
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
            if (count($data) >= 6) {
                $instructor_id = intval($data[0]);
                $instructor_name = trim($data[1]);
                $instructor_phone = intval($data[2]);
                $instructor_email = trim($data[3]);
                $instructor_office = trim($data[4]);
                $instructor_dep = trim($data[5]);
                
                $stmt = $conn->prepare("INSERT IGNORE INTO instructor (instructor_id, instructor_name, instructor_phone, instructor_email, instructor_office, instructor_dep) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isisss', $instructor_id, $instructor_name, $instructor_phone, $instructor_email, $instructor_office, $instructor_dep);
                
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
                $name = trim($_POST['instructor_name']);
                $email = trim($_POST['instructor_email']);
                $department = trim($_POST['instructor_dep']);
                $phone = intval($_POST['instructor_phone']);
                $office = trim($_POST['instructor_office']);
                
                $errors = [];
                
                if (empty($name)) {
                    $errors[] = 'Instructor name is required.';
                }
                
                if (!empty($email)) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'Please enter a valid email address.';
                    } elseif (!preg_match('/@utdallas\.edu$/', $email)) {
                        $errors[] = 'Email must be a valid @utdallas.edu address.';
                    }
                }
                
                if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
                    $errors[] = 'Phone number must be exactly 10 digits.';
                }
                
                if (empty($errors)) {
                    $stmt = $conn->prepare("INSERT INTO instructor (instructor_name, instructor_email, instructor_dep, instructor_phone, instructor_office) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param('sssis', $name, $email, $department, $phone, $office);
                    
                    if ($stmt->execute()) {
                        $message = 'Instructor added successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error adding instructor.';
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
                        $import_results = importInstructorsFromCSV($conn, $uploadedFile['tmp_name']);
                        $message = getCSVImportMessage($import_results['success'], $import_results['errors'], 'instructors');
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
                $instructor_id = intval($_POST['instructor_id']);
                $name = trim($_POST['instructor_name']);
                $email = trim($_POST['instructor_email']);
                $department = trim($_POST['instructor_dep']);
                $phone = intval($_POST['instructor_phone']);
                $office = trim($_POST['instructor_office']);
                
                $errors = [];
                
                if (empty($name)) {
                    $errors[] = 'Instructor name is required.';
                }
                
                if (!empty($email)) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $errors[] = 'Please enter a valid email address.';
                    } elseif (!preg_match('/@utdallas\.edu$/', $email)) {
                        $errors[] = 'Email must be a valid @utdallas.edu address.';
                    }
                }
                
                if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
                    $errors[] = 'Phone number must be exactly 10 digits.';
                }
                
                if (empty($errors)) {
                    $stmt = $conn->prepare("UPDATE instructor SET instructor_name = ?, instructor_email = ?, instructor_dep = ?, instructor_phone = ?, instructor_office = ? WHERE instructor_id = ?");
                    $stmt->bind_param('sssisi', $name, $email, $department, $phone, $office, $instructor_id);
                    
                    if ($stmt->execute()) {
                        $message = 'Instructor updated successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error updating instructor.';
                        $message_type = 'error';
                    }
                    $stmt->close();
                } else {
                    $message = implode('<br>', $errors);
                    $message_type = 'error';
                }
                break;
                
            case 'delete':
                $instructor_id = intval($_POST['instructor_id']);
                if ($instructor_id > 0) {
                    $stmt = $conn->prepare("DELETE FROM instructor WHERE instructor_id = ?");
                    $stmt->bind_param('i', $instructor_id);
                    
                    if ($stmt->execute()) {
                        $message = 'Instructor deleted successfully!';
                        $message_type = 'success';
                    } else {
                        $message = 'Error deleting instructor: ' . $conn->error;
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

// Get all instructors
$instructors_query = "SELECT * FROM instructor ORDER BY instructor_name";
$instructors_result = $conn->query($instructors_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Instructors - Course Compass Admin</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <div class="page-header">
            <h1>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">person</i>
                Manage Instructors
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
                Add New Instructor
            </h3>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="instructor_name">Instructor Name *</label>
                        <input type="text" id="instructor_name" name="instructor_name" required placeholder="Enter instructor name" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="instructor_email">Email</label>
                        <input type="email" id="instructor_email" name="instructor_email" placeholder="instructor@utdallas.edu" pattern=".*@utdallas\.edu$" title="Please enter a valid @utdallas.edu email address" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="instructor_phone">Phone</label>
                        <input type="text" id="instructor_phone" name="instructor_phone" pattern="[0-9]{10}" title="Please enter a 10-digit phone number" placeholder="1234567890" maxlength="10">
                    </div>
                    <div class="form-group">
                        <label for="instructor_office">Office</label>
                        <input type="text" id="instructor_office" name="instructor_office" placeholder="e.g., ECSS 3.714" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="instructor_dep">Department</label>
                        <input type="text" id="instructor_dep" name="instructor_dep" placeholder="e.g., Computer Science" maxlength="100">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">add</i>
                    Add Instructor
                </button>
            </form>
        </div>

        <div class="form-section">
            <h3>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">file_upload</i>
                Import from CSV
            </h3>
            <p style="color: #666; margin-bottom: 15px;">Upload a CSV file with instructor data (format: instructor_id, instructor_name, instructor_phone, instructor_email, instructor_office, instructor_dep)</p>
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
                Existing Instructors
            </h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Office</th>
                            <th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($instructors_result && $instructors_result->num_rows > 0): ?>
                            <?php while ($instructor = $instructors_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($instructor['instructor_name']); ?></td>
                                    <td><?php echo htmlspecialchars($instructor['instructor_email'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($instructor['instructor_phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($instructor['instructor_office'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($instructor['instructor_dep'] ?? '-'); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-secondary" onclick="openEditModal(<?php echo $instructor['instructor_id']; ?>, '<?php echo addslashes($instructor['instructor_name']); ?>', '<?php echo addslashes($instructor['instructor_email'] ?? ''); ?>', '<?php echo addslashes($instructor['instructor_dep'] ?? ''); ?>', '<?php echo $instructor['instructor_phone'] ?? 0; ?>', '<?php echo addslashes($instructor['instructor_office'] ?? ''); ?>')">
                                            <i class="material-icons" style="vertical-align: middle;">edit</i>
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="openDeleteModal(<?php echo $instructor['instructor_id']; ?>, '<?php echo addslashes($instructor['instructor_name']); ?>', '<?php echo addslashes($instructor['instructor_email'] ?? 'No email'); ?>')">
                                            <i class="material-icons" style="vertical-align: middle;">delete</i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #666; padding: 30px;">
                                    No instructors found. Add some instructors to get started.
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
                    Edit Instructor
                </h3>
                <span class="close" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="instructor_id" id="edit_instructor_id">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_instructor_name">Instructor Name *</label>
                        <input type="text" id="edit_instructor_name" name="instructor_name" required placeholder="Dr. John Smith" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="edit_instructor_email">Email (@utdallas.edu)</label>
                        <input type="email" id="edit_instructor_email" name="instructor_email" placeholder="john.smith@utdallas.edu" maxlength="100" pattern="[a-zA-Z0-9._%+-]+@utdallas\.edu$">
                    </div>
                    <div class="form-group">
                        <label for="edit_instructor_dep">Department</label>
                        <input type="text" id="edit_instructor_dep" name="instructor_dep" placeholder="Computer Science" maxlength="100">
                    </div>
                    <div class="form-group">
                        <label for="edit_instructor_phone">Phone Number</label>
                        <input type="tel" id="edit_instructor_phone" name="instructor_phone" placeholder="9725551234" maxlength="10" pattern="[0-9]{10}" title="Please enter a 10-digit phone number">
                    </div>
                    <div class="form-group">
                        <label for="edit_instructor_office">Office Location</label>
                        <input type="text" id="edit_instructor_office" name="instructor_office" placeholder="ECSS 4.702" maxlength="50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">close</i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">save</i>
                        Update Instructor
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
                <p>Are you sure you want to delete this instructor?</p>
                <div class="section-info">
                    <strong>Instructor:</strong> <span id="delete_instructor_name"></span><br>
                    <strong>Email:</strong> <span id="delete_instructor_email"></span>
                </div>
                <p><strong>This action cannot be undone.</strong></p>
            </div>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="instructor_id" id="delete_instructor_id">
                
                <div class="modal-footer">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functions
        function openEditModal(instructorId, name, email, department, phone, office) {
            document.getElementById('edit_instructor_id').value = instructorId;
            document.getElementById('edit_instructor_name').value = name;
            document.getElementById('edit_instructor_email').value = email;
            document.getElementById('edit_instructor_dep').value = department;
            document.getElementById('edit_instructor_phone').value = phone || '';
            document.getElementById('edit_instructor_office').value = office;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Delete modal functions
        function openDeleteModal(instructorId, instructorName, instructorEmail) {
            document.getElementById('delete_instructor_id').value = instructorId;
            document.getElementById('delete_instructor_name').textContent = instructorName;
            document.getElementById('delete_instructor_email').textContent = instructorEmail;
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

        // Phone number validation for both forms
        function validatePhone(input) {
            input.value = input.value.replace(/\D/g, '');
            if (input.value.length > 10) {
                input.value = input.value.slice(0, 10);
            }
        }

        document.getElementById('instructor_phone').addEventListener('input', function() {
            validatePhone(this);
        });

        document.getElementById('edit_instructor_phone').addEventListener('input', function() {
            validatePhone(this);
        });
    </script>
</body>
</html>