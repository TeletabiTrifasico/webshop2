// JavaScript for managing the shopping cart.

let cart = JSON.parse(localStorage.getItem('cart')) || [];

// Adding items to the cart
function handleAddToCart(productId, quantity) {
    const userLoggedIn = document.body.dataset.userLoggedIn === 'true';
    
    if (!userLoggedIn) {
        window.location.href = '/auth/login';
        return;
    }

    const formData = new FormData();
    formData.append('product_id', productId);

    fetch('/cart/add', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (response.ok) {
            let cartData = JSON.parse(sessionStorage.getItem('cart')) || [];
            const existingItem = cartData.find(item => item.productId === productId);
            
            if (existingItem) {
                existingItem.quantity += quantity;
            } else {
                cartData.push({ productId, quantity });
            }
            
            sessionStorage.setItem('cart', JSON.stringify(cartData));
            updateCartDisplay();
            showMessage('Product added to cart!', 'success');
        } else {
            showMessage('Failed to add product to cart.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred.', 'error');
    });
}

// Add items to cart
function addToCart(productId, quantity) {
    const existingItem = cart.find(item => item.productId === productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({ productId, quantity });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartDisplay();
}

// Update quantity of item in cart
function updateQuantity(productId, newQuantity) {
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', newQuantity);

    fetch('/cart/update', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            showMessage('Failed to update quantity.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred.', 'error');
    });
}

// Remove items from cart
function removeFromCart(productId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    const formData = new FormData();
    formData.append('product_id', productId);

    fetch('/cart/remove', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (response.ok) {
            const row = document.querySelector(`tr[data-product-id="${productId}"]`);
            if (row) {
                row.remove();
                updateCartTotals();
                showMessage('Product removed from cart', 'success');
            }
        } else {
            throw new Error('Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Failed to remove item from cart', 'error');
    });
}

// Update totals
function updateCartTotals() {
    const rows = document.querySelectorAll('tbody tr');
    let total = 0;

    rows.forEach(row => {
        const price = parseFloat(row.querySelector('td:nth-child(2)').textContent.replace('$', '').replace(',', ''));
        const quantity = parseInt(row.querySelector('.quantity-control span').textContent);
        total += price * quantity;
    });

    const totalElement = document.querySelector('#cart-total');
    if (totalElement) {
        totalElement.textContent = `$${total.toFixed(2)}`;
    }

    // Update cart badge
    const cartBadge = document.querySelector('.cart-count');
    if (cartBadge) {
        const totalItems = Array.from(rows).reduce((sum, row) => {
            return sum + parseInt(row.querySelector('.quantity-control span').textContent);
        }, 0);
        cartBadge.textContent = totalItems;
        cartBadge.style.display = totalItems > 0 ? 'inline-block' : 'none';
    }

    // Reload to show empty cart message if no items left
    if (rows.length === 0) {
        location.reload(); 
    }
}

// Update cart display
function updateCartDisplay() {
    const cartData = JSON.parse(sessionStorage.getItem('cart')) || [];
    const cartCount = document.querySelector('.cart-count');
    
    if (cartCount) {
        // Calculate total quantity of all items
        const totalItems = cartData.reduce((sum, item) => sum + (parseInt(item.quantity) || 0), 0);
        
        // Update badge visibility and count
        cartCount.textContent = totalItems;
        cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
    }
}

// Checkout
function checkout() {
    console.log('Proceeding to checkout with items:', cart);
}

// Initialize cart display
document.addEventListener('DOMContentLoaded', () => {
    updateCartDisplay();
    document.getElementById('checkout-button').addEventListener('click', checkout);
});

function updateCartCount(increment = 0) {
    const cartBadge = document.querySelector('.cart-count');
    if (cartBadge) {
        let count = parseInt(cartBadge.textContent || '0');
        count += increment;
        cartBadge.textContent = count;
        cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
    }
}

function showMessage(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast position-fixed top-0 end-0 m-3 ${type === 'error' ? 'bg-danger' : 'bg-success'} text-white`;
    toast.style.zIndex = '1050';
    toast.innerHTML = `
        <div class="toast-body">
            ${message}
        </div>
    `;

    document.body.appendChild(toast);

    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();

    toast.addEventListener('hidden.bs.toast', () => {
        document.body.removeChild(toast);
    });
}