<?php
$message_sent = false;
$sender_name  = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_name = htmlspecialchars($_POST['name']);
    $email       = htmlspecialchars($_POST['email']);
    $message     = htmlspecialchars($_POST['message']);
    $message_sent = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Contact Us – EduGuide</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fraunces:opsz,wght@9..144,700&display=swap" rel="stylesheet"/>
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    :root{--bg:#f0f4f9;--white:#fff;--blue:#2563eb;--blue-dark:#1d4ed8;--blue-soft:#eff6ff;--border:#dde3ee;--text:#1a2235;--muted:#6b7c99;--green:#16a34a;--green-soft:#f0fdf4;}
    body{min-height:100vh;font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);display:flex;flex-direction:column;}
    header{position:sticky;top:0;z-index:200;background:rgba(255,255,255,0.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);height:62px;display:flex;align-items:center;justify-content:space-between;padding:0 2.5rem;}
    header h1{font-family:'Fraunces',serif;font-size:1.5rem;color:var(--blue);font-weight:700;}
    header nav{display:flex;align-items:center;gap:0.25rem;}
    header nav a{font-size:0.85rem;font-weight:500;color:var(--muted);text-decoration:none;padding:7px 16px;border-radius:8px;transition:background 0.18s,color 0.18s;}
    header nav a:hover{background:var(--blue-soft);color:var(--blue);}
    header nav a.active{color:var(--blue);font-weight:600;}
    .page{flex:1;max-width:700px;margin:0 auto;padding:4rem 1.5rem;}
    .page-badge{display:inline-flex;align-items:center;gap:6px;font-size:0.75rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;color:var(--blue);background:var(--blue-soft);border:1px solid #bfdbfe;padding:6px 16px;border-radius:20px;margin-bottom:1.2rem;}
    .page h2{font-family:'Fraunces',serif;font-size:2.2rem;color:var(--text);margin-bottom:0.5rem;letter-spacing:-0.02em;}
    .page .sub{font-size:0.95rem;color:var(--muted);margin-bottom:2.5rem;line-height:1.6;}
    .success-box{background:var(--green-soft);border:1px solid #bbf7d0;color:var(--green);border-radius:12px;padding:1rem 1.2rem;font-size:0.9rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:0.6rem;}
    .form-card{background:var(--white);border:1px solid var(--border);border-radius:20px;padding:2.2rem;box-shadow:0 2px 12px rgba(0,0,0,0.05);}
    .field{margin-bottom:1.1rem;}
    .field label{display:block;font-size:0.78rem;font-weight:600;color:var(--text);letter-spacing:0.03em;text-transform:uppercase;margin-bottom:0.4rem;}
    .field input,.field textarea{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:10px;padding:0.72rem 0.95rem;font-size:0.92rem;font-family:'Plus Jakarta Sans',sans-serif;color:var(--text);outline:none;transition:border-color 0.2s,box-shadow 0.2s;}
    .field textarea{resize:vertical;min-height:130px;}
    .field input::placeholder,.field textarea::placeholder{color:#b0bcd0;}
    .field input:focus,.field textarea:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(37,99,235,0.12);background:var(--white);}
    .btn-submit{width:100%;margin-top:0.5rem;padding:0.85rem;background:var(--blue);color:#fff;font-family:'Plus Jakarta Sans',sans-serif;font-size:0.97rem;font-weight:600;border:none;border-radius:10px;cursor:pointer;transition:background 0.2s,transform 0.15s;box-shadow:0 4px 16px rgba(37,99,235,0.3);}
    .btn-submit:hover{background:var(--blue-dark);transform:translateY(-1px);}
    .contact-info{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:2.5rem;}
    .info-card{background:var(--white);border:1px solid var(--border);border-radius:14px;padding:1.2rem;text-align:center;}
    .info-icon{font-size:1.5rem;margin-bottom:0.5rem;}
    .info-title{font-size:0.82rem;font-weight:700;color:var(--text);margin-bottom:0.25rem;}
    .info-val{font-size:0.78rem;color:var(--muted);}
    footer{text-align:center;padding:2rem 1.5rem;font-size:0.8rem;color:var(--muted);border-top:1px solid var(--border);}
    @media(max-width:600px){.contact-info{grid-template-columns:1fr;}.page{padding:2rem 1rem;}}
  </style>
</head>
<body>
<header>
  <h1>EduGuide</h1>
  <nav>
    <a href="index.php">Home</a>
    <a href="Student.php">Student</a>
    <a href="Tutor.php">Tutor</a>
    <a href="Contact.php" class="active">Contact Us</a>
  </nav>
</header>

<div class="page">
  <div class="page-badge">💬 Support</div>
  <h2>Get in Touch</h2>
  <p class="sub">Have questions about EduGuide or need help with your account? We're here for you.</p>

  <div class="contact-info">
    <div class="info-card"><div class="info-icon">📧</div><div class="info-title">Email</div><div class="info-val">EduGuide@eduguide.my</div></div>
    <div class="info-card"><div class="info-icon">📍</div><div class="info-title">Location</div><div class="info-val">Kuala Lumpur, Malaysia</div></div>
    <div class="info-card"><div class="info-icon">🕐</div><div class="info-title">Response Time</div><div class="info-val">Within 24 hours</div></div>
  </div>

  <?php if($message_sent):?>
  <div class="success-box">✅ Thank you, <?=$sender_name?>! Your message has been sent. We'll get back to you soon.</div>
  <?php endif;?>

  <div class="form-card">
    <form action="Contact.php" method="POST">
      <div class="field"><label>Your Name</label><input type="text" name="name" placeholder="Full name" required></div>
      <div class="field"><label>Email Address</label><input type="email" name="email" placeholder="email@example.com" required></div>
      <div class="field"><label>Message</label><textarea name="message" placeholder="How can we help you today?" required></textarea></div>
      <button type="submit" class="btn-submit">Send Message →</button>
    </form>
  </div>
</div>

<footer>© <?php echo date('Y'); ?> EduGuide. All rights reserved.</footer>
</body>
</html>