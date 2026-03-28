<?php
session_start();
include 'db.php';
require_once "auth.php";
auto_login();

auto_mark_past_events($conn);

$student_id = isset($_SESSION['student_id']) ? (int)$_SESSION['student_id'] : 0;
$is_student_logged_in = $student_id > 0;

if ($is_student_logged_in) {
    $stmt = $conn->prepare("
        SELECT
            e.Event_id AS event_id,
            e.Title,
            e.Description,
            e.Event_date,
            e.event_time,
            e.location,
            e.event_image,
            CASE WHEN r.Registration_id IS NULL THEN 0 ELSE 1 END AS is_registered
        FROM event_table e
        LEFT JOIN registration_table r
            ON e.Event_id = r.Event_id
           AND r.Student_id = ?
        WHERE STR_TO_DATE(CONCAT(e.Event_date, ' ', e.event_time), '%Y-%m-%d %H:%i:%s') >= NOW()
          AND (e.Status IS NULL OR e.Status <> 'past')
        ORDER BY e.Event_date ASC, e.event_time ASC
    ");
    $stmt->bind_param("i", $student_id);
} else {
    $stmt = $conn->prepare("
        SELECT
            e.Event_id AS event_id,
            e.Title,
            e.Description,
            e.Event_date,
            e.event_time,
            e.location,
            e.event_image,
            0 AS is_registered
        FROM event_table e
        WHERE STR_TO_DATE(CONCAT(e.Event_date, ' ', e.event_time), '%Y-%m-%d %H:%i:%s') >= NOW()
          AND (e.Status IS NULL OR e.Status <> 'past')
        ORDER BY e.Event_date ASC, e.event_time ASC
    ");
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register for Events - CEMS</title>
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
            <?php if ($is_student_logged_in): ?>
                <a href="student_dashboard.php">Dashboard</a>
                <a href="my_registrations.php">My Registrations</a>
            <?php elseif (isset($_SESSION['faculty_id'])): ?>
                <a href="faculty_dashboard.php">Dashboard</a>
            <?php else: ?>
                <a href="student_login.php">Student Login</a>
            <?php endif; ?>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/event_current.jpg');">
    <div class="container">
        <h1>Register for Events</h1>
        <p>Choose from the active events below and register directly.</p>
        <?php if (!$is_student_logged_in): ?>
            <div class="hero-actions">
                <a href="student_login.php" class="btn btn-primary">Login to Register</a>
                <a href="student_register.php" class="btn btn-outline">Create Student Account</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Active Events</h2>
        <p class="section-subtitle">Only upcoming events are shown here.</p>

        <div class="card-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($row['event_image'] ?: 'images/event_current.jpg'); ?>" alt="Event">
                        <div class="card-body">
                            <h3><?php echo htmlspecialchars($row['Title']); ?></h3>
                            <p><?php echo htmlspecialchars($row['Description']); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($row['Event_date']); ?></p>
                            <p><strong>Time:</strong> <?php echo htmlspecialchars($row['event_time']); ?></p>
                            <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                            <div class="hero-actions">
                                <a href="event_details.php?id=<?php echo (int)$row['event_id']; ?>" class="btn btn-outline-dark">Details</a>
                                <?php if ($is_student_logged_in && (int)$row['is_registered'] === 1): ?>
                                    <span class="btn btn-muted">Registered</span>
                                <?php elseif ($is_student_logged_in): ?>
                                    <a href="success.php?id=<?php echo (int)$row['event_id']; ?>" class="btn btn-primary">Register Now</a>
                                <?php else: ?>
                                    <a href="student_login.php" class="btn btn-primary">Login to Register</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No active events are available for registration right now.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<footer class="footer">
    &copy; 2026 College Event Management System
</footer>

</body>
</html>
