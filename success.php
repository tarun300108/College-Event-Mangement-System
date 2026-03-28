<?php
session_start();
include 'db.php';
require_once "auth.php";
auto_login();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

$student_id = (int)$_SESSION['student_id'];
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = "Your request has been completed successfully.";

if ($event_id > 0) {
    $event_check = $conn->prepare("SELECT Event_id FROM event_table WHERE Event_id = ?");
    $event_check->bind_param("i", $event_id);
    $event_check->execute();
    $event_result = $event_check->get_result();

    if ($event_result->num_rows === 1) {
        $check_stmt = $conn->prepare("SELECT Registration_id FROM registration_table WHERE Student_id = ? AND Event_id = ?");
        $check_stmt->bind_param("ii", $student_id, $event_id);
        $check_stmt->execute();
        $existing = $check_stmt->get_result();

        if ($existing->num_rows === 0) {
            $stmt = $conn->prepare("INSERT INTO registration_table (Student_id, Event_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $student_id, $event_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $message = "You are already registered for this event.";
        }

        $check_stmt->close();
    } else {
        $message = "Invalid event selected.";
    }

    $event_check->close();
} else {
    $message = "Missing event id.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Status - CEMS</title>
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
            <a href="register_event.php">Register</a>
            <a href="event.php">All Events</a>
            <a href="student_dashboard.php">Dashboard</a>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/success_hero.jpg');">
    <div class="container">
        <h1>Registration Status</h1>
        <p>Confirmation and next steps.</p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="panel">
            <h2>Status</h2>
            <p><?php echo htmlspecialchars($message); ?></p>
            <div class="hero-actions">
                <a href="register_event.php" class="btn btn-primary">Register More</a>
                <a href="student_dashboard.php" class="btn btn-outline">Dashboard</a>
            </div>
        </div>
        <div class="card">
            <img src="images/success_card.jpg" alt="Student">
            <div class="card-body">
                <h3>Stay Updated</h3>
                <p>Check your dashboard for all registrations.</p>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    &copy; 2026 College Event Management System
</footer>

</body>
</html>

