<?php
include('db.php');
include('navbar.php');
$message = '';
$sql = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'], $_POST['username'], $_POST['password'])) {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];


    $sql = "UPDATE users SET username = '$username', password = '$password' WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        $message = "Record updated successfully.";
    } else {
        $message = "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SQL Injection Demo - Update</title>
</head>
<body>
    <h2>Update User Record (Vulnerable)</h2>

    <form method="post" action="">
        <label for="id">User ID to update:</label><br>
        <input type="text" name="id" id="id" required><br><br>

        <label for="username">New Username:</label><br>
        <input type="text" name="username" id="username" required><br><br>

        <label for="password">New Password:</label><br>
        <input type="text" name="password" id="password" required><br><br>

        <input type="submit" value="Update User">
    </form>

    <?php if ($sql): ?>
        <h3>Executed SQL Query:</h3>
        <pre><?php echo htmlspecialchars($sql); ?></pre>
    <?php endif; ?>

    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <p><strong>Try SQL Injection:</strong><br>
       For example, put this in the <em>User ID</em> field:<br>
       <code>1 OR 1=1</code><br>
       This will update all users in the table!</p>
</body>
</html>

<?php
$conn->close();
?>
