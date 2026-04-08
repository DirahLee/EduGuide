<?php
// This file is kept for backwards compatibility but
// session_chat.php now handles AJAX sends internally.
// Redirecting to safety.
session_start();
if (isset($_SESSION['id_no'])) header("Location: Student_dashboard.php");
elseif (isset($_SESSION['tutor_id'])) header("Location: Tdashboard.php");
else header("Location: index.php");
exit();
?>