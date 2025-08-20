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

// --- Wishlist Functions ---
// FIX: Accept the button element directly as a parameter
async function addToWishlist(productId, button) {
    if (!button || button.disabled) return;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<span class="loading"></span>';

    try {
         const data = await fetchApi('wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', product_id: productId })
        });
        if (data.success) {
            showAlert('Added to wishlist!', 'success');
            button.innerHTML = '❤️'; // Keep it red on success
            return;
        }
    } finally {
        setTimeout(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        }, 2000);
    }
}

function removeFromWishlist(productId) {
    if (!confirm('Remove this item from your wishlist?')) return;
    fetchApi('wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&productId=${productId}`
    }).then(() => location.reload());
}

// --- Checkout ---
function checkout() {
    window.location.href = 'checkout.php';
}

document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
});