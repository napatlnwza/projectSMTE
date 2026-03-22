<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — trash svk</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="stylelogin.css">
</head>
<body>


    <div class="login-container">
        <img src="swk-logo.jpg" alt="logo swk" class="custom-logo">
        <h2>Please Login</h2>
        <!-- <p class="sub">trash svk</p> -->

        <form action="insert.php" method="post">

            <div class="input-box">
                <i class="bx bx-user"></i>
                <input type="email" name="id" required placeholder="example@email.com">
            </div>

            <div class="input-box">
                <i class="bx bx-lock-alt"></i>
                <input type="password" name="password" id="typepass" required placeholder="Password">
                <button type="button" class="toggle" onclick="openpassword()">
                    <i id="eyeicon" class="bi bi-eye-slash-fill"></i>
                </button>
            </div>

            <?php
                if(isset($_GET['error'])){
                    echo "<div class='error-msg'>รหัสประจำตัว หรือรหัสผ่าน ไม่ถูกต้อง โปรดลองอีกครั้ง!</div>";
                }
            ?>

            <button type="submit" class="login-btn">Login</button>

        </form>
        <p class="text-muted">© Napat Singdeang</p>
    </div>

    <script src="script.js"></script>
</body>
</html>