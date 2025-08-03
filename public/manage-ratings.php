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

function normalize($str) {
    if (!is_string($str)) return $str;
    $bom = "\xEF\xBB\xBF";
    if (strpos($str, $bom) === 0) {
        $str = substr($str, 3);
    }
    return trim($str);
}

function readCsv($filepath) {
    $rows = [];
    if (!file_exists($filepath)) {
        return $rows;
    }
    if (($handle = fopen($filepath, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
            $data = array_map(fn($v) => is_string($v) ? normalize($v) : $v, $data);
            $rows[] = $data;
        }
        fclose($handle);
    }
    return $rows;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && !empty($_POST['action'])
    && $_POST['action'] === 'import_csv'
) {
    if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $message = 'Failed to upload file. Please try again.';
        $message_type = 'error';
    } else {
        $tmpPath = $_FILES['csv_file']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));

        if ($ext !== 'csv') {
            $message = 'Uploaded file is not a CSV.';
            $message_type = 'error';
        } else {
            $imported = 0;
            $skipped = 0;
            $toAppend = [];

            if (($h = fopen($tmpPath, 'r')) !== FALSE) {
                while (($row = fgetcsv($h, 1000, ",", '"', "\\")) !== FALSE) {
                    $row = array_map(fn($v) => is_string($v) ? normalize($v) : $v, $row);
                    // Need at least 4 columns: id, instr, rating, grade
                    if (count($row) < 4) {
                        $skipped++;
                        continue;
                    }
                    $rid = trim($row[0]);
                    $iid = trim($row[1]);
                    $rn  = intval($row[2]);
                    if ($rid === '' || $iid === '' || $rn < 1 || $rn > 5) {
                        $skipped++;
                        continue;
                    }
                    $toAppend[] = $row;
                    $imported++;
                }
                fclose($h);
            }

            if ($imported === 0) {
                $message = 'No valid rows found to import.';
                $message_type = 'error';
            } else {
                $dataDir = realpath(__DIR__ . '/../data');
                $ratingCsv = $dataDir . '/Rating.csv';

                if (!is_writable(dirname($ratingCsv))) {
                    $message = 'Cannot write to data directory.';
                    $message_type = 'error';
                } else if (($out = fopen($ratingCsv, 'a')) === FALSE) {
                    $message = 'Failed to open Rating.csv for appending.';
                    $message_type = 'error';
                } else {
                    if (flock($out, LOCK_EX)) {
                        foreach ($toAppend as $r) {
                            fputcsv($out, $r);
                        }
                        fflush($out);
                        flock($out, LOCK_UN);
                        $message = "Imported {$imported} row(s)" 
                                 . ($skipped ? " ({$skipped} skipped)" : "")
                                 . " successfully.";
                        $message_type = 'success';
                    } else {
                        $message = 'Could not lock Rating.csv.';
                        $message_type = 'error';
                    }
                    fclose($out);
                }
            }
        }
    }
}
$base = realpath(__DIR__ . '/../data');
$instCsv   = $base . '/Instructor.csv';
$ratingCsv = $base . '/Rating.csv';

$instructors = [];
foreach (readCsv($instCsv) as $row) {
    if (count($row) < 2) continue;
    $id = $row[0];
    $instructors[$id] = [
        'id'=>$id,
        'name'=>$row[1],
        'ratings'=>[1=>0,2=>0,3=>0,4=>0,5=>0],
        'total_ratings'=>0
    ];
}

foreach (readCsv($ratingCsv) as $row) {
    if (count($row) < 4) continue;
    [$rid, $iid, $num] = [$row[0], $row[1], intval($row[2])];
    if (!isset($instructors[$iid]) || $num<1||$num>5) continue;
    $instructors[$iid]['ratings'][$num]++;
    $instructors[$iid]['total_ratings']++;
}

if (empty($instructors) && !$message) {
    $message = "No instructor data found. Checked path: {$instCsv}";
    $message_type = 'error';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Manage Ratings - Course Compass Admin</title>
    <link rel="stylesheet" href="assets/css/styles.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .instructor-block {
            border: 1px solid #e0e0e0;
            padding: 16px;
            margin-bottom: 14px;
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
        body {
            background: #f5f7fa;
            font-family: system-ui,-apple-system,BlinkMacSystemFont,sans-serif;
        }
        .admin-container {
            max-width: 960px;
            margin: 30px auto;
            padding: 0 12px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="admin-container">
        <div class="page-header">
            <h1>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">bar_chart</i>
                Manage Ratings
            </h1>
            <a href="admin-dashboard.php" class="back-btn">
                <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">arrow_back</i>
                Back to Dashboard
            </a>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">
                    <?php echo $message_type === 'success' ? 'check_circle' : 'error'; ?>
                </i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h3>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">bar_chart</i>
                Instructor Ratings Overview
            </h3>
            <?php if (empty($instructors)): ?>
                <p class="no-data">
                    No instructor data or ratings found. Verify Instructor.csv and Rating.csv in <code>data/</code>.
                </p>
            <?php else: ?>
                <?php foreach ($instructors as $instr): ?>
                    <div class="instructor-block">
                        <div class="instr-info">
                            <p class="small-title">
                                <?php echo htmlspecialchars($instr['name']); ?>
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
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="form-section" style="margin-top: 40px;">
            <h3>
                <i class="material-icons" style="vertical-align: middle; margin-right: 10px;">file_upload</i>
                Import from CSV
            </h3>
            <p style="color: #666; margin-bottom: 15px;">
                Upload a CSV (rating_id, instructor_id, rating_number, rating_student_grade)
            </p>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="import_csv" />
                <div class="form-group">
                    <label for="csv_file">Select CSV File</label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv" required />
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="material-icons" style="vertical-align: middle; margin-right: 5px;">upload_file</i>
                    Import CSV File
                </button>
            </form>
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
