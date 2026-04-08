<?php
session_start();
$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sid  = $_POST['id_no'];
    $pass = $_POST['password'];

    $sname   = "localhost:3307";
    $uname   = "root";
    $db_name = "eduguide_db";

    $conn = @mysqli_connect($sname, $uname, "", $db_name);
    if (!$conn) $conn = mysqli_connect($sname, $uname, "root", $db_name);
    if (!$conn) die("Connection failed: " . mysqli_connect_error());

    $sql  = "SELECT * FROM students WHERE id_no = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $sid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if ($pass === $row['password']) {
                // ✅ FIXED: was 'student_id'/'student_name' — now matches what dashboard reads
                $_SESSION['id_no']    = $row['id_no'];
                $_SESSION['fullname'] = $row['fullname'];
                // ✅ FIXED: redirect to student_dashboard.php
                header("Location: Student_dashboard.php");
                exit();
            } else {
                $login_error = "Incorrect password. Please try again.";
            }
        } else {
            $login_error = "No account found with that Student ID.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $login_error = "Query Error: " . mysqli_error($conn);
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Login – EduGuide</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:        #f0f4f9;
      --white:     #ffffff;
      --blue:      #2563eb;
      --blue-dark: #1d4ed8;
      --blue-soft: #eff6ff;
      --border:    #dde3ee;
      --text:      #1a2235;
      --muted:     #6b7c99;
      --error:     #dc2626;
      --radius:    14px;
    }

    body {
      min-height: 100vh;
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: var(--bg);
      display: flex;
      flex-direction: column;
    }

    /* ── HEADER ── */
    header {
      background: var(--white);
      border-bottom: 1px solid var(--border);
      padding: 0 2rem;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 10;
    }

    .logo {
      font-family: 'Fraunces', serif;
      font-size: 1.4rem;
      color: var(--blue);
      letter-spacing: -0.02em;
    }
    .logo span { color: var(--text); }

    nav { display: flex; gap: 0.5rem; }
    nav a {
      font-size: 0.82rem;
      font-weight: 500;
      color: var(--muted);
      text-decoration: none;
      padding: 6px 14px;
      border-radius: 8px;
      transition: background 0.18s, color 0.18s;
    }
    nav a:hover { background: var(--blue-soft); color: var(--blue); }

    /* ── LAYOUT ── */
    main {
      flex: 1;
      display: grid;
      grid-template-columns: 1fr 1fr;
      min-height: calc(100vh - 60px);
    }

    /* LEFT PANEL */
    .panel-left {
      background: linear-gradient(155deg, #1e3a8a 0%, #2563eb 55%, #38bdf8 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 4rem 3.5rem;
      position: relative;
      overflow: hidden;
    }

    .panel-left::before {
      content: '';
      position: absolute;
      width: 380px; height: 380px;
      border-radius: 50%;
      background: rgba(255,255,255,0.06);
      top: -80px; right: -80px;
    }
    .panel-left::after {
      content: '';
      position: absolute;
      width: 260px; height: 260px;
      border-radius: 50%;
      background: rgba(255,255,255,0.06);
      bottom: -60px; left: -60px;
    }

    .panel-left h2 {
      font-family: 'Fraunces', serif;
      font-size: 2.6rem;
      color: #fff;
      line-height: 1.15;
      margin-bottom: 1rem;
      position: relative;
      z-index: 1;
    }

    .panel-left p {
      font-size: 0.95rem;
      color: rgba(255,255,255,0.78);
      line-height: 1.7;
      max-width: 340px;
      position: relative;
      z-index: 1;
    }

    .features {
      margin-top: 2.5rem;
      display: flex;
      flex-direction: column;
      gap: 1rem;
      position: relative;
      z-index: 1;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 0.85rem;
    }

    .feature-icon {
      width: 38px; height: 38px;
      border-radius: 10px;
      background: rgba(255,255,255,0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.1rem;
      flex-shrink: 0;
    }

    .feature-text {
      font-size: 0.88rem;
      color: rgba(255,255,255,0.9);
      font-weight: 500;
    }

    /* RIGHT PANEL */
    .panel-right {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3rem 2.5rem;
      background: var(--bg);
    }

    .form-card {
      width: 100%;
      max-width: 400px;
      animation: slideUp 0.5s ease both;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .form-card h3 {
      font-size: 1.55rem;
      font-weight: 700;
      color: var(--text);
      margin-bottom: 0.35rem;
      letter-spacing: -0.02em;
    }

    .form-card .sub {
      font-size: 0.88rem;
      color: var(--muted);
      margin-bottom: 1.8rem;
    }

    /* Error / success banners */
    .alert {
      border-radius: 10px;
      padding: 0.75rem 1rem;
      font-size: 0.84rem;
      margin-bottom: 1.2rem;
      border: 1px solid;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }
    .alert-error {
      background: #fef2f2;
      border-color: #fecaca;
      color: var(--error);
    }

    /* Fields */
    .field { margin-bottom: 1.1rem; }
    .field label {
      display: block;
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--text);
      letter-spacing: 0.03em;
      text-transform: uppercase;
      margin-bottom: 0.4rem;
    }

    .field input {
      width: 100%;
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: 10px;
      padding: 0.72rem 0.95rem;
      font-size: 0.92rem;
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: var(--text);
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .field input::placeholder { color: #b0bcd0; }
    .field input:focus {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    /* Password wrapper */
    .pass-wrap { position: relative; }
    .pass-wrap input { padding-right: 3rem; }
    .eye-btn {
      position: absolute;
      right: 0.85rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: var(--muted);
      font-size: 1rem;
      padding: 0;
      line-height: 1;
      transition: color 0.2s;
    }
    .eye-btn:hover { color: var(--blue); }

    /* Remember me row */
    .remember-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 1.4rem;
      margin-top: 0.2rem;
    }
    .remember-row label {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: 0.84rem;
      color: var(--muted);
      cursor: pointer;
      font-weight: 400;
    }
    .remember-row input[type="checkbox"] {
      accent-color: var(--blue);
      width: 15px;
      height: 15px;
      cursor: pointer;
    }
    .forgot-link {
      font-size: 0.82rem;
      color: var(--blue);
      text-decoration: none;
      font-weight: 500;
    }
    .forgot-link:hover { text-decoration: underline; }

    /* Submit */
    .btn-submit {
      width: 100%;
      padding: 0.85rem;
      background: var(--blue);
      color: #fff;
      font-family: 'Plus Jakarta Sans', sans-serif;
      font-size: 0.97rem;
      font-weight: 600;
      border: none;
      border-radius: 10px;
      cursor: pointer;
      transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
      box-shadow: 0 4px 16px rgba(37,99,235,0.3);
      letter-spacing: 0.01em;
    }
    .btn-submit:hover { background: var(--blue-dark); transform: translateY(-1px); box-shadow: 0 8px 24px rgba(37,99,235,0.38); }
    .btn-submit:active { transform: translateY(0); }

    /* Divider */
    .divider {
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin: 1.4rem 0;
      color: var(--muted);
      font-size: 0.78rem;
    }
    .divider::before, .divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border);
    }

    .register-link {
      text-align: center;
      font-size: 0.84rem;
      color: var(--muted);
    }
    .register-link a { color: var(--blue); font-weight: 600; text-decoration: none; }
    .register-link a:hover { text-decoration: underline; }

    /* Welcome back badge */
    .welcome-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.75rem;
      font-weight: 600;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--blue);
      background: var(--blue-soft);
      border: 1px solid #bfdbfe;
      padding: 5px 12px;
      border-radius: 20px;
      margin-bottom: 1rem;
    }

    @media (max-width: 820px) {
      main { grid-template-columns: 1fr; }
      .panel-left { display: none; }
      .panel-right { padding: 2rem 1.2rem; min-height: calc(100vh - 60px); }
    }
  </style>
