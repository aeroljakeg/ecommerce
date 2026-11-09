/**
 * TechMoto PH - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert-dismissible').forEach(function (el) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // Live search suggestions
    const searchInput = document.getElementById('searchInput');
    const suggestionsBox = document.getElementById('searchSuggestions');
    let searchTimeout = null;

    if (searchInput && suggestionsBox) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            const q = this.value.trim();
            if (q.length < 2) {
                suggestionsBox.classList.add('d-none');
                suggestionsBox.innerHTML = '';
                return;
            }
            searchTimeout = setTimeout(function () {
                fetch(SITE_URL + '/api/search.php?q=' + encodeURIComponent(q))
                    .then(r => r.json())
                    .then(data => {
                        if (data.products && data.products.length) {
                            let html = '';
                            data.products.forEach(p => {
                                html += `<a href="${SITE_URL}/product-details.php?id=${p.id}" class="suggestion-item text-decoration-none text-dark">
                                    <img src="${p.image}" alt="">
                                    <div>
                                        <div class="fw-semibold small">${escapeHtml(p.name)}</div>
                                        <div class="text-primary small">${p.price_formatted}</div>
                                    </div>
                                </a>`;
                            });
                            suggestionsBox.innerHTML = html;
                            suggestionsBox.classList.remove('d-none');
                        } else {
                            suggestionsBox.classList.add('d-none');
                        }
                    })
                    .catch(() => suggestionsBox.classList.add('d-none'));
            }, 300);
        });

        document.addEventListener('click', function (e) {
            if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.classList.add('d-none');
            }
        });
    }

    // Add to cart buttons
    document.querySelectorAll('.btn-add-cart').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.dataset.id;
            const qty = this.dataset.qty || 1;
            addToCart(productId, qty, this);
        });
    });

    // Wishlist toggle
    document.querySelectorAll('.btn-wishlist').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const productId = this.dataset.id;
            toggleWishlist(productId, this);
        });
    });
});

// Global SITE_URL (set in pages if needed)
const SITE_URL = document.querySelector('meta[name="site-url"]')?.content || 
                 (window.location.origin + '/ecommerce');

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function addToCart(productId, qty = 1, btn = null) {
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }
    fetch(SITE_URL + '/api/cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'add', product_id: productId, quantity: qty })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Added to cart!', 'success');
            // Update cart badge
            const badge = document.querySelector('.navbar .badge');
            if (badge) {
                badge.textContent = data.cart_count;
                badge.classList.remove('d-none');
            } else if (data.cart_count > 0) {
                location.reload();
            }
        } else {
            showToast(data.message || 'Failed to add to cart', 'danger');
        }
    })
    .catch(() => showToast('Network error', 'danger'))
    .finally(() => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-cart-plus me-1"></i> Add to Cart';
        }
    });
}

function toggleWishlist(productId, btn) {
    fetch(SITE_URL + '/api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle', product_id: productId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.classList.toggle('active', data.in_wishlist);
            showToast(data.in_wishlist ? 'Added to wishlist' : 'Removed from wishlist', 'success');
        } else if (data.message === 'login_required') {
            window.location.href = SITE_URL + '/user/login.php';
        } else {
            showToast(data.message || 'Error', 'danger');
        }
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed bottom-0 end-0 m-3 shadow`;
    toast.style.zIndex = 9999;
    toast.innerHTML = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Quantity controls
function updateQty(input, delta) {
    let val = parseInt(input.value) || 1;
    val = Math.max(1, val + delta);
    input.value = val;
    input.dispatchEvent(new Event('change'));
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    });
}