<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Compass - Search Courses</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="top-bar">
    <div class="left-logo">
        <img src="utd-logo.svg" alt="Logo" class="logo-img">
    </div>
    <div class="right-section">
        <span class="title">Course Compass</span>
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
                  <input type="text" id="search" name="search"
                         placeholder="Search by course number, course name, or instructor..."
                         value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                  <button type="submit">Search</button>
                  <button type="button" class="clear-btn" onclick="clearForm()">Clear</button>
            </div>
        </form>
    </div>

    <?php
    // Only show results if there's a search
    if (!empty($_GET['search'])) {
        echo '<h2>Search Results</h2>';
        
        $search_term = $_GET['search'];
        
        // Build the SQL query to search across all fields
        $sql = "SELECT DISTINCT
            s.section_id,
            CONCAT(c.course_prefix, ' ', c.course_number) AS course_code,
            c.course_description AS course_description,
            i.instructor_name AS instructor_name,
            s.term,
            s.days,
            s.start_time,
            s.end_time
        FROM section s
        LEFT JOIN course c ON s.course_id = c.course_id
        LEFT JOIN instructor i ON s.instructor_id = i.instructor_id
        WHERE c.course_prefix LIKE ? 
           OR c.course_number LIKE ? 
           OR c.course_description LIKE ? 
           OR i.instructor_name LIKE ?
        ORDER BY c.course_prefix, c.course_number, s.term, s.start_time";

        // Prepare and execute the statement
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $search_pattern = '%' . $search_term . '%';
            $stmt->bind_param('ssss', $search_pattern, $search_pattern, $search_pattern, $search_pattern);
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
                echo '<th>Start Time</th>';
                echo '<th>End Time</th>';
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                while ($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['course_code']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['instructor'] ?? 'TBD') . '</td>';
                    echo '<td>' . htmlspecialchars($row['term'] ?? 'TBD') . '</td>';
                    echo '<td>' . htmlspecialchars($row['days'] ?? '-') . '</td>';
                    echo '<td>' . htmlspecialchars($row['start_time'] ?? '-') . '</td>';
                    echo '<td>' . htmlspecialchars($row['end_time'] ?? '-') . '</td>';
                    echo '</tr>';
                }

                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<div class="no-results">No courses found matching your search criteria.</div>';
            }
            $stmt->close();
        } else {
            echo '<div class="no-results">Error preparing search query.</div>';
        }
    }
    ?>
  </div>

  <script>
    function clearForm() {
      document.getElementById('search').value = '';
      window.location.href = window.location.pathname;
    }
  </script>
</body>
</html> 