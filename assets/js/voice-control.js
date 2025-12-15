class VoiceControl {
    constructor() {
        this.btn = null;
        this.feedback = null;
        this.recognition = null;
        this.isListening = false;
        // Use global BASE_URL if available, otherwise fallback to relative path
        this.basePath = (typeof BASE_URL !== 'undefined') ? BASE_URL : '/ecommerce/public/';
        // Ensure basePath ends with a slash
        if (!this.basePath.endsWith('/')) this.basePath += '/';

        this.init();
    }

    init() {
        // Check browser support
        if (!('webkitSpeechRecognition' in window) && !('SpeechRecognition' in window)) {
            console.warn('Voice control not supported in this browser.');
            return;
        }

        this.createUI();
        this.setupRecognition();
        this.setupListeners();
    }

    createUI() {
        // Create Button
        this.btn = document.createElement('button');
        this.btn.className = 'voice-control-btn';
        this.btn.innerHTML = '<i class="fas fa-microphone"></i>';
        this.btn.title = 'Voice Control';
        document.body.appendChild(this.btn);

        // Create Feedback Tooltip
        this.feedback = document.createElement('div');
        this.feedback.className = 'voice-feedback';
        document.body.appendChild(this.feedback);
    }

    setupRecognition() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        this.recognition = new SpeechRecognition();
        this.recognition.continuous = false;
        this.recognition.lang = 'en-US';
        this.recognition.interimResults = false;
        this.recognition.maxAlternatives = 1;

        this.recognition.onstart = () => {
            this.isListening = true;
            this.btn.classList.add('listening');
            this.showFeedback('Listening...');
        };

        this.recognition.onend = () => {
            this.isListening = false;
            this.btn.classList.remove('listening');
        };

        this.recognition.onresult = (event) => {
            const command = event.results[0][0].transcript.toLowerCase().trim();
            this.showFeedback(`Heard: "${command}"`);

            // Debug: Direct check
            if (command === 'add galaxy watch') {
                console.log('Direct match found for galaxy watch');
                this.addToCartByName('Galaxy Watch');
                return;
            }

            this.processCommand(command);
        };

        this.recognition.onerror = (event) => {
            console.error('Speech recognition error', event.error);
            let msg = 'Error: ' + event.error;
            if (event.error === 'not-allowed') {
                msg = 'Microphone access denied';
            } else if (event.error === 'no-speech') {
                msg = 'No speech detected';
            }
            this.showFeedback(msg, true);
            this.isListening = false;
            this.btn.classList.remove('listening');
        };
    }

    setupListeners() {
        this.btn.addEventListener('click', () => {
            if (this.isListening) {
                this.recognition.stop();
            } else {
                this.recognition.start();
            }
        });
    }

    showFeedback(text, isError = false) {
        this.feedback.textContent = text;
        this.feedback.className = 'voice-feedback show'; // Reset classes
        if (isError) this.feedback.classList.add('error');

        setTimeout(() => {
            if (!this.isListening) {
                this.feedback.classList.remove('show');
            }
        }, 3000);
    }

    processCommand(command) {
        console.log('Processing command:', command);
        // Debug alert to see exactly what is being processed
        // alert('Debug: ' + command);

        // --- NAVIGATION ---
        if (command.includes('home')) {
            window.location.href = this.basePath + 'index.php';
        } else if (command.includes('product') || command.includes('shop')) {
            window.location.href = this.basePath + 'products.php';
        } else if (command.includes('cart')) {
            window.location.href = this.basePath + 'cart.php';
        } else if (command.includes('login')) {
            window.location.href = this.basePath + 'login.php';
        } else if (command.includes('register') || command.includes('sign up')) {
            window.location.href = this.basePath + 'register.php';
        } else if (command.includes('logout') || command.includes('sign out')) {
            window.location.href = this.basePath + 'logout.php';
        } else if (command.includes('wishlist')) {
            window.location.href = this.basePath + 'wishlist.php';
        } else if (command.includes('order')) {
            window.location.href = this.basePath + 'orders.php';
        }

        // --- SEARCH ---
        else if (command.includes('search for') || command.includes('find')) {
            const query = command.replace('search for', '').replace('find', '').trim();
            if (query) {
                window.location.href = this.basePath + 'products.php?q=' + encodeURIComponent(query);
            }
        }

        // --- BUY / PURCHASE (Add + Checkout) ---
        else if (command.includes('buy') || command.includes('purchase') || (command.includes('add') && command.includes('checkout'))) {
            // "Buy Samsung", "Purchase iPhone", "Add Samsung and checkout"
            const match = command.match(/(?:buy|purchase|add) (.+?)(?: to cart| and checkout|$)/);
            if (match && match[1]) {
                const productName = match[1].replace('and checkout', '').trim();
                this.addToCartByName(productName, true); // true = autoCheckout
            }
        }

        // --- SPECIFIC PRODUCT HARDCODED COMMANDS ---
        // Galaxy Watch
        else if (command.includes('add') && command.includes('galaxy watch')) {
            this.addToCartByName('Galaxy Watch');
        }
        // Galaxy S25
        else if (command.includes('add') && /galaxy s\s?25/.test(command)) {
            this.addToCartByName('Galaxy S25');
        }
        // Vivobook
        else if (command.includes('add') && command.includes('vivobook')) {
            this.addToCartByName('Vivobook');
        }
        // HP Keyboard
        else if (command.includes('add') && command.includes('hp keyboard')) {
            this.addToCartByName('HP Keyboard');
        }
        // iPhone 16 (Handles "sixteen", "i phone", "eye phone")
        else if (command.includes('add') && /(?:i\s?phone|eye\s?phone) (?:16|sixteen)/.test(command)) {
            this.addToCartByName('iPhone 16');
        }
        // Bose Headphones
        else if (command.includes('add') && command.includes('bose headphones')) {
            this.addToCartByName('Bose Headphones');
        }
        // Corsair Mouse
        else if (command.includes('add') && command.includes('corsair mouse')) {
            this.addToCartByName('Corsair Mouse');
        }
        // HP Omen
        else if (command.includes('add') && command.includes('hp omen')) {
            this.addToCartByName('HP Omen');
        }
        // Dell Mouse
        else if (command.includes('add') && command.includes('dell mouse')) {
            this.addToCartByName('Dell Mouse');
        }
        // Sony Camera
        else if (command.includes('add') && command.includes('sony camera')) {
            this.addToCartByName('Sony Camera');
        }
        // Marshall Speaker
        else if (command.includes('add') && command.includes('marshall speaker')) {
            this.addToCartByName('Marshall Speaker');
        }

        // --- ADD TO CART (Generic/Other Products) ---
        else if (command.includes('add') && command.includes('to cart')) {
            // Extract product name: "add samsung galaxy to cart" -> "samsung galaxy"
            const match = command.match(/add (.+) to cart/);
            if (match && match[1]) {
                const productName = match[1].trim();
                this.addToCartByName(productName, false);
            } else {
                // Fallback for generic "add to cart" (current page product)
                this.addCurrentProductToCart();
            }
        }

        // --- FILTERING (Category) ---
        else if (command.includes('show') || command.includes('category')) {
            // "Show laptops", "Category mobiles"
            const match = command.match(/(?:show|category) (.+)/);
            if (match && match[1]) {
                const categoryName = match[1].trim();
                this.filterByCategory(categoryName);
            }
        }

        // --- SORTING ---
        else if (command.includes('sort by')) {
            if (command.includes('price low') || command.includes('cheap')) {
                this.applySort('price_asc');
            } else if (command.includes('price high') || command.includes('expensive')) {
                this.applySort('price_desc');
            } else if (command.includes('new') || command.includes('latest')) {
                this.applySort('newest');
            }
        }

        // --- SCROLLING ---
        else if (command.includes('scroll down')) {
            window.scrollBy({ top: 500, behavior: 'smooth' });
        } else if (command.includes('scroll up')) {
            window.scrollBy({ top: -500, behavior: 'smooth' });
        } else if (command.includes('top')) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else if (command.includes('bottom')) {
            window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
        }

        // --- CHECKOUT ---
        else if (command.includes('checkout')) {
            if (window.location.href.includes('cart.php')) {
                const checkoutBtn = document.querySelector('a[href*="checkout.php"]');
                if (checkoutBtn) checkoutBtn.click();
                else window.location.href = this.basePath + 'checkout.php';
            } else {
                window.location.href = this.basePath + 'checkout.php';
            }
        }

        // --- THEME ---
        else if (command.includes('dark mode')) {
            if (typeof toggleDarkMode === 'function') toggleDarkMode();
        } else if (command.includes('light mode')) {
            if (typeof toggleDarkMode === 'function') toggleDarkMode();
        }

        // --- REMOVE FROM CART ---
        else if (command.includes('remove') && command.includes('from cart')) {
            // "Remove Samsung from cart"
            const match = command.match(/remove (.+) from cart/);
            if (match && match[1]) {
                const productName = match[1].trim();
                this.removeFromCartByName(productName);
            }
        }

        // --- CLEAR CART ---
        else if (command.includes('clear cart') || command.includes('empty cart')) {
            this.clearCart();
        }

        // --- HELP ---
        else if (command.includes('help') || command.includes('what can i say')) {
            alert('Voice Commands:\n- Go to [Home/Cart/Products/Login]\n- Search for [Product]\n- Add [Product] to cart\n- Remove [Product] from cart\n- Clear cart\n- Show [Category]\n- Sort by [Price Low/High/Newest]\n- Scroll [Up/Down/Top/Bottom]\n- Dark/Light Mode');
        }

        // --- CLICK ANY BUTTON ---
        else if (command.startsWith('click') || command.startsWith('press') || command.startsWith('go to') || command.startsWith('open')) {
            // "Click checkout", "Press submit", "Go to details", "Open cart"
            const match = command.match(/(?:click|press|go to|open) (.+)/);
            if (match && match[1]) {
                const targetText = match[1].trim();
                if (this.clickButtonByText(targetText)) {
                    return; // Success
                }
            }
        }

        else {
            console.warn('Command not recognized:', command);
            this.showFeedback(`Not recognized: "${command}"`, true);
        }
    }

    removeFromCartByName(productName) {
        this.showFeedback(`Removing "${productName}"...`);
        fetch(this.basePath + 'api/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'remove_by_name',
                name: productName
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showFeedback(data.message);
                    if (typeof CartAPI !== 'undefined' && data.data.cartCount !== undefined) {
                        CartAPI.updateCartCount(data.data.cartCount);
                    }
                    // Reload if on cart page to show changes
                    if (window.location.href.includes('cart.php')) {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    this.showFeedback(data.message, true);
                }
            })
            .catch(err => {
                console.error(err);
                this.showFeedback('Error removing item', true);
            });
    }

    clearCart() {
        if (!confirm('Are you sure you want to clear your cart?')) return;

        this.showFeedback('Clearing cart...');
        fetch(this.basePath + 'api/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'clear' })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showFeedback('Cart cleared');
                    if (typeof CartAPI !== 'undefined') {
                        CartAPI.updateCartCount(0);
                    }
                    if (window.location.href.includes('cart.php')) {
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } else {
                    this.showFeedback('Failed to clear cart', true);
                }
            })
            .catch(err => {
                console.error(err);
                this.showFeedback('Error clearing cart', true);
            });
    }

    addToCartByName(productName, autoCheckout = false) {
        // 1. Check if we are on a product detail page matching the name
        const mainTitle = document.querySelector('h1.product-name');
        if (mainTitle && mainTitle.textContent.toLowerCase().includes(productName)) {
            this.addCurrentProductToCart(autoCheckout);
            return;
        }

        // 2. Try to find product card on listing page
        const cards = document.querySelectorAll('.product-card');
        let found = false;

        for (const card of cards) {
            const title = card.querySelector('.product-title');
            if (title && title.textContent.toLowerCase().includes(productName)) {
                // Look for "Add to Cart" button
                const btn = card.querySelector('.btn-add-to-cart, .btn-primary');
                if (btn) {
                    btn.click();
                    this.showFeedback(`Added ${productName} to cart`);
                    found = true;

                    if (autoCheckout) {
                        this.showFeedback(`Proceeding to checkout...`);
                        setTimeout(() => {
                            window.location.href = this.basePath + 'checkout.php';
                        }, 1500);
                    }
                    break;
                }
            }
        }

        if (found) return;

        // 3. Fallback: Call API to add by name
        this.showFeedback(`Searching for "${productName}"...`);
        fetch(this.basePath + 'api/cart_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'add_by_name',
                name: productName
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.showFeedback(data.message);

                    // Update cart count if CartAPI is available
                    if (typeof CartAPI !== 'undefined' && data.data.cartCount) {
                        CartAPI.updateCartCount(data.data.cartCount);
                    }

                    if (autoCheckout) {
                        this.showFeedback(`Proceeding to checkout...`);
                        setTimeout(() => {
                            window.location.href = this.basePath + 'checkout.php';
                        }, 1500);
                    }
                } else {
                    this.showFeedback(data.message, true);
                }
            })
            .catch(err => {
                console.error(err);
                this.showFeedback('Error adding to cart', true);
            });
    }

    addCurrentProductToCart(autoCheckout = false) {
        // Look for the main Add to Cart button on product detail page
        const addToCartBtn = document.querySelector('#addToCartForm button[type="submit"], .btn-add-to-cart, .btn-primary');

        // Check if button text implies adding to cart
        if (addToCartBtn && (addToCartBtn.textContent.toLowerCase().includes('cart') || addToCartBtn.querySelector('.fa-shopping-cart'))) {
            addToCartBtn.click();
            this.showFeedback('Adding to cart...');
            if (autoCheckout) {
                setTimeout(() => {
                    window.location.href = this.basePath + 'checkout.php';
                }, 1500);
            }
        } else {
            this.showFeedback('Add to cart button not found.');
        }
    }

    clickButtonByText(text) {
        // Find all clickable elements
        const elements = document.querySelectorAll('button, a, input[type="submit"], input[type="button"], [role="button"]');
        let bestMatch = null;

        for (const el of elements) {
            // Gather all possible text sources
            const content = (el.textContent || '').toLowerCase().trim();
            const value = (el.value || '').toLowerCase().trim();
            const title = (el.title || '').toLowerCase().trim();
            const ariaLabel = (el.getAttribute('aria-label') || '').toLowerCase().trim();
            const alt = (el.getAttribute('alt') || '').toLowerCase().trim(); // For image buttons
            const name = (el.getAttribute('name') || '').toLowerCase().trim();

            // Check exact matches first
            if (content === text || value === text || title === text || ariaLabel === text || alt === text) {
                el.click();
                this.showFeedback(`Clicked "${text}"`);
                return true;
            }

            // Check partial matches
            if (content.includes(text) || value.includes(text) || title.includes(text) || ariaLabel.includes(text) || alt.includes(text) || name.includes(text)) {
                if (!bestMatch) bestMatch = el;
            }
        }

        if (bestMatch) {
            bestMatch.click();
            this.showFeedback(`Clicked "${text}"`);
            return true;
        }

        return false;
    }

    filterByCategory(categoryName) {
        if (!window.location.href.includes('products.php')) {
            window.location.href = this.basePath + 'products.php?q=&cat=' + encodeURIComponent(categoryName); // Attempt to pass category name if ID not known, but logic below handles dropdown
            // Ideally we should redirect then select, but simple redirect is safer if we can't select immediately.
            // However, let's just redirect to products.php and let the user try again or handle query param if supported.
            // Better: Redirect and try to match category by text if possible, or just redirect.
            window.location.href = this.basePath + 'products.php';
            return;
        }

        const select = document.querySelector('select[name="cat"]');
        if (select) {
            let found = false;
            for (const option of select.options) {
                if (option.text.toLowerCase().includes(categoryName)) {
                    select.value = option.value;
                    found = true;
                    this.showFeedback(`Showing ${option.text}`);
                    // Trigger change or submit form
                    const form = select.closest('form');
                    if (form) form.submit();
                    break;
                }
            }
            if (!found) this.showFeedback(`Category "${categoryName}" not found`);
        }
    }

    applySort(sortType) {
        if (!window.location.href.includes('products.php')) {
            window.location.href = this.basePath + 'products.php?sort=' + sortType;
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set('sort', sortType);
        window.location.href = url.toString();
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    new VoiceControl();
});
