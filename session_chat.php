<?php
session_start();
include "db_conn.php";

$is_student = isset($_SESSION['id_no']);
$is_tutor   = isset($_SESSION['tutor_id']);

if (!$is_student && !$is_tutor) { header("Location: index.php"); exit(); }

$request_id = (int)($_GET['request_id'] ?? 0);
if (!$request_id) { header("Location: index.php"); exit(); }

// ── Load session data ────────────────────────────────────────────────────────
$req = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT r.*, s.fullname AS sname, s.id_no AS sid,
            t.fullname AS tname, t.expertise, t.id AS tid
     FROM requests r
     JOIN students s ON r.student_id = s.id_no
     JOIN tutors   t ON r.tutor_id   = t.id
     WHERE r.request_id = $request_id AND r.status = 'accepted'"));

if (!$req) {
    echo "<script>alert('Session not found or not active.'); window.history.back();</script>";
    exit();
}

// Security
if ($is_student && $req['sid'] !== $_SESSION['id_no'])   { header("Location: Student_dashboard.php"); exit(); }
if ($is_tutor   && $req['tid'] != $_SESSION['tutor_id']) { header("Location: Tdashboard.php");        exit(); }

$sender_role = $is_student ? 'student' : 'tutor';
$sender_name = $is_student ? $_SESSION['fullname'] : $_SESSION['tutor_name'];
$back_link   = $is_student ? 'Student_dashboard.php' : 'Tdashboard.php';

// Upload directory — create if missing
$upload_dir = __DIR__ . '/uploads/chat/';
if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }

// ── Helper: allowed file types ───────────────────────────────────────────────
function allowed_file($mime, $ext) {
    $allowed_ext  = ['pdf','jpg','jpeg','png','gif','webp','doc','docx','ppt','pptx','xls','xlsx','txt','zip'];
    $allowed_mime = [
        'application/pdf',
        'image/jpeg','image/png','image/gif','image/webp',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'application/zip','application/x-zip-compressed',
    ];
    return in_array(strtolower($ext), $allowed_ext) && in_array($mime, $allowed_mime);
}

// ── AJAX: poll new messages ──────────────────────────────────────────────────
if (isset($_GET['poll'])) {
    $after = (int)($_GET['after'] ?? 0);
    $rows  = [];
    $res   = mysqli_query($conn,
        "SELECT msg_id, sender_role, sender_name, msg_type,
                message, file_path, file_name,
                reply_to_id, reply_name, reply_text, sent_at
         FROM session_messages
         WHERE request_id = $request_id AND msg_id > $after
         ORDER BY sent_at ASC");
    while ($m = mysqli_fetch_assoc($res)) { $rows[] = $m; }
    header('Content-Type: application/json');
    echo json_encode($rows);
    exit();
}

// ── AJAX: send text message ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_msg'])) {
    $msg = trim($_POST['message'] ?? '');
    if ($msg !== '') {
        $msg_esc    = mysqli_real_escape_string($conn, $msg);
        $role_esc   = mysqli_real_escape_string($conn, $sender_role);
        $name_esc   = mysqli_real_escape_string($conn, $sender_name);
        $reply_id   = isset($_POST['reply_to_id'])   ? (int)$_POST['reply_to_id']   : 'NULL';
        $reply_name = isset($_POST['reply_to_name'])
                        ? "'" . mysqli_real_escape_string($conn, $_POST['reply_to_name']) . "'"
                        : 'NULL';
        $reply_text = isset($_POST['reply_to_text'])
                        ? "'" . mysqli_real_escape_string($conn, $_POST['reply_to_text']) . "'"
                        : 'NULL';

        mysqli_query($conn,
            "INSERT INTO session_messages
             (request_id, sender_role, sender_name, msg_type, message, reply_to_id, reply_name, reply_text)
             VALUES ($request_id, '$role_esc', '$name_esc', 'text', '$msg_esc', $reply_id, $reply_name, $reply_text)");

        $new_id = mysqli_insert_id($conn);
        $row    = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT msg_id, sender_role, sender_name, msg_type,
                    message, file_path, file_name,
                    reply_to_id, reply_name, reply_text, sent_at
             FROM session_messages WHERE msg_id = $new_id"));
        header('Content-Type: application/json');
        echo json_encode($row);
    }
    exit();
}

// ── AJAX: upload file ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['chat_file'])) {
    $file     = $_FILES['chat_file'];
    $orig     = basename($file['name']);
    $ext      = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
    $mime     = mime_content_type($file['tmp_name']);
    $max_size = 10 * 1024 * 1024; // 10 MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Upload error: ' . $file['error']]);
        exit();
    }
    if ($file['size'] > $max_size) {
        http_response_code(400);
        echo json_encode(['error' => 'File too large. Maximum is 10 MB.']);
        exit();
    }
    if (!allowed_file($mime, $ext)) {
        http_response_code(400);
        echo json_encode(['error' => 'File type not allowed.']);
        exit();
    }

    // Unique filename to prevent collisions
    $safe_name  = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $orig);
    $stored_name = uniqid('chat_', true) . '_' . $safe_name;
    $dest        = $upload_dir . $stored_name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        http_response_code(500);
        echo json_encode(['error' => 'Could not save file.']);
        exit();
    }

    $rel_path   = 'uploads/chat/' . $stored_name;
    $role_esc   = mysqli_real_escape_string($conn, $sender_role);
    $name_esc   = mysqli_real_escape_string($conn, $sender_name);
    $path_esc   = mysqli_real_escape_string($conn, $rel_path);
    $fname_esc  = mysqli_real_escape_string($conn, $orig);
    $caption    = mysqli_real_escape_string($conn, trim($_POST['caption'] ?? ''));
    $msg_label  = $caption !== '' ? $caption : $orig;

    mysqli_query($conn,
        "INSERT INTO session_messages
         (request_id, sender_role, sender_name, msg_type, message, file_path, file_name)
         VALUES ($request_id, '$role_esc', '$name_esc', 'file', '$msg_label', '$path_esc', '$fname_esc')");

    $new_id = mysqli_insert_id($conn);
    $row    = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT msg_id, sender_role, sender_name, msg_type,
                message, file_path, file_name,
                reply_to_id, reply_name, reply_text, sent_at
         FROM session_messages WHERE msg_id = $new_id"));
    header('Content-Type: application/json');
    echo json_encode($row);
    exit();
}

