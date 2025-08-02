<?php
$host = 'localhost';
$user = 'root';
$password = 'root';
$database = 'course_compass'; 

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?> 