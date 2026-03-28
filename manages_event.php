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

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];

    $conn->begin_transaction();
    try {
        $delete_reg_stmt = $conn->prepare("DELETE FROM registration_table WHERE Event_id = ?");
        $delete_reg_stmt->bind_param("i", $delete_id);
        $delete_reg_stmt->execute();
        $delete_reg_stmt->close();

        $delete_stmt = $conn->prepare("DELETE FROM event_table WHERE Event_id = ? AND Created_by = ?");
        $delete_stmt->bind_param("ii", $delete_id, $faculty_id);
        $delete_stmt->execute();
        $delete_stmt->close();

        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
    }

    header("Location: manages_event.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['mark_past_id'])) {
    $mark_id = (int)$_POST['mark_past_id'];
    $stmt = $conn->prepare("UPDATE event_table SET Status = 'past' WHERE Event_id = ? AND Created_by = ?");
    $stmt->bind_param("ii", $mark_id, $faculty_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manages_event.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT
        e.Event_id AS event_id,
        e.Title,
        e.Event_date,
        e.event_time,
        e.Status,
        COUNT(r.Registration_id) AS total_registrations
    FROM event_table e
    LEFT JOIN registration_table r ON e.Event_id = r.Event_id
    WHERE e.Created_by = ?
    GROUP BY e.Event_id, e.Title, e.Event_date, e.event_time, e.Status
    ORDER BY e.Event_date DESC, e.event_time DESC
");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
$summary = [
    'active_events' => 0,
    'past_events' => 0,
    'total_registrations' => 0,
    'hot_events' => 0
];

while ($row = $result->fetch_assoc()) {
    $event_timestamp = strtotime($row['Event_date'] . ' ' . $row['event_time']);
    $seconds_left = $event_timestamp - time();
    $days_left = $seconds_left > 0 ? (int)ceil($seconds_left / 86400) : 0;
    $registrations = (int)$row['total_registrations'];
    $is_past = !empty($row['Status']) && $row['Status'] === 'past';

    if ($is_past) {
        $summary['past_events']++;
    } else {
        $summary['active_events']++;
    }

    $summary['total_registrations'] += $registrations;

    if ($registrations >= 15 || ($registrations >= 8 && $days_left >= 3)) {
        $pulse_label = 'Hot';
        $pulse_class = 'hot';
        $pulse_tip = 'Strong demand. Keep logistics ready.';
        $summary['hot_events']++;
    } elseif ($registrations >= 5) {
        $pulse_label = 'Steady';
        $pulse_class = 'steady';
        $pulse_tip = 'Healthy pace. Registration is moving well.';
    } elseif ($days_left <= 2 && $registrations <= 2) {
        $pulse_label = 'Needs Push';
        $pulse_class = 'low';
        $pulse_tip = 'Low traction close to event day. Promote now.';
    } else {
        $pulse_label = 'Watch';
        $pulse_class = 'watch';
        $pulse_tip = 'Moderate interest. Monitor and remind students.';
    }

    $row['pulse_label'] = $pulse_label;
    $row['pulse_class'] = $pulse_class;
    $row['pulse_tip'] = $pulse_tip;
    $row['days_left'] = $days_left;
    $events[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Events - CEMS</title>
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
            <a href="faculty_dashboard.php">Dashboard</a>
            <a href="create_event.php">Create Event</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/manage_hero.jpg');">
    <div class="container">
        <h1>Manage Events</h1>
        <p>Review registrations, edit details, and maintain faculty control.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Active Events</h3>
                <strong><?php echo $summary['active_events']; ?></strong>
                <p>Events still open or upcoming.</p>
            </div>
            <div class="stat-card">
                <h3>Total Registrations</h3>
                <strong><?php echo $summary['total_registrations']; ?></strong>
                <p>Combined registrations across your events.</p>
            </div>
            <div class="stat-card">
                <h3>Hot Events</h3>
                <strong><?php echo $summary['hot_events']; ?></strong>
                <p>Events showing strong student demand.</p>
            </div>
            <div class="stat-card">
                <h3>Past Events</h3>
                <strong><?php echo $summary['past_events']; ?></strong>
                <p>Completed events in your archive.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>Event Name</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Registrations</th>
                    <th>Pulse</th>
                    <th>Faculty Tip</th>
                    <th>Action</th>
                </tr>

                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Title']); ?></td>
                            <td><?php echo htmlspecialchars($row['Event_date']); ?><br><span class="status-note"><?php echo htmlspecialchars($row['event_time']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['Status'] ?: 'current'); ?></td>
                            <td><?php echo (int)$row['total_registrations']; ?></td>
                            <td><span class="status-chip <?php echo htmlspecialchars($row['pulse_class']); ?>"><?php echo htmlspecialchars($row['pulse_label']); ?></span></td>
                            <td>
                                <span class="status-note"><?php echo htmlspecialchars($row['pulse_tip']); ?></span>
                                <?php if ((int)$row['days_left'] > 0 && (empty($row['Status']) || $row['Status'] !== 'past')): ?>
                                    <br><span class="status-note"><?php echo (int)$row['days_left']; ?> day(s) left</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-row">
                                    <a href="event_details.php?id=<?php echo (int)$row['event_id']; ?>" class="btn btn-outline">View</a>
                                    <a href="edit_event.php?id=<?php echo (int)$row['event_id']; ?>" class="btn btn-primary">Edit</a>
                                    <?php if (empty($row['Status']) || $row['Status'] !== 'past'): ?>
                                        <form method="POST">
                                            <input type="hidden" name="mark_past_id" value="<?php echo (int)$row['event_id']; ?>">
                                            <button type="submit" class="btn btn-outline">Mark Past</button>
                                        </form>
                                    <?php endif; ?>
                                    <form method="POST">
                                        <input type="hidden" name="delete_id" value="<?php echo (int)$row['event_id']; ?>">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this event?');">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No events created yet.</td>
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

