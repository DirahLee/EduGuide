<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['fullname'];
    $sid  = $_POST['id_no'];
    $subj = $_POST['subject'];
    $sess = $_POST['session_type'];
    $pass = $_POST['password'];

    $sname   = "localhost:3307";
    $uname   = "root";
    $db_name = "eduguide_db";

    $conn = @mysqli_connect($sname, $uname, "", $db_name);
    if (!$conn) $conn = mysqli_connect($sname, $uname, "root", $db_name);
    if (!$conn) die("Connection failed: " . mysqli_connect_error());

    $sql  = "INSERT INTO students (fullname, id_no, subject, session_type, password) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssss", $name, $sid, $subj, $sess, $pass);
        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Registration successful!'); window.location='login.php';</script>";
        } else {
            $reg_error = "Registration Error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    } else {
        $reg_error = "Prepare Error: " . mysqli_error($conn);
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Registration – EduGuide</title>
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
      max-width: 440px;
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

    /* Error banner */
    .error-box {
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: var(--error);
      border-radius: 10px;
      padding: 0.75rem 1rem;
      font-size: 0.84rem;
      margin-bottom: 1.2rem;
    }

    /* Step indicator */
    .steps {
      display: flex;
      align-items: center;
      gap: 6px;
      margin-bottom: 1.6rem;
    }
    .step {
      height: 4px;
      border-radius: 4px;
      flex: 1;
      background: var(--border);
      transition: background 0.3s;
    }
    .step.active { background: var(--blue); }
    .steps-label {
      font-size: 0.75rem;
      color: var(--muted);
      margin-bottom: 0.5rem;
    }

    /* Fields */
    .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; }

    .field { margin-bottom: 1rem; }
    .field label {
      display: block;
      font-size: 0.78rem;
      font-weight: 600;
      color: var(--text);
      letter-spacing: 0.03em;
      text-transform: uppercase;
      margin-bottom: 0.4rem;
    }

    .field input,
    .field select {
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
      appearance: none;
      -webkit-appearance: none;
    }
    .field input::placeholder { color: #b0bcd0; }
    .field input:focus,
    .field select:focus {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.12);
    }

    .select-wrap { position: relative; }
    .select-wrap::after {
      content: '▾';
      position: absolute;
      right: 0.95rem;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      pointer-events: none;
      font-size: 0.8rem;
    }

    /* Session cards */
    .session-cards { display: grid; grid-template-columns: 1fr 1fr; gap: 0.7rem; }
    .session-card {
      position: relative;
      cursor: pointer;
    }
    .session-card input[type="radio"] {
      position: absolute;
      opacity: 0;
      width: 0; height: 0;
    }
    .session-card label {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      padding: 0.85rem 0.5rem;
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: 12px;
      cursor: pointer;
      transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
      text-transform: none;
      letter-spacing: 0;
      color: var(--muted);
      font-size: 0.82rem;
      font-weight: 500;
    }
    .session-card label .icon { font-size: 1.5rem; }
    .session-card input:checked + label {
      border-color: var(--blue);
      background: var(--blue-soft);
      color: var(--blue);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }

    /* Password */
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

    /* Strength */
    .strength-track { display: flex; gap: 4px; margin-top: 6px; }
    .strength-track span {
      flex: 1; height: 3px;
      border-radius: 3px;
      background: var(--border);
      transition: background 0.3s;
    }
    .strength-track.s1 span:nth-child(1) { background: #ef4444; }
    .strength-track.s2 span:nth-child(-n+2) { background: #f59e0b; }
    .strength-track.s3 span { background: #22c55e; }
    .s-label { font-size: 0.72rem; color: var(--muted); margin-top: 3px; }

    /* Submit */
    .btn-submit {
      width: 100%;
      margin-top: 1.2rem;
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

    .login-link {
      text-align: center;
      margin-top: 1.2rem;
      font-size: 0.84rem;
      color: var(--muted);
    }
    .login-link a { color: var(--blue); font-weight: 600; text-decoration: none; }
    .login-link a:hover { text-decoration: underline; }

    /* Responsive */
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

  <!-- LEFT DECORATIVE PANEL -->
  <div class="panel-left">
    <h2>Start Your Learning Journey</h2>
    <p>Join hundreds of students already improving their grades with personalised tutoring sessions.</p>

    <div class="features">
      <div class="feature-item">
        <div class="feature-icon">🎯</div>
        <div class="feature-text">Get matched with the right tutor</div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">📅</div>
        <div class="feature-text">Book sessions at your convenience</div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">📈</div>
        <div class="feature-text">Track your progress over time</div>
      </div>
    </div>
  </div>

  <!-- RIGHT FORM PANEL -->
  <div class="panel-right">
    <div class="form-card">

      <p class="steps-label">Registration — Step 1 of 1</p>
      <div class="steps">
        <div class="step active"></div>
        <div class="step active"></div>
        <div class="step active"></div>
      </div>

      <h3>Create your account</h3>
      <p class="sub">Fill in your details below to get started.</p>

      <?php if (!empty($reg_error)): ?>
        <div class="error-box">⚠ <?= htmlspecialchars($reg_error) ?></div>
      <?php endif; ?>

      <form action="register.php" method="POST" autocomplete="off">

        <div class="field">
          <label for="fullname">Full Name</label>
          <input type="text" id="fullname" name="fullname" placeholder="e.g. Amirah Binti Zainal" required/>
        </div>

        <div class="field">
          <label for="id_no">Student ID No.</label>
          <input type="text" id="id_no" name="id_no" placeholder="e.g. AM2412318269" required/>
        </div>

        <div class="field">
          <label for="subject">Select a class</label>
          <div class="select-wrap">
            <select id="subject" name="subject" required>
            <option value="" disabled selected>classes code</option>
            <option value="CT204">CT204</option>
            <option value="CT206">CT206</option>
            <option value="CT207">CT207</option>
            <option value="CC01">CC01</option>
            </select>
          </div>
        </div>

        <div class="field">
          <label>Session Preference</label>
          <div class="session-cards">
            <div class="session-card">
              <input type="radio" name="session_type" id="individual" value="individual" checked/>
              <label for="individual">
                <span class="icon">👤</span>
                One-on-One
              </label>
            </div>
            <div class="session-card">
              <input type="radio" name="session_type" id="group" value="group"/>
              <label for="group">
                <span class="icon">👥</span>
                Group Session
              </label>
            </div>
          </div>
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="pass-wrap">
            <input type="password" id="password" name="password" placeholder="Create a password" required oninput="checkStr(this.value)"/>
            <button type="button" class="eye-btn" onclick="togglePwd()">👁</button>
          </div>
          <div class="strength-track" id="strack">
            <span></span><span></span><span></span>
          </div>
          <div class="s-label" id="slabel"></div>
        </div>

        <button type="submit" class="btn-submit">Register Now →</button>
      </form>

      <p class="login-link">Already have an account? <a href="login.php">Log in here</a></p>
    </div>
  </div>

</main>

<script>
  function togglePwd() {
    const f = document.getElementById('password');
    f.type = f.type === 'password' ? 'text' : 'password';
  }

  function checkStr(v) {
    const t = document.getElementById('strack');
    const l = document.getElementById('slabel');
    t.className = 'strength-track';
    if (!v) { l.textContent = ''; return; }
    let s = 0;
    if (v.length >= 8) s++;
    if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
    if (/[0-9]/.test(v) && /\W/.test(v)) s++;
    const map = { 1: ['s1','Weak','#ef4444'], 2: ['s2','Fair','#f59e0b'], 3: ['s3','Strong','#22c55e'] };
    if (map[s]) {
      t.classList.add(map[s][0]);
      l.textContent = map[s][1] + ' password';
      l.style.color = map[s][2];
    }
  }
</script>

</body>
</html>