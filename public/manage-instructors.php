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
                        $message = "CSV Import completed: {$import_results['success']} instructors added";
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
    <div class="top-bar">
        <div class="left-section">
            <span class="title">
                <a href="index.php">Course Compass</a>
            </span>
        </div>
        <div class="logo">
            <img src="assets/images/utd-logo.svg" alt="Logo" class="logo-img">
        </div>
        <div class="right-section">
            <div class="dropdown">
                <div class="icon">
                    <i class="dropbtn material-icons">menu</i>
                </div>
                <div class="dropdown-content">
                    <a href="index.php">Home</a>
                    <a href="admin-dashboard.php">Dashboard</a>
                    <a href="?logout=1">Log Out</a>
                </div>
            </div>
        </div>
    </div>

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
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this instructor?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="instructor_id" value="<?php echo $instructor['instructor_id']; ?>">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="material-icons" style="vertical-align: middle;">delete</i>
                                            </button>
                                        </form>
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
</body>
</html>