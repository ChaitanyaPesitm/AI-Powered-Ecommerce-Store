/**
 * PWA Initialization Script for The Seventh Com
 * Handles service worker registration, install prompt, and PWA features
 */

let deferredPrompt;
let swRegistration;

// Initialize PWA on page load
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        // 🛑 Skip PWA on checkout page to prevent payment conflicts
        if (!window.location.href.includes('checkout.php')) {
            initializePWA();
        } else {
            console.log('[PWA] Skipped on checkout page');
        }
    });
}

/**
 * Initialize PWA features
 */
async function initializePWA() {
    try {
        // Register service worker
        await registerServiceWorker();

        // Setup install prompt
        setupInstallPrompt();

        // Check if already installed
        checkIfInstalled();

        // Setup update checker
        setupUpdateChecker();

        console.log('[PWA] Initialization complete');
    } catch (error) {
        console.error('[PWA] Initialization failed:', error);
    }
}

/**
 * Register service worker
 */
async function registerServiceWorker() {
    try {
        swRegistration = await navigator.serviceWorker.register('/ecommerce/service-worker.js', {
            scope: '/ecommerce/'
        });

        console.log('[PWA] Service Worker registered:', swRegistration.scope);

        // Handle updates
        swRegistration.addEventListener('updatefound', () => {
            const newWorker = swRegistration.installing;

            newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    // New service worker available
                    showUpdateNotification();
                }
            });
        });

        return swRegistration;
    } catch (error) {
        console.error('[PWA] Service Worker registration failed:', error);
        throw error;
    }
}

/**
 * Setup install prompt
 */
function setupInstallPrompt() {
    window.addEventListener('beforeinstallprompt', (event) => {
        console.log('[PWA] Install prompt available');

        // Prevent default install prompt
        event.preventDefault();

        // Store event for later use
        deferredPrompt = event;

        // Show custom install button
        showInstallButton();
    });

    // Listen for app installed event
    window.addEventListener('appinstalled', () => {
        console.log('[PWA] App installed successfully');
        deferredPrompt = null;
        hideInstallButton();

        // Show thank you message
        showNotification('✅ App Installed!', 'The Seventh Com has been added to your home screen.');
    });
}

/**
 * Show install button
 */
function showInstallButton() {
    const installBtn = document.getElementById('pwaInstallBtn');
    const installBanner = document.getElementById('pwaInstallBanner');

    if (installBtn) {
        installBtn.style.display = 'block';
        installBtn.classList.add('pulse-animation');
    }

    if (installBanner) {
        installBanner.style.display = 'block';
    }
}

/**
 * Hide install button
 */
function hideInstallButton() {
    const installBtn = document.getElementById('pwaInstallBtn');
    const installBanner = document.getElementById('pwaInstallBanner');

    if (installBtn) {
        installBtn.style.display = 'none';
    }

    if (installBanner) {
        installBanner.style.display = 'none';
    }
}

/**
 * Trigger install prompt
 */
async function installPWA() {
    if (!deferredPrompt) {
        console.log('[PWA] Install prompt not available');

        // Check if already installed
        if (window.matchMedia('(display-mode: standalone)').matches) {
            showNotification('Already Installed', 'The app is already installed on your device.');
        } else {
            showNotification('Install Not Available', 'Please use Chrome, Edge, or Samsung Internet browser to install.');
        }

        return;
    }

    // Show install prompt
    deferredPrompt.prompt();

    // Wait for user response
    const { outcome } = await deferredPrompt.userChoice;

    console.log('[PWA] Install prompt outcome:', outcome);

    if (outcome === 'accepted') {
        console.log('[PWA] User accepted install');
        showNotification('Installing...', 'The app is being installed to your device.');
    } else {
        console.log('[PWA] User dismissed install');
    }

    // Clear deferred prompt
    deferredPrompt = null;
    hideInstallButton();
}

/**
 * Check if app is already installed
 */
function checkIfInstalled() {
    // Check if running in standalone mode
    if (window.matchMedia('(display-mode: standalone)').matches ||
        window.navigator.standalone === true) {
        console.log('[PWA] App is running in standalone mode');
        hideInstallButton();

        // Add installed class to body
        document.body.classList.add('pwa-installed');
    }
}

/**
 * Setup update checker
 */
