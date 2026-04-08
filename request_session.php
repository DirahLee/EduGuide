<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['id_no'])) { 
    header("Location: login.php"); 
    exit(); 
}

$sid        = $_SESSION['id_no'];
$tutor_id   = (int)$_POST['tutor_id'];
$subject    = $_POST['subject'] ?? null;

// Validate inputs
if (empty($subject) || empty($tutor_id)) {
    header("Location: Student_dashboard.php");
    exit();
}

// Sanitize
$subject = htmlspecialchars($subject);

// Check for duplicate pending request
$check = mysqli_query($conn, "SELECT * FROM requests 
                             WHERE student_id='$sid' 
                             AND tutor_id='$tutor_id' 
                             AND status='pending'");

if (mysqli_num_rows($check) > 0) { 
    echo "<script>alert('You already have a pending request with this tutor!'); window.location.href='Student_dashboard.php';</script>";
    exit(); 
}

// Check if accepted request already exists
$check_accepted = mysqli_query($conn, "SELECT * FROM requests 
                                       WHERE student_id='$sid' 
                                       AND tutor_id='$tutor_id' 
                                       AND status='accepted'");

if (mysqli_num_rows($check_accepted) > 0) { 
    echo "<script>alert('You already have an accepted session with this tutor!'); window.location.href='Student_dashboard.php';</script>";
    exit(); 
}

// Insert new request
$sql = "INSERT INTO requests (student_id, tutor_id, subject, status, created_at, updated_at) 
        VALUES ('$sid', '$tutor_id', '$subject', 'pending', NOW(), NOW())";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Request sent successfully! Waiting for tutor response.'); window.location.href='Student_dashboard.php';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}
exit();
?>