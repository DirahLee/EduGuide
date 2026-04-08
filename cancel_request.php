<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['id_no'])) {
    header("Location: login.php");
    exit();
}

$request_id = (int)$_POST['request_id'];
$sid = $_SESSION['id_no'];

// Verify the request belongs to the student
$check = mysqli_query($conn, "SELECT * FROM requests WHERE request_id='$request_id' AND student_id='$sid' AND status='pending'");

if (mysqli_num_rows($check) === 0) {
    echo "<script>alert('Cannot cancel this request.'); window.history.back();</script>";
    exit();
}

// Delete the request
$sql = "DELETE FROM requests WHERE request_id='$request_id'";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('Request cancelled.'); window.location.href='Student_dashboard.php';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}
exit();
?>