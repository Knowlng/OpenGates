<?php

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encrypt the password
    $email = $_POST['email'];

    $sql = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username, $password, $email]);

    if (!$stmt) {
        echo "Something went wrong registering new user!";
        exit;
    }

    // fetch newly created user record so that we can log the user in
    $sql = "SELECT id, username FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Registration successful!";
    // Redirect to login page or home page
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $user['username'];
    $_SESSION['userid'] = $user['id'];
    // Redirect user to home page
    header("Location: /");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="resources/css/style.css">
</head>

<body>
    <?php include 'layout/header.php'; ?>
    <section style="margin-top: 100px; display: flex;
  align-items: center; justify-content: center; flex-direction: column;">
        <form method="post">
            Username: <input type="text" name="username" required><br>
            Password: <input type="password" name="password" required><br>
            Email: <input type="email" name="email" required><br>
            <input type="submit" value="Register">
        </form>
    </section>
    <?php include 'layout/footer.php'; ?>
</body>

</html>