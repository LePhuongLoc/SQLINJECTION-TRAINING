<?php
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

// Sinh token nếu chưa có
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Thêm biến để lưu username nhập vào (phục vụ test XSS)
$input_username = '';
if (!empty($_POST['username'])) {
    $input_username = $_POST['username'];
}

if (!empty($_POST['submit'])) {
    // Kiểm tra CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF validation failed");
    }

    $users = [
        'username' => $_POST['username'],
        'password' => $_POST['password']
    ];

    if ($user = $userModel->auth($users['username'], $users['password'])) {
        $_SESSION['id'] = $user[0]['id'];
        $_SESSION['message'] = 'Login successful';
        $_SESSION['created'] = time(); 
        $_SESSION['expire'] = $_SESSION['created'] + 18; // session hết hạn sau 18 giây

        header('location: list_users.php');
        exit;
    }
}
?>

<?php if (!empty($_GET['message'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['message']); ?></div>
<?php endif; ?>

<?php if (!empty($input_username)): ?>
    <div class="alert alert-info">
        Bạn vừa nhập username: <b><?php echo htmlspecialchars($input_username); ?></b>
    </div>
<?php endif; ?>

<!DOCTYPE html>
<html>
<head>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php'?>

    <div class="container">
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info" >
                <div class="panel-heading">
                    <div class="panel-title">Login</div>
                    <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
                </div>

                <div style="padding-top:30px" class="panel-body" >
                    <form method="post" class="form-horizontal" role="form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="username" value="" placeholder="username or email">
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="login-password" type="password" class="form-control" name="password" placeholder="password">
                        </div>

                        <div class="margin-bottom-25">
                            <input type="checkbox" tabindex="3" class="" name="remember" id="remember">
                            <label for="remember"> Remember Me</label>
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <!-- Button -->
                            <div class="col-sm-12 controls">
                                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
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

</body>

<script>
// Khi trang load, nếu có dữ liệu trong localStorage thì tự động điền
document.addEventListener("DOMContentLoaded", function() {
    const savedUsername = localStorage.getItem("username");
    const savedPassword = localStorage.getItem("password");

    if (savedUsername) {
        document.getElementById("login-username").value = savedUsername;
    }
    if (savedPassword) {
        document.getElementById("login-password").value = savedPassword;
    }

    // Check lại ô Remember Me
    if (savedUsername || savedPassword) {
        document.getElementById("remember").checked = true;
    }
});

// Khi submit form, nếu "Remember Me" được check thì lưu lại vào localStorage
document.getElementById("login-username").form.onsubmit = function() {
    if (document.getElementById("remember").checked) {
        localStorage.setItem("username", document.getElementById("login-username").value);
        localStorage.setItem("password", document.getElementById("login-password").value);
    } else {
        localStorage.removeItem("username");
        localStorage.removeItem("password");
    }
};

</script>

</html>