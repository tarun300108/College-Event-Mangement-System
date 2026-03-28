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

$stmt = $conn->prepare("
    SELECT
        e.Event_id AS event_id,
        e.Title,
        e.Event_date,
        e.event_time,
        e.location AS location
    FROM registration_table r
    INNER JOIN event_table e ON r.Event_id = e.Event_id
    WHERE r.Student_id = ?
    ORDER BY e.Event_date ASC, e.event_time ASC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Registrations - CEMS</title>
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
            <a href="student_dashboard.php">Dashboard</a>
            <a href="register_event.php">Register</a>
            <a href="event.php">All Events</a>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/my_registrations_hero.jpg');">
    <div class="container">
        <h1>My Registrations</h1>
        <p>Your confirmed event participation list.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="table-wrap">
            <table>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Details</th>
                </tr>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Title']); ?></td>
                            <td><?php echo htmlspecialchars($row['Event_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['event_time']); ?></td>
                            <td><?php echo htmlspecialchars($row['location']); ?></td>
                            <td><a href="event_details.php?id=<?php echo (int)$row['event_id']; ?>" class="btn btn-primary">View</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">You have not registered for any events yet.</td>
                    </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</section>

<footer class="footer">
    &copy; 2026 College Event Management System
</footer>

</body>
</html>

