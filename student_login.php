<?php
session_start();
require_once "db.php";
require_once "auth.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT Student_id AS student_id, name, password FROM student_table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {

        $student = $result->fetch_assoc();

        if (password_verify($password, $student['password'])) {

            $_SESSION['student_id'] = $student['student_id'];
            $_SESSION['student_name'] = $student['name'];
            if (!empty($_POST['remember_me'])) {
                create_remember_token($conn, 'student', (int)$student['student_id']);
            }

            header("Location: student_dashboard.php");
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
    <title>Student Login - CEMS</title>
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
            <a href="register_event.php">Register</a>
            <a href="student_register.php">Student Register</a>
            <a href="student_login.php">Student</a>
            <a href="faculty_login.php">Faculty</a>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/student_login_hero.jpg');">
    <div class="container">
        <h1>Student Login</h1>
        <p>Register for events and keep track of your schedule.</p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="panel">
            <h2>Login</h2>
            <p class="section-subtitle">Use your student email address. New students should create an account first.</p>
            <?php if ($error != "") { ?>
                <div class="alert"><?php echo $error; ?></div>
            <?php } ?>
            <form method="POST" class="form-grid">
                <label>Email</label>
                <input type="email" name="email" placeholder="student@college.edu" required>
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter password" required>
                <label class="checkbox-row"><input type="checkbox" name="remember_me"> Remember me</label>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            <p class="section-subtitle">New student? <a href="student_register.php">Create student account</a></p>
        </div>
        <div class="card">
            <img src="images/student_login_card.jpg" alt="Students">
            <div class="card-body">
                <h3>Student Experience</h3>
                <p>Fast registration and clear event details.</p>
                <div class="hero-actions">
                    <a href="student_register.php" class="btn btn-primary">Create Account</a>
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

