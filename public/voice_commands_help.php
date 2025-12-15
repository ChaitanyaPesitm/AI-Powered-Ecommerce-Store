<?php require_once '../partials/header.php'; ?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8 text-center">Voice Command Reference</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Specific Products -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-indigo-600">🛍️ Product Commands</h2>
            <p class="mb-2 text-gray-600">Say "Add [Product Name]" to add specific items:</p>
            <ul class="list-disc list-inside space-y-1">
                <li>"Add Galaxy Watch"</li>
                <li>"Add Galaxy S25"</li>
                <li>"Add Vivobook"</li>
                <li>"Add HP Keyboard"</li>
                <li>"Add iPhone 16"</li>
                <li>"Add Bose Headphones"</li>
                <li>"Add Corsair Mouse"</li>
                <li>"Add HP Omen"</li>
                <li>"Add Dell Mouse"</li>
                <li>"Add Sony Camera"</li>
                <li>"Add Marshall Speaker"</li>
            </ul>
        </div>

        <!-- Cart Management -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-indigo-600">🛒 Cart Management</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>"Go to cart"</li>
                <li>"Remove [Product Name] from cart"</li>
                <li>"Clear cart" / "Empty cart"</li>
                <li>"Checkout"</li>
                <li>"Buy [Product Name]" (Adds & goes to checkout)</li>
            </ul>
        </div>

        <!-- Navigation -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-indigo-600">🧭 Navigation</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>"Go to home"</li>
                <li>"Go to products" / "Go to shop"</li>
                <li>"Go to login"</li>
                <li>"Go to register"</li>
                <li>"Go to wishlist"</li>
                <li>"Go to orders"</li>
            </ul>
        </div>

        <!-- Browsing & Tools -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4 text-indigo-600">🔧 Browsing & Tools</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>"Search for [Item]"</li>
                <li>"Show [Category]" (e.g., "Show Laptops")</li>
                <li>"Sort by price low" / "high"</li>
                <li>"Sort by new"</li>
                <li>"Scroll down" / "Scroll up"</li>
                <li>"Dark mode" / "Light mode"</li>
                <li>"Help"</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once '../partials/footer.php'; ?>
