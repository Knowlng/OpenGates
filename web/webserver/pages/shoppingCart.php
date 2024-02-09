<?php
// Initialize the shopping cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Function to display the cart
function displayCart()
{
    if (empty($_SESSION['cart'])) {
        echo "Tuščias krepšelis";
    } else {
        echo "<ul>";
        foreach ($_SESSION['cart'] as $product) {
            echo "<li>" . htmlspecialchars($product['name']) . " - " . htmlspecialchars($product['price']) . " €/mėn.</li>";
        }
        echo "</ul>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="resources/css/style.css">
    <title>Pirkinių krepšelis</title>
</head>

<body>
    <?php include 'layout/header.php'; ?>
    <section class="shopping-page">
        <div class="shopping-cart">
            <h2>Pirkinių krepšelis</h2>
            <?php displayCart(); ?>
            <?php if (isset($_SESSION['loggedin']) & !empty($_SESSION['cart'])) : ?>
                <button id="checkout-button">Checkout</button>
            <?php else : ?>
                <?php if (!isset($_SESSION['loggedin'])) : ?>
                    <p>Pirkti gali tik prisijungia vartotojai</p>
                <?php endif; ?>
                <button disabled id="checkout-button">Pirkti</button>
            <?php endif; ?>
            <p>- - - - - - - - - - - - - - - - - - - - - - - - </p>

            <div id="script-output">
                <img src="../resources/img/shopping-cart-lines.svg" alt="">
                <span id="s-output"></span>
            </div>
        </div>
    </section>
    <?php include 'layout/footer.php'; ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to execute the checkout when the button is clicked
            document.getElementById('checkout-button').addEventListener('click', function() {
                document.getElementById('checkout-button').disabled = true;
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '/checkout', true); // Use GET request
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 3) { // Check if response is partially received
                        // Process the received part of the response line by line
                        var lines = xhr.responseText.split('\n');
                        for (var i = 0; i < lines.length; i++) {
                            // Process each line here
                            console.log(lines[i]); // Log the line to the console
                        }
                    } else if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            // Display the final part of the response
                            document.getElementById('s-output').innerHTML = xhr.responseText;
                        } else {
                            document.getElementById('s-output').innerHTML = 'Error executing script.';
                        }
                    }
                };
                xhr.send();
            });
        });
    </script>
</body>

</html>