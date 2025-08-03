<?php
session_start();
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

$baseDataDir = realpath(__DIR__ . '/../data');
$instructorCsv = $baseDataDir . '/Instructor.csv';
$ratingCsv = $baseDataDir . '/Rating.csv';

$instructors = []; 
$raw_instructors = readCsv($instructorCsv);
foreach ($raw_instructors as $row) {
    if (count($row) < 2) continue;
    $instr_id = $row[0];
    $name = $row[1];
    $instructors[$instr_id] = $name;
}

$instructorRatings = []; 
foreach ($instructors as $id => $name) {
    $instructorRatings[$id] = [
        'ratings' => [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0],
        'total' => 0,
    ];
}
$raw_ratings = readCsv($ratingCsv);
foreach ($raw_ratings as $row) {
    if (count($row) < 4) continue;
    $instructor_id = $row[1];
    $rating_number = intval($row[2]);
    if (!isset($instructorRatings[$instructor_id])) continue;
    if ($rating_number < 1 || $rating_number > 5) continue;
    $instructorRatings[$instructor_id]['ratings'][$rating_number]++;
    $instructorRatings[$instructor_id]['total']++;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructor_id = isset($_POST['instructor_id']) ? normalize($_POST['instructor_id']) : '';
    $rating_number = isset($_POST['rating_number']) ? intval($_POST['rating_number']) : 0;
    $student_grade = isset($_POST['student_grade']) ? normalize($_POST['student_grade']) : '';

    if ($instructor_id === '' || !isset($instructors[$instructor_id])) {
        $message = 'Please select a valid instructor.';
        $message_type = 'error';
    } elseif ($rating_number < 1 || $rating_number > 5) {
        $message = 'Please select a rating between 1 and 5.';
        $message_type = 'error';
    } elseif (!in_array($student_grade, ['A','B','C','D','F','N/A'], true)) {
        $message = 'Please select a valid grade.';
        $message_type = 'error';
    } else {
        $existing = readCsv($ratingCsv);
        $max_id = 0;
        foreach ($existing as $row) {
            if (isset($row[0]) && is_numeric($row[0])) {
                $id = intval($row[0]);
                if ($id > $max_id) $max_id = $id;
            }
        }
        $next_id = $max_id + 1;
        $new_row = [$next_id, $instructor_id, $rating_number, $student_grade];

        $fp = fopen($ratingCsv, 'a');
        if ($fp) {
            if (flock($fp, LOCK_EX)) {
                fputcsv($fp, $new_row);
                fflush($fp);
                flock($fp, LOCK_UN);
                $message = 'Rating submitted successfully.';
                $message_type = 'success';
                if (isset($instructorRatings[$instructor_id])) {
                    $instructorRatings[$instructor_id]['ratings'][$rating_number]++;
                    $instructorRatings[$instructor_id]['total']++;
                }
            } else {
                $message = 'Could not lock rating file for writing.';
                $message_type = 'error';
            }
            fclose($fp);
        } else {
            $message = 'Failed to open rating storage.';
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Submit Rating - Course Compass</title>
<link rel="stylesheet" href="assets/css/styles.css" />
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    body { background: #f2f5f9; font-family: system-ui,-apple-system,BlinkMacSystemFont,sans-serif; margin:0; }
    .top-bar {
        background: #a35200;
        padding: 10px 20px;
        display: flex;
        align-items: center;
        color: white;
        gap: 20px;
    }
    .top-bar .title a { color: white; text-decoration: none; font-weight: bold; font-size: 1.2rem; }
    .logo img { height: 30px; }
    .right-section { margin-left: auto; }
    .dropdown { position: relative; display: inline-block; }
    .dropbtn { cursor: pointer; color: white; font-size: 24px; }
    .dropdown-content {
        display: none;
        position: absolute;
        right: 0;
        background: white;
        min-width: 160px;
        box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        z-index: 10;
        border-radius: 5px;
        overflow: hidden;
    }
    .dropdown-content a {
        display: block;
        padding: 10px 14px;
        color: #333;
        text-decoration: none;
        font-size: 0.9rem;
    }
    .dropdown-content a:hover { background: #f1f1f1; }
    .dropdown:hover .dropdown-content { display: block; }

    .container { max-width: 960px; margin: 30px auto; padding: 0 12px; }
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .page-header h1 { margin:0; font-size: 1.8rem; color: #8b4000; }
    .back-btn {
        text-decoration: none;
        background: #555;
        color: #fff;
        padding: 10px 16px;
        border-radius: 5px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.9rem;
    }
    .form-card {
        background: #fff;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.08);
        margin-top: 20px;
    }
    .selector-row { display: flex; gap: 16px; flex-wrap: wrap; }
    .form-group { margin-bottom: 18px; flex:1; min-width:150px; }
    label { font-weight: 600; display: block; margin-bottom: 6px; }
    select { width: 100%; padding: 10px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 1rem; appearance: none; }
    .btn-primary {
        background: #a35200;
        color: #fff;
        border: none;
        padding: 10px 18px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 4px;
    }
    .btn-primary:hover { filter: brightness(1.05); }
    .message { padding: 12px 16px; border-radius: 5px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
    .message.success { background: #e6f7ed; border: 1px solid #2f8f4e; color: #1f4f2f; }
    .message.error { background: #ffe6e6; border: 1px solid #b33a3a; color: #5f1f1f; }
    .chart-container { margin-top: 20px; display: flex; gap: 30px; flex-wrap: wrap; }
    .chart-box {
        flex: 1 1 340px;
        background: #fafafa;
        padding: 16px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }
    .chart-title { font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
    .small-note { font-size: 0.85rem; color: #555; margin-top: 4px; }
</style>
</head>
<body>
    <div class="top-bar">
        <div class="left-section">
            <span class="title"><a href="/">COURSE COMPASS</a></span>
        </div>
        <div class="logo">
            <img src="assets/images/utd-logo.svg" alt="Logo">
        </div>
        <div class="right-section">
            <div class="dropdown">
                <i class="material-icons dropbtn">menu</i>
                <div class="dropdown-content">
                    <a href="/">Home</a>
                    <a href="manage-ratings.php">Ratings</a>
                    <a href="?logout=1">Log Out</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="material-icons" style="vertical-align: middle; margin-right: 8px;">rate_review</i>Submit Instructor Rating</h1>
            <a href="manage-ratings.php" class="back-btn">
                <i class="material-icons" style="vertical-align: middle; margin-right:5px;">arrow_back</i>
                Back to Ratings
            </a>
        </div>

        <div class="form-card">
            <?php if (!empty($message)): ?>
                <div class="message <?php echo htmlspecialchars($message_type); ?>">
                    <i class="material-icons">
                        <?php echo $message_type === 'success' ? 'check_circle' : 'error'; ?>
                    </i>
                    <div><?php echo htmlspecialchars($message); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" id="rating-form" novalidate>
                <div class="selector-row">
                    <div class="form-group">
                        <label for="instructor_id">Instructor</label>
                        <select name="instructor_id" id="instructor_id" required>
                            <option value="">-- Select Instructor --</option>
                            <?php foreach ($instructors as $id => $name): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>" <?php if (isset($_POST['instructor_id']) && $_POST['instructor_id'] === $id) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="rating_number">Rating (1-5)</label>
                        <select name="rating_number" id="rating_number" required>
                            <?php for ($i = 1; $i <=5; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php if (isset($_POST['rating_number']) && intval($_POST['rating_number']) === $i) echo 'selected'; ?>>
                                    <?php echo $i; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="student_grade">Your Grade</label>
                        <select name="student_grade" id="student_grade" required>
                            <?php
                            $grades = ['A','B','C','D','F','N/A'];
                            foreach ($grades as $g): ?>
                                <option value="<?php echo $g; ?>" <?php if (isset($_POST['student_grade']) && $_POST['student_grade'] === $g) echo 'selected'; ?>>
                                    <?php echo $g; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="material-icons" style="vertical-align: middle; margin-right:5px;">send</i>
                    Submit Rating
                </button>
            </form>

            <div class="chart-container">
                <div class="chart-box">
                    <div class="chart-title">
                        <i class="material-icons" style="vertical-align: middle;">bar_chart</i>
                        <div>Selected Instructor Rating Distribution</div>
                    </div>
                    <canvas id="instructor-chart" aria-label="Rating distribution" role="img" style="max-height:220px;"></canvas>
                    <div class="small-note">Shows percentage of ratings 1–5 for the selected instructor.</div>
                </div>
            </div>
        </div>
    </div>

<script>
    const instructorRatingsMap = {};
    <?php
        foreach ($instructorRatings as $id => $info) {
            $ratings = [
                intval($info['ratings'][1] ?? 0),
                intval($info['ratings'][2] ?? 0),
                intval($info['ratings'][3] ?? 0),
                intval($info['ratings'][4] ?? 0),
                intval($info['ratings'][5] ?? 0),
            ];
            $total = intval($info['total'] ?? 0);
            $jsId = json_encode($id);
            $jsRatings = json_encode($ratings);
            echo "instructorRatingsMap[$jsId] = { ratings: $jsRatings, total: $total };\n";
        }
    ?>

    function computePercentages(ratings, total) {
        if (total === 0) {
            return [0,0,0,0,0];
        }
        return ratings.map(c => Math.round((c / total) * 100));
    }

    const ctx = document.getElementById('instructor-chart');
    const initialData = {
        labels: ['1','2','3','4','5'],
        datasets: [{
            label: '% of Ratings',
            data: [0,0,0,0,0],
            borderWidth: 1
        }]
    };
    const chartOptions = {
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
            tooltip: {
                callbacks: {
                    label: ctx => ctx.parsed.y + '%'
                }
            }
        }
    };
    let instructorChart = new Chart(ctx, {
        type: 'bar',
        data: initialData,
        options: chartOptions
    });

    function updateChartForInstructor(instructorId) {
        const entry = instructorRatingsMap[instructorId];
        let perc = [0,0,0,0,0];
        if (entry) {
            perc = computePercentages(entry.ratings, entry.total);
        }
        instructorChart.data.datasets[0].data = perc;
        instructorChart.update();
    }

    document.getElementById('instructor_id').addEventListener('change', function() {
        const sel = this.value;
        updateChartForInstructor(sel);
    });

    window.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById('instructor_id').value;
        updateChartForInstructor(sel);
        console.log('InstructorRatingsMap:', instructorRatingsMap, 'Initial selection:', sel);
    });
  
</script>
</body>
</html>
