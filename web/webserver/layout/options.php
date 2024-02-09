<?php

// Define your products
$products = [
    "planas_start" => [
        "name" => "Planas Start",
        "description" => "Svetainių talpinimui, pašto serverių kūrimui, duomenų saugyklai",
        "price" => 3.49,
        "discountPrice" => 1.99,
        "features" => [
            "512 Mb. RAM",
            "8 Gb. SSD",
            ["Priority support", "lack"], // Feature with 'lack' class
            "Neriboti duomenys"
        ]
    ],
    "planas_greitis" => [
        "name" => "Planas Greitis",
        "description" => "Žaidimų serverių talpinimui, e-shop sveitainių talpinimui",
        "price" => 6.69,
        "discountPrice" => 3.49,
        "features" => [
            "2 Gb. RAM",
            "14 Gb. SSD",
            "Priority support",
            "Neriboti duomenys"
        ]
    ]
];

// Initialize the shopping cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding items to the cart
if (isset($_POST['add_to_cart'])) {
    $productKey = $_POST['product_key'];
    if (isset($products[$productKey])) {
        // Check if the item is already in the cart
        if (!isset($_SESSION['cart'][$productKey])) {
            $_SESSION['cart'][$productKey] = $products[$productKey];
        } else {
        }
    }
    header("Location: /cart");
    exit();
}

?>

<div class="plan-container" id="plans">
    <?php foreach ($products as $key => $product) : ?>
        <div class="plan">
            <div class="plan-header"><?= htmlspecialchars($product['name']) ?></div>
            <p class="plan-description"><?= htmlspecialchars($product['description']) ?></p>
            <ul class="plan-features">
                <?php foreach ($product['features'] as $feature) : ?>
                    <?php if (is_array($feature)) : ?>
                        <li class="<?= htmlspecialchars($feature[1]) ?>"><?= htmlspecialchars($feature[0]) ?></li>
                    <?php else : ?>
                        <li><?= htmlspecialchars($feature) ?></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
            <div class="plan-price"><?= htmlspecialchars($product['price']) ?> €/mėn.</div>
            <div class="plan-discount"><?= htmlspecialchars($product['discountPrice']) ?> €/mėn.</div>
            <!-- disable default form action -->
            <form method="post">
                <input type="hidden" name="product_key" value="<?= htmlspecialchars($key) ?>">
                <button type="submit" name="add_to_cart" class="plan-button">Add to Cart</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>