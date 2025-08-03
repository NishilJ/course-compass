<?php
include('db.php'); 
include('navbar.php');
$message = "";
$executed_sql = "";  // variable to store the executed query

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'], $_POST['old_password'], $_POST['new_password'])) {
    $username = $_POST['username'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Build the vulnerable SQL query (for demo)
    $sql = "UPDATE users SET password = '$new_password' WHERE username = '$username' AND password = '$old_password'";

    // Store the executed query safely for display
    $executed_sql = htmlspecialchars($sql);

    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            $message = "Password updated successfully.";
        } else {
            $message = "No matching user found or old password incorrect.";
        }
    } else {
        $message = "Error updating password: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SQL Injection Demo - Update Password with Old Password</title>
</head>
<body>
    <h2>Update Password Form (Vulnerable)</h2>

    <form method="post" action="">
        <label for="username">Username:</label><br>
        <input type="text" name="username" id="username" required><br><br>

        <label for="old_password">Old Password:</label><br>
        <input type="text" name="old_password" id="old_password" required><br><br>

        <label for="new_password">New Password:</label><br>
        <input type="text" name="new_password" id="new_password" required><br><br>

        <input type="submit" value="Update Password">
    </form>

    <?php if ($executed_sql): ?>
        <h3>Executed SQL Query:</h3>
        <pre><?php echo $executed_sql; ?></pre>
    <?php endif; ?>

    <?php if ($message): ?>
        <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
    <?php endif; ?>

    <hr>
    <h3>Try SQL Injection!</h3>
    <p>For example, try injecting in the <b>old password</b> field to bypass the check:</p>
    <ul>
        <li>Username: <code>1</code></li>
        <li>Old Password: <code>' OR '1'='1</code></li>
        <li>New Password: <code>hacked</code></li>
    </ul>
</body>
</html>

<?php
$conn->close();
?>
