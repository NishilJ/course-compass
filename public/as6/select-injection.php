<?php
include('../db.php');  
include('navbar.php');

$username = $password = "";
$sql = "";
$results = null;
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];


    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $results = $result->fetch_all(MYSQLI_ASSOC);
        $message = null;
    } else {
        $message = "No matching user found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SQL Injection Demo - Login</title>
</head>
<body>
    <h2>Login Form (Vulnerable)</h2>

    <form method="post" action="">
        <label for="username">Username:</label><br>
        <input type="text" name="username" id="username" required value="<?php echo htmlspecialchars($username); ?>"><br><br>

        <label for="password">Password:</label><br>
        <input type="text" name="password" id="password" required value="<?php echo htmlspecialchars($password); ?>"><br><br>

        <input type="submit" value="Login">
    </form>

    <p> <br>
       Username: <code>' OR '1'='1' -- </code><br>
       Password: <code>anything</code>
    </p>

    <?php if ($sql): ?>
        <h3>Executed SQL Query:</h3>
        <pre><?php echo htmlspecialchars($sql); ?></pre>
    <?php endif; ?>

    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($results): ?>
        <h3>Results:</h3>
        <?php foreach ($results as $row): ?>
            ID: <?php echo htmlspecialchars($row['id']); ?> |
            Username: <?php echo htmlspecialchars($row['username']); ?> |
            Password: <?php echo htmlspecialchars($row['password']); ?><br>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>

<?php
$conn->close();
?>
