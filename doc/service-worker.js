/**
 * Service Worker for The Seventh Com PWA
 * Handles caching, offline functionality, and performance optimization
 */

const CACHE_VERSION = 'v1.0.0';
const CACHE_NAME = `seventh-com-${CACHE_VERSION}`;
const MAX_CACHE_SIZE = 50 * 1024 * 1024; // 50MB limit

// Files to cache immediately on install
const STATIC_CACHE_URLS = [
    '/ecommerce/public/index.php',
    '/ecommerce/public/products.php',
    '/ecommerce/public/cart.php',
    '/ecommerce/public/offline.html',
    '/ecommerce/manifest.json',
    '/ecommerce/assets/icons/icon-192x192.png',
    '/ecommerce/assets/icons/icon-512x512.png'
];

// Cache strategies
const CACHE_STRATEGIES = {
    // Cache first, then network (for static assets)
    CACHE_FIRST: 'cache-first',
    // Network first, then cache (for dynamic content)
    NETWORK_FIRST: 'network-first',
    // Network only (for API calls that need fresh data)
    NETWORK_ONLY: 'network-only',
    // Cache only (for offline fallback)
    CACHE_ONLY: 'cache-only'
};

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...', CACHE_VERSION);
    
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[Service Worker] Caching static assets');
                return cache.addAll(STATIC_CACHE_URLS);
            })
            .then(() => {
                console.log('[Service Worker] Installation complete');
                return self.skipWaiting(); // Activate immediately
            })
            .catch((error) => {
                console.error('[Service Worker] Installation failed:', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...', CACHE_VERSION);
    
    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('[Service Worker] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[Service Worker] Activation complete');
                return self.clients.claim(); // Take control immediately
            })
    );
});

// Fetch event - handle requests with caching strategies
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip chrome extensions and other protocols
    if (!url.protocol.startsWith('http')) {
        return;
    }
    
    // Determine caching strategy based on request type
    const strategy = getCachingStrategy(request);
    
    event.respondWith(
        handleRequest(request, strategy)
    );
});

/**
 * Determine caching strategy based on request
 */
function getCachingStrategy(request) {
    const url = new URL(request.url);
    const path = url.pathname;
    
    // Static assets - Cache First
    if (
        path.match(/\.(css|js|jpg|jpeg|png|gif|svg|webp|woff|woff2|ttf|eot)$/i) ||
        path.includes('/assets/')
    ) {
        return CACHE_STRATEGIES.CACHE_FIRST;
    }
    
    // API calls and dynamic data - Network First
    if (
        path.includes('/api/') ||
        path.includes('cart.php') ||
        path.includes('checkout.php') ||
        path.includes('orders.php')
    ) {
        return CACHE_STRATEGIES.NETWORK_FIRST;
    }
    
    // Product pages - Network First (to get latest data)
    if (path.includes('product.php') || path.includes('products.php')) {
        return CACHE_STRATEGIES.NETWORK_FIRST;
    }
    
    // Default - Network First
    return CACHE_STRATEGIES.NETWORK_FIRST;
}

/**
 * Handle request with specified caching strategy
 */
async function handleRequest(request, strategy) {
    switch (strategy) {
        case CACHE_STRATEGIES.CACHE_FIRST:
            return cacheFirst(request);
        
        case CACHE_STRATEGIES.NETWORK_FIRST:
            return networkFirst(request);
        
        case CACHE_STRATEGIES.NETWORK_ONLY:
            return networkOnly(request);
        
        case CACHE_STRATEGIES.CACHE_ONLY:
            return cacheOnly(request);
        
        default:
            return networkFirst(request);
    }
}

/**
 * Cache First strategy
 * Try cache first, fallback to network
 */
async function cacheFirst(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);
    
    if (cached) {
        console.log('[Service Worker] Cache hit:', request.url);
        return cached;
    }
    
    try {
        const response = await fetch(request);
        
        // Cache successful responses
        if (response.ok) {
            await checkCacheSizeAndAdd(cache, request, response.clone());
        }
        
        return response;
    } catch (error) {
        console.error('[Service Worker] Fetch failed:', error);
        return getOfflineFallback(request);
    }
}

/**
 * Network First strategy
 * Try network first, fallback to cache
 */
