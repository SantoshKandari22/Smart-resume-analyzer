<?php include_once __DIR__ . '/config/db.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<!-- ── Hero ── -->
<section style="padding:4rem 0 3rem">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="badge text-bg-primary mb-3 px-3 py-2 rounded-pill" style="font-size:.8rem;font-weight:600;letter-spacing:.5px">
                    <i class="bi bi-stars me-1"></i> AI-Powered Matching
                </span>
                <h1 class="display-4 fw-8 lh-sm mb-4">
                    Analyze Your Resume.<br>
                    <span style="background:linear-gradient(135deg,#4361ee,#4cc9f0);-webkit-background-clip:text;-webkit-text-fill-color:transparent">Land Your Dream Job.</span>
                </h1>
                <p class="lead text-secondary mb-5" style="max-width:480px">
                    Upload your PDF or DOCX resume — we extract your skills, then match you with real job opportunities and show your exact percentage fit.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="/job-matcher/auth/register.php" class="btn btn-primary btn-lg px-5">
                        <i class="bi bi-rocket-takeoff me-2"></i>Get Started Free
                    </a>
                    <a href="/job-matcher/auth/login.php" class="btn btn-outline-primary btn-lg px-4">
                        Sign In
                    </a>
                </div>

                <!-- Stats Row -->
                <div class="d-flex gap-4 mt-5">
                    <div>
                        <div class="fs-4 fw-8" style="color:var(--clr-primary)">30+</div>
                        <div class="text-secondary small">Skills Tracked</div>
                    </div>
                    <div style="border-left:2px solid #e2e8f0; padding-left:1.5rem">
                        <div class="fs-4 fw-8" style="color:var(--clr-primary)">10+</div>
                        <div class="text-secondary small">Job Roles</div>
                    </div>
                    <div style="border-left:2px solid #e2e8f0; padding-left:1.5rem">
                        <div class="fs-4 fw-8" style="color:var(--clr-primary)">100%</div>
                        <div class="text-secondary small">Free to Use</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="position-relative">
                    <!-- Decorative blob -->
                    <div style="position:absolute;inset:-20px;background:linear-gradient(135deg,rgba(67,97,238,.12),rgba(76,201,240,.1));border-radius:32px;transform:rotate(-3deg);z-index:0"></div>
                    <!-- Mockup Card -->
                    <div class="card p-4 position-relative" style="z-index:1;border-radius:20px">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div style="width:44px;height:44px;background:linear-gradient(135deg,#4361ee,#4cc9f0);border-radius:12px;display:flex;align-items:center;justify-content:center">
                                <i class="bi bi-file-earmark-person text-white fs-5"></i>
                            </div>
                            <div>
                                <div class="fw-bold">resume_john_doe.pdf</div>
                                <div class="text-secondary small">Uploaded just now</div>
                            </div>
                            <span class="badge bg-success ms-auto">Analyzed ✓</span>
                        </div>
                        <hr class="my-2">
                        <div class="mb-3">
                            <div class="small fw-600 text-secondary mb-2">EXTRACTED SKILLS</div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach(['PHP','JavaScript','SQL','HTML','CSS','Laravel','MySQL','Git'] as $s): ?>
                                    <span class="skill-badge"><?php echo $s; ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="small fw-600 text-secondary mb-2">TOP JOB MATCH</div>
                        <div class="d-flex align-items-center gap-3 p-2 rounded-3" style="background:#f8faff">
                            <div class="flex-grow-1">
                                <div class="fw-bold small">Full Stack Engineer</div>
                                <div class="match-bar-wrap mt-1">
                                    <div class="match-bar" style="width:80%;background:linear-gradient(90deg,#4361ee,#4cc9f0)"></div>
                                </div>
                            </div>
                            <span class="badge" style="background:#4361ee;font-size:.85rem;padding:6px 10px">80%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── How It Works ── -->
<section style="padding:3rem 0;background:#fff">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-8 mb-2">How It Works</h2>
            <p class="text-secondary">Three simple steps to find your perfect job match</p>
        </div>
        <div class="row g-4 text-center">
            <?php
            $steps = [
                ['bi-cloud-arrow-up', '#4361ee', 'Upload Resume', 'Upload your PDF or DOCX resume securely to our platform.'],
                ['bi-cpu',            '#7c3aed', 'Skill Extraction', 'We automatically detect technical skills from your resume text.'],
                ['bi-briefcase-fill', '#0891b2', 'Job Matching', 'Get ranked job matches with a real percentage fit score.'],
            ];
            foreach ($steps as $i => [$icon, $color, $title, $desc]):
            ?>
            <div class="col-md-4">
                <div class="card h-100 p-4 text-center">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-3"
                         style="width:60px;height:60px;background:<?php echo $color; ?>1a">
                        <i class="bi <?php echo $icon; ?> fs-3" style="color:<?php echo $color; ?>"></i>
                    </div>
                    <div class="fw-700 mb-1" style="font-size:1.05rem"><?php echo $title; ?></div>
                    <p class="text-secondary small mb-0"><?php echo $desc; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── CTA ── -->
<section style="padding:4rem 0">
    <div class="container text-center">
        <div class="card mx-auto p-5" style="max-width:640px;background:linear-gradient(135deg,var(--clr-primary),var(--clr-primary-d));border-radius:24px;color:#fff">
            <i class="bi bi-rocket-takeoff-fill fs-1 mb-3"></i>
            <h2 class="fw-8 mb-2">Ready to find your dream job?</h2>
            <p class="mb-4 opacity-75">Join today and let your resume speak for itself.</p>
            <a href="/job-matcher/auth/register.php" class="btn btn-light btn-lg px-5 fw-600" style="color:var(--clr-primary)">
                Create Free Account
            </a>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