// ── Mark as Completed (tutor only) ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete']) && $is_tutor) {
    mysqli_query($conn, "UPDATE requests SET status='completed', updated_at=NOW() WHERE request_id=$request_id");
    header("Location: Tdashboard.php");
    exit();
}

// ── Load initial messages ────────────────────────────────────────────────────
$msgs = mysqli_query($conn,
    "SELECT msg_id, sender_role, sender_name, msg_type,
            message, file_path, file_name,
            reply_to_id, reply_name, reply_text, sent_at
     FROM session_messages WHERE request_id=$request_id ORDER BY sent_at ASC");

$last_id  = 0;
$msg_rows = [];
while ($m = mysqli_fetch_assoc($msgs)) {
    $msg_rows[] = $m;
    $last_id    = $m['msg_id'];
}

// Helper: icon for file type
function file_icon($name) {
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $icons = [
        'pdf'  => '📄', 'doc' => '📝', 'docx' => '📝',
        'ppt'  => '📊', 'pptx'=> '📊',
        'xls'  => '📊', 'xlsx'=> '📊',
        'txt'  => '📃', 'zip' => '🗜',
        'jpg'  => '🖼', 'jpeg'=> '🖼', 'png'  => '🖼',
        'gif'  => '🖼', 'webp'=> '🖼',
    ];
    return $icons[$ext] ?? '📎';
}

function is_image($name) {
    return in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), ['jpg','jpeg','png','gif','webp']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Session Chat – EduGuide</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{
      --bg:#f0f4f9;--white:#fff;
      --blue:#2563eb;--blue-dark:#1d4ed8;--blue-soft:#eff6ff;
      --orange:#e67e22;
      --border:#dde3ee;--text:#1a2235;--muted:#6b7c99;
      --green:#16a34a;--green-soft:#f0fdf4;
      --red:#dc2626;--red-soft:#fef2f2;
      --me-bg:#2563eb;--them-bg:#f1f5f9;
      --accent:<?php echo $is_student ? 'var(--blue)' : 'var(--orange)'; ?>;
    }
    body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column;}

    /* HEADER */
    header{background:var(--white);border-bottom:1px solid var(--border);height:62px;display:flex;align-items:center;justify-content:space-between;padding:0 2rem;position:sticky;top:0;z-index:100;box-shadow:0 1px 6px rgba(0,0,0,0.05);}
    .logo{font-family:'Fraunces',serif;font-size:1.4rem;color:var(--accent);font-weight:700;}
    .header-right{display:flex;align-items:center;gap:0.75rem;}
    .back-btn{font-size:0.82rem;font-weight:600;color:var(--muted);background:var(--bg);border:1px solid var(--border);padding:6px 14px;border-radius:8px;text-decoration:none;transition:background 0.18s;}
    .back-btn:hover{background:var(--border);}
    .logout-btn{font-size:0.82rem;font-weight:600;color:var(--red);background:var(--red-soft);border:1px solid #fecaca;padding:6px 14px;border-radius:8px;text-decoration:none;transition:background 0.18s;}
    .logout-btn:hover{background:#fee2e2;}

    /* PAGE */
    .page{flex:1;max-width:840px;margin:0 auto;padding:1.5rem;display:flex;flex-direction:column;gap:1rem;width:100%;}

    /* SESSION INFO */
    .info-card{background:var(--white);border:1px solid var(--border);border-radius:16px;padding:1.2rem 1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;}
    .info-left h2{font-family:'Fraunces',serif;font-size:1.1rem;color:var(--text);margin-bottom:0.2rem;}
    .info-left p{font-size:0.83rem;color:var(--muted);}
    .info-left p strong{color:var(--text);}
    .session-badge{font-size:0.72rem;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:var(--green);background:var(--green-soft);border:1px solid #bbf7d0;padding:5px 12px;border-radius:20px;white-space:nowrap;}

    /* COMPLETE BUTTON (tutor) */
    .complete-wrap{display:flex;justify-content:flex-end;}
    .complete-btn{padding:0.6rem 1.4rem;background:var(--green-soft);color:var(--green);font-family:'Plus Jakarta Sans',sans-serif;font-size:0.85rem;font-weight:600;border:1px solid #bbf7d0;border-radius:10px;cursor:pointer;transition:background 0.18s;}
    .complete-btn:hover{background:#dcfce7;}

    /* CHAT BOX */
    .chat-box{background:var(--white);border:1px solid var(--border);border-radius:16px;display:flex;flex-direction:column;overflow:hidden;flex:1;min-height:460px;}

    .chat-header{padding:0.75rem 1.2rem;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:0.6rem;background:var(--white);}
    .online-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;}
    .chat-header-text{font-size:0.82rem;font-weight:600;color:var(--muted);}

    /* Messages */
    .chat-messages{flex:1;overflow-y:auto;padding:1.2rem;display:flex;flex-direction:column;gap:0.85rem;scroll-behavior:smooth;}
    .date-sep{text-align:center;font-size:0.7rem;font-weight:600;color:var(--muted);letter-spacing:0.05em;text-transform:uppercase;position:relative;margin:0.5rem 0;}
    .date-sep::before,.date-sep::after{content:'';position:absolute;top:50%;width:calc(50% - 60px);height:1px;background:var(--border);}
    .date-sep::before{left:0;}.date-sep::after{right:0;}

    /* Bubbles */
    .bubble-wrap{display:flex;flex-direction:column;max-width:72%;}
    .bubble-wrap.me  {align-self:flex-end;align-items:flex-end;}
    .bubble-wrap.them{align-self:flex-start;align-items:flex-start;}
    .bubble-name{font-size:0.7rem;font-weight:600;color:var(--muted);margin-bottom:3px;padding:0 4px;}
    .bubble{padding:0.65rem 1rem;border-radius:18px;font-size:0.88rem;line-height:1.55;word-break:break-word;}
    .bubble-wrap.me   .bubble{background:var(--me-bg);color:#fff;border-bottom-right-radius:4px;}
    .bubble-wrap.them .bubble{background:var(--them-bg);color:var(--text);border-bottom-left-radius:4px;}

    /* Reply quote */
    .reply-quote{background:rgba(255,255,255,0.18);border-left:3px solid rgba(255,255,255,0.5);border-radius:6px;padding:5px 8px;margin-bottom:5px;font-size:0.76rem;opacity:0.9;}
    .bubble-wrap.them .reply-quote{background:rgba(0,0,0,0.06);border-left-color:rgba(0,0,0,0.2);}

    /* File bubble */
    .file-bubble{display:flex;align-items:center;gap:0.65rem;padding:0.6rem 0.85rem;border-radius:12px;border:1px solid rgba(255,255,255,0.3);margin-top:2px;cursor:pointer;text-decoration:none;}
    .bubble-wrap.me   .file-bubble{background:rgba(255,255,255,0.15);color:#fff;border-color:rgba(255,255,255,0.25);}
    .bubble-wrap.them .file-bubble{background:var(--bg);color:var(--text);border-color:var(--border);}
    .file-icon{font-size:1.4rem;flex-shrink:0;}
    .file-info{display:flex;flex-direction:column;min-width:0;}
    .file-name{font-size:0.82rem;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px;}
    .file-caption{font-size:0.72rem;opacity:0.75;margin-top:1px;}
    .file-dl{font-size:0.7rem;font-weight:600;opacity:0.8;margin-top:2px;white-space:nowrap;}

    /* Image preview in bubble */
    .img-preview{max-width:220px;max-height:160px;border-radius:10px;display:block;margin-top:4px;cursor:pointer;object-fit:cover;}

    .bubble-footer{display:flex;align-items:center;gap:8px;margin-top:3px;padding:0 4px;}
    .bubble-time{font-size:0.67rem;color:var(--muted);}
    .reply-btn{font-size:0.68rem;font-weight:600;color:var(--muted);background:none;border:none;cursor:pointer;padding:0;opacity:0;transition:color 0.18s;}
    .bubble-wrap:hover .reply-btn{opacity:1;}
    .reply-btn:hover{color:var(--blue);}

    .chat-empty{text-align:center;color:var(--muted);font-size:0.88rem;margin:auto;padding:2.5rem;}

    /* Reply banner */
    .reply-banner{display:none;background:#f8fafc;border-top:1px solid var(--border);padding:0.6rem 1.2rem;font-size:0.8rem;color:var(--muted);align-items:center;justify-content:space-between;gap:0.5rem;}
    .reply-banner.show{display:flex;}
    .reply-preview{flex:1;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;}
    .reply-preview strong{color:var(--text);}
    .reply-cancel{background:none;border:none;cursor:pointer;font-size:1.1rem;color:var(--muted);line-height:1;padding:0;}
    .reply-cancel:hover{color:var(--red);}

    /* File preview before send */
    .file-preview-bar{display:none;background:#f0f7ff;border-top:1px solid var(--border);padding:0.6rem 1.2rem;align-items:center;gap:0.6rem;}
    .file-preview-bar.show{display:flex;}
    .file-preview-info{flex:1;font-size:0.82rem;color:var(--text);overflow:hidden;white-space:nowrap;text-overflow:ellipsis;}
    .file-preview-size{font-size:0.72rem;color:var(--muted);margin-left:4px;}
    .file-remove-btn{background:none;border:none;cursor:pointer;font-size:1.1rem;color:var(--muted);padding:0;}
    .file-remove-btn:hover{color:var(--red);}

    /* Input area */
    .chat-input{border-top:1px solid var(--border);padding:0.9rem 1.2rem;display:flex;gap:0.6rem;align-items:flex-end;background:var(--white);}
    .attach-btn{background:none;border:1.5px solid var(--border);border-radius:10px;padding:0.55rem 0.75rem;cursor:pointer;font-size:1.1rem;line-height:1;color:var(--muted);transition:border-color 0.18s,color 0.18s;flex-shrink:0;}
    .attach-btn:hover{border-color:var(--accent);color:var(--accent);}
    .chat-input textarea{flex:1;resize:none;border:1.5px solid var(--border);border-radius:12px;padding:0.6rem 0.85rem;font-family:'Plus Jakarta Sans',sans-serif;font-size:0.9rem;color:var(--text);outline:none;transition:border-color 0.2s;line-height:1.5;min-height:42px;max-height:120px;}
    .chat-input textarea:focus{border-color:var(--accent);}
    .send-btn{padding:0.6rem 1.1rem;background:var(--accent);color:#fff;font-family:'Plus Jakarta Sans',sans-serif;font-size:0.88rem;font-weight:600;border:none;border-radius:10px;cursor:pointer;transition:opacity 0.18s;white-space:nowrap;flex-shrink:0;}
    .send-btn:hover{opacity:0.85;}
    .send-btn:disabled{opacity:0.45;cursor:not-allowed;}

    /* Upload progress */
    .upload-progress{display:none;height:3px;background:var(--blue-soft);border-radius:2px;margin:0 1.2rem 0.5rem;}
    .upload-progress-bar{height:100%;background:var(--accent);border-radius:2px;width:0;transition:width 0.3s;}
    .upload-progress.show{display:block;}

    /* Lightbox */
    .lightbox{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:9999;align-items:center;justify-content:center;}
    .lightbox.show{display:flex;}
    .lightbox img{max-width:90vw;max-height:90vh;border-radius:12px;object-fit:contain;}
    .lightbox-close{position:absolute;top:1rem;right:1.2rem;background:none;border:none;color:#fff;font-size:2rem;cursor:pointer;line-height:1;}

    @media(max-width:600px){
      .page{padding:0.75rem;}
      .bubble-wrap{max-width:90%;}
      .info-card{flex-direction:column;}
      .chat-box{min-height:380px;}
      .file-name{max-width:130px;}
    }
  </style>
</head>
<body>

<header>
  <div class="logo">EduGuide</div>
  <div class="header-right">
    <a href="<?= $back_link ?>" class="back-btn">← Dashboard</a>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</header>

<div class="page">

  <!-- SESSION INFO -->
  <div class="info-card">
    <div class="info-left">
      <h2>
        <?php if ($is_student): ?>
          💬 Session with <?= htmlspecialchars($req['tname']) ?>
        <?php else: ?>
          💬 Session with <?= htmlspecialchars($req['sname']) ?>
        <?php endif; ?>
      </h2>
      <p>
        📘 <strong>Subject:</strong> <?= htmlspecialchars($req['subject']) ?>
        &nbsp;|&nbsp;
        <?php if ($is_student): ?>
          🎓 <strong>Tutor:</strong> <?= htmlspecialchars($req['tname']) ?> — <?= htmlspecialchars($req['expertise']) ?>
        <?php else: ?>
          👤 <strong>Student ID:</strong> <?= htmlspecialchars($req['sid']) ?>
        <?php endif; ?>
      </p>
    </div>
    <div class="session-badge">✅ Session Active</div>
  </div>

  <!-- MARK COMPLETE (tutor only) -->
  <?php if ($is_tutor): ?>
  <div class="complete-wrap">
    <form method="POST" onsubmit="return confirm('Mark this session as completed?')">
      <button type="submit" name="complete" value="1" class="complete-btn">
        ✓ Mark Session as Completed
      </button>
    </form>
  </div>
  <?php endif; ?>

  <!-- CHAT BOX -->
  <div class="chat-box">

    <div class="chat-header">
      <span class="online-dot"></span>
      <span class="chat-header-text">Live session · messages and files appear instantly</span>
    </div>

    <div class="chat-messages" id="chatMessages">
      <?php if (empty($msg_rows)): ?>
        <div class="chat-empty" id="emptyMsg">No messages yet. Say hello or share a file! 👋</div>
      <?php else: ?>
        <?php
          $prev_date = '';
          foreach ($msg_rows as $m):
            $is_me    = ($m['sender_role'] === $sender_role);
            $msg_date = date('d M Y', strtotime($m['sent_at']));
            $today    = date('d M Y');
            $yesterday= date('d M Y', strtotime('-1 day'));
            $label    = $msg_date === $today ? 'Today' : ($msg_date === $yesterday ? 'Yesterday' : $msg_date);
            if ($msg_date !== $prev_date): $prev_date = $msg_date; ?>
              <div class="date-sep"><?= $label ?></div>
            <?php endif; ?>

            <div class="bubble-wrap <?= $is_me ? 'me' : 'them' ?>"
                 id="msg-<?= $m['msg_id'] ?>"
                 data-id="<?= $m['msg_id'] ?>"
                 data-name="<?= htmlspecialchars($m['sender_name']) ?>"
                 data-text="<?= htmlspecialchars($m['message']) ?>">

              <div class="bubble-name"><?= htmlspecialchars($m['sender_name']) ?></div>

              <div class="bubble">
                <?php if (!empty($m['reply_name'])): ?>
                  <div class="reply-quote">
                    <strong><?= htmlspecialchars($m['reply_name']) ?></strong>:
                    <?= htmlspecialchars(substr($m['reply_text'] ?? '', 0, 80)) ?><?= strlen($m['reply_text'] ?? '') > 80 ? '…' : '' ?>
                  </div>
                <?php endif; ?>

                <?php if ($m['msg_type'] === 'file' && !empty($m['file_path'])): ?>
                  <?php if (is_image($m['file_name'])): ?>
                    <!-- Image preview -->
                    <img src="<?= htmlspecialchars($m['file_path']) ?>"
                         alt="<?= htmlspecialchars($m['file_name']) ?>"
                         class="img-preview"
                         onclick="openLightbox(this.src)"
                         loading="lazy"/>
                    <?php if ($m['message'] !== $m['file_name']): ?>
                      <div style="margin-top:6px;font-size:0.83rem;"><?= nl2br(htmlspecialchars($m['message'])) ?></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <!-- File download card -->
                    <a href="<?= htmlspecialchars($m['file_path']) ?>"
                       target="_blank" download="<?= htmlspecialchars($m['file_name']) ?>"
                       class="file-bubble">
                      <div class="file-icon"><?= file_icon($m['file_name']) ?></div>
                      <div class="file-info">
                        <div class="file-name"><?= htmlspecialchars($m['file_name']) ?></div>
                        <?php if ($m['message'] !== $m['file_name']): ?>
                          <div class="file-caption"><?= htmlspecialchars($m['message']) ?></div>
                        <?php endif; ?>
                        <div class="file-dl">Click to download ↓</div>
                      </div>
                    </a>
                  <?php endif; ?>
                <?php else: ?>
                  <?= nl2br(htmlspecialchars($m['message'])) ?>
                <?php endif; ?>
              </div>

              <div class="bubble-footer">
                <span class="bubble-time"><?= date('g:i A', strtotime($m['sent_at'])) ?></span>
                <button class="reply-btn" onclick="setReply(<?= $m['msg_id'] ?>)">↩ Reply</button>
              </div>
            </div>
          <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Reply banner -->
    <div class="reply-banner" id="replyBanner">
      <div class="reply-preview"><strong id="replyName"></strong>: <span id="replyText"></span></div>
      <button class="reply-cancel" onclick="clearReply()" title="Cancel reply">✕</button>
    </div>

    <!-- File preview bar (shown when file is selected) -->
    <div class="file-preview-bar" id="filePreviewBar">
      <span style="font-size:1.2rem;" id="filePreviewIcon">📎</span>
      <span class="file-preview-info" id="filePreviewName"></span>
      <span class="file-preview-size" id="filePreviewSize"></span>
      <button class="file-remove-btn" onclick="clearFile()" title="Remove file">✕</button>
    </div>

    <!-- Upload progress bar -->
    <div class="upload-progress" id="uploadProgress">
      <div class="upload-progress-bar" id="uploadProgressBar"></div>
    </div>

    <!-- Input area -->
    <div class="chat-input">
      <!-- Hidden file input -->
      <input type="file" id="fileInput" style="display:none"
             accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.zip,.jpg,.jpeg,.png,.gif,.webp"
             onchange="onFileSelected(this)"/>
      <button type="button" class="attach-btn" onclick="document.getElementById('fileInput').click()"
              title="Attach file (PDF, image, doc — max 10MB)">📎</button>

      <textarea id="msgInput"
                placeholder="Type a message… (Enter to send, Shift+Enter for new line)"
                rows="1" maxlength="2000"
                onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>

      <button class="send-btn" id="sendBtn" onclick="sendOrUpload()">Send →</button>
    </div>

  </div><!-- /chat-box -->

</div><!-- /page -->

<!-- Lightbox for image preview -->
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()">✕</button>
  <img id="lightboxImg" src="" alt="Preview"/>
</div>

<script>
  const REQUEST_ID  = <?= $request_id ?>;
  const SENDER_ROLE = '<?= $sender_role ?>';
  const SENDER_NAME = '<?= addslashes($sender_name) ?>';
  let   lastMsgId   = <?= $last_id ?>;
  let   replyToId   = null, replyToName = '', replyToText = '';
  let   selectedFile = null;
  let   isSending   = false;

  const box         = document.getElementById('chatMessages');
  const input       = document.getElementById('msgInput');
  const sendBtn     = document.getElementById('sendBtn');
  const emptyMsg    = document.getElementById('emptyMsg');
  const banner      = document.getElementById('replyBanner');
  const rnName      = document.getElementById('replyName');
  const rnText      = document.getElementById('replyText');
  const filebar     = document.getElementById('filePreviewBar');
  const fileInput   = document.getElementById('fileInput');
  const progressWrap= document.getElementById('uploadProgress');
  const progressBar = document.getElementById('uploadProgressBar');

  // ── Scroll ───────────────────────────────────────────────────────────────────
  function scrollBottom(force=false){
    const near = box.scrollHeight - box.scrollTop - box.clientHeight < 150;
    if(force||near) box.scrollTop = box.scrollHeight;
  }
  scrollBottom(true);

  // ── Textarea resize ──────────────────────────────────────────────────────────
  function autoResize(el){
    el.style.height='auto';
    el.style.height=Math.min(el.scrollHeight,120)+'px';
  }

  // ── Enter key ────────────────────────────────────────────────────────────────
  function handleKey(e){
    if(e.key==='Enter'&&!e.shiftKey){ e.preventDefault(); sendOrUpload(); }
  }

  // ── Decide: upload file or send text ─────────────────────────────────────────
  function sendOrUpload(){
    if(selectedFile) uploadFile();
    else             sendMessage();
  }

  // ── File selection ───────────────────────────────────────────────────────────
  function onFileSelected(input){
    const f = input.files[0];
    if(!f){ clearFile(); return; }
    if(f.size > 10*1024*1024){
      alert('File is too large. Maximum size is 10 MB.');
      clearFile(); return;
    }
    selectedFile = f;
    document.getElementById('filePreviewIcon').textContent = fileIcon(f.name);
    document.getElementById('filePreviewName').textContent = f.name;
    document.getElementById('filePreviewSize').textContent = formatBytes(f.size);
    filebar.classList.add('show');
    // Suggest caption placeholder
    document.getElementById('msgInput').placeholder = 'Add a caption (optional)…';
  }

  function clearFile(){
    selectedFile = null;
    fileInput.value = '';
    filebar.classList.remove('show');
    document.getElementById('msgInput').placeholder = 'Type a message… (Enter to send, Shift+Enter for new line)';
  }

  // ── Upload file ──────────────────────────────────────────────────────────────
  async function uploadFile(){
    if(!selectedFile || isSending) return;
    isSending = true;
    sendBtn.disabled = true;

    const caption = input.value.trim();
    const fd = new FormData();
    fd.append('chat_file', selectedFile);
    if(caption) fd.append('caption', caption);

    // Show progress
    progressWrap.classList.add('show');
    progressBar.style.width = '0%';

    try {
      const xhr = new XMLHttpRequest();
      xhr.open('POST', 'session_chat.php?request_id=' + REQUEST_ID);

      xhr.upload.onprogress = e => {
        if(e.lengthComputable){
          progressBar.style.width = Math.round(e.loaded/e.total*100) + '%';
        }
      };

      xhr.onload = () => {
        progressWrap.classList.remove('show');
        progressBar.style.width = '0%';
        try {
          const data = JSON.parse(xhr.responseText);
          if(data.error){ alert('Upload failed: ' + data.error); }
          else if(data.msg_id){
            if(emptyMsg) emptyMsg.remove();
            box.appendChild(renderBubble(data, true));
            lastMsgId = data.msg_id;
            scrollBottom(true);
          }
        } catch(e){ alert('Upload response error.'); }
        clearFile();
        input.value = '';
        input.style.height = 'auto';
        isSending = false;
        sendBtn.disabled = false;
        input.focus();
      };

      xhr.onerror = () => {
        progressWrap.classList.remove('show');
        alert('Network error during upload.');
        isSending = false;
        sendBtn.disabled = false;
      };

      xhr.send(fd);
    } catch(e){
      alert('Upload failed: ' + e.message);
      isSending = false;
      sendBtn.disabled = false;
    }
  }

  // ── Send text message ────────────────────────────────────────────────────────
  async function sendMessage(){
    const text = input.value.trim();
    if(!text || isSending) return;
    isSending = true;
    sendBtn.disabled = true;

    const fd = new FormData();
    fd.append('ajax_msg','1');
    fd.append('message', text);
    if(replyToId){
      fd.append('reply_to_id',   replyToId);
      fd.append('reply_to_name', replyToName);
      fd.append('reply_to_text', replyToText);
    }

    input.value = '';
    input.style.height = 'auto';
    clearReply();

    try{
      const res  = await fetch('session_chat.php?request_id='+REQUEST_ID, {method:'POST', body:fd});
      const data = await res.json();
      if(data && data.msg_id){
        if(emptyMsg) emptyMsg.remove();
        box.appendChild(renderBubble(data, true));
        lastMsgId = data.msg_id;
        scrollBottom(true);
      }
    } catch(e){ console.error('Send failed', e); }

    isSending = false;
    sendBtn.disabled = false;
    input.focus();
  }

  // ── Poll new messages ────────────────────────────────────────────────────────
  async function poll(){
    try{
      const res  = await fetch('session_chat.php?request_id='+REQUEST_ID+'&poll=1&after='+lastMsgId);
      const msgs = await res.json();
      if(msgs && msgs.length>0){
        if(emptyMsg) emptyMsg.remove();
        msgs.forEach(m=>{
          const isMe = (m.sender_role===SENDER_ROLE);
          box.appendChild(renderBubble(m, isMe));
          lastMsgId = m.msg_id;
        });
        scrollBottom();
      }
    } catch(e){}
  }
  setInterval(poll, 2000);

  // ── Render bubble ────────────────────────────────────────────────────────────
  function renderBubble(m, isMe){
    const wrap = document.createElement('div');
    wrap.className = 'bubble-wrap ' + (isMe?'me':'them');
    wrap.id        = 'msg-'+m.msg_id;
    wrap.dataset.id   = m.msg_id;
    wrap.dataset.name = m.sender_name;
    wrap.dataset.text = m.message;

    const nameEl = document.createElement('div');
    nameEl.className = 'bubble-name';
    nameEl.textContent = m.sender_name;

    const bub = document.createElement('div');
    bub.className = 'bubble';

    // Reply quote
    if(m.reply_name){
      const q = document.createElement('div');
      q.className = 'reply-quote';
      q.innerHTML = '<strong>'+escHtml(m.reply_name)+'</strong>: '+escHtml((m.reply_text||'').substring(0,80));
      bub.appendChild(q);
    }

    // File or text content
    if(m.msg_type === 'file' && m.file_path){
      if(isImageFile(m.file_name)){
        // Image preview
        const img = document.createElement('img');
        img.src       = m.file_path;
        img.alt       = m.file_name;
        img.className = 'img-preview';
        img.loading   = 'lazy';
        img.onclick   = () => openLightbox(img.src);
        bub.appendChild(img);
        if(m.message && m.message !== m.file_name){
          const cap = document.createElement('div');
          cap.style.cssText = 'margin-top:6px;font-size:0.83rem;';
          cap.innerHTML = nl2br(escHtml(m.message));
          bub.appendChild(cap);
        }
      } else {
        // File card
        const a = document.createElement('a');
        a.href      = m.file_path;
        a.target    = '_blank';
        a.download  = m.file_name;
        a.className = 'file-bubble';
        a.innerHTML =
          '<div class="file-icon">'+fileIcon(m.file_name)+'</div>'+
          '<div class="file-info">'+
            '<div class="file-name">'+escHtml(m.file_name)+'</div>'+
            (m.message && m.message!==m.file_name ? '<div class="file-caption">'+escHtml(m.message)+'</div>' : '')+
            '<div class="file-dl">Click to download ↓</div>'+
          '</div>';
        bub.appendChild(a);
      }
    } else {
      const txt = document.createElement('span');
      txt.innerHTML = nl2br(escHtml(m.message));
      bub.appendChild(txt);
    }

    const footer = document.createElement('div');
    footer.className = 'bubble-footer';

    const time = document.createElement('span');
    time.className   = 'bubble-time';
    time.textContent = formatTime(m.sent_at);

    const rBtn = document.createElement('button');
    rBtn.className   = 'reply-btn';
    rBtn.textContent = '↩ Reply';
    rBtn.onclick     = () => setReply(m.msg_id);

    footer.appendChild(time);
    footer.appendChild(rBtn);
    wrap.appendChild(nameEl);
    wrap.appendChild(bub);
    wrap.appendChild(footer);
    return wrap;
  }

  // ── Reply helpers ────────────────────────────────────────────────────────────
  function setReply(msgId){
    const el = document.getElementById('msg-'+msgId);
    if(!el) return;
    replyToId   = msgId;
    replyToName = el.dataset.name;
    replyToText = el.dataset.text.substring(0,80)+(el.dataset.text.length>80?'…':'');
    rnName.textContent = replyToName;
    rnText.textContent = replyToText;
    banner.classList.add('show');
    input.focus();
  }
  function clearReply(){
    replyToId=null; replyToName=''; replyToText='';
    banner.classList.remove('show');
  }

  // ── Lightbox ─────────────────────────────────────────────────────────────────
  function openLightbox(src){
    document.getElementById('lightboxImg').src = src;
    document.getElementById('lightbox').classList.add('show');
  }
  function closeLightbox(){
    document.getElementById('lightbox').classList.remove('show');
    document.getElementById('lightboxImg').src = '';
  }
  document.addEventListener('keydown', e=>{ if(e.key==='Escape') closeLightbox(); });

  // ── Helpers ──────────────────────────────────────────────────────────────────
  function escHtml(s){
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function nl2br(s){ return s.replace(/\n/g,'<br>'); }
  function formatTime(ts){
    const d = new Date(ts.replace(' ','T'));
    return isNaN(d) ? ts : d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'});
  }
  function formatBytes(b){
    if(b<1024) return b+' B';
    if(b<1024*1024) return (b/1024).toFixed(1)+' KB';
    return (b/1024/1024).toFixed(1)+' MB';
  }
  function isImageFile(name){
    return /\.(jpg|jpeg|png|gif|webp)$/i.test(name||'');
  }
  function fileIcon(name){
    const ext = (name||'').split('.').pop().toLowerCase();
    const icons = {pdf:'📄',doc:'📝',docx:'📝',ppt:'📊',pptx:'📊',xls:'📊',xlsx:'📊',txt:'📃',zip:'🗜',jpg:'🖼',jpeg:'🖼',png:'🖼',gif:'🖼',webp:'🖼'};
    return icons[ext]||'📎';
  }
</script>

</body>
</html>