<?php
session_start();
require_once "auth.php";
auto_login();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
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
            <a href="my_registrations.php">My Registrations</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/student_login_hero.jpg');">
    <div class="container">
        <h1>Student Dashboard</h1>
        <p>Discover events, register, and track your participation.</p>
    </div>
</section>

<section class="section">
    <div class="container card-grid">
        <div class="card">
            <img src="images/student_dash_events.jpg" alt="Events">
            <div class="card-body">
                <h3>Register for Events</h3>
                <p>Browse active events and complete registration from one page.</p>
                <a href="register_event.php" class="btn btn-primary">Register</a>
            </div>
        </div>
        <div class="card">
            <img src="images/student_dash_regs.jpg" alt="Registrations">
            <div class="card-body">
                <h3>My Registrations</h3>
                <p>See events you have registered for.</p>
                <a href="my_registrations.php" class="btn btn-primary">View</a>
            </div>
        </div>
        <div class="card">
            <img src="images/student_dash_profile.jpg" alt="Profile">
            <div class="card-body">
                <h3>Profile</h3>
                <p>Student profile settings coming soon.</p>
                <a href="student_dashboard.php" class="btn btn-primary">Refresh</a>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    &copy; 2026 College Event Management System
</footer>

</body>
</html>

