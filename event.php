<?php
session_start();
include 'db.php';

auto_mark_past_events($conn);

$current_stmt = $conn->prepare("
    SELECT
        Event_id AS event_id,
        Title,
        Description,
        Event_date,
        event_time,
        location,
        Category_id,
        event_image
    FROM event_table
    WHERE STR_TO_DATE(CONCAT(Event_date, ' ', event_time), '%Y-%m-%d %H:%i:%s') >= NOW()
      AND (Status IS NULL OR Status <> 'past')
    ORDER BY Event_date ASC, event_time ASC
");
$current_stmt->execute();
$current_result = $current_stmt->get_result();

$past_stmt = $conn->prepare("
    SELECT
        Event_id AS event_id,
        Title,
        Description,
        Event_date,
        event_time,
        location,
        Category_id,
        event_image
    FROM event_table
    WHERE STR_TO_DATE(CONCAT(Event_date, ' ', event_time), '%Y-%m-%d %H:%i:%s') < NOW()
       OR Status = 'past'
    ORDER BY Event_date DESC, event_time DESC
");
$past_stmt->execute();
$past_result = $past_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Events - CEMS</title>
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

<section class="page-hero" style="--hero: url('images/event_current.jpg');">
    <div class="container">
        <h1>Event Catalog</h1>
        <p>Browse current and past events with full details.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Current Events</h2>
        <p class="section-subtitle">Active events open for participation.</p>
        <div class="card-grid">
            <?php if ($current_result->num_rows > 0): ?>
                <?php while ($row = $current_result->fetch_assoc()): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($row['event_image'] ?: 'images/event_current.jpg'); ?>" alt="Event">
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
                            <p><?php echo htmlspecialchars($row['Description']); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($row['Event_date']); ?></p>
                            <p><strong>Time:</strong> <?php echo htmlspecialchars($row['event_time']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <a href="event_details.php?id=<?php echo (int)$row['event_id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No current events available.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Past Events</h2>
        <p class="section-subtitle">Archive of completed events.</p>
        <div class="card-grid">
            <?php if ($past_result->num_rows > 0): ?>
                <?php while ($row = $past_result->fetch_assoc()): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($row['event_image'] ?: 'images/event_past.jpg'); ?>" alt="Event">
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
                            <p><?php echo htmlspecialchars($row['Description']); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($row['Event_date']); ?></p>
                            <p><strong>Time:</strong> <?php echo htmlspecialchars($row['event_time']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <a href="event_details.php?id=<?php echo (int)$row['event_id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No past events available.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<footer class="footer">
    &copy; 2026 College Event Management System
</footer>

</body>
</html>

