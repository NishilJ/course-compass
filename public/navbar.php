<div class="nav-bar">
    <div class="left-section">
        <span class="title">
            <a href="index.php">Course Compass</a>
        </span>
    </div>
    <div class="logo">
        <img src="assets/images/utd-logo.svg" alt="Logo" class="logo-img">
    </div>
    <div class="right-section">
        <div class="dropdown">
            <div class="icon">
                <i class="dropbtn material-icons">menu</i>
            </div>
            <div class="dropdown-content">
                <a href="index.php">Home</a>
                <a href="admin-dashboard.php">Admin</a>
                <a href="as6/select-injection.php">Assignment 6 Demo</a>
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <a href="?logout=1">Log Out</a>
                <?php endif ?>    
            </div>
        </div>
    </div>
</div>