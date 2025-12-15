/**
 * Quick View Functionality
 */

const QuickView = {
    modal: null,

    init() {
        // Initialize Bootstrap Modal
        const modalEl = document.getElementById('quickViewModal');
        if (modalEl) {
            this.modal = new bootstrap.Modal(modalEl);

            // Event delegation for Quick View buttons
            document.addEventListener('click', (e) => {
                const btn = e.target.closest('.btn-quick-view');
                if (btn) {
                    e.preventDefault();
                    const productId = btn.dataset.id;
                    this.open(productId);
                }
            });
        }
    },

    async open(productId) {
        try {
            const response = await fetch(`/ecommerce/public/api/get_product.php?id=${productId}`);
            const data = await response.json();

            if (data.success) {
                this.populate(data.data);
                this.modal.show();
            } else {
                console.error('Product not found');
            }
        } catch (error) {
            console.error('Error fetching product:', error);
        }
    },

    populate(product) {
        document.getElementById('qvTitle').textContent = product.name;
        document.getElementById('qvPrice').textContent = '₹' + parseFloat(product.price).toFixed(2);
        document.getElementById('qvDescription').textContent = product.description || 'No description available.';
        document.getElementById('qvCategory').textContent = product.category_name || 'Product';

        const img = document.getElementById('qvImage');
        img.src = product.image_url;
        img.alt = product.name;

        const addToCartBtn = document.getElementById('qvAddToCart');
        addToCartBtn.dataset.id = product.id;

        const viewDetailsBtn = document.getElementById('qvViewDetails');

        if (product.url) {
            viewDetailsBtn.style.display = 'inline-flex';
            viewDetailsBtn.href = product.url;
            // Force navigation via JS as a backup
            viewDetailsBtn.onclick = function (e) {
                e.preventDefault();
                window.location.href = product.url;
            };
        } else {
            viewDetailsBtn.style.display = 'none';
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    QuickView.init();
});
