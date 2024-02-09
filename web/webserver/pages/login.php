<?php

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, so start a new session
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['userid'] = $user['id'];
        // Redirect user to home page
        header("Location: /");
    } else {
        echo "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="resources/css/style.css">
</head>

<body>
    <?php include 'layout/header.php'; ?>
    <section style="margin-top: 100px; display: flex;
  align-items: center; justify-content: center; flex-direction: column;">
        <form method="post">
            Username: <input type="text" name="username" required><br>
            Password: <input type="password" name="password" required><br>
            <input type="submit" value="Login">
        </form>
    </section>
    <?php include 'layout/footer.php'; ?>
</body>

</html>