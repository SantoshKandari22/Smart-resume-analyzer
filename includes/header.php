<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Resume Analyzer</title>
    <meta name="description" content="Upload your resume and instantly match your skills with the best job opportunities. AI-powered skill extraction and job matching.">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        /* ── Design Tokens ── */
        :root {
            --clr-primary:   #4361ee;
            --clr-primary-d: #3a0ca3;
            --clr-accent:    #4cc9f0;
            --clr-success:   #22c55e;
            --clr-warn:      #f59e0b;
            --clr-danger:    #ef4444;
            --clr-bg:        #f0f4ff;
            --clr-card:      #ffffff;
            --shadow-sm: 0 4px 14px rgba(67,97,238,.08);
            --shadow-md: 0 10px 40px rgba(67,97,238,.12);
            --radius:    14px;
        }

        /* ── Base ── */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--clr-bg);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main { flex: 1; padding: 2.5rem 0; }

        /* ── Navbar ── */
        .navbar {
            background: rgba(255,255,255,.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(67,97,238,.1);
            padding: .9rem 0;
            position: sticky;
            top: 0;
            z-index: 1040;
        }
        .navbar-brand {
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -.5px;
            color: #1e293b !important;
        }
        .navbar-brand .brand-accent { color: var(--clr-primary); }
        .nav-link {
            font-weight: 500;
            color: #475569 !important;
            transition: color .2s;
        }
        .nav-link:hover { color: var(--clr-primary) !important; }
        .nav-link.active { color: var(--clr-primary) !important; }

        /* ── Buttons ── */
        .btn-primary {
            background: linear-gradient(135deg, var(--clr-primary), var(--clr-primary-d));
            border: none;
            border-radius: 10px;
            font-weight: 600;
            letter-spacing: .2px;
            transition: transform .2s, box-shadow .2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67,97,238,.35);
            background: linear-gradient(135deg, #5372f5, var(--clr-primary));
        }
        .btn-outline-primary {
            border-color: var(--clr-primary);
            color: var(--clr-primary);
            border-radius: 10px;
            font-weight: 500;
            transition: all .2s;
        }
        .btn-outline-primary:hover {
            background: var(--clr-primary);
            color: #fff;
            transform: translateY(-2px);
        }
        .btn-outline-danger {
            border-radius: 10px;
            font-weight: 500;
            transition: all .2s;
        }

        /* ── Cards ── */
        .card {
            border: none;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            background: var(--clr-card);
            transition: box-shadow .25s, transform .25s;
        }
        .card:hover { box-shadow: var(--shadow-md); }

        /* ── Skill Badges ── */
        .skill-badge {
            background: #eff6ff;
            color: var(--clr-primary);
            border: 1.5px solid #bfdbfe;
            border-radius: 50px;
            padding: 5px 14px;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .3px;
            display: inline-block;
            transition: background .2s;
        }
        .skill-badge:hover { background: #dbeafe; }
        .skill-badge.missing {
            background: #fff7ed;
            color: #c2410c;
            border-color: #fed7aa;
        }

        /* ── Match Bar ── */
        .match-bar-wrap { height: 6px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
        .match-bar { height: 6px; border-radius: 99px; transition: width .8s cubic-bezier(.4,0,.2,1); }

        /* ── Alerts ── */
        .alert { border: none; border-radius: 12px; font-weight: 500; }

        /* ── Footer ── */
        footer {
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 1.4rem 0;
            margin-top: auto;
        }

        /* ── Helpers ── */
        .text-primary { color: var(--clr-primary) !important; }
        .fw-8 { font-weight: 800; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand" href="/job-matcher/index.php">
            <i class="bi bi-file-earmark-person-fill me-1" style="color:var(--clr-primary)"></i>
            Resume<span class="brand-accent">Matcher</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/job-matcher/dashboard/index.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-outline-danger btn-sm px-3" href="/job-matcher/auth/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/job-matcher/auth/login.php">Login</a>
                    </li>
                    <li class="nav-item ms-2">
                        <a class="btn btn-primary btn-sm px-4" href="/job-matcher/auth/register.php">Get Started</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main>
