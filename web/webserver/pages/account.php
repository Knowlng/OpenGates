<?php require_once 'scripts.php'; ?>
<?php include 'db_connect.php'; ?>
<?php require_once 'db_queries.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="resources/css/style.css">
    <title>Account</title>
</head>

<body>
    <?php include 'layout/header.php'; ?>
    <div style="padding: 100px 0px 0px 50px;">
        <?php

        echo 'Welcome to the member area, ' . $_SESSION['username'] . '!' . '<br>';

        $userID = $_SESSION['userid']; // Assuming you've stored the user's ID in the session

        // Retrieve the user's balance from the database
        $balance = getUserBalance($conn, $userID);


        #check if balance is not null
        if (!is_null($balance)) {
            echo "User's Balance: " . $balance;
        } else {
            echo "User not found or balance unavailable.";
        }

        // Retrieve the user's services from the database
        $services = getUserServices($conn, $userID);
        # check if services is not null
        if (!is_null($services)) {
            echo "<h2>Services <img src=\"../resources/img/command-line.svg\" alt=\"Command-line-logo\"> </h2>";

            echo "<ul>";
            foreach ($services as $service) {
                echo "<li>" . $service['service_name'] . " - " . $service['cost'] . " €/mėn.</li>";
                $userInput = $service['vmid'] . ' ' . getenv('VM_USER') . ' ' . getenv('VM_PASSWORD');
                // $escapedInput = escapeshellarg($userInput);

                $output = showVMdetails($userInput);

                echo 'VM DETAILS: <br>';
                foreach ($output as $line) {
                    echo $line . "<br>";
                }
            }

            echo "</ul>";
        } else {
            echo "No services found.";
        }
        ?>
    </div>
    <?php include 'layout/footer.php'; ?>
</body>

</html>
