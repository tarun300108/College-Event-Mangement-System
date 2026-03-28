<?php
session_start();
include 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header("Location: event.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT
        Event_id AS event_id,
        Title,
        Description,
        Event_date,
        event_time,
        location,
        Category_id AS category_id,
        event_image
    FROM event_table
    WHERE Event_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    header("Location: event.php");
    exit();
}

$category_map = [
    "1" => "Technical",
    "2" => "Cultural",
    "3" => "Sports"
];

$category_label = $category_map[$event['category_id']] ?? "General";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Details - CEMS</title>
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
            <a href="event.php">All Events</a>
            <?php if (isset($_SESSION['student_id'])): ?>
                <a href="student_dashboard.php">Dashboard</a>
                <a href="register_event.php">Register</a>
            <?php elseif (isset($_SESSION['faculty_id'])): ?>
                <a href="faculty_dashboard.php">Dashboard</a>
            <?php else: ?>
                <a href="register_event.php">Register</a>
                <a href="student_login.php">Student Login</a>
            <?php endif; ?>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/event_details_hero.jpg');">
    <div class="container">
        <h1><?php echo htmlspecialchars($event['Title']); ?></h1>
        <p><?php echo htmlspecialchars($category_label); ?> event</p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="card">
            <img src="<?php echo htmlspecialchars($event['event_image'] ?: 'images/event_details_card.jpg'); ?>" alt="Event image">
            <div class="card-body">
                <h3>Event Summary</h3>
                <p><?php echo nl2br(htmlspecialchars($event['Description'])); ?></p>
            </div>
        </div>
        <div class="panel">
            <h2>Details</h2>
            <p><strong>Date:</strong> <?php echo htmlspecialchars($event['Event_date']); ?></p>
            <p><strong>Time:</strong> <?php echo htmlspecialchars($event['event_time']); ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($category_label); ?></p>
            <div class="hero-actions">
                <?php if (isset($_SESSION['student_id'])): ?>
                    <a href="register_event.php" class="btn btn-primary">Go to Registration Page</a>
                <?php elseif (isset($_SESSION['faculty_id'])): ?>
                    <a href="manages_event.php" class="btn btn-primary">Manage Events</a>
                <?php else: ?>
                    <a href="register_event.php" class="btn btn-primary">Login to Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    &copy; 2026 College Event Management System
</footer>

</body>
</html>

