<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login trash svk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- icon -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="stylelogin.css">
</head>
<body>

<div class="login-container">

    <h2>Please Login 🌿</h2>
    <p class="sub">trash svk</p>

    <form action="insert.php" method="post">

        <!-- EMAIL -->
        <div class="input-box">
            <input type="email" name="id" required placeholder="example@email.com">
            <i class='bx bx-user'></i>
        </div>
        <!-- PASSWORD -->
        <div class="input-box">
            <input type="password" name="password" id="typepass" required placeholder="Password">
            <i class='bx bx-lock-alt'></i>
            <button type="button" class="toggle" onclick="openpassword()">
                <i id="eyeicon" class="bi bi-eye-slash-fill"></i>
            </button>
        </div>
        <!-- ERROR -->
        <?php
            if(isset($_GET['error'])){
                echo "<div class='error-msg'>รหัสประจำตัว หรือรหัสผ่าน ไม่ถูกต้อง โปรดลองอีกครั้ง!</div>";
            }
        ?>

        <button type="submit" class="login-btn">Login</button>

    </form>

</div>

<script src="script.js"></script>

</body>
</html>