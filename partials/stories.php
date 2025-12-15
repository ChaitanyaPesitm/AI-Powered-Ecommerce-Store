<?php
// Fetch stories from database
global $pdo;
$stmt = $pdo->query("SELECT * FROM stories ORDER BY created_at DESC");
$dbStories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Transform for JS
$stories = [];
foreach ($dbStories as $s) {
    $image = $s['image'];
    if (!preg_match('/^https?:\/\//', $image)) {
        $image = base_url('assets/uploads/' . $image);
    }

    $video = $s['video'];
    if ($video && !preg_match('/^https?:\/\//', $video)) {
        $video = base_url('assets/uploads/' . $video);
    }

    $content_image = $s['content_image'];
    if ($content_image && !preg_match('/^https?:\/\//', $content_image)) {
        $content_image = base_url('assets/uploads/' . $content_image);
    }

    $stories[] = [
        'id' => $s['id'],
        'name' => $s['title'],
        'image' => $image,
        'video' => $video,
        'content_image' => $content_image,
        'seen' => false
    ];
}

// If no stories, hide the section
if (empty($stories)) return;
?>

<!-- Stories Bar -->
<div class="stories-container container mt-4 mb-4">
    <div class="stories-scroll-wrapper">
        <?php foreach ($stories as $story): ?>
            <div class="story-item" onclick="openStory(<?= $story['id'] ?>)">
                <div class="story-ring <?= $story['seen'] ? 'seen' : '' ?>">
                    <div class="story-avatar">
                        <img src="<?= $story['image'] ?>" alt="<?= htmlspecialchars($story['name']) ?>">
                    </div>
                </div>
                <span class="story-name"><?= htmlspecialchars($story['name']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Full Screen Story Modal -->
<div id="storyModal" class="story-modal">
    <button class="close-story" onclick="closeStory()">&times;</button>
    
    <div class="story-progress-bar">
        <div class="progress-fill" id="storyProgress"></div>
    </div>

    <div class="story-content-wrapper">
        <!-- Content injected via JS -->
        <div id="storyViewer"></div>
    </div>

    <div class="story-controls">
        <div class="control-left" onclick="prevStory()"></div>
        <div class="control-right" onclick="nextStory()"></div>
    </div>
</div>

<style>
/* Stories Bar */
.stories-container {
    overflow-x: auto;
    padding: 20px 0;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    background: linear-gradient(to bottom, rgba(255,255,255,0.5), rgba(255,255,255,0));
    backdrop-filter: blur(10px);
}

.stories-container::-webkit-scrollbar {
    display: none;
}

.stories-scroll-wrapper {
    display: flex;
    gap: 20px;
    padding: 0 15px;
    justify-content: center; /* Center if few stories */
}

.story-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    width: 80px;
    flex-shrink: 0;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.story-item:hover {
    transform: translateY(-5px);
}

.story-ring {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    padding: 3px;
    background: linear-gradient(45deg, #f09433 0%, #e6683c 25%, #dc2743 50%, #cc2366 75%, #bc1888 100%);
    position: relative;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

/* Unseen Story Animation */
.story-ring:not(.seen)::after {
    content: '';
    position: absolute;
    top: -2px; left: -2px; right: -2px; bottom: -2px;
    border-radius: 50%;
    background: inherit;
    z-index: -1;
    animation: spin 3s linear infinite;
    opacity: 0.7;
    filter: blur(4px);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.story-ring.seen {
    background: #e0e0e0;
    box-shadow: none;
}

.story-ring.seen::after {
    display: none;
}

.story-avatar {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 3px solid white;
    overflow: hidden;
    background: white;
    position: relative;
    z-index: 2;
}

.story-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.story-item:hover .story-avatar img {
    transform: scale(1.1);
}

.story-name {
    font-size: 13px;
    margin-top: 8px;
    color: #333;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
    font-weight: 600;
    letter-spacing: 0.3px;
}

/* Dark Mode Support */
[data-theme="dark"] .story-name {
    color: #e0e0e0;
}
[data-theme="dark"] .story-avatar {
    border-color: #1a1a1a;
}
[data-theme="dark"] .stories-container {
    background: linear-gradient(to bottom, rgba(0,0,0,0.5), rgba(0,0,0,0));
}

/* Story Modal */
.story-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 9999;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(15px);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.story-modal.active {
    display: flex;
    opacity: 1;
}

.story-content-wrapper {
    width: 100%;
    height: 100%;
    max-width: 450px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
    box-shadow: 0 0 50px rgba(0,0,0,0.5);
}

@media (min-width: 768px) {
    .story-content-wrapper {
        height: 85vh;
        border-radius: 16px;
        overflow: hidden;
    }
}

#storyViewer {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

#storyViewer img, #storyViewer video {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: #000;
}

.close-story {
    position: absolute;
    top: 20px;
    right: 20px;
    color: white;
    font-size: 32px;
    background: rgba(255,255,255,0.1);
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    z-index: 10001;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.close-story:hover {
    background: rgba(255,255,255,0.2);
}

.story-progress-bar {
    position: absolute;
    top: 15px;
    left: 50%;
    transform: translateX(-50%);
    width: 92%;
    max-width: 440px;
    height: 4px;
    background: rgba(255,255,255,0.2);
    border-radius: 4px;
    z-index: 10001;
    overflow: hidden;
    display: flex;
}

.progress-fill {
    height: 100%;
    background: white;
    width: 0%;
    border-radius: 4px;
    box-shadow: 0 0 10px rgba(255,255,255,0.5);
}

.story-controls {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    z-index: 10000;
}

.control-left, .control-right {
    flex: 1;
    height: 100%;
    cursor: pointer;
}
</style>

<script>
const storiesData = <?= json_encode($stories) ?>;
let currentStoryIndex = 0;
let storyTimer = null;
let progressInterval = null;
const STORY_DURATION = 5000; // 5 seconds per story

function openStory(id) {
    const index = storiesData.findIndex(s => s.id === id);
    if (index === -1) return;
    
    currentStoryIndex = index;
    document.getElementById('storyModal').classList.add('active');
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
    showStory();
}

function closeStory() {
    document.getElementById('storyModal').classList.remove('active');
    document.body.style.overflow = '';
    clearTimeout(storyTimer);
    clearInterval(progressInterval);
    
    // Stop video if playing
    const viewer = document.getElementById('storyViewer');
    viewer.innerHTML = '';
}

function showStory() {
    clearTimeout(storyTimer);
    clearInterval(progressInterval);
    
    const story = storiesData[currentStoryIndex];
    const viewer = document.getElementById('storyViewer');
    const progressBar = document.getElementById('storyProgress');
    
    // Reset progress
    progressBar.style.width = '0%';
    progressBar.style.transition = 'none';
    
    // Render Content
    if (story.video) {
        viewer.innerHTML = `<video src="${story.video}" autoplay playsinline muted class="story-media"></video>`;
        // Unmute after interaction if needed, but start muted for autoplay policy
    } else {
        const imgUrl = story.content_image || story.image;
        viewer.innerHTML = `<img src="${imgUrl}" class="story-media">`;
    }
    
    // Start Progress Animation
    setTimeout(() => {
        progressBar.style.transition = `width ${STORY_DURATION}ms linear`;
        progressBar.style.width = '100%';
    }, 50);
    
    // Auto advance
    storyTimer = setTimeout(nextStory, STORY_DURATION);
}

function nextStory() {
    if (currentStoryIndex < storiesData.length - 1) {
        currentStoryIndex++;
        showStory();
    } else {
        closeStory();
    }
}

function prevStory() {
    if (currentStoryIndex > 0) {
        currentStoryIndex--;
        showStory();
    } else {
        // Restart current story
        showStory();
    }
}
</script>
