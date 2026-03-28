<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$database   = "event_management";

$conn = new mysqli($servername, $username, $password, $database, 3306);

if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function auto_mark_past_events(mysqli $conn): void {
    $conn->query("
        UPDATE event_table
        SET Status = 'past'
        WHERE STR_TO_DATE(CONCAT(Event_date, ' ', event_time), '%Y-%m-%d %H:%i:%s') < NOW()
    ");
}

?>
