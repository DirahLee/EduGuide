<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduGuide - Home</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,700;1,9..144,400&display=swap" rel="stylesheet"/>
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
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── HEADER ── */
        header {
            position: sticky;
            top: 0;
            z-index: 200;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            height: 62px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.5rem;
        }

        header h1 {
            font-family: 'Fraunces', serif;
            font-size: 1.5rem;
            color: var(--blue);
            letter-spacing: -0.02em;
            font-weight: 700;
        }

        header nav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        header nav a {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--muted);
            text-decoration: none;
            padding: 7px 16px;
            border-radius: 8px;
            transition: background 0.18s, color 0.18s;
        }
        header nav a:hover { background: var(--blue-soft); color: var(--blue); }
        header nav a.active { color: var(--blue); font-weight: 600; }

        /* ── HERO ── */
        .hero {
            position: relative;
            min-height: calc(100vh - 62px);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 1.5rem 3rem;
            overflow: hidden;
        }

        /* Animated gradient background */
        .hero-bg {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #f0f4f9 0%, #e8f0fe 50%, #f0f4f9 100%);
            z-index: 0;
        }

        /* Floating orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.5;
            animation: drift 8s ease-in-out infinite alternate;
            pointer-events: none;
        }
        .orb-1 { width: 500px; height: 500px; background: #bfdbfe; top: -120px; left: -100px; animation-duration: 9s; }
        .orb-2 { width: 400px; height: 400px; background: #c7d2fe; bottom: -80px; right: -60px; animation-duration: 11s; animation-delay: -3s; }
        .orb-3 { width: 300px; height: 300px; background: #bae6fd; top: 30%; left: 40%; animation-duration: 13s; animation-delay: -6s; opacity: 0.3; }

        @keyframes drift {
            from { transform: translate(0, 0) scale(1); }
            to   { transform: translate(30px, 20px) scale(1.05); }
        }

        /* Grid pattern overlay */
        .hero-grid {
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(37,99,235,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(37,99,235,0.04) 1px, transparent 1px);
            background-size: 40px 40px;
            z-index: 1;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 760px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--blue);
            background: var(--blue-soft);
            border: 1px solid #bfdbfe;
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            animation: fadeDown 0.6s ease both;
        }

        .hero h2 {
            font-family: 'Fraunces', serif;
            font-size: clamp(2.4rem, 5vw, 3.8rem);
            color: var(--text);
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 1.2rem;
            animation: fadeDown 0.6s ease 0.1s both;
        }

        .hero h2 em {
            font-style: italic;
            color: var(--blue);
        }

        .hero p {
            font-size: 1.05rem;
            color: var(--muted);
            line-height: 1.7;
            max-width: 520px;
            margin: 0 auto 2.5rem;
            font-weight: 400;
            animation: fadeDown 0.6s ease 0.2s both;
        }

        .hero-cta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
            animation: fadeDown 0.6s ease 0.3s both;
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.85rem 2rem;
            background: var(--blue);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(37,99,235,0.35);
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        }
        .btn-primary:hover { background: var(--blue-dark); transform: translateY(-2px); box-shadow: 0 8px 30px rgba(37,99,235,0.45); }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 0.85rem 2rem;
            background: var(--white);
            color: var(--text);
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            transition: border-color 0.2s, transform 0.15s, box-shadow 0.2s;
        }
        .btn-secondary:hover { border-color: var(--blue); color: var(--blue); transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,0.08); }

        /* Scroll indicator */
        .scroll-hint {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            color: var(--muted);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            animation: fadeDown 0.6s ease 0.6s both;
        }
        .scroll-arrow {
            width: 28px; height: 28px;
            border: 1.5px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            animation: bounce 2s ease-in-out infinite;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(5px); }
        }

        /* ── PORTAL CARDS SECTION ── */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 5rem 1.5rem 6rem;
        }

        .section-label {
            text-align: center;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--blue);
            margin-bottom: 0.75rem;
        }

        .section-title {
            text-align: center;
            font-family: 'Fraunces', serif;
            font-size: 2rem;
            color: var(--text);
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }

        .section-sub {
            text-align: center;
            font-size: 0.9rem;
            color: var(--muted);
            margin-bottom: 3rem;
        }

        .card-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.4rem;
        }

        /* PORTAL CARD */
        .card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            position: relative;
            overflow: hidden;
            transition: box-shadow 0.25s, transform 0.25s;
            animation: fadeUp 0.5s ease both;
        }
        .card:nth-child(2) { animation-delay: 0.1s; }

        .card:hover { box-shadow: 0 16px 48px rgba(0,0,0,0.1); transform: translateY(-4px); }

        /* Decorative corner accent */
        .card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 120px; height: 120px;
            border-radius: 0 20px 0 120px;
            opacity: 0.06;
        }
        .card:nth-child(1)::before { background: var(--blue); }
        .card:nth-child(2)::before { background: #0ea5e9; }

        .card-icon {
            width: 56px; height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
        }
        .card:nth-child(1) .card-icon { background: var(--blue-soft); }
        .card:nth-child(2) .card-icon { background: #f0f9ff; }

        .card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.01em;
        }

        .card p {
            font-size: 0.88rem;
            color: var(--muted);
            line-height: 1.65;
            flex: 1;
        }

        /* Feature dots */
        .card-features {
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            padding: 1rem;
            background: var(--bg);
            border-radius: 12px;
        }
        .card-feature {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            color: var(--muted);
            font-weight: 500;
        }
        .card-feature::before {
            content: '✓';
            width: 18px; height: 18px;
            border-radius: 50%;
            background: var(--blue-soft);
            color: var(--blue);
            font-size: 0.65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 0.75rem 1.5rem;
            background: var(--blue);
            color: #fff;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(37,99,235,0.3);
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            align-self: flex-start;
        }
        .btn:hover { background: var(--blue-dark); transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,0.38); }

        /* ── HOW IT WORKS ── */
        .how-section {
            background: var(--white);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 5rem 1.5rem;
        }

        .how-inner {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }

        .steps-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 3rem;
            position: relative;
        }

        .steps-row::before {
            content: '';
            position: absolute;
            top: 28px;
            left: calc(16.6% + 28px);
            right: calc(16.6% + 28px);
            height: 1px;
            background: var(--border);
            z-index: 0;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
            animation: fadeUp 0.5s ease both;
        }
        .step-item:nth-child(2) { animation-delay: 0.1s; }
        .step-item:nth-child(3) { animation-delay: 0.2s; }

        .step-num {
            width: 56px; height: 56px;
            border-radius: 50%;
            background: var(--blue);
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 16px rgba(37,99,235,0.3);
        }

        .step-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--text);
        }

        .step-desc {
            font-size: 0.82rem;
            color: var(--muted);
            line-height: 1.6;
            max-width: 200px;
        }

        /* ── FOOTER ── */
        footer {
            text-align: center;
            padding: 2rem 1.5rem;
            font-size: 0.8rem;
            color: var(--muted);
            border-top: 1px solid var(--border);
        }

        /* ── ANIMATIONS ── */
        @keyframes fadeDown {
            from { opacity: 0; transform: translateY(-16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 700px) {
            header { padding: 0 1rem; }
            header nav a { padding: 6px 10px; font-size: 0.78rem; }
            .card-container { grid-template-columns: 1fr; }
            .steps-row { grid-template-columns: 1fr; }
            .steps-row::before { display: none; }
            .hero h2 { font-size: 2.2rem; }
            .container { padding: 3rem 1rem 4rem; }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <header>
        <h1>EduGuide</h1>
        <nav>
            <a href="index.php" class="active">Home</a>
            <a href="Student.php" class="active"> Student</a>
            <a href="Tutor.php">Tutor</a>
            <a href="Contact.php">Contact Us</a>
        </nav>
    </header>

    <!-- HERO -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
        <div class="hero-grid"></div>

        <div class="hero-content">
            <div class="hero-badge">🎓 Smart Tutoring Platform</div>
            <h2>Personalized Learning &amp;<br><em>Expert Guidance</em></h2>
            <p>Connecting students with the right tutors for academic success — matched by subject, available on your schedule.</p>
            <div class="hero-cta">
                <a href="Student.php" class="btn-primary">I'm a Student →</a>
                <a href="Tutor.php" class="btn-secondary">I'm a Tutor</a>
            </div>
        </div>

        <div class="scroll-hint">
            <div class="scroll-arrow">↓</div>
            Scroll
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <div class="how-section">
        <div class="how-inner">
            <div class="section-label">How it works</div>
            <div class="section-title">Three simple steps</div>
            <p class="section-sub">Getting started with EduGuide takes less than a minute.</p>
            <div class="steps-row">
                <div class="step-item">
                    <div class="step-num">1</div>
                    <div class="step-title">Create an Account</div>
                    <p class="step-desc">Register as a student or tutor and fill in your profile details.</p>
                </div>
                <div class="step-item">
                    <div class="step-num">2</div>
                    <div class="step-title">Get Matched</div>
                    <p class="step-desc">Our system matches students with tutors based on subject expertise.</p>
                </div>
                <div class="step-item">
                    <div class="step-num">3</div>
                    <div class="step-title">Start Learning</div>
                    <p class="step-desc">Request a session and begin your personalized learning journey.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- PORTAL CARDS -->
    <div class="container">
        <div class="section-label">Choose your portal</div>
        <div class="section-title">Who are you?</div>
        <p class="section-sub">Select the portal that best describes you to get started.</p>

        <div class="card-container">
            <div class="card">
                <div class="card-icon">🎒</div>
                <h3>I am a Student</h3>
                <p>Register to find expert tutors matched to your subject and manage your learning sessions all in one place.</p>
                <div class="card-features">
                    <div class="card-feature">Get matched with subject-specific tutors</div>
                    <div class="card-feature">Request and track your sessions</div>
                    <div class="card-feature">Choose your session preference</div>
                </div>
                <a href="Student.php" class="btn">Enter Student Portal →</a>
            </div>

            <div class="card">
                <div class="card-icon">📖</div>
                <h3>I am a Tutor</h3>
                <p>Browse session requests from students, manage your availability, and grow your tutoring practice effortlessly.</p>
                <div class="card-features">
                    <div class="card-feature">View and accept student requests</div>
                    <div class="card-feature">Set your own availability</div>
                    <div class="card-feature">Manage your student assignments</div>
                </div>
                <a href="Tutor.php" class="btn">Enter Tutor Portal →</a>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer>
        © <?php echo date('Y'); ?> EduGuide. All rights reserved.
    </footer>

</body>
</html>