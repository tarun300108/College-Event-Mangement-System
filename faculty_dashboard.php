<?php
session_start();
include 'db.php';
require_once "auth.php";
auto_login();

if (!isset($_SESSION['faculty_id'])) {
    header("Location: faculty_login.php");
    exit();
}

auto_mark_past_events($conn);

$faculty_id = (int)$_SESSION['faculty_id'];
$summary_stmt = $conn->prepare("
    SELECT
        COUNT(*) AS total_events,
        SUM(CASE WHEN Status = 'past' THEN 1 ELSE 0 END) AS past_events,
        SUM(CASE WHEN Status IS NULL OR Status <> 'past' THEN 1 ELSE 0 END) AS active_events
    FROM event_table
    WHERE Created_by = ?
");
$summary_stmt->bind_param("i", $faculty_id);
$summary_stmt->execute();
$summary = $summary_stmt->get_result()->fetch_assoc();
$summary_stmt->close();

$reg_stmt = $conn->prepare("
    SELECT COUNT(r.Registration_id) AS total_registrations
    FROM event_table e
    LEFT JOIN registration_table r ON e.Event_id = r.Event_id
    WHERE e.Created_by = ?
");
$reg_stmt->bind_param("i", $faculty_id);
$reg_stmt->execute();
$registration_summary = $reg_stmt->get_result()->fetch_assoc();
$reg_stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Dashboard</title>
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
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/faculty_login_hero.jpg');">
    <div class="container">
        <h1>Faculty Dashboard</h1>
        <p>Plan new events, manage registrations, and review outcomes.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Active Events</h3>
                <strong><?php echo (int)($summary['active_events'] ?? 0); ?></strong>
                <p>Upcoming events still open for registrations.</p>
            </div>
            <div class="stat-card">
                <h3>Total Registrations</h3>
                <strong><?php echo (int)($registration_summary['total_registrations'] ?? 0); ?></strong>
                <p>Students registered across all your events.</p>
            </div>
            <div class="stat-card">
                <h3>Past Events</h3>
                <strong><?php echo (int)($summary['past_events'] ?? 0); ?></strong>
                <p>Events already completed and archived.</p>
            </div>
        </div>

        <div class="card-grid">
        <div class="card">
            <img src="images/faculty_dash_create.jpg" alt="Create">
            <div class="card-body">
                <h3>Create Event</h3>
                <p>Publish new events with clear details and deadlines.</p>
                <a href="create_event.php" class="btn btn-primary">Create</a>
            </div>
        </div>
        <div class="card">
            <img src="images/faculty_dash_view.jpg" alt="View">
            <div class="card-body">
                <h3>View All Events</h3>
                <p>Browse current and past events.</p>
                <a href="event.php" class="btn btn-primary">View</a>
            </div>
        </div>
        <div class="card">
            <img src="images/faculty_dash_manage.jpg" alt="Manage">
            <div class="card-body">
                <h3>Manage Registrations</h3>
                <p>Track participation and edit event details.</p>
                <a href="manages_event.php" class="btn btn-primary">Manage</a>
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

