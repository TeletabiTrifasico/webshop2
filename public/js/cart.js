// JavaScript for managing the shopping cart

function handleAddToCart(productId, quantity) {
    const userLoggedIn = document.body.getAttribute('data-user-logged-in') === 'true';
    
    if (!userLoggedIn) {
        window.location.href = '/auth/login';
        return;
    }

    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', quantity);

    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message, 'success');
            updateCartBadge(data.cartCount);
            
            if (window.location.pathname === '/cart') {
                window.location.reload();
            }
        } else {
            showMessage(data.message || 'Failed to add product to cart.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred.', 'error');
    });
}

function updateQuantity(productId, newQuantity) {
    if (newQuantity < 1) return;

    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('quantity', newQuantity);

    fetch('/cart/update', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cartCount);
            window.location.reload();
        } else {
            showMessage('Failed to update quantity', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while updating quantity', 'error');
    });
}

function removeFromCart(productId) {
    if (!confirm('Are you sure you want to remove this item?')) {
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
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartBadge(data.cartCount);
            window.location.reload();
        } else {
            showMessage('Failed to remove item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while removing item', 'error');
    });
}

function processCheckout() {
    if (confirm('Are you sure you want to complete your order?')) {
        fetch('/cart/checkout', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => {
                    window.location.href = '/';
                }, 2000);
            } else {
                showMessage(data.message || 'Failed to process order.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('An error occurred while processing your order.', 'error');
        });
    }
}

function updateCartBadge(count) {
    const cartBadge = document.querySelector('.cart-count');
    if (cartBadge) {
        if (count > 0) {
            cartBadge.textContent = count;
            cartBadge.style.display = 'inline-block';
        } else {
            cartBadge.textContent = '';
            cartBadge.style.display = 'none';
        }
    }
}

function showMessage(message, type) {
    const toastContainer = document.querySelector('.toast-container');
    
    const toast = document.createElement('div');
    toast.className = `toast ${type} show`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    toast.innerHTML = `
        <div class="toast-body d-flex justify-content-between align-items-center">
            <span>${message}</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    toastContainer.appendChild(toast);

    // Remove old toasts if there are more than 3
    const toasts = toastContainer.querySelectorAll('.toast');
    if (toasts.length > 3) {
        toastContainer.removeChild(toasts[0]);
    }

    // Auto-remove after 3 seconds
    setTimeout(() => {
        if (toast && toast.parentNode) {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 150);
        }
    }, 3000);

    // Handle manual close
    const closeButton = toast.querySelector('.btn-close');
    closeButton.addEventListener('click', () => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 150);
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const toastContainer = document.querySelector('.toast-container');
    toastContainer.innerHTML = ''; // Clear any existing toasts
});