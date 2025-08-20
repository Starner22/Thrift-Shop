// --- BUG FIX: Use a consistent base path for all API calls ---
const API_BASE_URL = '/shop/api';

async function fetchApi(endpoint, options = {}) {
    try {
        const response = await fetch(`${API_BASE_URL}/${endpoint}`, options);
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'An unknown error occurred.' }));
            throw new Error(errorData.message);
        }
        return response.json();
    } catch (error) {
        console.error(`API Error (${endpoint}):`, error);
        showAlert(error.message || 'Network request failed.', 'error');
        throw error;
    }
}

function showAlert(message, type = 'success') {
    const container = document.querySelector('.container');
    if (!container) return;
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.textContent = message;
    container.insertBefore(alert, container.firstChild);
    setTimeout(() => alert.remove(), 5000);
}

// --- Cart Functions ---
// FIX: Accept the button element directly as a parameter instead of using getElementById
async function addToCart(productId, button) {
    if (!button || button.disabled) return;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="loading"></span> Adding...';

    try {
        const data = await fetchApi('cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', product_id: productId, quantity: 1 })
        });
        if (data.success) {
            showAlert('Added to cart!', 'success');
            updateCartCount();
        }
    } finally {
        button.disabled = false;
        button.innerHTML = originalText;
    }
}

function updateQuantity(cartItemId, quantity) {
    if (quantity < 1) return removeFromCart(cartItemId);
    fetchApi('cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=update&cartItemId=${cartItemId}&quantity=${quantity}`
    }).then(() => location.reload());
}

function removeFromCart(cartItemId) {
    if (!confirm('Remove this item?')) return;
    fetchApi('cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&cartItemId=${cartItemId}`
    }).then(() => location.reload());
}

async function updateCartCount() {
    const cartCountElement = document.getElementById('cart-count');
    if (!cartCountElement) return;
    try {
        const data = await fetchApi('cart.php?action=count');
        cartCountElement.textContent = data.count > 0 ? data.count : '';
        cartCountElement.style.display = data.count > 0 ? 'inline' : 'none';
    } catch (error) {
        // Fail silently
    }
}
function addToCart(productID, button) {
    fetch("add_to_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "productID=" + productID
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.innerText = "✔ Added";
            button.disabled = true;

            // optional: update cart badge
            let badge = document.getElementById("cart-count");
            if (badge) {
                let count = parseInt(badge.innerText) || 0;
                badge.innerText = count + 1;
            }
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error(err));
}

function removeFromCart(cartItemID) {
    if (!confirm("Remove this item from your cart?")) return;

    fetch("remove_from_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "cartItemID=" + cartItemID
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error(err));
}

function updateQuantity(cartItemID, quantity) {
    fetch("update_cart_quantity.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "cartItemID=" + cartItemID + "&quantity=" + quantity
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error(err));
}

// --- Wishlist Functions ---
// FIX: Accept the button element directly as a parameter
function addToWishlist(productID, button) {
    fetch("add_to_wishlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "productID=" + productID
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.innerText = "❤️ Added";
            button.disabled = true;
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error(err));
}

function removeFromWishlist(productID) {
    if (!confirm("Are you sure you want to remove this item from your wishlist?")) {
        return;
    }

    fetch("remove_from_wishlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "productID=" + productID
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page so item disappears
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(err => console.error(err));
}

// --- Checkout ---
function checkout() {
    window.location.href = 'checkout.php';
}

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
});