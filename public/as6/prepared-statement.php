<?php
include('../db.php'); 
include('navbar.php');
$message = "";
$result_data = "";
$executed_sql = ""; 

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $executed_sql = "SELECT * FROM users WHERE username = ? AND password = ?";  

    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "User found! Data:";
        while ($row = $result->fetch_assoc()) {
            $result_data .= "Username: " . htmlspecialchars($row['username']) . "<br>";
            $result_data .= "Password: " . htmlspecialchars($row['password']) . "<br><hr>";
        }
    } else {
        $message = "No matching user found.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Safe Login - Prepared Statements</title>
</head>
<body>
     

    <form method="post" action="">
        <label for="username">Username:</label><br>
        <input type="text" name="username" id="username" required><br><br>

        <label for="password">Password:</label><br>
        <input type="text" name="password" id="password" required><br><br>

        <input type="submit" value="Login">
    </form>

    <?php if ($executed_sql): ?>
        <h3>Executed Prepared Statement:</h3>
        <pre><?php echo htmlspecialchars($executed_sql); ?></pre>
    <?php endif; ?>

    <?php if ($message): ?>
        <p><strong><?php echo htmlspecialchars($message); ?></strong></p>
        <div><?php echo $result_data; ?></div>
    <?php endif; ?>

    <hr>
     
    <p><code>' OR '1'='1</code> in the username or password field will NOT work here!</p>
</body>
</html>

<?php
$conn->close();
?>
