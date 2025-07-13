<?php include('../src/db.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Course Compass</title>
</head>
<body>
    <h1>Instructors</h1>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Office</th>
            <th>Department</th>
        </tr>
        <?php
        $sql = "SELECT * FROM instructor";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['instructor_id']}</td>
                    <td>{$row['instructor_name']}</td>
                    <td>{$row['instructor_email']}</td>
                    <td>{$row['instructor_office']}</td>
                    <td>{$row['instructor_dep']}</td>
                  </tr>";
        }
        ?>
    </table>

    <h1>Courses</h1>
    <table border="1">
        <tr>
            <th>Course Code</th>
            <th>Title</th>
            <th>Credits</th>
            <th>Subject</th>
        </tr>
        <?php
        $sql = "SELECT * FROM course";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
            $code = $row['course_prefix'] . $row['course_number'];
            echo "<tr>
                    <td>{$code}</td>
                    <td>{$row['course_description']}</td>
                    <td>{$row['course_credits']}</td>
                    <td>{$row['course_subject']}</td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>
