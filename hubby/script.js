let cart = [];

function addToCart(button) {
    let product = button.parentElement;
    let id = product.dataset.id;
    let name = product.dataset.name;
    let price = parseFloat(product.dataset.price);

    let existingItem = cart.find(item => item.id === id);

    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.push({ id, name, price, quantity: 1 });
    }

    updateCart();
}

function updateCart() {
    let cartItemsContainer = document.getElementById("cart-items");
    let totalPrice = 0;
    let cartCount = 0;
    
    cartItemsContainer.innerHTML = "";

    cart.forEach(item => {
        let itemElement = document.createElement("div");
        itemElement.innerHTML = `
            <p>${item.name} x ${item.quantity} - $${item.price * item.quantity}</p>
            <button onclick="removeFromCart('${item.id}')">Remove</button>
        `;
        cartItemsContainer.appendChild(itemElement);

        totalPrice += item.price * item.quantity;
        cartCount += item.quantity;
    });

    document.getElementById("total-price").textContent = totalPrice;
    document.getElementById("cart-count").textContent = cartCount;
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    updateCart();
}

function toggleCart() {
    let cartModal = document.getElementById("cart");
    cartModal.style.display = cartModal.style.display === "block" ? "none" : "block";
}

function checkout() {
    if (cart.length === 0) {
        alert("Your cart is empty!");
        return;
    }

    let confirmation = confirm("Proceed to checkout?");
    if (confirmation) {
        alert("Thank you for your purchase!");
        cart = [];
        updateCart();
        toggleCart();
    }
}
