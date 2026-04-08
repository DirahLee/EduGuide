<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['id_no'])) {
    header("Location: login.php");
    exit();
}

$sid = $_SESSION['id_no'];
$subjects = isset($_POST['subjects']) ? (array)$_POST['subjects'] : [];

if (empty($subjects)) {
    echo "<script>alert('Please select at least one subject!'); window.history.back();</script>";
    exit();
}

$added_count = 0;
$failed_count = 0;

foreach ($subjects as $subject) {
    $subject = mysqli_real_escape_string($conn, htmlspecialchars($subject));
    
    // Check if subject already exists for this student
    $check = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT id FROM student_subjects WHERE student_id='$sid' AND subject='$subject'"));
    
    if (!$check) {
        $insert_sql = "INSERT INTO student_subjects (student_id, subject) VALUES ('$sid', '$subject')";
        if (mysqli_query($conn, $insert_sql)) {
            $added_count++;
        } else {
            $failed_count++;
        }
    }
}

if ($added_count > 0 || $failed_count === 0) {
    if ($added_count > 0) {
        echo "<script>alert('Subjects added successfully! (" . $added_count . " new subjects)'); window.location.href='Student_dashboard.php';</script>";
    } else {
        echo "<script>alert('All selected subjects were already added.'); window.location.href='Student_dashboard.php';</script>";
    }
} else {
    echo "<script>alert('Added $added_count subjects, but $failed_count failed.'); window.location.href='Student_dashboard.php';</script>";
}
exit();
?>