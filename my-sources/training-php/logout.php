<?php
session_start();

// Xóa session
session_unset();
session_destroy();

// Xóa cookie
setcookie("user_id", "", time() - 3600, "/");
setcookie("username", "", time() - 3600, "/");

// Xóa localStorage (JS vì PHP không can thiệp trực tiếp)
echo "<script>
        localStorage.removeItem('user_id');
        localStorage.removeItem('username');
        window.location.href = 'login.php';
      </script>";
exit;
?>
