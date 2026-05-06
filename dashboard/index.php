<?php
require_once '../config/db.php';
require_once '../jobs/parser.php';
require_once '../jobs/matcher.php';

// Auth guard
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id   = (int) $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name']);
$message   = '';
$msg_type  = '';

// ── Handle Upload ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume'])) {

    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $orig_name = basename($_FILES['resume']['name']);
    $ext       = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
    $err       = $_FILES['resume']['error'];

    if ($err !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload_max_filesize limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form MAX_FILE_SIZE.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        ];
        $message  = $upload_errors[$err] ?? "Upload error code: $err";
        $msg_type = 'danger';

    } elseif (!in_array($ext, ['pdf', 'docx'])) {
        $message  = 'Only PDF and DOCX files are accepted.';
        $msg_type = 'danger';

    } elseif ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
        $message  = 'File size must not exceed 5 MB.';
        $msg_type = 'danger';

    } else {
        $new_name   = $user_id . '_' . time() . '.' . $ext;
        $dest       = $upload_dir . $new_name;

        if (move_uploaded_file($_FILES['resume']['tmp_name'], $dest)) {
            $text        = ResumeParser::extractText($dest);
            $clean_text  = $conn->real_escape_string($text);
            $clean_name  = $conn->real_escape_string($new_name);

            // Upsert resume (one row per user)
            $conn->query("INSERT INTO resumes (user_id, file_path, extracted_text)
                          VALUES ($user_id, '$clean_name', '$clean_text')
                          ON DUPLICATE KEY UPDATE file_path='$clean_name', extracted_text='$clean_text'");

            // Replace skills
            $conn->query("DELETE FROM user_skills WHERE user_id=$user_id");
            $skills = ResumeParser::extractSkills($text);
            $stmt = $conn->prepare("INSERT IGNORE INTO user_skills (user_id, skill) VALUES (?, ?)");
            foreach ($skills as $skill) {
                $stmt->bind_param("is", $user_id, $skill);
                $stmt->execute();
            }
            $stmt->close();

            $skill_count = count($skills);
            $message     = "✓ Resume analyzed! Found <strong>$skill_count skill" . ($skill_count !== 1 ? 's' : '') . "</strong>.";
            $msg_type    = 'success';
        } else {
            $message  = 'File move failed. Check uploads/ folder permissions.';
            $msg_type = 'danger';
        }
    }
}

// ── Fetch Data ─────────────────────────────────────────────────────────────
$resume_res  = $conn->query("SELECT * FROM resumes WHERE user_id=$user_id LIMIT 1");
$resume_data = $resume_res->fetch_assoc();

$skills_res  = $conn->query("SELECT skill FROM user_skills WHERE user_id=$user_id ORDER BY skill");
$user_skills = [];
while ($row = $skills_res->fetch_assoc()) $user_skills[] = $row['skill'];

$matched_jobs = getMatchedJobs($conn, $user_skills);
?>
<?php include '../includes/header.php'; ?>