async function networkFirst(request) {
    const cache = await caches.open(CACHE_NAME);
    
    try {
        const response = await fetch(request);
        
        // Cache successful responses
        if (response.ok) {
            await checkCacheSizeAndAdd(cache, request, response.clone());
        }
        
        return response;
    } catch (error) {
        console.log('[Service Worker] Network failed, trying cache:', request.url);
        const cached = await cache.match(request);
        
        if (cached) {
            return cached;
        }
        
        return getOfflineFallback(request);
    }
}

/**
 * Network Only strategy
 */
async function networkOnly(request) {
    try {
        return await fetch(request);
    } catch (error) {
        return getOfflineFallback(request);
    }
}

/**
 * Cache Only strategy
 */
async function cacheOnly(request) {
    const cache = await caches.open(CACHE_NAME);
    const cached = await cache.match(request);
    
    if (cached) {
        return cached;
    }
    
    return getOfflineFallback(request);
}

/**
 * Get offline fallback page
 */
async function getOfflineFallback(request) {
    const url = new URL(request.url);
    
    // For HTML pages, return offline page
    if (request.headers.get('accept').includes('text/html')) {
        const cache = await caches.open(CACHE_NAME);
        const offlinePage = await cache.match('/ecommerce/public/offline.html');
        
        if (offlinePage) {
            return offlinePage;
        }
    }
    
    // For other resources, return error response
    return new Response('Offline - Resource not available', {
        status: 503,
        statusText: 'Service Unavailable',
        headers: new Headers({
            'Content-Type': 'text/plain'
        })
    });
}

/**
 * Check cache size and add new item
 * Implements LRU (Least Recently Used) eviction
 */
async function checkCacheSizeAndAdd(cache, request, response) {
    // Get current cache size
    const keys = await cache.keys();
    let totalSize = 0;
    
    for (const key of keys) {
        const cachedResponse = await cache.match(key);
        if (cachedResponse) {
            const blob = await cachedResponse.blob();
            totalSize += blob.size;
        }
    }
    
    // If adding this would exceed limit, remove oldest items
    const responseBlob = await response.clone().blob();
    const newItemSize = responseBlob.size;
    
    if (totalSize + newItemSize > MAX_CACHE_SIZE) {
        console.log('[Service Worker] Cache size limit reached, removing old items');
        
        // Remove oldest items until we have space
        let removed = 0;
        for (const key of keys) {
            if (totalSize + newItemSize <= MAX_CACHE_SIZE) {
                break;
            }
            
            const cachedResponse = await cache.match(key);
            if (cachedResponse) {
                const blob = await cachedResponse.blob();
                totalSize -= blob.size;
                await cache.delete(key);
                removed++;
            }
        }
        
        console.log(`[Service Worker] Removed ${removed} items from cache`);
    }
    
    // Add new item to cache
    await cache.put(request, response);
}

/**
 * Background sync for offline actions
 */
self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync:', event.tag);
    
    if (event.tag === 'sync-cart') {
        event.waitUntil(syncCart());
    }
    
    if (event.tag === 'sync-orders') {
        event.waitUntil(syncOrders());
    }
});

/**
 * Sync cart data
 */
async function syncCart() {
    console.log('[Service Worker] Syncing cart...');
    // Implementation for syncing cart data when back online
}

/**
 * Sync orders data
 */
async function syncOrders() {
    console.log('[Service Worker] Syncing orders...');
    // Implementation for syncing orders when back online
}

/**
 * Push notifications
 */
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push notification received');
    
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'The Seventh Com';
    const options = {
        body: data.body || 'New notification',
        icon: '/ecommerce/assets/icons/icon-192x192.png',
        badge: '/ecommerce/assets/icons/icon-72x72.png',
        vibrate: [200, 100, 200],
        data: data.url || '/ecommerce/public/index.php',
        actions: [
            {
                action: 'open',
                title: 'View'
            },
            {
                action: 'close',
                title: 'Close'
            }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

/**
 * Notification click handler
 */
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked');
    
    event.notification.close();
    
    if (event.action === 'open') {
        event.waitUntil(
            clients.openWindow(event.notification.data)
        );
    }
});

/**
 * Message handler for communication with main app
 */
self.addEventListener('message', (event) => {
    console.log('[Service Worker] Message received:', event.data);
    
    if (event.data.action === 'skipWaiting') {
        self.skipWaiting();
    }
    
    if (event.data.action === 'clearCache') {
        event.waitUntil(
            caches.delete(CACHE_NAME).then(() => {
                console.log('[Service Worker] Cache cleared');
            })
        );
    }
});

console.log('[Service Worker] Loaded', CACHE_VERSION);
