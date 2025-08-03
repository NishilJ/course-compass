<?php
session_start();

// If already logged in as admin, redirect to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin-dashboard.php');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Simple admin credentials (in production, use hashed passwords from database)
    $admin_username = 'admin';
    $admin_password = 'admin123'; // Change this to a secure password

    if ($username === $admin_username && $password === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: admin-dashboard.php');
        exit();
    } else {
        $error_message = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Course Compass</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(159, 66, 0, 0.1);
        }

        .form-group input.invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.25);
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: 2px solid var(--primary-color);
            border-radius: 4px;
            font-size: 16px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .login-btn:hover {
            background-color: var(--primary-hover);
        }

        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
            border-left: 4px solid #c62828;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        /* Ensure dropdown works properly */
        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
</head>

<body>
    <?php include 'navbar.php'; ?>

    <div class="login-container">
        <div class="login-header">
            <h2>Admin Login</h2>
            <p>Please enter your administrator credentials</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <i class="material-icons" style="vertical-align: middle; margin-right: 8px;">error</i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required maxlength="50"
                    placeholder="Enter admin username" pattern="[A-Za-z0-9_]+"
                    title="Only letters, numbers, and underscores allowed"
                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6" maxlength="100"
                    placeholder="Enter admin password" title="Password must be at least 6 characters long">
            </div>

            <button type="submit" class="login-btn">Login</button>
        </form>

        <script>
            // Real-time validation for username
            document.getElementById('username').addEventListener('input', function(e) {
                // Allow only letters, numbers, and underscores
                this.value = this.value.replace(/[^A-Za-z0-9_]/g, '');

                // Visual validation
                if (this.value.length >= 3) {
                    this.classList.remove('invalid');
                } else if (this.value.length > 0) {
                    this.classList.add('invalid');
                } else {
                    this.classList.remove('invalid');
                }
            });

            // Real-time validation for password
            document.getElementById('password').addEventListener('input', function(e) {
                // Visual validation
                if (this.value.length >= 6) {
                    this.classList.remove('invalid');
                } else if (this.value.length > 0) {
                    this.classList.add('invalid');
                } else {
                    this.classList.remove('invalid');
                }
            });

            // Form validation before submit
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                const username = document.getElementById('username');
                const password = document.getElementById('password');
                let hasErrors = false;

                // Reset validation classes
                username.classList.remove('invalid');
                password.classList.remove('invalid');

                if (username.value.length < 3) {
                    username.classList.add('invalid');
                    if (!hasErrors) {
                        alert('Username must be at least 3 characters long');
                        username.focus();
                    }
                    hasErrors = true;
                }

                if (password.value.length < 6) {
                    password.classList.add('invalid');
                    if (!hasErrors) {
                        alert('Password must be at least 6 characters long');
                        password.focus();
                    }
                    hasErrors = true;
                }

                if (hasErrors) {
                    e.preventDefault();
                    return false;
                }
            });

            // Highlight fields with errors on page load if there are server-side errors
            <?php if (!empty($error_message)): ?>
                document.addEventListener('DOMContentLoaded', function() {
                    document.getElementById('username').classList.add('invalid');
                    document.getElementById('password').classList.add('invalid');
                });
            <?php endif; ?>
        </script>

        <div class="back-link">
            <a href="index.php">← Back to Course Search</a>
        </div>
    </div>
</body>

</html>