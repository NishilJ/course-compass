<?php include('db.php'); ?>

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
                                p.course_prerequisite,
                                CONCAT(c.course_prefix, ' ', c.course_number) as prereq_code,
                                c.course_description as prereq_title
                              FROM prerequisites p
                              JOIN course c ON p.course_prerequisite = c.course_id
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
                            echo '<li>' . htmlspecialchars($prereq['prereq_code']) . ' - ' . 
                                 htmlspecialchars($prereq['prereq_title']) . '</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                    $prereq_stmt->close();
                }
                ?>
            </div>
            <?php
        } else {
            echo '<div class="error-message">Course not found.</div>';
        }
        $stmt->close();
    } else {
        echo '<div class="error-message">Error retrieving course information.</div>';
    }
} else {
    echo '<div class="error-message">No course ID provided.</div>';
}
?> 