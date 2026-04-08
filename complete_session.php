<?php
session_start();
include "db_conn.php";

$request_id = (int)$_POST['request_id'];

// Verify the request exists and is accepted
$check = mysqli_query($conn, "SELECT * FROM requests WHERE request_id='$request_id' AND status='accepted'");

if (mysqli_num_rows($check) === 0) {
    echo "<script>alert('Session not found or already completed.'); window.history.back();</script>";
    exit();
}

// Update status to completed
$sql = "UPDATE requests SET status='completed', updated_at=NOW() WHERE request_id='$request_id'";

if (mysqli_query($conn, $sql)) {
    $is_student = isset($_SESSION['id_no']);
    $redirect = $is_student ? 'Student_dashboard.php' : 'Tdashboard.php';
    echo "<script>alert('✓ Session marked as completed!'); window.location.href='$redirect';</script>";
} else {
    echo "Error: " . mysqli_error($conn);
}
exit();
?>