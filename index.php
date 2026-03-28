<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>College Event Management System</title>
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
            <a href="#">Home</a>
            <a href="register_event.php">Register</a>
            <a href="student_login.php">Student</a>
            <a href="faculty_login.php">Faculty</a>
        </nav>
    </div>
</div>

<section class="page-hero" style="--hero: url('images/home_hero.jpg');">
    <div class="container">
        <h1>Run professional campus events with confidence</h1>
        <p>Faculty-led planning, student participation, and clean reporting in a single system built for academic workflows.</p>
        <div class="hero-actions">
            <a class="btn btn-primary" href="faculty_login.php">Faculty Login</a>
            <a class="btn btn-outline" href="faculty_register.php">Faculty Register</a>
            <a class="btn btn-outline" href="student_register.php">Student Register</a>
            <a class="btn btn-outline" href="register_event.php">Register for Events</a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Designed for faculty trust</h2>
        <p class="section-subtitle">Structured data, clear approvals, and no confusion for students or staff.</p>
        <div class="card-grid">
            <div class="card">
                <img src="images/home_card_library.jpg" alt="Planning">
                <div class="card-body">
                    <h3>Structured Planning</h3>
                    <p>Standardized forms ensure quality and completeness.</p>
                </div>
            </div>
            <div class="card">
                <img src="images/home_card_classroom.jpg" alt="Insights">
                <div class="card-body">
                    <h3>Clear Insights</h3>
                    <p>Track attendance and outcomes without manual effort.</p>
                </div>
            </div>
            <div class="card">
                <img src="images/home_card_campus_walk.jpg" alt="Governance">
                <div class="card-body">
                    <h3>Faculty Governance</h3>
                    <p>Oversight and approvals stay transparent and auditable.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container split">
        <div>
            <h2 class="section-title">Every event gets a clear home</h2>
            <p class="section-subtitle">From seminars to festivals, everything stays organized.</p>
            <div class="tag-row">
                <span class="tag">Seminars</span>
                <span class="tag">Workshops</span>
                <span class="tag">Cultural</span>
                <span class="tag">Sports</span>
            </div>
        </div>
        <div class="card">
            <img src="images/home_split_faculty.jpg" alt="Campus event">
            <div class="card-body">
                <h3>Faculty Dashboard</h3>
                <p>Plan, edit, and manage registrations from one place.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Student experience</h2>
        <p class="section-subtitle">Quick registration, clear details, and reminders.</p>
        <div class="card-grid">
            <div class="card">
                <img src="images/home_student_library.jpg" alt="Student activity">
                <div class="card-body">
                    <h3>Fast Sign-up</h3>
                    <p>Students register in seconds with simple forms.</p>
                </div>
            </div>
            <div class="card">
                <img src="images/home_student_gaming.jpg" alt="Clubs">
                <div class="card-body">
                    <h3>Club Events</h3>
                    <p>Keep club activities visible and well-organized.</p>
                </div>
            </div>
            <div class="card">
                <img src="images/home_student_sign.jpg" alt="Sports">
                <div class="card-body">
                    <h3>Sports & Culture</h3>
                    <p>All categories tracked with proper approvals.</p>
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

    
