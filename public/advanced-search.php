<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Course Compass</title>
        <link rel="stylesheet" href="assets/css/styles.css">
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    </head>
    <body>
    <?php include 'navbar.php'; ?>


        <?php
            $prefixes = $conn->query("SELECT DISTINCT course_prefix FROM course ORDER BY course_prefix")->fetch_all(MYSQLI_ASSOC);
            $numbers = $conn->query("SELECT DISTINCT course_number FROM course ORDER BY course_number")->fetch_all(MYSQLI_ASSOC);
            $instructors = $conn->query("SELECT DISTINCT instructor_name FROM instructor ORDER BY instructor_name")->fetch_all(MYSQLI_ASSOC);
            $terms = $conn->query("SELECT DISTINCT term FROM section ORDER BY term")->fetch_all(MYSQLI_ASSOC);
        ?>

        <div class="container">
            <div class="search-section">
                <div class="search-title">
                    <h2>Find a Class</h2>
                    <a href="index.php" class="adv-search-btn">Quick Search</a>
                </div>
                <form method="GET" action="advanced-search.php" id="search-form">
                    <div class="search-grid">
                        <div class="search-field">
                            <label for="course_prefix">Course Prefix</label>
                            <select name="course_prefix" id="course_prefix">
                                <option value="">Any</option>
                                <?php foreach ($prefixes as $prefix): ?>
                                    <option value="<?= htmlspecialchars($prefix['course_prefix']) ?>"
                                        <?= ($_GET['course_prefix'] ?? '') === $prefix['course_prefix'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prefix['course_prefix']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="search-field">
                            <label for="course_number">Course Number</label>
                            <select name="course_number" id="course_number">
                                <option value="">Any</option>
                                <?php foreach ($numbers as $number): ?>
                                    <option value="<?= htmlspecialchars($number['course_number']) ?>"
                                        <?= ($_GET['course_number'] ?? '') === $number['course_number'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($number['course_number']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="search-field">
                            <label for="instructor_name">Instructor</label>
                            <select name="instructor_name" id="instructor_name">
                                <option value="">Any</option>
                                <?php foreach ($instructors as $instructor): ?>
                                    <option value="<?= htmlspecialchars($instructor['instructor_name']) ?>"
                                        <?= ($_GET['instructor_name'] ?? '') === $instructor['instructor_name'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($instructor['instructor_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="search-field">
                            <label for="term">Term</label>
                            <select name="term" id="term">
                                <option value="">Any</option>
                                <?php foreach ($terms as $term): ?>
                                    <option value="<?= htmlspecialchars($term['term']) ?>"
                                        <?= ($_GET['term'] ?? '') === $term['term'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($term['term']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="search-buttons">
                        <button type="submit">Search</button>
                        <button type="button" class="clear-btn" onclick="clearForm()">Clear</button>
                    </div>
                </form>
            </div>

            <?php
            if ($_GET) {
                echo '<h2>Search Results</h2>';

                $where = [];
                $params = [];
                $types = '';

                if (!empty($_GET['course_prefix'])) {
                    $where[] = 'c.course_prefix = ?';
                    $params[] = $_GET['course_prefix'];
                    $types .= 's';
                }

                if (!empty($_GET['course_number'])) {
                    $where[] = 'c.course_number = ?';
                    $params[] = $_GET['course_number'];
                    $types .= 's';
                }

                if (!empty($_GET['instructor_name'])) {
                    $where[] = 'i.instructor_name = ?';
                    $params[] = $_GET['instructor_name'];
                    $types .= 's';
                }

                if (!empty($_GET['term'])) {
                    $where[] = 's.term = ?';
                    $params[] = $_GET['term'];
                    $types .= 's';
                }

                $sql = "SELECT DISTINCT
                        s.section_id,
                        c.course_prefix,
                        c.course_number,
                        CONCAT(c.course_prefix, ' ', c.course_number) AS course_code,
                        c.course_title,
                        i.instructor_name,
                        s.term,
                        s.days,
                        s.start_time,
                        s.end_time,
                        s.location
                    FROM section s
                    LEFT JOIN course c ON s.course_id = c.course_id
                    LEFT JOIN instructor i ON s.instructor_id = i.instructor_id";

                if ($where) {
                    $sql .= ' WHERE ' . implode(' AND ', $where);
                }

                $sql .= ' ORDER BY c.course_prefix, c.course_number, s.term, s.start_time';

                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    if (!empty($params)) {
                        $stmt->bind_param($types, ...$params);
                    }

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

                            $courseId = htmlspecialchars($row['section_id']);
                            echo "<tr class='clickable-row' data-course-id='$courseId' onclick='toggleCourseDetails($courseId)'>";
                            echo '<td>' . htmlspecialchars($row['course_code']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['course_title']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['instructor_name'] ?? 'TBD') . '</td>';
                            echo '<td>' . htmlspecialchars($row['term'] ?? 'TBD') . '</td>';
                            echo '<td>' . htmlspecialchars($row['days'] ?? '-') . '</td>';
                            echo '<td>' . $time . '</td>';
                            echo '<td>' . htmlspecialchars($row['location']) . '</td>';
                            echo '</tr>';
                            echo "<tr id='details-$courseId' class='course-details-row' style='display:none;'><td colspan='7'>Loading...</td></tr>";
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
                const form = document.getElementById("search-form");
                const selects = form.querySelectorAll("select");

                selects.forEach(select => {
                    select.selectedIndex = 0;
                });
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
