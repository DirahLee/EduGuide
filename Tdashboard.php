<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['tutor_id'])) { header("Location: Tutlogin.php"); exit(); }

$tutor_id   = $_SESSION['tutor_id'];
$tutor_name = $_SESSION['tutor_name'];

// Pending requests
$pending_sql = "SELECT r.request_id, s.fullname, s.subject, r.created_at
                FROM requests r JOIN students s ON r.student_id = s.id_no
                WHERE r.tutor_id='$tutor_id' AND r.status='pending'
                ORDER BY r.created_at DESC";
$pending = mysqli_query($conn, $pending_sql);

// Active (accepted) sessions
$active_sql = "SELECT r.request_id, s.fullname, s.subject, s.id_no
               FROM requests r JOIN students s ON r.student_id = s.id_no
               WHERE r.tutor_id='$tutor_id' AND r.status='accepted'
               ORDER BY r.request_id DESC";
$active = mysqli_query($conn, $active_sql);

// ALL completed sessions (no limit)
$done_sql = "SELECT r.request_id, s.fullname, s.id_no AS student_id, s.subject, r.updated_at
             FROM requests r JOIN students s ON r.student_id = s.id_no
             WHERE r.tutor_id='$tutor_id' AND r.status='completed'
             ORDER BY r.updated_at DESC";
$done = mysqli_query($conn, $done_sql);

$count_pending   = mysqli_num_rows($pending);
$count_active    = mysqli_num_rows($active);
$count_completed = mysqli_num_rows($done);

