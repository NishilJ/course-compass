<?php include('db.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Details - Course Compass</title>
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
    <?php
    if (isset($_GET['course_id'])) {
        $course_id = $_GET['course_id'];
        
        // Get detailed course information
        $sql = "SELECT 
                    c.course_id,
                    c.course_prefix,
                    c.course_number,
                    c.course_description,
                    c.course_credits,
                    c.course_subject,
                    GROUP_CONCAT(DISTINCT i.instructor_name SEPARATOR ', ') as instructors,
                    GROUP_CONCAT(DISTINCT s.section_id SEPARATOR ', ') as sections,
                    COUNT(DISTINCT s.section_id) as section_count
                FROM course c
                LEFT JOIN section s ON c.course_id = s.course_id
                LEFT JOIN instructor i ON s.instructor_id = i.instructor_id
                WHERE c.course_id = ?
                GROUP BY c.course_id";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $course_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $course = $result->fetch_assoc();
                ?>
                <div class="course-details">
                    <div class="course-header">
                        <h1><?php echo htmlspecialchars($course['course_prefix'] . ' ' . $course['course_number']); ?></h1>
                        <p class="course-title"><?php echo htmlspecialchars($course['course_description']); ?></p>
                    </div>
                    
                    <div class="course-info-grid">
                        <div class="info-card">
                            <h3>Course Information</h3>
                            <div class="info-item">
                                <strong>Subject:</strong> <?php echo htmlspecialchars($course['course_subject']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Credit Hours:</strong> <?php echo htmlspecialchars($course['course_credits']); ?>
                            </div>
                            <div class="info-item">
                                <strong>Course ID:</strong> <?php echo htmlspecialchars($course['course_id']); ?>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <h3>Teaching Information</h3>
                            <div class="info-item">
                                <strong>Instructors:</strong> 
                                <?php echo htmlspecialchars($course['instructors'] ?: 'TBD'); ?>
                            </div>
                            <div class="info-item">
                                <strong>Available Sections:</strong> <?php echo htmlspecialchars($course['section_count']); ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php
                    // Get prerequisites if any
                    $prereq_sql = "SELECT 
                                    p.prerequisite_course_id,
                                    CONCAT(c.course_prefix, ' ', c.course_number) as prereq_code,
                                    c.course_description as prereq_title
                                  FROM prerequisites p
                                  JOIN course c ON p.prerequisite_course_id = c.course_id
                                  WHERE p.course_id = ?";
                    
                    $prereq_stmt = $conn->prepare($prereq_sql);
                    if ($prereq_stmt) {
                        $prereq_stmt->bind_param('i', $course_id);
                        $prereq_stmt->execute();
                        $prereq_result = $prereq_stmt->get_result();
                        
                        if ($prereq_result->num_rows > 0) {
                            echo '<div class="info-card">';
                            echo '<h3>Prerequisites</h3>';
                            echo '<ul class="prereq-list">';
                            while ($prereq = $prereq_result->fetch_assoc()) {
                                echo '<li><a href="course_details.php?course_id=' . $prereq['prerequisite_course_id'] . '">' . 
                                     htmlspecialchars($prereq['prereq_code']) . ' - ' . 
                                     htmlspecialchars($prereq['prereq_title']) . '</a></li>';
                            }
                            echo '</ul>';
                            echo '</div>';
                        }
                        $prereq_stmt->close();
                    }
                    ?>
                    
                    <div class="back-link">
                        <a href="index.php" class="btn-secondary">← Back to Search</a>
                    </div>
                </div>
                <?php
            } else {
                echo '<div class="error-message">Course not found.</div>';
                echo '<div class="back-link"><a href="index.php" class="btn-secondary">← Back to Search</a></div>';
            }
            $stmt->close();
        } else {
            echo '<div class="error-message">Error retrieving course information.</div>';
            echo '<div class="back-link"><a href="index.php" class="btn-secondary">← Back to Search</a></div>';
        }
    } else {
        echo '<div class="error-message">No course ID provided.</div>';
        echo '<div class="back-link"><a href="index.php" class="btn-secondary">← Back to Search</a></div>';
    }
    ?>
</div>
</body>
</html> 