function setupUpdateChecker() {
    // Check for updates every hour
    setInterval(() => {
        if (swRegistration) {
            swRegistration.update();
        }
    }, 60 * 60 * 1000);
}

/**
 * Show update notification
 */
function showUpdateNotification() {
    const updateBanner = document.createElement('div');
    updateBanner.id = 'pwaUpdateBanner';
    updateBanner.className = 'pwa-update-banner';
    updateBanner.innerHTML = `
        <div class="update-content">
            <div class="update-icon">🔄</div>
            <div class="update-text">
                <strong>Update Available!</strong>
                <p>A new version of The Seventh Com is ready.</p>
            </div>
            <button onclick="updatePWA()" class="update-btn">Update Now</button>
            <button onclick="dismissUpdate()" class="dismiss-btn">×</button>
        </div>
    `;

    document.body.appendChild(updateBanner);

    // Auto-show after animation
    setTimeout(() => {
        updateBanner.classList.add('show');
    }, 100);
}

/**
 * Update PWA
 */
function updatePWA() {
    if (swRegistration && swRegistration.waiting) {
        // Tell service worker to skip waiting
        swRegistration.waiting.postMessage({ action: 'skipWaiting' });

        // Reload page
        window.location.reload();
    }
}

/**
 * Dismiss update notification
 */
function dismissUpdate() {
    const updateBanner = document.getElementById('pwaUpdateBanner');
    if (updateBanner) {
        updateBanner.classList.remove('show');
        setTimeout(() => {
            updateBanner.remove();
        }, 300);
    }
}

/**
 * Show notification
 */
function showNotification(title, message) {
    // Check if notifications are supported
    if (!('Notification' in window)) {
        alert(`${title}\n${message}`);
        return;
    }

    // Check notification permission
    if (Notification.permission === 'granted') {
        new Notification(title, {
            body: message,
            icon: '/ecommerce/assets/icons/icon-192x192.png',
            badge: '/ecommerce/assets/icons/icon-72x72.png'
        });
    } else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                new Notification(title, {
                    body: message,
                    icon: '/ecommerce/assets/icons/icon-192x192.png'
                });
            }
        });
    } else {
        // Fallback to alert
        alert(`${title}\n${message}`);
    }
}

/**
 * Check online/offline status
 */
window.addEventListener('online', () => {
    console.log('[PWA] Back online');
    showNotification('Back Online', 'Your internet connection has been restored.');
});

window.addEventListener('offline', () => {
    console.log('[PWA] Gone offline');
    showNotification('Offline Mode', 'You can still browse cached pages.');
});

/**
 * Share API integration
 */
async function sharePage(title, text, url) {
    if (navigator.share) {
        try {
            await navigator.share({
                title: title || document.title,
                text: text || 'Check out The Seventh Com!',
                url: url || window.location.href
            });
            console.log('[PWA] Shared successfully');
        } catch (error) {
            console.log('[PWA] Share cancelled or failed:', error);
        }
    } else {
        // Fallback - copy to clipboard
        navigator.clipboard.writeText(url || window.location.href);
        showNotification('Link Copied', 'The link has been copied to your clipboard.');
    }
}

/**
 * Add to home screen prompt for iOS
 */
function showIOSInstallPrompt() {
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    const isInStandaloneMode = window.navigator.standalone === true;

    if (isIOS && !isInStandaloneMode) {
        const iosBanner = document.createElement('div');
        iosBanner.id = 'iosInstallBanner';
        iosBanner.className = 'ios-install-banner';
        iosBanner.innerHTML = `
            <div class="ios-content">
                <div class="ios-icon">📱</div>
                <div class="ios-text">
                    <strong>Install The Seventh Com</strong>
                    <p>Tap <span class="share-icon">⎙</span> then "Add to Home Screen"</p>
                </div>
                <button onclick="dismissIOSPrompt()" class="ios-dismiss">×</button>
            </div>
        `;

        document.body.appendChild(iosBanner);
    }
}

/**
 * Dismiss iOS install prompt
 */
function dismissIOSPrompt() {
    const iosBanner = document.getElementById('iosInstallBanner');
    if (iosBanner) {
        iosBanner.remove();
    }
}

// Show iOS prompt after 3 seconds
setTimeout(showIOSInstallPrompt, 3000);

console.log('[PWA] Script loaded');
