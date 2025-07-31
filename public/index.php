<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Compass - Search Courses</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<div class="top-bar">
    <div class="left-section">
      <span class="title">
        <a href="/public/index.php">Course Compass</a>
      </span>
    </div>
    <div class="logo">
      <img src="utd-logo.svg" alt="Logo" class="logo-img">
    </div>
    <div class="right-section">
      <!-- <i class="menu material-icons">menu</i> -->
      <div class="dropdown">
        <div class="icon">
          <i class="dropbtn material-icons">menu</i>
        </div>
        <div class="dropdown-content">
          <a href="#">Home</a>
          <a href="#">Admin</a>
          <a href="#">Log Out</a>
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
                    c.course_id,
                    CONCAT(c.course_prefix, ' ', c.course_number) as course_code,
                    c.course_description as title,
                    i.instructor_name as instructor,
                    c.course_prefix,
                    c.course_number
                FROM course c
                LEFT JOIN section s ON c.course_id = s.course_id
                LEFT JOIN instructor i ON s.instructor_id = i.instructor_id
                WHERE c.course_prefix LIKE ? 
                   OR c.course_number LIKE ? 
                   OR c.course_description LIKE ? 
                   OR i.instructor_name LIKE ?
                ORDER BY c.course_prefix, c.course_number";
        
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
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';
                
                while ($row = $result->fetch_assoc()) {
                    echo '<tr class="clickable-row" onclick="toggleCourseDetails(' . $row['course_id'] . ')">';
                    echo '<td>' . htmlspecialchars($row['course_code']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['instructor'] ?? 'TBD') . '</td>';
                    echo '</tr>';
                    echo '<tr class="course-details-row" id="details-' . $row['course_id'] . '" style="display: none;">';
                    echo '<td colspan="3">';
                    echo '<div class="course-details-content" id="content-' . $row['course_id'] . '">';
                    echo '<div class="loading">Loading course details...</div>';
                    echo '</div>';
                    echo '</td>';
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

  </div>

  <script>
    function clearForm() {
      document.getElementById('search').value = '';
      window.location.href = window.location.pathname;
    }

    function toggleCourseDetails(courseId) {
      const detailsRow = document.getElementById('details-' + courseId);
      const contentDiv = document.getElementById('content-' + courseId);
      
      if (detailsRow.style.display === 'none') {
        // Show the details row
        detailsRow.style.display = 'table-row';
        
        // Load course details if not already loaded
        if (contentDiv.innerHTML.includes('Loading course details...')) {
          fetch('get_course_details.php?course_id=' + courseId)
            .then(response => response.text())
            .then(data => {
              contentDiv.innerHTML = data;
            })
            .catch(error => {
              contentDiv.innerHTML = '<div class="error-message">Error loading course details.</div>';
            });
        }
      } else {
        // Hide the details row
        detailsRow.style.display = 'none';
      }
    }
  </script>
</body>
</html> 