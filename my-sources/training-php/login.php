<?php
// Start the session
session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();

$redis = new Redis();
$redis->connect('redis', 6379); // dùng service name trong docker-compose.yml

// --- CSRF token: ensure one per session ---
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['submit'])) {
    // 1. Validate CSRF token
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        // invalid token
        http_response_code(403);
        $_SESSION['message'] = 'Invalid CSRF token';
        // Optionally log this event
        // error_log("CSRF token mismatch for IP: " . $_SERVER['REMOTE_ADDR']);
        header('Location: login.php'); // or show error
        exit;
    }

    // 2. Validate inputs (basic trimming)
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Simple input check
    if ($username === '' || $password === '') {
        $_SESSION['message'] = 'Please enter username and password';
    } else {
        // Authenticate user (assume UserModel::auth uses password_verify)
        if ($user = $userModel->auth($username, $password)) {
            $userId = $user[0]['id'];
            $username = $user[0]['name'];

            // Regenerate session id to prevent fixation
            session_regenerate_id(true);

            // 1. PHP Session
            $_SESSION['id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['message'] = 'Login successful';

            // 2. Redis Session (tạo token ngẫu nhiên)
            $sessionToken = bin2hex(random_bytes(16));
            $redisKey = "session:$sessionToken";
            $redis->set($redisKey, json_encode([
                'id' => $userId,
                'username' => $username
            ]));
            $redis->expire($redisKey, 3600); // 1 giờ

            // Gửi token Redis về Client qua cookie với flags an toàn
            // PHP >= 7.3 supports options array
            $cookieOptions = [
                'expires' => time() + 3600,
                'path' => '/',
                'domain' => '',       // set nếu cần
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', // true only on HTTPS
                'httponly' => true,
                'samesite' => 'Lax'   // 'Strict' or 'Lax' as you prefer
            ];
            setcookie('SESSION_TOKEN', $sessionToken, $cookieOptions);

            // --- IMPORTANT: do NOT write sensitive info to localStorage ---
            // Redirect to list_users.php (server-side session will track user)
            header('Location: list_users.php');
            exit;
        } else {
            $_SESSION['message'] = 'Login failed';
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <?php
    // head.php (or views/meta.php)
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>

<body>
    <?php include 'views/header.php' ?>

    <div class="container">
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">Login</div>
                    <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot
                            password?</a></div>
                </div>

                <div style="padding-top:30px" class="panel-body">
                    <form method="post" class="form-horizontal" role="form">
                        <!-- CSRF token hidden field -->
                        <input type="hidden" name="csrf_token"
                            value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="username" value=""
                                placeholder="username or email" required>
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="login-password" type="password" class="form-control" name="password"
                                placeholder="password" required>
                        </div>

                        <div class="margin-bottom-25">
                            <input type="checkbox" tabindex="3" class="" name="remember" id="remember">
                            <label for="remember"> Remember Me</label>
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <!-- Button -->
                            <div class="col-sm-12 controls">
                                <button type="submit" name="submit" value="submit"
                                    class="btn btn-primary">Submit</button>
                                <a id="btn-fblogin" href="#" class="btn btn-primary">Login with Facebook</a>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-md-12 control">
                                Don't have an account!
                                <a href="form_user.php">
                                    Sign Up Here
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="/public/js/save_data.js"></script>
</body>

</html>