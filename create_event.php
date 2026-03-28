<?php
session_start();
include 'db.php';
require_once "auth.php";
auto_login();

if (!isset($_SESSION['faculty_id'])) {
    header("Location: faculty_login.php");
    exit();
}

$faculty_id = $_SESSION['faculty_id'];
$message = "";

$categories = [];
$cat_result = $conn->query("SELECT Category_id, Category_name FROM category_table ORDER BY Category_name ASC");
if ($cat_result) {
    while ($cat = $cat_result->fetch_assoc()) {
        $categories[] = $cat;
    }
}

function handle_event_image_upload(string $input_name, int $faculty_id, string &$error): ?string {
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $upload_error = "";
    $event_image = handle_event_image_upload('event_image', (int)$faculty_id, $upload_error);

    $deadline = $date;
    $status = "active";
    $event_datetime = strtotime($date . ' ' . $time);

    if ($upload_error !== "") {
        $message = $upload_error;
    } elseif ($title === "" || $date === "" || $time === "" || $location === "" || $category_id <= 0) {
        $message = "Please fill all required fields.";
    } elseif ($event_datetime === false || $event_datetime <= time()) {
        $message = "Event date and time must be in the future.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO event_table 
                (Title, Description, Event_date, Registration_deadline, Category_id, Created_by, Status, event_time, location, event_image) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmt->bind_param("ssssiissss",
                $title,
                $desc,
                $date,
                $deadline,
                $category_id,
                $faculty_id,
                $status,
                $time,
                $location,
                $event_image
            );

            $stmt->execute();
            $stmt->close();

            header("Location: faculty_dashboard.php");
            exit();
        } catch (Throwable $e) {
            $message = "Unable to create event. Please check your category and account data.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Event</title>
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
            <a href="manages_event.php">Manage Events</a>
            <a href="logout.php">Logout</a>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/create_hero.jpg');">
    <div class="container">
        <h1>Create a New Event</h1>
        <p>Publish faculty-approved events with clear details and deadlines.</p>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div class="panel">
            <h2>Event Details</h2>
            <?php if ($message !== ""): ?>
                <div class="alert"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" class="form-grid" enctype="multipart/form-data">
                <label>Event Title</label>
                <input type="text" name="title" placeholder="Enter event title" required>

                <label>Description</label>
                <textarea name="description" rows="4" placeholder="Enter event description"></textarea>

                <label>Date</label>
                <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>

                <label>Time</label>
                <input type="time" name="time" required>

                <label>Location</label>
                <input type="text" name="location" placeholder="Enter event location" required>

                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int)$cat['Category_id']; ?>">
                            <?php echo htmlspecialchars($cat['Category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Event Image</label>
                <input type="file" name="event_image" accept="image/png,image/jpeg,image/webp">

                <button type="submit" class="btn btn-primary">Create Event</button>
            </form>
        </div>
        <div class="card">
            <img src="images/create_card.jpg" alt="Planning">
            <div class="card-body">
                <h3>Faculty Checklist</h3>
                <p>Confirm venue, capacity, and category before publishing.</p>
                <div class="tag-row">
                    <span class="tag">Venue</span>
                    <span class="tag">Capacity</span>
                    <span class="tag">Deadline</span>
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

