<?php
require_once 'admin-functions.php';
require_once 'db.php';

requireAdminAuth();
handleLogout();

// Get statistics
$stats = [
    'courses' => $conn->query("SELECT COUNT(*) as count FROM course")->fetch_assoc()['count'],
    'instructors' => $conn->query("SELECT COUNT(*) as count FROM instructor")->fetch_assoc()['count'],
    'sections' => $conn->query("SELECT COUNT(*) as count FROM section")->fetch_assoc()['count'],
    'ratings' => $conn->query("SELECT COUNT(*) as count FROM rating")->fetch_assoc()['count']
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Course Compass</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <div class="page-header">
            <h1>Admin Dashboard</h1>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="material-icons">school</i>
                <div class="number"><?php echo $stats['courses']; ?></div>
                <div class="label">Courses</div>
            </div>
            <div class="stat-card">
                <i class="material-icons">person</i>
                <div class="number"><?php echo $stats['instructors']; ?></div>
                <div class="label">Instructors</div>
            </div>
            <div class="stat-card">
                <i class="material-icons">class</i>
                <div class="number"><?php echo $stats['sections']; ?></div>
                <div class="label">Sections</div>
            </div>
            <div class="stat-card">
                <i class="material-icons">star</i>
                <div class="number"><?php echo $stats['ratings']; ?></div>
                <div class="label">Ratings</div>
            </div>
        </div>

        <div class="form-section">
            <h3>Management</h3>
            <div class="admin-links">
                <a href="manage-courses.php" class="btn btn-primary">
                    <i class="material-icons">book</i>
                    Manage Courses
                </a>
                <a href="manage-instructors.php" class="btn btn-primary">
                    <i class="material-icons">person</i>
                    Manage Instructors
                </a>
                <a href="manage-sections.php" class="btn btn-primary">
                    <i class="material-icons">class</i>
                    Manage Sections
                </a>
                <a href="manage-ratings.php" class="btn btn-primary">
                    <i class="material-icons">star</i>
                    Manage Ratings
                </a>
            </div>
        </div>
    </div>
</body>

</html>