<div class="container" style="max-width:1200px">

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <div>
            <h2 class="fw-8 mb-1">
                <i class="bi bi-speedometer2 me-2" style="color:var(--clr-primary)"></i>
                Dashboard
            </h2>
            <p class="text-secondary mb-0">Welcome back, <strong><?php echo $user_name; ?></strong>!</p>
        </div>
        <?php if($resume_data): ?>
        <a href="../uploads/<?php echo htmlspecialchars($resume_data['file_path']); ?>" target="_blank"
           class="btn btn-outline-primary btn-sm px-3">
            <i class="bi bi-eye me-1"></i> View Resume
        </a>
        <?php endif; ?>
    </div>

    <!-- Alert -->
    <?php if($message): ?>
    <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show mb-4">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- ── Row 1: Upload + Skills ── -->
    <div class="row g-4 mb-4">

        <!-- Upload Card -->
        <div class="col-lg-4">
            <div class="card p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="bi bi-cloud-arrow-up me-2" style="color:var(--clr-primary)"></i>Upload Resume</h5>
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="mb-3">
                        <div class="upload-zone" id="dropZone"
                             style="border:2px dashed #c7d2fe;border-radius:12px;padding:28px;text-align:center;cursor:pointer;transition:all .2s;background:#f8faff"
                             onclick="document.getElementById('resumeFile').click()">
                            <i class="bi bi-file-earmark-arrow-up fs-2 mb-2 d-block" style="color:var(--clr-primary)"></i>
                            <div class="fw-600 mb-1" style="font-size:.9rem">Click to browse</div>
                            <div class="text-secondary" style="font-size:.78rem">PDF or DOCX · Max 5 MB</div>
                        </div>
                        <input type="file" name="resume" id="resumeFile" class="d-none" accept=".pdf,.docx">
                        <div id="fileName" class="mt-2 small text-secondary text-center"></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                        <i class="bi bi-cpu me-1"></i> Analyze Resume
                    </button>
                </form>

                <?php if($resume_data): ?>
                <hr class="my-3">
                <div class="small">
                    <div class="text-secondary mb-1 fw-600" style="font-size:.72rem;letter-spacing:.5px">CURRENT RESUME</div>
                    <div class="d-flex align-items-center gap-2 p-2 rounded-2" style="background:#f8faff">
                        <i class="bi bi-file-earmark-pdf text-danger fs-5"></i>
                        <div class="overflow-hidden">
                            <div class="fw-600 text-truncate"><?php echo htmlspecialchars($resume_data['file_path']); ?></div>
                            <div class="text-secondary" style="font-size:.72rem">
                                <?php echo date('M d, Y • g:i A', strtotime($resume_data['uploaded_at'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Skills Card -->
        <div class="col-lg-8">
            <div class="card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold mb-0"><i class="bi bi-tags me-2" style="color:var(--clr-primary)"></i>Extracted Skills</h5>
                    <?php if(!empty($user_skills)): ?>
                    <span class="badge text-bg-primary rounded-pill px-3"><?php echo count($user_skills); ?> found</span>
                    <?php endif; ?>
                </div>

                <?php if(!empty($user_skills)): ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach($user_skills as $skill): ?>
                        <span class="skill-badge"><?php echo htmlspecialchars($skill); ?></span>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="d-flex flex-column align-items-center justify-content-center h-75 text-center py-4">
                    <i class="bi bi-file-earmark-break fs-1 text-secondary mb-3"></i>
                    <p class="text-secondary mb-0">Upload your resume to automatically extract skills.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Row 2: Job Matches ── -->
    <div class="card p-4">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <h5 class="fw-bold mb-0"><i class="bi bi-briefcase me-2" style="color:var(--clr-primary)"></i>Job Matches</h5>
            <?php if(!empty($matched_jobs)): ?>
            <span class="text-secondary small"><?php echo count($matched_jobs); ?> job<?php echo count($matched_jobs)!==1?'s':''; ?> matched</span>
            <?php endif; ?>
        </div>

        <?php if(!empty($matched_jobs)): ?>
        <div class="row g-3">
            <?php foreach($matched_jobs as $job):
                $pct    = $job['match_percent'];
                $color  = $pct >= 80 ? 'var(--clr-success)' : ($pct >= 50 ? 'var(--clr-primary)' : 'var(--clr-warn)');
                $badge  = $pct >= 80 ? 'bg-success' : ($pct >= 50 ? 'text-bg-primary' : 'bg-warning text-dark');
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card border h-100" style="border-color:#e2e8f0 !important">
                    <div class="card-body p-3">

                        <!-- Title + Badge -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold mb-0 me-2"><?php echo htmlspecialchars($job['title']); ?></h6>
                            <span class="badge <?php echo $badge; ?> rounded-pill flex-shrink-0" style="font-size:.75rem">
                                <?php echo $pct; ?>%
                            </span>
                        </div>

                        <!-- Match Bar -->
                        <div class="match-bar-wrap mb-3">
                            <div class="match-bar" style="width:<?php echo $pct; ?>%;background:<?php echo $color; ?>"></div>
                        </div>

                        <!-- Description -->
                        <p class="text-secondary mb-3" style="font-size:.8rem;line-height:1.5">
                            <?php echo htmlspecialchars(substr($job['description'], 0, 110)); ?>…
                        </p>

                        <!-- Matched Skills -->
                        <?php if(!empty($job['matched_skills'])): ?>
                        <div class="mb-2">
                            <div class="text-secondary mb-1" style="font-size:.68rem;font-weight:700;letter-spacing:.5px">MATCHED</div>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach($job['matched_skills'] as $ms): ?>
                                    <span class="skill-badge" style="font-size:.68rem;padding:3px 10px"><?php echo htmlspecialchars($ms); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Missing Skills -->
                        <?php if(!empty($job['missing_skills'])): ?>
                        <div>
                            <div class="text-secondary mb-1" style="font-size:.68rem;font-weight:700;letter-spacing:.5px">MISSING</div>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach($job['missing_skills'] as $ms): ?>
                                    <span class="skill-badge missing" style="font-size:.68rem;padding:3px 10px"><?php echo htmlspecialchars($ms); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-search fs-1 text-secondary mb-3 d-block"></i>
            <h6 class="fw-bold">No Matches Yet</h6>
            <p class="text-secondary small">Upload a resume containing skills like PHP, SQL, JavaScript, Python, etc.</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
// Drop-zone interactivity
const zone   = document.getElementById('dropZone');
const input  = document.getElementById('resumeFile');
const label  = document.getElementById('fileName');

input.addEventListener('change', () => {
    if (input.files[0]) {
        label.textContent = '📄 ' + input.files[0].name;
        zone.style.borderColor = 'var(--clr-primary)';
        zone.style.background  = '#eff6ff';
    }
});

['dragover','dragenter'].forEach(e => zone.addEventListener(e, ev => {
    ev.preventDefault();
    zone.style.borderColor = 'var(--clr-primary)';
    zone.style.background  = '#eff6ff';
}));

['dragleave','drop'].forEach(e => zone.addEventListener(e, ev => {
    ev.preventDefault();
    if (e === 'drop' && ev.dataTransfer.files[0]) {
        const dt = new DataTransfer();
        dt.items.add(ev.dataTransfer.files[0]);
        input.files = dt.files;
        label.textContent = '📄 ' + ev.dataTransfer.files[0].name;
    }
    zone.style.borderColor = '#c7d2fe';
    zone.style.background  = '#f8faff';
}));

// Submit loading state
document.getElementById('uploadForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Analyzing…';
});
</script>

<?php include '../includes/footer.php'; ?>
