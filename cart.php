<?php
session_start();
$dsn = 'mysql:host=localhost;dbname=machinga';
$username = 'root';
$password = '';

try {
    $db = new PDO($dsn, $username, $password);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

// Initialize cart from local storage
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cartData = json_decode(file_get_contents('php://input'), true);
    $_SESSION['cart'] = $cartData; // Save the cart to session

    foreach ($cartData as $item) {
        $stmt = $db->prepare("INSERT INTO orders (product_name, price, quantity) VALUES (:name, :price, :quantity)");
        $stmt->execute(['name' => $item['name'], 'price' => $item['price'], 'quantity' => $item['quantity']]);
    }
    echo "Order placed successfully!";
    // Clear the cart
    unset($_SESSION['cart']);
    exit; // Ensure script ends after checkout
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    <header>
        <h1>Your Shopping Cart</h1>
        <nav>
            <a href="index.html">Home</a>
            <a href="products.html">View Products</a>
        </nav>
    </header>

    <div class="cart">
        <h2>Items in Cart:</h2>
        <div id="cart-items"></div>
        <h3 id="total-price"></h3>
        <button onclick="checkout()">Checkout</button>
    </div>

    <footer>
        <p>&copy; 2024 Maendeleo Machinga Group. All Rights Reserved.</p>
    </footer>

    <script>
        // Fetch cart data from local storage
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const cartItemsDiv = document.getElementById('cart-items');

        function displayCartItems() {
            cartItemsDiv.innerHTML = '';

            if (cart.length === 0) {
                cartItemsDiv.innerHTML = '<p>Your cart is empty.</p>';
                return;
            }

            cart.forEach(item => {
                const itemDiv = document.createElement('div');
                itemDiv.className = 'cart-item';
                itemDiv.innerHTML = `
                    <span>${item.name} - Price: Tshs. ${item.price.toFixed(2)} - Quantity: ${item.quantity}</span>
                    <button onclick="removeFromCart('${item.name}')">Remove</button>
                `;
                cartItemsDiv.appendChild(itemDiv);
            });

            updateTotalPrice();
        }

        function updateTotalPrice() {
            const totalPrice = cart.reduce((total, item) => total + item.price * item.quantity, 0);
            document.getElementById('total-price').innerText = `Total: Tshs. ${totalPrice.toFixed(2)}`;
        }

        function removeFromCart(productName) {
            cart = cart.filter(item => item.name !== productName);
            localStorage.setItem('cart', JSON.stringify(cart)); // Update local storage
            displayCartItems(); // Refresh the display
        }

        function checkout() {
            if (cart.length === 0) {
                alert("Your cart is empty. Please add items to your cart before checking out.");
                return;
            }

            // Send cart data to the server for processing
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(cart)
            })
            .then(response => response.text())
            .then(data => {
                alert(data);
                // To clear the cart
                localStorage.removeItem('cart');
                cart = [];
                displayCartItems();
            });
        }

        // To initialize cart display
        document.addEventListener('DOMContentLoaded', displayCartItems);
    </script>
</body>
</html>
