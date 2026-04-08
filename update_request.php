<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['tutor_id'])) { 
    header("Location: Tutlogin.php"); 
    exit(); 
}

$request_id = (int)$_POST['request_id'];
$action     = $_POST['action'] ?? null;

// ✅ Validate action
if (!in_array($action, ['accepted', 'rejected'])) { 
    header("Location: Tdashboard.php"); 
    exit(); 
}

// ✅ Verify request belongs to this tutor
$check = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT tutor_id FROM requests WHERE request_id='$request_id'"));

if (!$check || $check['tutor_id'] != $_SESSION['tutor_id']) {
    header("Location: Tdashboard.php");
    exit();
}

// ✅ Update request status
$update_sql = "UPDATE requests SET status='$action', updated_at=NOW() WHERE request_id='$request_id'";

if (mysqli_query($conn, $update_sql)) {
    if ($action === 'accepted') {
        echo "<script>alert('✅ Request accepted! The student can now enter the session. You can view active sessions above.'); window.location.href='Tdashboard.php';</script>";
    } else {
        echo "<script>alert('❌ Request rejected.'); window.location.href='Tdashboard.php';</script>";
    }
} else {
    echo "Error updating request: " . mysqli_error($conn);
}
exit();
?>