<?php
require_once __DIR__ . '/../config/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM stories WHERE id = ?")->execute([$id]);
    redirect('admin/stories.php');
}

// Handle Edit Mode
$editMode = false;
$storyToEdit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM stories WHERE id = ?");
    $stmt->execute([$editId]);
    $storyToEdit = $stmt->fetch();
    if ($storyToEdit) {
        $editMode = true;
    }
}

// Handle Form Submit (Add or Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $image = $editMode ? $storyToEdit['image'] : '';
    $video = $editMode ? $storyToEdit['video'] : '';
    $content_image = $editMode ? $storyToEdit['content_image'] : '';

    // Upload Avatar Image
    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid('story_thumb_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/uploads/' . $image);
    }

    // Upload Content Image (optional)
    if (!empty($_FILES['content_image']['name'])) {
        $ext = pathinfo($_FILES['content_image']['name'], PATHINFO_EXTENSION);
        $content_image = uniqid('story_content_') . '.' . $ext;
        move_uploaded_file($_FILES['content_image']['tmp_name'], __DIR__ . '/../assets/uploads/' . $content_image);
    }

    // Upload Video (optional)
    if (!empty($_FILES['video']['name'])) {
        $ext = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
        $video = uniqid('story_vid_') . '.' . $ext;
        move_uploaded_file($_FILES['video']['tmp_name'], __DIR__ . '/../assets/uploads/' . $video);
    }

    if ($title && $image) {
        if ($editMode) {
            // Update
            $st = $pdo->prepare("UPDATE stories SET title=?, image=?, video=?, content_image=? WHERE id=?");
            $st->execute([$title, $image, $video, $content_image, $storyToEdit['id']]);
        } else {
            // Insert
            $st = $pdo->prepare("INSERT INTO stories (title, image, video, content_image) VALUES (?, ?, ?, ?)");
            $st->execute([$title, $image, $video, $content_image]);
        }
        redirect('admin/stories.php');
    }
}

// Fetch Stories
$stories = $pdo->query("SELECT * FROM stories ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/_admin-header.php';
?>

<div class="admin-header" data-aos="fade-down">
  <h1 class="page-title">
    <i class="fas fa-photo-video"></i> Manage Stories
  </h1>
</div>

<div class="admin-content" data-aos="fade-up">
    <!-- Add/Edit Story Form -->
    <div class="card mb-4 p-4">
        <h5 class="mb-3"><?= $editMode ? 'Edit Story' : 'Add New Story' ?></h5>
        <form method="post" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" class="form-input" placeholder="Story Title" required value="<?= $editMode ? htmlspecialchars($storyToEdit['title']) : '' ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Thumbnail Image <?= $editMode ? '(Leave empty to keep)' : '(Required)' ?></label>
                    <input type="file" name="image" accept="image/*" class="form-input" <?= $editMode ? '' : 'required' ?>>
                    <?php if ($editMode && $storyToEdit['image']): ?>
                        <div class="mt-2">
                            <img src="<?= base_url('assets/uploads/' . $storyToEdit['image']) ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <small class="text-muted">Current</small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Content Image (Optional)</label>
                    <input type="file" name="content_image" accept="image/*" class="form-input">
                    <?php if ($editMode && $storyToEdit['content_image']): ?>
                        <div class="mt-2">
                            <img src="<?= base_url('assets/uploads/' . $storyToEdit['content_image']) ?>" style="width: 40px; height: 40px; object-fit: cover;">
                            <small class="text-muted">Current</small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Video (Optional)</label>
                    <input type="file" name="video" accept="video/*" class="form-input">
                    <?php if ($editMode && $storyToEdit['video']): ?>
                        <div class="mt-2">
                            <small class="text-muted">Current Video: <?= htmlspecialchars($storyToEdit['video']) ?></small>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-save">
                        <i class="fas fa-save"></i> <?= $editMode ? 'Update Story' : 'Add Story' ?>
                    </button>
                    <?php if ($editMode): ?>
                        <a href="stories.php" class="btn btn-secondary ms-2" style="background: #e2e8f0; color: #4a5568;">Cancel</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Stories List -->
    <div class="table-container">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Thumbnail</th>
                    <th>Title</th>
                    <th>Content Type</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stories as $s): ?>
                <tr>
                    <td>#<?= $s['id'] ?></td>
                    <td>
                        <img src="<?= base_url('assets/uploads/' . $s['image']) ?>" 
                             style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
                    </td>
                    <td><?= htmlspecialchars($s['title']) ?></td>
                    <td>
                        <?php if ($s['video']): ?>
                            <span class="badge bg-info">Video</span>
                        <?php elseif ($s['content_image']): ?>
                            <span class="badge bg-success">Image</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Thumbnail Only</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <a href="?edit=<?= $s['id'] ?>" class="btn-action btn-edit" title="Edit" style="padding: 6px 10px; border-radius: 6px;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?= $s['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Delete this story?')" title="Delete" style="padding: 6px 10px; border-radius: 6px;">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($stories)): ?>
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">No stories found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/_admin-footer.php'; ?>
