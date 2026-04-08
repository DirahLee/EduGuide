<?php
session_start();
include "db_conn.php";

if (!isset($_SESSION['id_no'])) { 
    header("Location: login.php"); 
    exit(); 
}

$fullname = $_SESSION['fullname'];
$sid = $_SESSION['id_no'];

// ✅ Get all subjects for this student
$subjects_sql = "SELECT DISTINCT subject FROM student_subjects WHERE student_id='$sid' ORDER BY subject ASC";
$subjects_result = mysqli_query($conn, $subjects_sql);

if (!$subjects_result) {
    die("Error fetching subjects: " . mysqli_error($conn));
}

$my_subjects = [];
while ($row = mysqli_fetch_assoc($subjects_result)) {
    $my_subjects[] = $row['subject'];
}

// If no subjects in student_subjects table, fall back to primary subject
if (empty($my_subjects)) {
    $get_sub = mysqli_fetch_assoc(mysqli_query($conn, "SELECT subject FROM students WHERE id_no='$sid'"));
    if ($get_sub && !empty($get_sub['subject'])) {
        $my_subjects = [$get_sub['subject']];
    }
}

$selected_subject = $_GET['subject'] ?? (!empty($my_subjects) ? $my_subjects[0] : 'All');

// ✅ Get active (accepted) sessions
$active_sql = "SELECT r.request_id, t.fullname AS tname, t.expertise, r.subject, r.status
               FROM requests r 
               JOIN tutors t ON r.tutor_id = t.id
               WHERE r.student_id='$sid' AND r.status='accepted'
               ORDER BY r.request_id DESC";
$active = mysqli_query($conn, $active_sql);

// ✅ Get pending requests
$pending_sql = "SELECT r.request_id, t.fullname AS tname, t.expertise, r.subject
                FROM requests r 
                JOIN tutors t ON r.tutor_id = t.id
                WHERE r.student_id='$sid' AND r.status='pending'
                ORDER BY r.request_id DESC";
$pending = mysqli_query($conn, $pending_sql);

// ✅ Get completed sessions  
$completed_sql = "SELECT r.request_id, t.fullname AS tname, t.id AS tutor_id, r.subject, r.updated_at
                  FROM requests r 
                  JOIN tutors t ON r.tutor_id = t.id
                  WHERE r.student_id='$sid' AND r.status='completed'
                  ORDER BY r.updated_at DESC";
$completed = mysqli_query($conn, $completed_sql);

// Get tutors for selected subject
if ($selected_subject === 'All') {
    $sql = "SELECT * FROM tutors ORDER BY fullname ASC";
} else {
    $sql = "SELECT * FROM tutors WHERE expertise LIKE '%$selected_subject%' ORDER BY fullname ASC";
}
$result = mysqli_query($conn, $sql);

$count_active = mysqli_num_rows($active);
$count_pending = mysqli_num_rows($pending);
$count_completed = mysqli_num_rows($completed);

