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

// CSV Import Function for Ratings
function importRatingsFromCSV($conn, $csvFile) {
    $success = 0;
    $errors = 0;
    
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
            if (count($data) >= 4) {
                $rating_id = intval($data[0]);
                $instructor_id = intval($data[1]);
                $rating_number = intval($data[2]);
                $rating_student_grade = trim($data[3]);
                
                $stmt = $conn->prepare("INSERT IGNORE INTO rating (rating_id, instructor_id, rating_number, rating_student_grade) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('iiis', $rating_id, $instructor_id, $rating_number, $rating_student_grade);
                
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
            case 'import_csv':
                if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadedFile = $_FILES['csv_file'];
                    
                    if (strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION)) === 'csv') {
                        $import_results = importRatingsFromCSV($conn, $uploadedFile['tmp_name']);
                        $message = "CSV Import completed: {$import_results['success']} ratings added";
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
        }
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ratings - Course Compass Admin</title>
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
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">star</i>
                Manage Ratings
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
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">info</i>
                Rating Management
            </h3>
            <p style="color: #666; margin-bottom: 20px;">
                Rating management features are currently under construction. You can import rating data from CSV below.
            </p>
        </div>

        <div class="form-section">
            <h3>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">file_upload</i>
                Import from CSV
            </h3>
            <p style="color: #666; margin-bottom: 15px;">Upload a CSV file with rating data (format: rating_id, instructor_id, rating_number, rating_student_grade)</p>
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
    </div>
</body>
</html>
