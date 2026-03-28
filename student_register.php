<?php
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($name) || empty($email) || empty($password)) {
        $message = "All fields are required!";
    } else {

        $check = $conn->prepare("SELECT student_id FROM student_table WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Email already registered!";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO student_table (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $message = "Student Registration Successful!";
            } else {
                $message = "Something went wrong!";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Register - CEMS</title>
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

<section class="page-hero" style="--hero: url('images/student_register_hero.jpg');">
    <div class="container">
        <h1>Student Registration</h1>
        <p>Create your account to register for events.</p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="panel">
            <h2>Register</h2>
            <?php if($message != "") { ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php } ?>

            <form method="POST" class="form-grid">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter Name" required>
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter Email" required>
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter Password" required>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>

            <p class="section-subtitle">Already have an account? <a href="student_login.php">Login</a></p>
        </div>
        <div class="card">
            <img src="images/student_register_card.jpg" alt="Students">
            <div class="card-body">
                <h3>Student Access</h3>
                <p>Join events and stay on schedule.</p>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    &copy; 2026 College Event Management System
</footer>

</body>
</html>

