<?php
require_once 'scripts.php';
include 'db_connect.php';
require_once 'db_queries.php';

// Check if user is logged in
if (!isset($_SESSION['userid'])) {
    die("User is not logged in.");
}

// Function to calculate the total cost of items in the cart
function calculateTotal($cart)
{
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'];
    }
    return $total;
}

function generateRandomPassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()_-+=';
    $charactersLength = strlen($characters);
    $randomPassword = '';

    for ($i = 0; $i < $length; $i++) {
        $randomPassword .= $characters[rand(0, $charactersLength - 1)];
    }

    return $randomPassword;
}

$userId = $_SESSION['userid'];
$userBalance = getUserBalance($conn, $userId);

if ($userBalance === null) {
    die("User not found or balance unavailable.");
}

// Check if the cart exists and is not empty
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $totalCost = calculateTotal($_SESSION['cart']);

    // Check if the user has enough balance
    if ($userBalance >= $totalCost) {
        $newBalance = $userBalance - $totalCost;

        // Here you would typically process the transaction:
        // 1. Update the user's balance in the database
        // Update the user's balance
        if (updateUserBalance($conn, $userId, $newBalance)) {
            echo "Transaction successful. Total cost: " . $totalCost . " €.<br>";
            echo "Remaining Balance: " . $newBalance . " €.";
        } else {
            echo "Error updating user balance.";
            die;
        }

        foreach ($_SESSION['cart'] as $service) {
            // 2. Run shell script to start the purchased VMs 
            echo '<br>Starting VM...<br>';

            $password = generateRandomPassword(12);

            if ($service['name'] == 'Planas Start') {
                $resources = '512 8192 ' . getenv('VM_USER') . ' ' . getenv('VM_PASSWORD') . ' ' . $_SESSION['username'] . ' ' . $password;
            } else {
                $resources = '2048 12288' . getenv('VM_USER') . ' ' . getenv('VM_PASSWORD') . ' ' . $_SESSION['username'] . ' ' . $password;
            }

            $output = createVM($resources);

            // check if $line is a numeric value
            if (!is_numeric($output[0])) {
                echo 'There was an error creating A VM, please contact support' . $output[0];
                exit;
            }

            $VMID = $output[0];
            echo 'VM ID: ' . $VMID . '<br>';

            // 3. Record the transaction details in the database
            echo '<br>Services purchased:<br>';

            recordServicePurchase($conn, $userId, $VMID, $service, "ssh");
        }
        // 4. Empty the cart
       if (isset($_SESSION['cart'])) {
	  $_SESSION['cart'] = [];
        }
    } else {
        echo "Insufficient balance. Your balance is " . $userBalance . " €, but the total cost is " . $totalCost . " €.";
    }
} else {
    echo "Your cart is empty.";
}
