<?php
session_start();
require_once "db.php";
require_once "auth.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT faculty_id, name, password FROM faculty_table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $faculty = $result->fetch_assoc();

        if (password_verify($password, $faculty['password'])) {

            $_SESSION['faculty_id'] = $faculty['faculty_id'];
            $_SESSION['faculty_name'] = $faculty['name'];
            if (!empty($_POST['remember_me'])) {
                create_remember_token($conn, 'faculty', (int)$faculty['faculty_id']);
            }

            header("Location: faculty_dashboard.php");
            exit();
        } else {
            $error = "Invalid Email or Password!";
        }
    } else {
        $error = "Invalid Email or Password!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Login - CEMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="topbar">
    <div class="container topbar-inner">
        <div class="brand">
            <div class="brand-badge">CE</div>
            <span>College Events</span>
        </div>
        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="faculty_register.php">Faculty Register</a>
            <a href="student_login.php">Student</a>
            <a href="faculty_login.php">Faculty</a>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/faculty_login_hero.jpg');">
    <div class="container">
        <h1>Faculty Access</h1>
        <p>Secure approvals and manage registrations with complete visibility.</p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="panel">
            <h2>Login</h2>
            <p class="section-subtitle">Use your faculty credentials. New faculty should create an account first.</p>
            <?php if (!empty($error)): ?>
                <div class="alert"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" class="form-grid">
                <label>Email</label>
                <input type="email" name="email" placeholder="faculty@college.edu" required>
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
                <label class="checkbox-row"><input type="checkbox" name="remember_me"> Remember me</label>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <p class="section-subtitle">New faculty? <a href="faculty_register.php">Create faculty account</a></p>
        </div>
        <div class="card">
            <img src="images/faculty_login_card.jpg" alt="Faculty">
            <div class="card-body">
                <h3>Faculty Controls</h3>
                <p>Create events, track registrations, and review outcomes.</p>
                <div class="hero-actions">
                    <a href="faculty_register.php" class="btn btn-primary">Create Account</a>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    &copy; 2026 College Event Management System
</footer>

</body>
</html>

