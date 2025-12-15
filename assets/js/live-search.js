/**
 * Live Search Functionality
 */

const LiveSearch = {
    init() {
        const searchInput = document.querySelector('input[name="q"]');
        if (!searchInput) return;

        // Create results container
        const container = document.createElement('div');
        container.id = 'search-results';
        container.className = 'search-results-dropdown';
        searchInput.parentNode.style.position = 'relative';
        searchInput.parentNode.appendChild(container);

        let timeout = null;

        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.trim();

            clearTimeout(timeout);

            if (query.length < 2) {
                container.style.display = 'none';
                return;
            }

            timeout = setTimeout(() => {
                this.search(query, container);
            }, 300);
        });

        // Close on click outside
        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !container.contains(e.target)) {
                container.style.display = 'none';
            }
        });
    },

    async search(query, container) {
        try {
            const response = await fetch(`/ecommerce/public/api/search.php?q=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (data.success && data.data.length > 0) {
                this.render(data.data, container);
            } else {
                container.style.display = 'none';
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    },

    render(results, container) {
        container.innerHTML = '';

        results.forEach(product => {
            const item = document.createElement('a');
            item.href = product.url;
            item.className = 'search-result-item';

            const imgHtml = product.image
                ? `<img src="${product.image}" alt="${product.name}">`
                : '<div class="no-image"><i class="fas fa-box"></i></div>';

            item.innerHTML = `
                ${imgHtml}
                <div class="info">
                    <div class="name">${product.name}</div>
                    <div class="price">₹${parseFloat(product.price).toFixed(2)}</div>
                </div>
            `;

            container.appendChild(item);
        });

        container.style.display = 'block';
    }
};

document.addEventListener('DOMContentLoaded', () => {
    LiveSearch.init();
});