mysqli_data_seek($active, 0);
mysqli_data_seek($pending, 0);
mysqli_data_seek($completed, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Student Dashboard – EduGuide</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #f0f4f9; --white: #fff; --blue: #2563eb; --blue-dark: #1d4ed8;
            --blue-soft: #eff6ff; --border: #dde3ee; --text: #1a2235; --muted: #6b7c99;
            --green: #16a34a; --green-soft: #f0fdf4; --orange: #d97706; --orange-soft: #fffbeb;
            --red: #dc2626; --red-soft: #fef2f2;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

        header { background: var(--white); border-bottom: 1px solid var(--border); height: 62px; display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 6px rgba(0,0,0,0.04); }
        header h1 { font-family: 'Fraunces', serif; font-size: 1.4rem; color: var(--blue); font-weight: 700; }
        header nav { display: flex; align-items: center; gap: 0.5rem; }
        header nav span { font-size: 0.85rem; font-weight: 600; color: var(--text); background: var(--bg); border: 1px solid var(--border); padding: 6px 14px; border-radius: 8px; }
        header nav a.logout-btn { font-size: 0.82rem; font-weight: 600; color: var(--red); background: var(--red-soft); border: 1px solid #fecaca; padding: 6px 14px; border-radius: 8px; text-decoration: none; transition: background 0.18s; }
        header nav a.logout-btn:hover { background: #fee2e2; }

        .container { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem; display: flex; flex-direction: column; gap: 2rem; }

        .subject-selector { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; margin-bottom: 1rem; }
        .subject-filter { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .subject-btn { padding: 7px 14px; background: var(--white); border: 1.5px solid var(--border); color: var(--muted); border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; }
        .subject-btn:hover { border-color: var(--blue); color: var(--blue); }
        .subject-btn.active { background: var(--blue); color: #fff; border-color: var(--blue); }

        .add-subject-btn { padding: 7px 14px; background: var(--green); color: #fff; border: none; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .add-subject-btn:hover { background: #15803d; }

        .stats-bar { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
        .stat-card { background: var(--white); border: 1px solid var(--border); border-radius: 14px; padding: 1.2rem 1.4rem; display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
        .stat-icon.green { background: var(--green-soft); }
        .stat-icon.orange { background: var(--orange-soft); }
        .stat-icon.blue { background: var(--blue-soft); }
        .stat-info strong { font-size: 1.5rem; font-weight: 700; color: var(--text); display: block; line-height: 1; }
        .stat-info span { font-size: 0.78rem; color: var(--muted); }

        .section { display: flex; flex-direction: column; gap: 1rem; }
        .section-header { display: flex; align-items: center; justify-content: space-between; }
        .section-header h2 { font-family: 'Fraunces', serif; font-size: 1.2rem; color: var(--text); }
        .section-card { background: var(--white); border: 1px solid var(--border); border-radius: 16px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); }

        .active-list { display: flex; flex-wrap: wrap; gap: 1rem; }
        .active-card { background: linear-gradient(135deg, #f0fdf4 0%, #dbeafe 100%); border: 1.5px solid #bbf7d0; border-radius: 14px; padding: 1.2rem; display: flex; flex-direction: column; gap: 0.8rem; min-width: 280px; box-shadow: 0 2px 8px rgba(22,163,74,0.08); }
        .active-info strong { font-size: 0.95rem; color: var(--text); font-weight: 600; display: block; margin-bottom: 2px; }
        .active-info span { font-size: 0.82rem; color: var(--muted); display: block; }

        .btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 8px 16px; background: var(--green); color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.82rem; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; transition: background 0.18s; white-space: nowrap; }
        .btn-primary:hover { background: #15803d; }
        .btn-secondary { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 8px 16px; background: var(--blue); color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.82rem; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; transition: background 0.18s; white-space: nowrap; }
        .btn-secondary:hover { background: var(--blue-dark); }
        .btn-danger { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 8px 16px; background: var(--red); color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.82rem; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; transition: background 0.18s; white-space: nowrap; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-outline { display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 8px 16px; background: transparent; color: var(--blue); border: 1.5px solid var(--blue); font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.82rem; font-weight: 600; border-radius: 8px; cursor: pointer; text-decoration: none; transition: background 0.18s; white-space: nowrap; }
        .btn-outline:hover { background: var(--blue-soft); }
        .btn-group { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        button[disabled] { opacity: 0.5; cursor: not-allowed; }

        table { width: 100%; border-collapse: collapse; }
        thead tr { background: linear-gradient(135deg, #1e40af, var(--blue)); }
        thead th { padding: 11px 16px; color: #fff; font-size: 0.8rem; font-weight: 600; text-align: left; text-transform: uppercase; letter-spacing: 0.04em; }
        tbody tr { border-bottom: 1px solid var(--border); transition: background 0.15s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--bg); }
        td { padding: 12px 16px; font-size: 0.85rem; color: var(--muted); }
        td:first-child { color: var(--text); font-weight: 600; }

        .status-badge { display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; }
        .status-pending { background: var(--orange-soft); color: var(--orange); }
        .status-accepted { background: var(--green-soft); color: var(--green); }
        .status-completed { background: var(--blue-soft); color: var(--blue); }

        .card-container { display: flex; flex-wrap: wrap; gap: 1.2rem; }
        .tutor-card { background: var(--white); border: 1px solid var(--border); border-radius: 14px; padding: 1.2rem; width: 280px; display: flex; flex-direction: column; gap: 0.75rem; transition: box-shadow 0.2s, transform 0.2s; }
        .tutor-card:hover { box-shadow: 0 8px 28px rgba(0,0,0,0.09); transform: translateY(-2px); }
        .tutor-card h3 { font-size: 1rem; font-weight: 700; color: var(--text); }
        .tutor-card p { font-size: 0.84rem; color: var(--muted); line-height: 1.5; }
        .tutor-card strong { color: var(--text); font-weight: 600; }

        .empty-state { text-align: center; padding: 2.5rem; color: var(--muted); font-size: 0.9rem; }
        .empty-state a { color: var(--blue); font-weight: 600; text-decoration: none; }

        /* Modal */
        .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 1rem; }
        .modal.active { display: flex; }
        .modal-content { background: var(--white); border-radius: 16px; padding: 2rem; max-width: 400px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .modal-header h3 { font-family: 'Fraunces', serif; font-size: 1.4rem; color: var(--text); }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--muted); }
        .modal-subjects { display: grid; grid-template-columns: 1fr; gap: 0.6rem; margin-bottom: 1.5rem; }
        .modal-subject { display: flex; align-items: center; gap: 8px; }
        .modal-subject input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--blue); }
        .modal-subject label { flex: 1; margin: 0; cursor: pointer; font-size: 0.85rem; color: var(--text); }
        .modal-actions { display: flex; gap: 0.75rem; }
        .modal-actions button { flex: 1; padding: 0.75rem; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .modal-save { background: var(--green); color: #fff; }
        .modal-save:hover { background: #15803d; }
        .modal-cancel { background: var(--border); color: var(--text); }
        .modal-cancel:hover { background: #cad9e8; }

        @media (max-width: 700px) {
            .card-container { flex-direction: column; }
            .tutor-card { width: 100%; }
            .active-card { min-width: unset; width: 100%; }
            .stats-bar { grid-template-columns: 1fr; }
            .subject-selector { flex-direction: column; align-items: flex-start; }
            .subject-filter { width: 100%; }
        }
    </style>
</head>
<body>
<header>
    <h1>EduGuide</h1>
    <nav>
        <span>👋 <?= htmlspecialchars($fullname) ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </nav>
</header>

<div class="container">

    <!-- SUBJECT SELECTOR -->
    <div class="subject-selector">
        <span style="font-weight:600; color:var(--text); font-size:0.9rem;">Filter by Subject:</span>
        <div class="subject-filter">
            <a href="?subject=All" class="subject-btn <?= $selected_subject === 'All' ? 'active' : '' ?>">All Subjects</a>
            <?php foreach ($my_subjects as $subj): ?>
                <a href="?subject=<?= urlencode($subj) ?>" class="subject-btn <?= $selected_subject === $subj ? 'active' : '' ?>">
                    <?= htmlspecialchars($subj) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <button class="add-subject-btn" onclick="openSubjectModal()">➕ Add Subject</button>
    </div>

    <!-- STATS BAR -->
    <div class="stats-bar">
        <div class="stat-card">
            <div class="stat-icon green">✅</div>
            <div class="stat-info"><strong><?= $count_active ?></strong><span>Active Sessions</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon orange">⏳</div>
            <div class="stat-info"><strong><?= $count_pending ?></strong><span>Pending Requests</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon blue">🎓</div>
            <div class="stat-info"><strong><?= $count_completed ?></strong><span>Completed Sessions</span></div>
        </div>
    </div>

    <!-- ACTIVE SESSIONS -->
    <div class="section">
        <div class="section-header">
            <h2>✅ Active Sessions <?= $count_active > 0 ? "($count_active)" : "" ?></h2>
        </div>

        <?php if ($count_active > 0): ?>
        <div class="active-list">
            <?php while ($a = mysqli_fetch_assoc($active)): ?>
            <div class="active-card">
                <div class="active-info">
                    <strong>📚 <?= htmlspecialchars($a['tname']) ?></strong>
                    <span><strong>Subject:</strong> <?= htmlspecialchars($a['subject']) ?></span>
                    <span><strong>Expertise:</strong> <?= htmlspecialchars($a['expertise']) ?></span>
                    <span class="status-badge status-accepted">Active</span>
                </div>
                <div class="btn-group">
                    <a href="session_chat.php?request_id=<?= $a['request_id'] ?>" class="btn-primary">💬 Enter Session</a>
                    <form action="complete_session.php" method="POST" style="display:inline;">
                        <input type="hidden" name="request_id" value="<?= $a['request_id'] ?>">
                        <button type="submit" class="btn-danger"
                                onclick="return confirm('Mark this session as completed? You can request a new session afterwards.');">
                            ✓ Complete
                        </button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="section-card">
            <div class="empty-state">No active sessions right now. Request one from the tutors below! 👇</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- PENDING REQUESTS -->
    <?php if ($count_pending > 0): ?>
    <div class="section">
        <div class="section-header">
            <h2>⏳ Pending Requests (<?= $count_pending ?>)</h2>
        </div>
        <div class="section-card">
            <table>
                <thead>
                    <tr>
                        <th>Tutor Name</th>
                        <th>Subject</th>
                        <th>Expertise</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($pending)): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['tname']) ?></td>
                        <td><?= htmlspecialchars($p['subject']) ?></td>
                        <td><?= htmlspecialchars($p['expertise']) ?></td>
                        <td><span class="status-badge status-pending">Pending</span></td>
                        <td>
                            <form action="cancel_request.php" method="POST" style="display:inline;">
                                <input type="hidden" name="request_id" value="<?= $p['request_id'] ?>">
                                <button type="submit" class="btn-danger"
                                        onclick="return confirm('Cancel this request?');">✕ Cancel</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- COMPLETED SESSIONS -->
    <div class="section">
        <div class="section-header">
            <h2>🎓 Completed Sessions <?= $count_completed > 0 ? "($count_completed)" : "" ?></h2>
        </div>
        <div class="section-card">
            <?php if ($count_completed > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Tutor Name</th>
                        <th>Subject</th>
                        <th>Completed On</th>
                        <th>New Session</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($c = mysqli_fetch_assoc($completed)):
                        $tid = (int)$c['tutor_id'];
                        $block = mysqli_fetch_assoc(mysqli_query($conn,
                            "SELECT status FROM requests
                             WHERE student_id='$sid' AND tutor_id='$tid'
                             AND status IN ('pending','accepted')
                             LIMIT 1"));
                        $blocked_status = $block['status'] ?? null;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($c['tname']) ?></td>
                        <td><?= htmlspecialchars($c['subject']) ?></td>
                        <td><?= date('d M Y, H:i', strtotime($c['updated_at'])) ?></td>
                        <td>
                            <?php if ($blocked_status === 'pending'): ?>
                                <button disabled class="btn-secondary">⏳ Pending</button>
                            <?php elseif ($blocked_status === 'accepted'): ?>
                                <button disabled class="btn-primary">✅ Active</button>
                            <?php else: ?>
                                <form action="request_session.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="tutor_id"  value="<?= $tid ?>">
                                    <input type="hidden" name="subject"   value="<?= htmlspecialchars($c['subject']) ?>">
                                    <button type="submit" class="btn-outline"
                                            onclick="return confirm('Request a new session with <?= htmlspecialchars(addslashes($c['tname'])) ?>?');">
                                        ➕ Book Again
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">No completed sessions yet. Start your first session above! 🚀</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- AVAILABLE TUTORS -->
    <div class="section">
        <div class="section-header">
            <h2>📚 Available Tutors <?= $selected_subject !== 'All' ? "for: <em style='color:var(--blue); font-style:normal;'>" . htmlspecialchars($selected_subject) . "</em>" : "" ?></h2>
        </div>
        <div class="card-container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)):
                    $tutor_id  = $row['id'];
                    $check_req = mysqli_query($conn,
                        "SELECT request_id, status FROM requests
                         WHERE student_id='$sid' AND tutor_id='$tutor_id'
                         ORDER BY request_id DESC LIMIT 1");
                    $has_req  = mysqli_num_rows($check_req) > 0;
                    $req_row  = $has_req ? mysqli_fetch_assoc($check_req) : null;
                    $status   = $req_row['status'] ?? null;
                ?>
                <div class="tutor-card">
                    <h3>👨‍🏫 <?= htmlspecialchars($row['fullname']) ?></h3>
                    <p><strong>Expertise:</strong> <?= htmlspecialchars($row['expertise']) ?></p>
                    <p><strong>Available:</strong> <?= htmlspecialchars($row['availability']) ?></p>
                    <p><strong>Bio:</strong> <em><?= htmlspecialchars($row['bio']) ?></em></p>
                    <p><strong>Rating:</strong> ⭐ <?= htmlspecialchars($row['rating'] ?? '4.5/5') ?></p>

                    <?php if ($status === 'pending'): ?>
                        <button disabled class="btn-secondary">⏳ Request Pending</button>

                    <?php elseif ($status === 'accepted'): ?>
                        <a href="session_chat.php?request_id=<?= $req_row['request_id'] ?>" class="btn-primary">💬 Enter Session</a>

                    <?php elseif ($status === 'rejected' || $status === 'completed'): ?>
                        <form action="request_session.php" method="POST">
                            <input type="hidden" name="tutor_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="subject"  value="<?= htmlspecialchars($selected_subject !== 'All' ? $selected_subject : (!empty($my_subjects) ? $my_subjects[0] : '')) ?>">
                            <button type="submit" class="btn-secondary">
                                <?= $status === 'completed' ? '➕ Request New Session' : '📨 Request Again' ?>
                            </button>
                        </form>

                    <?php else: ?>
                        <form action="request_session.php" method="POST">
                            <input type="hidden" name="tutor_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="subject"  value="<?= htmlspecialchars($selected_subject !== 'All' ? $selected_subject : (!empty($my_subjects) ? $my_subjects[0] : '')) ?>">
                            <button type="submit" class="btn-secondary">📨 Request Session</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state" style="width:100%;">
                    No tutors currently available<?= $selected_subject !== 'All' ? " for <strong>$selected_subject</strong>" : "" ?>.
                    <br><a href="Contact.php">Contact support</a> for help.
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ADD SUBJECT MODAL -->
<div class="modal" id="subjectModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add More Subjects</h3>
            <button class="modal-close" onclick="closeSubjectModal()">✕</button>
        </div>

        <form action="add_student_subject.php" method="POST">
            <div class="modal-subjects">
                <div class="modal-subject">
                    <input type="checkbox" id="m_bm" name="subjects" value="Bahasa Melayu"/>
                    <label for="m_bm">Bahasa Melayu</label>
                </div>
                <div class="modal-subject">
                    <input type="checkbox" id="m_en" name="subjects" value="English"/>
                    <label for="m_en">English</label>
                </div>
                <div class="modal-subject">
                    <input type="checkbox" id="m_math" name="subjects" value="Mathematics"/>
                    <label for="m_math">Mathematics</label>
                </div>
                <div class="modal-subject">
                    <input type="checkbox" id="m_sci" name="subjects" value="Science"/>
                    <label for="m_sci">Science</label>
                </div>
                <div class="modal-subject">
                    <input type="checkbox" id="m_hist" name="subjects" value="History"/>
                    <label for="m_hist">History</label>
                </div>
                <div class="modal-subject">
                    <input type="checkbox" id="m_islam" name="subjects" value="Pendidikan Islam"/>
                    <label for="m_islam">Pendidikan Islam</label>
                </div>
                <div class="modal-subject">
                    <input type="checkbox" id="m_moral" name="subjects" value="Pendidikan Moral"/>
                    <label for="m_moral">Pendidikan Moral</label>
                </div>
            </div>

            <div class="modal-actions">
                <button type="button" class="modal-cancel" onclick="closeSubjectModal()">Cancel</button>
                <button type="submit" class="modal-save">Add Subjects</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openSubjectModal() {
        document.getElementById('subjectModal').classList.add('active');
    }

    function closeSubjectModal() {
        document.getElementById('subjectModal').classList.remove('active');
    }

    document.getElementById('subjectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSubjectModal();
        }
    });
</script>

</body>
</html>