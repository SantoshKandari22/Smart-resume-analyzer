<?php
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../dashboard/index.php"); exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $safe_email = $conn->real_escape_string($email);
    $result     = $conn->query("SELECT * FROM users WHERE email='$safe_email' LIMIT 1");

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header("Location: ../dashboard/index.php");
            exit();
        } else {
            $error = 'Incorrect password.';
        }
    } else {
        $error = 'No account found with that email.';
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="container" style="max-width:480px">
    <div class="card p-4 p-md-5">

        <div class="text-center mb-4">
            <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-3"
                 style="width:56px;height:56px;background:linear-gradient(135deg,var(--clr-primary),var(--clr-primary-d))">
                <i class="bi bi-shield-lock-fill text-white fs-4"></i>
            </div>
            <h2 class="fw-8 mb-1">Welcome Back</h2>
            <p class="text-secondary small">Sign in to view your job matches</p>
        </div>

        <?php if($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label fw-600">Email Address</label>
                <input type="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       placeholder="john@example.com" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label fw-600">Password</label>
                <input type="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Sign In</button>
            </div>
        </form>

        <div class="text-center mt-4 small text-secondary">
            Don't have an account? <a href="register.php" class="fw-600 text-decoration-none" style="color:var(--clr-primary)">Create one free</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
