<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Course Compass - Search Courses</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      background-color: #f5f5f5;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    h1 {
      color: #333;
      text-align: center;
      margin-bottom: 30px;
    }
    .search-section {
      background-color: #f9f9f9;
      padding: 20px;
      border-radius: 5px;
      margin-bottom: 30px;
    }
    .search-row {
      display: flex;
      gap: 20px;
      margin-bottom: 15px;
      align-items: center;
    }
    label {
      font-weight: bold;
      min-width: 120px;
    }
    input[type="text"] {
      flex: 1;
      padding: 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    button {
      background-color: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
    }
    button:hover {
      background-color: #0056b3;
    }
    .clear-btn {
      background-color: #6c757d;
      margin-left: 10px;
    }
    .clear-btn:hover {
      background-color: #545b62;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #ddd;
      padding: 12px;
      text-align: left;
    }
    th {
      background-color: #007bff;
      color: white;
      font-weight: bold;
    }
    tr:nth-child(even) {
      background-color: #f2f2f2;
    }
    tr:hover {
      background-color: #e9e9e9;
    }
    .no-results {
      text-align: center;
      padding: 20px;
      color: #666;
      font-style: italic;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>Course Compass</h1>
    
    <div class="search-section">
      <h2>Search Courses</h2>
      <form method="GET" action="">
        <div class="search-row">
          <label for="search">Search:</label>
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
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['course_code']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['instructor'] ?? 'TBD') . '</td>';
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