<?php
session_start();
include "db_conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $t_user = $_POST['t_user'];
    $t_pass = $_POST['t_pass'];

    $sql    = "SELECT * FROM tutors WHERE fullname='$t_user' AND password='$t_pass'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        // ✅ Keys match exactly what Tdashboard.php reads
        $_SESSION['tutor_id']   = $row['id'];
        $_SESSION['tutor_name'] = $row['fullname'];
        // ✅ Correct destination
        header("Location: Tdashboard.php");
        exit();
    } else {
        $error = "Invalid Username or Password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Tutor Login – EduGuide</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --bg: #f0f4f9; --white: #fff; --orange: #e67e22; --orange-dark: #ca6f1e; --orange-soft: #fef3e2; --border: #dde3ee; --text: #1a2235; --muted: #6b7c99; --error: #dc2626; }
        body { min-height: 100vh; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); display: flex; flex-direction: column; }
        header { background: var(--white); border-bottom: 1px solid var(--border); padding: 0 2rem; height: 60px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 10; }
        .logo { font-family: 'Fraunces', serif; font-size: 1.4rem; color: var(--orange); letter-spacing: -0.02em; }
        .logo span { color: var(--text); }
        nav a { font-size: 0.82rem; font-weight: 500; color: var(--muted); text-decoration: none; padding: 6px 14px; border-radius: 8px; }
        nav a:hover { background: var(--orange-soft); color: var(--orange); }
        main { flex: 1; display: grid; grid-template-columns: 1fr 1fr; min-height: calc(100vh - 60px); }
        .panel-left { background: linear-gradient(155deg, #92400e 0%, #e67e22 55%, #f59e0b 100%); display: flex; flex-direction: column; justify-content: center; padding: 4rem 3.5rem; position: relative; overflow: hidden; }
        .panel-left::before { content: ''; position: absolute; width: 380px; height: 380px; border-radius: 50%; background: rgba(255,255,255,0.06); top: -80px; right: -80px; }
        .panel-left h2 { font-family: 'Fraunces', serif; font-size: 2.4rem; color: #fff; line-height: 1.15; margin-bottom: 1rem; position: relative; z-index: 1; }
        .panel-left p { font-size: 0.95rem; color: rgba(255,255,255,0.82); line-height: 1.7; max-width: 340px; position: relative; z-index: 1; }
        .features { margin-top: 2rem; display: flex; flex-direction: column; gap: 1rem; position: relative; z-index: 1; }
        .feature-item { display: flex; align-items: center; gap: 0.85rem; }
        .feature-icon { width: 38px; height: 38px; border-radius: 10px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .feature-text { font-size: 0.88rem; color: rgba(255,255,255,0.92); font-weight: 500; }
        .panel-right { display: flex; align-items: center; justify-content: center; padding: 3rem 2.5rem; background: var(--bg); }
        .form-card { width: 100%; max-width: 400px; animation: slideUp 0.5s ease both; }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .form-card h3 { font-size: 1.5rem; font-weight: 700; color: var(--text); margin-bottom: 0.35rem; letter-spacing: -0.02em; }
        .form-card .sub { font-size: 0.88rem; color: var(--muted); margin-bottom: 1.8rem; }
        .tutor-badge { display: inline-flex; align-items: center; gap: 6px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: var(--orange); background: var(--orange-soft); border: 1px solid #fcd99a; padding: 5px 12px; border-radius: 20px; margin-bottom: 1rem; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: var(--error); border-radius: 10px; padding: 0.75rem 1rem; font-size: 0.84rem; margin-bottom: 1.2rem; }
        .field { margin-bottom: 1.1rem; }
        .field label { display: block; font-size: 0.78rem; font-weight: 600; color: var(--text); letter-spacing: 0.03em; text-transform: uppercase; margin-bottom: 0.4rem; }
        .field input { width: 100%; background: var(--white); border: 1.5px solid var(--border); border-radius: 10px; padding: 0.72rem 0.95rem; font-size: 0.92rem; font-family: 'Plus Jakarta Sans', sans-serif; color: var(--text); outline: none; transition: border-color 0.2s, box-shadow 0.2s; }
        .field input::placeholder { color: #b0bcd0; }
        .field input:focus { border-color: var(--orange); box-shadow: 0 0 0 3px rgba(230,126,34,0.12); }
        .pass-wrap { position: relative; }
        .pass-wrap input { padding-right: 3rem; }
        .eye-btn { position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--muted); font-size: 1rem; padding: 0; }
        .btn-submit { width: 100%; padding: 0.85rem; background: var(--orange); color: #fff; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.97rem; font-weight: 600; border: none; border-radius: 10px; cursor: pointer; transition: background 0.2s, transform 0.15s; box-shadow: 0 4px 16px rgba(230,126,34,0.3); }
        .btn-submit:hover { background: var(--orange-dark); transform: translateY(-1px); }
        .divider { display: flex; align-items: center; gap: 0.75rem; margin: 1.4rem 0; color: var(--muted); font-size: 0.78rem; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
        .register-link { text-align: center; font-size: 0.84rem; color: var(--muted); }
        .register-link a { color: var(--orange); font-weight: 600; text-decoration: none; }
        @media (max-width: 820px) { main { grid-template-columns: 1fr; } .panel-left { display: none; } }
    </style>
</head>
<body>
<header>
    <div class="logo">Edu<span>Guide</span></div>
    <nav>
        <a href="index.php">Home</a>
        <a href="Tutor.php">← Back</a>
    </nav>
</header>
<main>
    <div class="panel-left">
        <h2>Tutor Access Portal</h2>
        <p>Manage your student requests and help learners achieve their academic goals.</p>
        <div class="features">
            <div class="feature-item"><div class="feature-icon">📋</div><div class="feature-text">View pending session requests</div></div>
            <div class="feature-item"><div class="feature-icon">✅</div><div class="feature-text">Accept or decline students</div></div>
            <div class="feature-item"><div class="feature-icon">📈</div><div class="feature-text">Track your teaching activity</div></div>
        </div>
    </div>
    <div class="panel-right">
        <div class="form-card">
            <div class="tutor-badge">📖 Tutor Portal</div>
            <h3>Sign in as Tutor</h3>
            <p class="sub">Enter your full name and password to access your dashboard.</p>
            <?php if (isset($error)): ?>
                <div class="alert-error">⚠ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form action="Tutlogin.php" method="POST" autocomplete="off">
                <div class="field">
                    <label for="t_user">Full Name</label>
                    <input type="text" id="t_user" name="t_user" placeholder="Enter your full name" required
                           value="<?= isset($_POST['t_user']) ? htmlspecialchars($_POST['t_user']) : '' ?>"/>
                </div>
                <div class="field">
                    <label for="t_pass">Password</label>
                    <div class="pass-wrap">
                        <input type="password" id="t_pass" name="t_pass" placeholder="Enter your password" required/>
                        <button type="button" class="eye-btn" onclick="togglePwd()">👁</button>
                    </div>
                </div>
                <!-- ✅ Clean submit button -->
                <button type="submit" class="btn-submit">Sign In →</button>
            </form>
            <div class="divider">or</div>
            <p class="register-link">New tutor? <a href="Tutregister.php">Register here</a></p>
        </div>
    </div>
</main>
<script>
    function togglePwd() {
        const f = document.getElementById('t_pass');
        f.type = f.type === 'password' ? 'text' : 'password';
    }
</script>
</body>
</html>