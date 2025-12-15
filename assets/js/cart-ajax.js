/**
 * AJAX Cart Functionality
 * Handles adding to cart without reload and showing toast notifications
 */

const CartAPI = {
    baseUrl: '/ecommerce/public/api/cart_action.php', // Adjust if needed based on base_url

    async addToCart(productId, qty = 1) {
        try {
            const response = await fetch(this.baseUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    qty: qty
                })
            });

            const data = await response.json();

            if (data.success) {
                this.updateCartCount(data.data.cartCount);
                this.showToast(data.message, 'success');
            } else {
                this.showToast(data.message || 'Failed to add to cart', 'error');
            }
        } catch (error) {
            console.error('Cart Error:', error);
            this.showToast('Something went wrong', 'error');
        }
    },

    updateCartCount(count) {
        const cartBadges = document.querySelectorAll('.cart-count-badge');
        // If badge doesn't exist but we have items, we might need to create it (or just update existing ones)
        // For now, let's assume there's a badge or we find the cart icon

        // Find cart nav link
        const cartLink = document.querySelector('a[href*="cart.php"]');
        if (cartLink) {
            let badge = cartLink.querySelector('.badge-custom');
            if (!badge && count > 0) {
                badge = document.createElement('span');
                badge.className = 'badge-custom cart-count-badge';
                cartLink.appendChild(badge);
            }

            if (badge) {
                if (count > 0) {
                    badge.textContent = count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
    },

    showToast(message, type = 'success') {
        // Create container if not exists
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;

        const icon = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';

        toast.innerHTML = `
            <div class="toast-content">
                ${icon}
                <span>${message}</span>
            </div>
        `;

        container.appendChild(toast);

        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    },

    init() {
        // Attach listeners to "Add to Cart" buttons
        // We use delegation for dynamic content
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-add-to-cart');
            if (btn) {
                e.preventDefault();
                const productId = btn.dataset.id;
                const qty = 1; // Default to 1 for list view

                // Add loading state
                const originalContent = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                this.addToCart(productId, qty).then(() => {
                    // Restore button
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                });
            }
        });
    }
};

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    CartAPI.init();
});
