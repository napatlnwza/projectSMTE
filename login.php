<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login trash svk</title>
</head>
<body>
    <form action="insert.php" method="post">
        <table>
            <tr>
                <td>รหัสประจำตัว</td>
                <td><input type="number" name="id" required></td>
            </tr>
            <tr>
                <td>รหัสผ่าน</td>
                <td><input type="password" name="birthday" id="birthday"required placeholder="DD/MM/YYYY"></td>
                <td><button type="button" onclick="openpassword()">👁</button></td>
            </tr>
            <?php
                if(isset($_GET['error'])){
                    echo "<p style='color:red;'>รหัสประจำตัว หรือรหัสผ่าน ไม่ถูกต้องโปรดลองอีกครั้ง!</p>";
                }
            ?>
            <tr>
                <td><input type="submit" value="Login"></td>
            </tr>
        </table>
    </form>
</body>
<script src="script.js"></script>
</html>