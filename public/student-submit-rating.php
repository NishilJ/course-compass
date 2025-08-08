<?php
session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once 'db.php';

$message = '';
$message_type = '';

try {
    $stmt = $conn->prepare("
        SELECT
          instructor_id,
          instructor_name
        FROM `instructor`
        ORDER BY instructor_name
    ");
    $stmt->execute();
    $stmt->bind_result($instr_id, $instr_name);

    $instructors = [];
    while ($stmt->fetch()) {
        $instructors[$instr_id] = $instr_name;
    }
    $stmt->close();
} catch (mysqli_sql_exception $e) {
    die("Error fetching instructors: " . $e->getMessage());
}

$instructorRatings = [];
foreach ($instructors as $id => $name) {
    $instructorRatings[$id] = [
        'ratings' => [1=>0,2=>0,3=>0,4=>0,5=>0],
        'total'   => 0,
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $instructor_id = $_POST['instructor_id']      ?? '';
    $rating_number = intval($_POST['rating_number'] ?? 0);
    $rating_student_grade = $_POST['rating_student_grade']      ?? '';

    if ($rating_student_grade === '') {
        $rating_student_grade = null;
    }
    
    $instructor_id = (int)($_POST['instructor_id'] ?? 0);
    if (! isset($instructors[$instructor_id])) {
        $message = 'Please select a valid instructor.';
        $message_type = 'error';
    } elseif ($rating_number < 1 || $rating_number > 5) {
        $message = 'Please select a rating between 1 and 5.';
        $message_type = 'error';
    } elseif (! in_array($rating_student_grade, ['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'F', null], true)) {
        $message = 'Please select a valid grade.';
        $message_type = 'error';
    } else {
        try {
            $ins = $conn->prepare("
                INSERT INTO `rating`
                  (instructor_id, rating_number, rating_student_grade)
                VALUES (?,?,?)
            ");
            $ins->bind_param("iis", $instructor_id, $rating_number, $rating_student_grade);
            $ins->execute();
            $ins->close();
            $message = 'Rating submitted successfully.';
            $message_type = 'success';
        } catch (mysqli_sql_exception $e) {
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

try {
    $stat = $conn->prepare("
        SELECT
          instructor_id,
          rating_number,
          COUNT(*) AS cnt
        FROM `rating`
        GROUP BY instructor_id, rating_number
    ");
    $stat->execute();
    $stat->bind_result($iid, $rnum, $cnt);

    while ($stat->fetch()) {
        if (isset($instructorRatings[$iid])) {
            $instructorRatings[$iid]['ratings'][(int)$rnum] = (int)$cnt;
            $instructorRatings[$iid]['total']   += (int)$cnt;
        }
    }
    $stat->close();
} catch (mysqli_sql_exception $e) {
    die("Error fetching rating stats: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Submit Rating – Course Compass</title>
  <link rel="stylesheet" href="assets/css/styles.css"/>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <?php include 'navbar.php'; ?>

  <div class="container">
    <div class="page-header">
      <h1>
        <i class="material-icons" style="vertical-align:middle;margin-right:8px;">
          rate_review
        </i>
        Submit Instructor Rating
      </h1>
      <a href="javascript:history.back()" class="back-btn">
        <i class="material-icons" style="vertical-align:middle;margin-right:5px;">
          arrow_back
        </i>
        Go Back
      </a>
    </div>

    <div class="form-card">
      <?php if ($message): ?>
        <div class="message <?php echo htmlspecialchars($message_type); ?>">
          <i class="material-icons">
            <?php echo $message_type==='success'?'check_circle':'error'; ?>
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
                <option value="<?php echo htmlspecialchars($id); ?>"
                  <?php if (($_POST['instructor_id'] ?? '') == $id) echo 'selected'; ?>>
                  <?php echo htmlspecialchars($name); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="rating_number">Rating (1–5)</label>
            <select name="rating_number" id="rating_number" required>
              <option value="">Select rating</option>
              <?php for ($i=1; $i<=5; $i++): ?>
                <option value="<?php echo $i; ?>"
                  <?php if ((int)($_POST['rating_number'] ?? 0) === $i) echo 'selected'; ?>>
                  <?php echo $i; ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="rating_student_grade">Your Grade</label>
            <select name="rating_student_grade" id="rating_student_grade" required>
                <option value="">N/A</option>
              <?php foreach (['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'F'] as $g): ?>
                <option value="<?php echo $g; ?>"
                  <?php if (($_POST['rating_student_grade'] ?? '') === $g) echo 'selected'; ?>>
                  <?php echo $g; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <button type="submit" class="btn-primary">
          <i class="material-icons" style="vertical-align:middle;margin-right:5px;">
            send
          </i>
          Submit Rating
        </button>
      </form>

      <div class="chart-container">
        <div class="chart-box">
          <div class="chart-title">
            <i class="material-icons">bar_chart</i>
            <div>Selected Instructor Rating Distribution</div>
          </div>
          <canvas id="instructor-chart"
                  aria-label="Rating distribution"
                  role="img"
                  style="max-height:220px;"></canvas>
          <div class="small-note">
            Shows percentage of ratings 1–5 for the selected instructor.
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    const instructorRatingsMap = {};
    <?php foreach ($instructorRatings as $id => $info):
        $jsRatings = json_encode(array_values($info['ratings']));
        $jsTotal   = json_encode($info['total']);
    ?>
    instructorRatingsMap[<?php echo json_encode($id); ?>] = {
      ratings: <?php echo $jsRatings; ?>,
      total:   <?php echo $jsTotal; ?>
    };
    <?php endforeach; ?>

    function computePercentages(ratings, total) {
      return total
        ? ratings.map(c => Math.round((c/total)*100))
        : [0,0,0,0,0];
    }

    const ctx = document.getElementById('instructor-chart');
    const chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['1','2','3','4','5'],
        datasets: [{ label: '% of Ratings', data:[0,0,0,0,0], borderWidth:1 }]
      },
      options: {
        responsive: true,
        scales: {
          x: { title:{display:true,text:'Rating'} },
          y: {
            beginAtZero:true,
            max:100,
            ticks:{ callback: v => v + '%' },
            title:{display:true,text:'Percent'}
          }
        },
        plugins: {
          legend:{display:false},
          tooltip:{ callbacks:{ label: ctx => ctx.parsed.y + '%' } }
        }
      }
    });

    document.getElementById('instructor_id')
      .addEventListener('change', function() {
        const d = instructorRatingsMap[this.value] || {ratings:[0,0,0,0,0],total:0};
        chart.data.datasets[0].data = computePercentages(d.ratings, d.total);
        chart.update();
      });

    window.addEventListener('DOMContentLoaded', () => {
      const sel = document.getElementById('instructor_id').value;
      if (sel) {
        const d = instructorRatingsMap[sel] || {ratings:[0,0,0,0,0],total:0};
        chart.data.datasets[0].data = computePercentages(d.ratings, d.total);
        chart.update();
      }
    });
  </script>
</body>
</html>
