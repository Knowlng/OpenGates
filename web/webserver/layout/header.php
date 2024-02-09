<header>
    <!-- login section -->
    <a class="BrandName" href="/">
        <i>
            <p style="text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);"><span class="Open" style="color:#3bd461;">O</span>Gates</p>
        </i>
    </a>
    <!-- only show loggin section is user is logged in -->
    <?php if (isset($_SESSION['loggedin'])) : ?>
        <div class="login-section">
            <a href="/cart">
                <img style="position: relative; margin: 0px; padding: 0px; top: -5px; width: 30px; height: 30px;" src="resources/img/shopping-cart.png" alt="shopping cart">
                <span class="cart-count">
                    <?php
                    if (isset($_SESSION['cart'])) {
                        $cartCount = count($_SESSION['cart']);
                        if ($cartCount > 0) {
                            echo $cartCount;
                        }
                    }
                    ?>
                </span>
            </a>
            <a href="/account">Mano paskyra</a>
            <a href="/logout">Atsijungti</a>
        </div>
    <?php else : ?>
        <div class="login-section">
            <a href="/cart">
                <img style="position: relative; margin: 0px; padding: 0px; top: -5px; width: 30px; height: 30px;" src="resources/img/shopping-cart.png" alt="shopping cart">
                <span class="cart-count">
                    <?php
                    if (isset($_SESSION['cart'])) {
                        $cartCount = count($_SESSION['cart']);
                        if ($cartCount > 0) {
                            echo $cartCount;
                        }
                    }
                    ?>
                </span>
            </a>
            <a href="/login">Prisijungti</a>
            <a href="/register">Registruotis</a>
        </div>
    <?php endif; ?>
</header>
<script>
    const brandName = document.querySelector('.BrandName');
    const open = document.querySelector('.Open');
    let animating = false;

    const addLetters = () => {
        const word = ['p', 'e', 'n'];
        let i = 0;

        const addLetter = () => {
            if (i < word.length) {
                if (open.innerHTML.length + 1 != 5) {
                    open.innerHTML += word[i];
                }
                i++;
                requestAnimationFrame(addLetter);
            } else {
                animating = false;
            }
        };

        animating = true;
        requestAnimationFrame(addLetter);
    };

    const removeLetters = () => {
        let i = 0;

        const removeLetter = () => {
            if (i < 3 && open.innerHTML.length > 1) {
                open.innerHTML = open.innerHTML.slice(0, -1);
                i++;
                requestAnimationFrame(removeLetter);
            } else {
                animating = false;
            }
        };

        if (!animating) {
            animating = true;
            requestAnimationFrame(removeLetter);
        }
    };

    brandName.addEventListener('mouseover', () => {
        if (!animating) {
            addLetters();
        }
    });

    brandName.addEventListener('mouseout', () => {
        if (open.innerHTML.length > 1) {
            removeLetters();
        }
    });
</script>