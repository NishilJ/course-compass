<?php
require_once 'admin-functions.php';
requireAdminAuth();

$message = '';
$message_type = '';

// Database connection
require_once 'db.php';

// Handle logout
if (isset($_POST['logout'])) {
    handleLogout();
}

function importRatingsFromCSV($conn, $filePath) {
    $success = 0;
    $errors = 0;
    
    if (($handle = fopen($filePath, 'r')) !== FALSE) {
        $row_number = 0;
        while (($data = fgetcsv($handle, 1000, ',', '"', '\\')) !== FALSE) {
            $row_number++;
            
            // Skip header row
            if ($row_number === 1) {
                continue;
            }
            
            // Skip empty rows
            if (count($data) < 4) {
                $errors++;
                continue;
            }
            
            $rating_id = trim($data[0]);
            $instructor_id = trim($data[1]);
            $rating_number = intval($data[2]);
            $student_grade = trim($data[3]);
            
            // Validate data
            if (empty($rating_id) || empty($instructor_id) || $rating_number < 1 || $rating_number > 5) {
                $errors++;
                continue;
            }
            
            $stmt = $conn->prepare("INSERT IGNORE INTO Rating (rating_id, instructor_id, rating_number, rating_student_grade) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("siis", $rating_id, $instructor_id, $rating_number, $student_grade);
                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $success++;
                    }
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
            case 'import_csv':
                if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadedFile = $_FILES['csv_file'];
                    
                    if (strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION)) === 'csv') {
                        $import_results = importRatingsFromCSV($conn, $uploadedFile['tmp_name']);
                        $message = getCSVImportMessage($import_results['success'], $import_results['errors'], 'ratings');
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

// Get instructor ratings data from database
$instructors = [];

// First get all instructors
$result = $conn->query("SELECT instructor_id, instructor_name FROM Instructor ORDER BY instructor_name");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instructors[$row['instructor_id']] = [
            'id' => $row['instructor_id'],
            'name' => $row['instructor_name'],
            'ratings' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
            'total_ratings' => 0
        ];
    }
}

// Then get ratings data
$result = $conn->query("SELECT instructor_id, rating_number FROM Rating WHERE rating_number BETWEEN 1 AND 5");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instructor_id = $row['instructor_id'];
        $rating_number = intval($row['rating_number']);
        
        if (isset($instructors[$instructor_id])) {
            $instructors[$instructor_id]['ratings'][$rating_number]++;
            $instructors[$instructor_id]['total_ratings']++;
        }
    }
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .instructor-wrapper {
            margin: 16px;
        }
        .instructor-block {
            border: 1px solid #e0e0e0;
            padding: 16px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 20px;
            background: #fff;
        }
        .instr-info {
            flex: 1 1 200px;
        }
        .chart-wrapper {
            width: 260px;
            max-width: 100%;
        }
        .no-data {
            color: #555;
            font-style: italic;
        }
        .small-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .subtext {
            margin: 2px 0;
            font-size: 0.9rem;
            color: #444;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

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

        <div class="data-table">
            <h3>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">bar_chart</i>
                Rating Analytics
            </h3>

            <?php if (empty($instructors)): ?>
                <p class="no-data">
                    No instructor data or ratings found. Please ensure instructors and ratings are properly added to the database.
                </p>
            <?php else: ?>
                <?php foreach ($instructors as $instr): ?>
                    <div class="instructor-wrapper">
                        <div class="instructor-block">
                            <div class="instr-info">
                                <p class="small-title">
                                    <?php echo htmlspecialchars($instr['name']); ?>
                                </p>
                                <p class="subtext">
                                    ID: <?php echo htmlspecialchars($instr['id']); ?>
                                </p>
                                <p class="subtext">
                                    Total ratings: <?php echo intval($instr['total_ratings']); ?>
                                </p>
                            </div>
                            <div class="chart-wrapper">
                                <canvas id="chart-<?php echo htmlspecialchars($instr['id']); ?>"
                                        aria-label="Rating distribution for <?php echo htmlspecialchars($instr['name']); ?>"
                                        role="img"></canvas>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        const instructors = <?php
            $out = [];
            foreach ($instructors as $i) {
                $counts = [];
                for ($j = 1; $j <= 5; $j++) {
                    $counts[] = intval($i['ratings'][$j] ?? 0);
                }
                $out[] = [
                    'id'=>$i['id'],
                    'name'=>$i['name'],
                    'counts'=>$counts,
                    'total'=>$i['total_ratings']
                ];
            }
            echo json_encode($out, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT);
        ?>;

        instructors.forEach(instr => {
            const ctx = document.getElementById(`chart-${instr.id}`);
            if (!ctx) return;
            const total = instr.total || 0;
            const labels = ['1','2','3','4','5'];
            const dataPerc = instr.counts.map(c => total>0 ? Math.round(c/total*100) : 0);

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ label: '% of Ratings', data: dataPerc, borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: { title: { display: true, text: 'Rating' } },
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { callback: v => v + '%' },
                            title: { display: true, text: 'Percent' }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: ctx => ctx.parsed.y + '%' } }
                    }
                }
            });
        });
    </script>
</body>
</html>
