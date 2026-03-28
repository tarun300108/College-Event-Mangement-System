<?php
session_start();
include 'db.php';
require_once "auth.php";
auto_login();

if (!isset($_SESSION['faculty_id'])) {
    header("Location: faculty_login.php");
    exit();
}

$faculty_id = (int)$_SESSION['faculty_id'];
$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($event_id <= 0) {
    header("Location: manages_event.php");
    exit();
}

$msg = "";

function handle_event_image_upload_edit(string $input_name, int $faculty_id, string &$error): ?string {
    if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$input_name]['error'] !== UPLOAD_ERR_OK) {
        $error = "Image upload failed. Please try again.";
        return null;
    }

    $max_size = 5 * 1024 * 1024;
    if ($_FILES[$input_name]['size'] > $max_size) {
        $error = "Image too large. Max size is 5MB.";
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES[$input_name]['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowed[$mime])) {
        $error = "Unsupported image format. Use JPG, PNG, or WEBP.";
        return null;
    }

    $dir = __DIR__ . '/images/events';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $filename = 'event_' . $faculty_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $dest_path = $dir . '/' . $filename;

    if (!move_uploaded_file($_FILES[$input_name]['tmp_name'], $dest_path)) {
        $error = "Unable to save uploaded image.";
        return null;
    }

    return 'images/events/' . $filename;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $upload_error = "";
    $event_image = handle_event_image_upload_edit('event_image', (int)$faculty_id, $upload_error);
    $event_datetime = strtotime($date . ' ' . $time);

    if ($upload_error !== "") {
        $msg = $upload_error;
    } elseif ($title === '' || $date === '' || $time === '' || $location === '' || $category_id <= 0) {
        $msg = "Please fill all required fields.";
    } elseif ($event_datetime === false || $event_datetime <= time()) {
        $msg = "Event date and time must be in the future.";
    } else {
        $deadline = $date;
        if ($event_image) {
            $update_stmt = $conn->prepare("
                UPDATE event_table
                SET Title = ?, Description = ?, Event_date = ?, Registration_deadline = ?, Category_id = ?, event_time = ?, location = ?, event_image = ?
                WHERE Event_id = ? AND Created_by = ?
            ");
            $update_stmt->bind_param(
                "ssssisssii",
                $title,
                $desc,
                $date,
                $deadline,
                $category_id,
                $time,
                $location,
                $event_image,
                $event_id,
                $faculty_id
            );
        } else {
            $update_stmt = $conn->prepare("
                UPDATE event_table
                SET Title = ?, Description = ?, Event_date = ?, Registration_deadline = ?, Category_id = ?, event_time = ?, location = ?
                WHERE Event_id = ? AND Created_by = ?
            ");
            $update_stmt->bind_param(
                "ssssissii",
                $title,
                $desc,
                $date,
                $deadline,
                $category_id,
                $time,
                $location,
                $event_id,
                $faculty_id
            );
        }
        $update_stmt->execute();
        $update_stmt->close();

        header("Location: manages_event.php");
        exit();
    }
}

$event_stmt = $conn->prepare("
    SELECT
        Event_id AS event_id,
        Title,
        Description,
        Event_date,
        event_time,
        location AS location,
        Category_id,
        event_image
    FROM event_table
    WHERE Event_id = ? AND Created_by = ?
");
$event_stmt->bind_param("ii", $event_id, $faculty_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();
$event_stmt->close();

if (!$event) {
    header("Location: manages_event.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Event - CEMS</title>
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
            <a href="manages_event.php">Manage Events</a>
            <a href="faculty_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/edit_hero.jpg');">
    <div class="container">
        <h1>Edit Event</h1>
        <p>Update details while maintaining faculty oversight.</p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="panel">
            <h2>Update Details</h2>
            <?php if ($msg !== ""): ?>
                <div class="alert"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <form method="POST" class="form-grid" enctype="multipart/form-data">
                <label>Event Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($event['Title']); ?>" required>

                <label>Description</label>
                <textarea name="description" rows="4"><?php echo htmlspecialchars($event['Description']); ?></textarea>

                <label>Date</label>
                <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo htmlspecialchars($event['Event_date']); ?>" required>

                <label>Time</label>
                <input type="time" name="time" value="<?php echo htmlspecialchars($event['event_time']); ?>" required>

                <label>Location</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>

                <label>Category</label>
                <select name="category_id" required>
                    <option value="1" <?php echo ((int)$event['Category_id'] === 1) ? 'selected' : ''; ?>>Technical</option>
                    <option value="2" <?php echo ((int)$event['Category_id'] === 2) ? 'selected' : ''; ?>>Cultural</option>
                    <option value="3" <?php echo ((int)$event['Category_id'] === 3) ? 'selected' : ''; ?>>Sports</option>
                </select>

                <label>Event Image (optional)</label>
                <input type="file" name="event_image" accept="image/png,image/jpeg,image/webp">

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
        <div class="card">
            <img src="<?php echo htmlspecialchars($event['event_image'] ?: 'images/edit_card.jpg'); ?>" alt="Event">
            <div class="card-body">
                <h3>Stay Consistent</h3>
                <p>Keep information aligned with faculty approvals.</p>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    &copy; 2026 College Event Management System
</footer>

</body>
</html>

