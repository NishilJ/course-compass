<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Compass - Search Courses</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<div class="top-bar">
    <div class="left-section">
      <span class="title">
        <a href="/">Course Compass</a>
      </span>
    </div>
    <div class="logo">
      <img src="assets/images/utd-logo.svg" alt="Logo" class="logo-img">
    </div>
    <div class="right-section">
      <!-- <i class="menu material-icons">menu</i> -->
      <div class="dropdown">
        <div class="icon">
          <i class="dropbtn material-icons">menu</i>
        </div>
        <div class="dropdown-content">
          <a href="/">Home</a>
          <a href="admin-login.php">Admin</a>
        </div>
      </div>
    </div>
</div>

<div class="container">
    <div class="search-section">
        <div class="search-title">
            <h2>Find a Class</h2>
            <a href="advanced-search.php" class="adv-search-btn">Advanced Search</a>
        </div>
        <form method="GET" action="">
            <div class="search-row">
                <input type="text" id="search" name="search" maxlength="100"
                       placeholder="Search by course number, course name, or instructor..."
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
                       title="Search by course number, course name, or instructor...">
                <button type="submit">Search</button>
                <button type="button" class="clear-btn" onclick="clearForm()">Clear</button>
            </div>
        </form>
    </div>

            <?php
            if (!empty($_GET['search'])) {
                echo '<h2>Search Results</h2>';

                $search_term = $_GET['search'];

                $sql = "SELECT DISTINCT
                        s.section_id,
                        c.course_prefix,
                        c.course_number,
                        CONCAT(c.course_prefix, ' ', c.course_number) AS course_code,
                        c.course_subject AS course_title,
                        c.course_title AS course_title,
                        i.instructor_name AS instructor_name,
                        s.term,
                        s.days,
                        s.start_time,
                        s.end_time,
                        s.location
                    FROM section s
                    LEFT JOIN course c ON s.course_id = c.course_id
                    LEFT JOIN instructor i ON s.instructor_id = i.instructor_id
                    WHERE CONCAT(c.course_prefix, ' ', c.course_number) LIKE ?
                       OR c.course_prefix LIKE ? 
                       OR c.course_number LIKE ? 
                       OR c.course_subject LIKE ? 
                       OR c.course_title LIKE ? 
                       OR i.instructor_name LIKE ?
                    ORDER BY c.course_prefix, c.course_number, s.term, s.start_time";

                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $search_pattern = '%' . $search_term . '%';
                    $stmt->bind_param('ssssss', $search_pattern, $search_pattern, $search_pattern, $search_pattern, $search_pattern, $search_pattern);
                    $stmt->execute();
                    $result = $stmt->get_result();


                    if ($result->num_rows > 0) {
                        echo '<table>';
                        echo '<thead>';
                        echo '<tr>';
                        echo '<th>Course Code</th>';
                        echo '<th>Title</th>';
                        echo '<th>Instructor</th>';
                        echo '<th>Term</th>';
                        echo '<th>Days</th>';
                        echo '<th>Time</th>';
                        echo '<th>Location</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';

                        while ($row = $result->fetch_assoc()) {
                            $start = date("g:i A", strtotime($row["start_time"]));
                            $end = date("g:i A", strtotime($row["end_time"]));
                            $time = "$start – $end";

                            $courseId = htmlspecialchars($row['section_id']); // Use section_id for details
                            echo "<tr class='clickable-row' data-course-id='{$courseId}' onclick='toggleCourseDetails({$courseId})'>";
                            echo '<td>' . htmlspecialchars($row['course_code']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['course_title']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['instructor_name'] ?? 'TBD') . '</td>';
                            echo '<td>' . htmlspecialchars($row['term'] ?? 'TBD') . '</td>';
                            echo '<td>' . htmlspecialchars($row['days'] ?? '-') . '</td>';
                            echo '<td>' . $time . '</td>';
                            echo '<td>' . htmlspecialchars($row['location']) . '</td>';
                            echo '</tr>';
                            echo "<tr id='details-{$courseId}' class='course-details-row' style='display:none;'><td colspan='7'>Loading...</td></tr>";
                        }

                        echo '</tbody>';
                        echo '</table>';
                    } else {
                        echo '<div class="no-results">No courses found matching your search criteria.</div>';
                    }
                    $stmt->close();
                } else {
                    echo '<div class="no-results"> Error preparing search query: ' . htmlspecialchars($conn->error) . '</div>';
                }
            }
            ?>
        </div>

        <script>
            function clearForm() {
              document.getElementById('search').value = '';
              window.location.href = window.location.pathname;
            }
            function toggleCourseDetails(courseId) {
                const detailsRow = document.getElementById('details-' + courseId);
                if (detailsRow.style.display === 'none') {
                    detailsRow.style.display = '';
                    if (!detailsRow.dataset.loaded) {
                        fetch('get_course_details.php?course_id=' + courseId)
                            .then(response => response.text())
                            .then(data => {
                                detailsRow.children[0].innerHTML = data;
                                detailsRow.dataset.loaded = "true";
                            })
                            .catch(() => {
                                detailsRow.children[0].innerHTML = "Error loading details.";
                            });
                    }
                } else {
                    detailsRow.style.display = 'none';
                }
            }
        </script>
    </body>
</html>