</head>
<body>

<header>
  <div class="logo">Edu<span>Guide</span></div>
  <nav>
    <a href="index.php">Home</a>
    <a href="Student.php">← Back</a>
  </nav>
</header>

<main>

  <!-- LEFT PANEL -->
  <div class="panel-left">
    <h2>Welcome Back, Scholar!</h2>
    <p>Log in to access your personalised learning sessions and connect with your tutors.</p>

    <div class="features">
      <div class="feature-item">
        <div class="feature-icon">📚</div>
        <div class="feature-text">View your upcoming sessions</div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">💬</div>
        <div class="feature-text">Chat with your assigned tutor</div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">📊</div>
        <div class="feature-text">Check your learning progress</div>
      </div>
    </div>
  </div>

  <!-- RIGHT PANEL -->
  <div class="panel-right">
    <div class="form-card">

      <div class="welcome-badge">🎓 Student Portal</div>
      <h3>Sign in to your account</h3>
      <p class="sub">Enter your Student ID and password to continue.</p>

      <?php if (!empty($login_error)): ?>
        <div class="alert alert-error">⚠ <?= htmlspecialchars($login_error) ?></div>
      <?php endif; ?>

      <form action="login.php" method="POST" autocomplete="off">

        <div class="field">
          <label for="id_no">Student ID No.</label>
          <input type="text" id="id_no" name="id_no" placeholder="e.g. AM2412318269" required
                 value="<?= isset($_POST['id_no']) ? htmlspecialchars($_POST['id_no']) : '' ?>"/>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="pass-wrap">
            <input type="password" id="password" name="password" placeholder="Enter your password" required/>
            <button type="button" class="eye-btn" onclick="togglePwd()" title="Show/hide password">👁</button>
          </div>
        </div>

        <div class="remember-row">
          <label>
            <input type="checkbox" name="remember"/> Remember me
          </label>
          <a href="forgot_password.php" class="forgot-link">Forgot password?</a>
        </div>

       <button type="submit" class="btn-submit">Sign In →</button>

      </form>

      <div class="divider">or</div>

      <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>

    </div>
  </div>

</main>

<script>
  function togglePwd() {
    const f = document.getElementById('password');
    f.type = f.type === 'password' ? 'text' : 'password';
  }
</script>

</body>
</html>