<?php
require_once __DIR__ . '/config/functions.php';

echo "🌱 Seeding Immersive Content...\n";

// 1. Seed Stories
$pdo->exec("TRUNCATE TABLE stories"); // Clear existing
$stories = [
    [
        'title' => 'New Drops',
        'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
        'video' => 'https://assets.mixkit.co/videos/preview/mixkit-girl-in-neon-sign-1232-large.mp4',
        'content_image' => null
    ],
    [
        'title' => 'Flash Sale',
        'image' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
        'video' => null,
        'content_image' => 'https://images.unsplash.com/photo-1607082349566-187342175e2f?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
    ],
    [
        'title' => 'Summer',
        'image' => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
        'video' => 'https://assets.mixkit.co/videos/preview/mixkit-woman-running-above-the-camera-on-a-running-track-32840-large.mp4',
        'content_image' => null
    ],
    [
        'title' => 'Tech',
        'image' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
        'video' => null,
        'content_image' => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
    ]
];

$stmt = $pdo->prepare("INSERT INTO stories (title, image, video, content_image) VALUES (?, ?, ?, ?)");
foreach ($stories as $s) {
    $stmt->execute([$s['title'], $s['image'], $s['video'], $s['content_image']]);
}
echo "✅ Added " . count($stories) . " stories.\n";

// 2. Add 3D Model to ALL products
$modelUrl = 'https://modelviewer.dev/shared-assets/models/Astronaut.glb';
$pdo->exec("UPDATE products SET model_glb = '$modelUrl'");
echo "✅ Added 3D Astronaut model to ALL products.\n";

echo "🚀 Done! Refresh your homepage and product page.\n";
?>
