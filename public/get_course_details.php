<?php include('db.php'); ?>

<?php
if (isset($_GET['course_id'])) {
    $section_id = $_GET['course_id']; // This is actually the section_id

    // Get detailed course, section, and instructor information by section_id
    $sql = "SELECT 
                c.course_id,
                CONCAT(c.course_prefix, ' ', c.course_number) as course_code,
                c.course_subject,
                c.course_credits,
                c.course_title,
                c.course_description,
                s.section_id,
                s.term,
                s.days,
                s.start_time,
                s.end_time,
                s.location,
                i.instructor_id,
                i.instructor_name,
                i.instructor_email,
                i.instructor_office
            FROM section s
            JOIN course c ON s.course_id = c.course_id
            LEFT JOIN instructor i ON s.instructor_id = i.instructor_id
            WHERE s.section_id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('i', $section_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $course = $result->fetch_assoc();
            ?>
            <div class="course-details">
                <div class="course-header">
                    <h1><?php echo htmlspecialchars($course['course_code']); ?></h1>
                    <p class="course-title"><?php echo htmlspecialchars($course['course_title']); ?></p>
                </div>

                <div class="info-card">
                    <h3>Course Information</h3>
                    <div class="info-item">
                        <?php echo htmlspecialchars($course['course_description']); ?>
                    </div>

                    <div class="info-item">
                        <strong>Subject Area:</strong> <?php echo htmlspecialchars($course['course_subject']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Credit Hours:</strong> <?php echo htmlspecialchars($course['course_credits']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Prerequisites:</strong>
                        <?php
                        // Get prerequisites if any
                        $prereq_sql = "SELECT 
                                    p.course_prerequisite,
                                    CONCAT(c2.course_prefix, ' ', c2.course_number) as prereq_code,
                                    c2.course_title as prereq_title
                                  FROM prerequisite p
                                  JOIN course c2 ON p.course_prerequisite = c2.course_id
                                  WHERE p.course_id = ?";

                        $prereq_stmt = $conn->prepare($prereq_sql);
                        if ($prereq_stmt) {
                            $prereq_stmt->bind_param('i', $course['course_id']);
                            $prereq_stmt->execute();
                            $prereq_result = $prereq_stmt->get_result();

                            if ($prereq_result->num_rows > 0) {
                                $prereqs = [];
                                while ($prereq = $prereq_result->fetch_assoc()) {
                                    $prereqs[] = htmlspecialchars($prereq['prereq_code']) . ' - ' . htmlspecialchars($prereq['prereq_title']);
                                }
                                if (!empty($prereqs)) {
                                    echo implode(', ', $prereqs);
                                }
                            }
                            $prereq_stmt->close();
                        }
                        ?>
                    </div>
                </div>
                <div class="course-info-grid">
                    <div class="info-card">
                        <h3>Section Information</h3>
                        <div class="info-item">
                            <strong>Term:</strong> <?php echo htmlspecialchars($course['term']); ?>
                        </div>
                        <div class="info-item">
                            <strong>Days:</strong> <?php echo htmlspecialchars($course['days']); ?>
                        </div>
                        <div class="info-item">
                            <strong>Time:</strong> 
                            <?php 
                                echo htmlspecialchars(
                                    date("g:i A", strtotime($course['start_time'])) . ' – ' . 
                                    date("g:i A", strtotime($course['end_time']))
                                ); 
                            ?>
                        </div>
                        <div class="info-item">
                            <strong>Location:</strong> <?php echo htmlspecialchars($course['location']); ?>
                        </div>
                    </div>

                    <div class="info-card">
                        <h3>Instructor Information</h3>
                        <?php if (!empty($course['instructor_name'])): ?>
                            <div class="info-item"><strong>Name:</strong> <?php echo htmlspecialchars($course['instructor_name']); ?></div>
                            <div class="info-item"><strong>Email:</strong> <?php echo htmlspecialchars($course['instructor_email'] ?? 'N/A'); ?></div>
                            <div class="info-item"><strong>Office:</strong> <?php echo htmlspecialchars($course['instructor_office'] ?? 'N/A'); ?></div>
                            <?php
                            if (!empty($course['instructor_id'])) {
                                // Average numeric rating
                                $avg_sql = "SELECT 
                                            AVG(rating_number) AS avg_rating,
                                            AVG(
                                                CASE rating_student_grade
                                                    WHEN 'A+' THEN 4.0
                                                    WHEN 'A'  THEN 4.0
                                                    WHEN 'A-' THEN 3.67
                                                    WHEN 'B+' THEN 3.33
                                                    WHEN 'B'  THEN 3.0
                                                    WHEN 'B-' THEN 2.67
                                                    WHEN 'C+' THEN 2.33
                                                    WHEN 'C'  THEN 2.0
                                                    WHEN 'C-' THEN 1.67
                                                    WHEN 'D+' THEN 1.33
                                                    WHEN 'D'  THEN 1.0
                                                    WHEN 'D-' THEN 0.67
                                                    WHEN 'F'  THEN 0.0
                                                    ELSE NULL
                                                END
                                            ) AS avg_gpa
                                        FROM rating 
                                        WHERE instructor_id = ?";
                                $avg_stmt = $conn->prepare($avg_sql);
                                $avg_stmt->bind_param('i', $course['instructor_id']);
                                $avg_stmt->execute();
                                $avg_result = $avg_stmt->get_result();
                                $avg_row = $avg_result->fetch_assoc();
                                $avg_stmt->close();

                                $average_rating = $avg_row['avg_rating'];
                                $average_gpa = $avg_row['avg_gpa'];
                                ?>
                                <div class="info-item">
                                    <strong>Average Rating:</strong>
                                    <?php echo $average_rating !== null ? number_format($average_rating, 2) . ' / 5' : 'N/A'; ?>
                                </div>
                                <div class="info-item">
                                    <strong>Average Student Grade:</strong>
                                    <?php echo $average_gpa !== null ? number_format($average_gpa, 2) . ' GPA' : 'N/A'; ?>
                                </div>
                                <?php
                            }
                            ?>
                            <div class="info-item">
                                <strong>Courses Taught:</strong>
                                <?php
                                // Show courses taught by this instructor (now inside the Instructor Information card)
                                if (!empty($course['instructor_id'])) {
                                    $courses_sql = "SELECT DISTINCT 
                                                        CONCAT(c.course_prefix, ' ', c.course_number) as course_code, 
                                                        c.course_title
                                                    FROM section s
                                                    JOIN course c ON s.course_id = c.course_id
                                                    WHERE s.instructor_id = ?";
                                    $courses_stmt = $conn->prepare($courses_sql);
                                    $courses_stmt->bind_param('i', $course['instructor_id']);
                                    $courses_stmt->execute();
                                    $courses_result = $courses_stmt->get_result();
                                    if ($courses_result->num_rows > 0) {
                                        while ($taught = $courses_result->fetch_assoc()) {
                                            echo htmlspecialchars($taught['course_code']) . ' - ' . htmlspecialchars($taught['course_title']);
                                        }
                                    }
                                    $courses_stmt->close();
                                }
                                ?>
                            </div>
                        <?php else: ?>
                            <div class="info-item">TBD</div>
                        <?php endif; ?>
                    </div>
                </div>
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