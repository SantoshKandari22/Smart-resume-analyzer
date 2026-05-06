<?php
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php"); exit();
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($name) < 2)          $errors[] = 'Name must be at least 2 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if (strlen($password) < 6)      $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm)     $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        $safe_email = $conn->real_escape_string($email);
        $check = $conn->query("SELECT id FROM users WHERE email='$safe_email' LIMIT 1");
        if ($check->num_rows > 0) {
            $errors[] = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $safe_name  = $conn->real_escape_string($name);
            $safe_hash  = $conn->real_escape_string($hash);
            $conn->query("INSERT INTO users (name, email, password) VALUES ('$safe_name','$safe_email','$safe_hash')");
            $success = true;
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container" style="max-width:480px">
    <div class="card p-4 p-md-5">

        <div class="text-center mb-4">
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-3"
                 style="width:56px;height:56px;background:linear-gradient(135deg,var(--clr-primary),var(--clr-primary-d))">
                <i class="bi bi-person-plus-fill text-white fs-4"></i>
            </div>
            <h2 class="fw-8 mb-1">Create Account</h2>
            <p class="text-secondary small">Start analyzing your resume for free</p>
        </div>

        <?php if(!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                <?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if($success): ?>
        <div class="alert alert-success">
            Account created! <a href="login.php" class="fw-bold">Sign in now &rarr;</a>
        </div>
        <?php else: ?>
        <form method="POST" action="" novalidate>
            <div class="mb-3">
                <label class="form-label fw-600">Full Name</label>
                <input type="text" name="name" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                       placeholder="John Doe" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-600">Email Address</label>
                <input type="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="john@example.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-600">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="Min. 6 characters" required>
            </div>
            <div class="mb-4">
                <label class="form-label fw-600">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control"
                       placeholder="Repeat password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Create Account</button>
            </div>
        </form>
        <?php endif; ?>

        <div class="text-center mt-4 small text-secondary">
            Already have an account? <a href="login.php" class="fw-600 text-decoration-none" style="color:var(--clr-primary)">Sign in</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
