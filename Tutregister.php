<?php
include "db_conn.php";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = mysqli_real_escape_string($conn, $_POST['t_name']);
    $exp   = mysqli_real_escape_string($conn, $_POST['expertise']);
    $avail = $_POST['availability'];
    $bio   = mysqli_real_escape_string($conn, $_POST['bio']);
    $pass  = $_POST['t_pass'];
    $sql   = "INSERT INTO tutors (fullname, expertise, availability, bio, password) VALUES ('$name','$exp','$avail','$bio','$pass')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Registration successful! Please log in.'); window.location='Tutlogin.php';</script>";
    } else { $reg_error = "Error: " . mysqli_error($conn); }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tutor Registration – EduGuide</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{--bg:#f0f4f9;--white:#fff;--orange:#e67e22;--orange-dark:#ca6f1e;--orange-soft:#fef3e2;--border:#dde3ee;--text:#1a2235;--muted:#6b7c99;--error:#dc2626;}
    body{min-height:100vh;font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);display:flex;flex-direction:column;}
    header{background:var(--white);border-bottom:1px solid var(--border);padding:0 2rem;height:60px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10;}
    .logo{font-family:'Fraunces',serif;font-size:1.4rem;color:var(--orange);letter-spacing:-0.02em;text-decoration:none;}
    .logo span{color:var(--text);}
    nav a{font-size:0.82rem;font-weight:500;color:var(--muted);text-decoration:none;padding:6px 14px;border-radius:8px;transition:background 0.18s,color 0.18s;margin-left:4px;}
    nav a:hover{background:var(--orange-soft);color:var(--orange);}
    main{flex:1;display:grid;grid-template-columns:1fr 1fr;min-height:calc(100vh - 60px);}
    .panel-left{background:linear-gradient(155deg,#92400e 0%,#e67e22 55%,#f59e0b 100%);display:flex;flex-direction:column;justify-content:center;padding:4rem 3.5rem;position:relative;overflow:hidden;}
    .panel-left::before{content:'';position:absolute;width:380px;height:380px;border-radius:50%;background:rgba(255,255,255,0.06);top:-80px;right:-80px;}
    .panel-left::after{content:'';position:absolute;width:260px;height:260px;border-radius:50%;background:rgba(255,255,255,0.06);bottom:-60px;left:-60px;}
    .panel-left h2{font-family:'Fraunces',serif;font-size:2.4rem;color:#fff;line-height:1.15;margin-bottom:1rem;position:relative;z-index:1;}
    .panel-left p{font-size:0.95rem;color:rgba(255,255,255,0.82);line-height:1.7;max-width:340px;position:relative;z-index:1;}
    .features{margin-top:2.5rem;display:flex;flex-direction:column;gap:1rem;position:relative;z-index:1;}
    .feature-item{display:flex;align-items:center;gap:0.85rem;}
    .feature-icon{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,0.18);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
    .feature-text{font-size:0.88rem;color:rgba(255,255,255,0.92);font-weight:500;}
    .panel-right{display:flex;align-items:center;justify-content:center;padding:3rem 2.5rem;background:var(--bg);overflow-y:auto;}
    .form-card{width:100%;max-width:440px;animation:slideUp 0.5s ease both;}
    @keyframes slideUp{from{opacity:0;transform:translateY(20px);}to{opacity:1;transform:translateY(0);}}
    .tutor-badge{display:inline-flex;align-items:center;gap:6px;font-size:0.75rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--orange);background:var(--orange-soft);border:1px solid #fcd99a;padding:5px 12px;border-radius:20px;margin-bottom:1rem;}
    .form-card h3{font-size:1.55rem;font-weight:700;color:var(--text);margin-bottom:0.35rem;letter-spacing:-0.02em;}
    .form-card .sub{font-size:0.88rem;color:var(--muted);margin-bottom:1.8rem;}
    .error-box{background:#fef2f2;border:1px solid #fecaca;color:var(--error);border-radius:10px;padding:0.75rem 1rem;font-size:0.84rem;margin-bottom:1.2rem;}
    .field{margin-bottom:1rem;}
    .field label{display:block;font-size:0.78rem;font-weight:600;color:var(--text);letter-spacing:0.03em;text-transform:uppercase;margin-bottom:0.4rem;}
    .field input,.field select,.field textarea{width:100%;background:var(--white);border:1.5px solid var(--border);border-radius:10px;padding:0.72rem 0.95rem;font-size:0.92rem;font-family:'Plus Jakarta Sans',sans-serif;color:var(--text);outline:none;transition:border-color 0.2s,box-shadow 0.2s;appearance:none;-webkit-appearance:none;}
    .field textarea{resize:vertical;min-height:90px;}
    .field input::placeholder,.field textarea::placeholder{color:#b0bcd0;}
    .field input:focus,.field select:focus,.field textarea:focus{border-color:var(--orange);box-shadow:0 0 0 3px rgba(230,126,34,0.12);}
    .select-wrap{position:relative;}
    .select-wrap::after{content:'▾';position:absolute;right:0.95rem;top:50%;transform:translateY(-50%);color:var(--muted);pointer-events:none;font-size:0.8rem;}
    .pass-wrap{position:relative;}
    .pass-wrap input{padding-right:3rem;}
    .eye-btn{position:absolute;right:0.85rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--muted);font-size:1rem;padding:0;line-height:1;}
    .btn-submit{width:100%;margin-top:1rem;padding:0.85rem;background:var(--orange);color:#fff;font-family:'Plus Jakarta Sans',sans-serif;font-size:0.97rem;font-weight:600;border:none;border-radius:10px;cursor:pointer;transition:background 0.2s,transform 0.15s;box-shadow:0 4px 16px rgba(230,126,34,0.3);}
    .btn-submit:hover{background:var(--orange-dark);transform:translateY(-1px);}
    .login-link{text-align:center;margin-top:1.2rem;font-size:0.84rem;color:var(--muted);}
    .login-link a{color:var(--orange);font-weight:600;text-decoration:none;}
    @media(max-width:820px){main{grid-template-columns:1fr;}.panel-left{display:none;}.panel-right{padding:2rem 1.2rem;min-height:calc(100vh - 60px);}}
  </style>
</head>
<body>
<header>
  <a href="index.php" class="logo">Edu<span>Guide</span></a>
  <nav><a href="index.php">Home</a><a href="Tutor.php">← Back</a></nav>
</header>
<main>
  <div class="panel-left">
    <h2>Join as a Tutor</h2>
    <p>Share your expertise, set your schedule, and help students succeed in their academic journey.</p>
    <div class="features">
      <div class="feature-item"><div class="feature-icon">📋</div><div class="feature-text">Receive matched student requests</div></div>
      <div class="feature-item"><div class="feature-icon">🕐</div><div class="feature-text">Set your own availability</div></div>
      <div class="feature-item"><div class="feature-icon">💬</div><div class="feature-text">Chat with students in sessions</div></div>
    </div>
  </div>
  <div class="panel-right">
    <div class="form-card">
      <div class="tutor-badge">📖 Tutor Registration</div>
      <h3>Create your profile</h3>
      <p class="sub">Fill in your details to start receiving student requests.</p>
      <?php if(!empty($reg_error)):?><div class="error-box">⚠ <?=htmlspecialchars($reg_error)?></div><?php endif;?>
      <form method="POST" autocomplete="off">
        <div class="field"><label>Full Name</label><input type="text" name="t_name" placeholder="Enter your full name" required/></div>
        <div class="field"><label>Expertise (Subject)</label>
          <div class="select-wrap">
            <select name="expertise" required>
              <option value="" disabled selected>Select your subject…</option>
              <option value="Bahasa Melayu">Bahasa Melayu</option>
              <option value="English">English</option>
              <option value="Mathematics">Mathematics</option>
              <option value="Science">Science</option>
              <option value="History">History</option>
              <option value="Pendidikan Islam">Pendidikan Islam</option>
              <option value="Pendidikan Moral">Pendidikan Moral</option>
            </select>
          </div>
        </div>
        <div class="field"><label>Availability</label>
          <div class="select-wrap">
            <select name="availability" required>
              <option value="" disabled selected>Select availability…</option>
              <option value="Morning">Morning</option>
              <option value="Afternoon">Afternoon</option>
              <option value="Evening">Evening</option>
              <option value="Weekend">Weekend</option>
            </select>
          </div>
        </div>
        <div class="field"><label>Bio</label><textarea name="bio" placeholder="Tell students about yourself and your teaching style…" required></textarea></div>
        <div class="field"><label>Password</label>
          <div class="pass-wrap">
            <input type="password" id="t_pass" name="t_pass" placeholder="Create a password" required/>
            <button type="button" class="eye-btn" onclick="togglePwd()">👁</button>
          </div>
        </div>
        <button type="submit" class="btn-submit">Register Now →</button>
      </form>
      <p class="login-link">Already have an account? <a href="Tutlogin.php">Log in here</a></p>
    </div>
  </div>
</main>
<script>function togglePwd(){const f=document.getElementById('t_pass');f.type=f.type==='password'?'text':'password';}</script>
</body>
</html>