// Reset pointers
mysqli_data_seek($pending, 0);
mysqli_data_seek($active, 0);
mysqli_data_seek($done, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Tutor Dashboard – EduGuide</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #f0f4f9; --white: #fff; --orange: #e67e22; --orange-dark: #ca6f1e;
            --orange-soft: #fef3e2; --border: #dde3ee; --text: #1a2235; --muted: #6b7c99;
            --green: #16a34a; --green-soft: #f0fdf4; --red: #dc2626; --red-soft: #fef2f2;
            --blue: #2563eb; --blue-soft: #eff6ff;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        /* HEADER */
        header { background: var(--white); border-bottom: 1px solid var(--border); height: 62px; display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 6px rgba(0,0,0,0.04); }
        header h1 { font-family: 'Fraunces', serif; font-size: 1.4rem; color: var(--orange); font-weight: 700; }
        header nav { display: flex; align-items: center; gap: 0.5rem; }
        header nav span { font-size: 0.85rem; font-weight: 600; color: var(--text); background: var(--bg); border: 1px solid var(--border); padding: 6px 14px; border-radius: 8px; }
        header nav a.logout-btn { font-size: 0.82rem; font-weight: 600; color: var(--red); background: var(--red-soft); border: 1px solid #fecaca; padding: 6px 14px; border-radius: 8px; text-decoration: none; transition: background 0.18s; }
        header nav a.logout-btn:hover { background: #fee2e2; }

        .container { max-width: 1050px; margin: 0 auto; padding: 2rem 1.5rem; display: flex; flex-direction: column; gap: 2rem; }

        /* PAGE TITLE */
        .page-header { display: flex; flex-direction: column; gap: 0.2rem; }
        .page-title { font-family: 'Fraunces', serif; font-size: 1.5rem; color: var(--text); }
        .page-sub { font-size: 0.88rem; color: var(--muted); }

        /* STATS */
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
        .stat-card { background: var(--white); border: 1px solid var(--border); border-radius: 14px; padding: 1.2rem 1.4rem; display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .stat-icon.orange { background: var(--orange-soft); }
        .stat-icon.green  { background: var(--green-soft); }
        .stat-icon.blue   { background: var(--blue-soft); }
        .stat-info strong { font-size: 1.5rem; font-weight: 700; color: var(--text); display: block; line-height: 1; }
        .stat-info span   { font-size: 0.78rem; color: var(--muted); }

        /* SECTION CARDS */
        .section-card { background: var(--white); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }
        .section-head { padding: 1rem 1.4rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .section-head h3 { font-size: 0.97rem; font-weight: 700; color: var(--text); }
        .count-badge { font-size: 0.72rem; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
        .badge-orange { background: var(--orange-soft); color: var(--orange); }
        .badge-green  { background: var(--green-soft); color: var(--green); }
        .badge-blue   { background: var(--blue-soft); color: var(--blue); }
        .badge-gray   { background: var(--bg); color: var(--muted); }

        /* ACTIVE SESSION ROWS */
        .active-list { padding: 1rem 1.4rem; display: flex; flex-direction: column; gap: 0.75rem; }
        .active-row { display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: var(--bg); border-radius: 12px; gap: 1rem; flex-wrap: wrap; }
        .active-info strong { font-size: 0.9rem; color: var(--text); display: block; font-weight: 600; }
        .active-info span   { font-size: 0.78rem; color: var(--muted); }

        /* BUTTONS */
        .btn-primary  { display: inline-flex; align-items: center; gap: 5px; padding: 8px 16px; background: var(--green); color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.82rem; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; transition: background 0.18s; white-space: nowrap; }
        .btn-primary:hover { background: #15803d; }
        .btn-accept   { padding: 7px 14px; background: var(--green-soft); color: var(--green); border: 1px solid #bbf7d0; border-radius: 7px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: opacity 0.18s; white-space: nowrap; }
        .btn-accept:hover { opacity: 0.75; }
        .btn-reject   { padding: 7px 14px; background: var(--red-soft); color: var(--red); border: 1px solid #fecaca; border-radius: 7px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: opacity 0.18s; white-space: nowrap; }
        .btn-reject:hover { opacity: 0.75; }
        .btn-complete { padding: 7px 14px; background: #dbeafe; color: #0284c7; border: 1px solid #7dd3fc; border-radius: 7px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.8rem; font-weight: 600; cursor: pointer; transition: opacity 0.18s; white-space: nowrap; }
        .btn-complete:hover { opacity: 0.75; }
        .btn-chat     { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; background: var(--orange-soft); color: var(--orange); border: 1px solid #fcd99a; border-radius: 7px; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.8rem; font-weight: 600; cursor: pointer; text-decoration: none; transition: opacity 0.18s; white-space: nowrap; }
        .btn-chat:hover { opacity: 0.75; }

        .action-cell { display: flex; gap: 8px; flex-wrap: wrap; }

        /* TABLE */
        table { width: 100%; border-collapse: collapse; }
        thead tr { background: linear-gradient(135deg, #92400e, var(--orange)); }
        thead th { padding: 11px 16px; color: #fff; font-size: 0.8rem; font-weight: 600; text-align: left; text-transform: uppercase; letter-spacing: 0.04em; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--bg); }
        td { padding: 12px 16px; font-size: 0.86rem; color: var(--muted); }
        td:first-child { color: var(--text); font-weight: 600; }
        .empty-cell { text-align: center; padding: 2.5rem; color: var(--muted); font-size: 0.88rem; }

        /* Completed table: blue header */
        .completed-head tr { background: linear-gradient(135deg, #1e3a8a, var(--blue)); }

        @media (max-width: 700px) {
            header { padding: 0 1rem; }
            .container { padding: 1rem; }
            .stats { grid-template-columns: 1fr; }
            .action-cell { flex-direction: column; }
            .active-row { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
<header>
    <h1>EduGuide</h1>
    <nav>
        <span>📖 <?= htmlspecialchars($tutor_name) ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
</header>

<div class="container">

    <div class="page-header">
        <div class="page-title">Welcome back, <?= htmlspecialchars($tutor_name) ?>! 👋</div>
        <div class="page-sub">Manage your student requests and active sessions below.</div>
    </div>

    <!-- STATS -->
    <div class="stats">
        <div class="stat-card">
            <div class="stat-icon orange">📋</div>
            <div class="stat-info"><strong><?= $count_pending ?></strong><span>Pending Requests</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">💬</div>
            <div class="stat-info"><strong><?= $count_active ?></strong><span>Active Sessions</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">✅</div>
            <div class="stat-info"><strong><?= $count_completed ?></strong><span>Completed Sessions</span></div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         ACTIVE SESSIONS
    ═══════════════════════════════════════════ -->
    <div class="section-card">
        <div class="section-head">
            <h3>💬 Active Sessions</h3>
            <span class="count-badge badge-green"><?= $count_active ?> active</span>
        </div>
        <?php if ($count_active > 0): ?>
        <div class="active-list">
            <?php while ($a = mysqli_fetch_assoc($active)): ?>
            <div class="active-row">
                <div class="active-info">
                    <strong>📚 <?= htmlspecialchars($a['fullname']) ?></strong>
                    <span>Subject: <?= htmlspecialchars($a['subject']) ?> &nbsp;|&nbsp; ID: <?= htmlspecialchars($a['id_no']) ?></span>
                </div>
                <div class="action-cell">
                    <a href="session_chat.php?request_id=<?= $a['request_id'] ?>" class="btn-chat">💬 Open Chat</a>
                    <form action="complete_session.php" method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $a['request_id'] ?>">
                        <button type="submit" class="btn-complete"
                                onclick="return confirm('Mark this session as completed?');">✓ Mark Complete</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-cell">No active sessions yet. Accept a pending request to start teaching.</div>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════
         PENDING REQUESTS
    ═══════════════════════════════════════════ -->
    <div class="section-card">
        <div class="section-head">
            <h3>📋 Pending Requests</h3>
            <span class="count-badge badge-orange"><?= $count_pending ?> pending</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Classes Code</th>
                    <th>Requested On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($count_pending > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($pending)): ?>
                <tr>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td><?= date('d M Y, H:i', strtotime($row['created_at'])) ?></td>
                    <td>
                        <div class="action-cell">
                            <form action="update_request.php" method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                <button type="submit" name="action" value="accepted" class="btn-accept">✓ Accept</button>
                            </form>
                            <form action="update_request.php" method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $row['request_id'] ?>">
                                <button type="submit" name="action" value="rejected" class="btn-reject">✕ Reject</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="4" class="empty-cell">No pending requests at the moment.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- ══════════════════════════════════════════
         COMPLETED SESSIONS  (full list + re-invite info)
    ═══════════════════════════════════════════ -->
    <div class="section-card">
        <div class="section-head">
            <h3>✅ Completed Sessions</h3>
            <span class="count-badge badge-blue"><?= $count_completed ?> total</span>
        </div>
        <table>
            <thead class="completed-head">
                <tr>
                    <th>Student Name</th>
                    <th>Student ID</th>
                    <th>Classes Code</th>
                    <th>Completed On</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($count_completed > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($done)):
                    // Check if the student has sent a new pending request after this session
                    $s_id = mysqli_real_escape_string($conn, $row['student_id']);
                    $new_req = mysqli_fetch_assoc(mysqli_query($conn,
                        "SELECT status FROM requests
                         WHERE student_id='$s_id' AND tutor_id='$tutor_id'
                         AND status IN ('pending','accepted')
                         LIMIT 1"));
                    $new_status = $new_req['status'] ?? null;
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                    <td><?= htmlspecialchars($row['student_id']) ?></td>
                    <td><?= htmlspecialchars($row['subject']) ?></td>
                    <td><?= date('d M Y, H:i', strtotime($row['updated_at'])) ?></td>
                    <td>
                        <?php if ($new_status === 'pending'): ?>
                            <span style="font-size:0.78rem; font-weight:600; color:var(--orange);">
                                ⏳ New request pending
                            </span>
                        <?php elseif ($new_status === 'accepted'): ?>
                            <span style="font-size:0.78rem; font-weight:600; color:var(--green);">
                                💬 Session active
                            </span>
                        <?php else: ?>
                            <span style="font-size:0.78rem; color:var(--muted);">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5" class="empty-cell">No completed sessions yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>