<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Form Test</title>
</head>
<body>
    <h1>Form Submission Test</h1>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div style="background: lightgreen; padding: 20px; margin: 20px 0;">
            <h2>✅ Form Submitted Successfully!</h2>
            <p><strong>Query:</strong> <?= htmlspecialchars($_POST['query'] ?? 'N/A') ?></p>
            <p><strong>Method:</strong> <?= $_SERVER['REQUEST_METHOD'] ?></p>
            <p><strong>Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
        </div>
    <?php endif; ?>
    
    <form method="post" action="" style="margin: 20px 0;">
        <label>Enter your question:</label><br>
        <input type="text" name="query" placeholder="Type something..." required style="padding: 10px; width: 300px; margin: 10px 0;">
        <button type="submit" style="padding: 10px 20px; background: #667eea; color: white; border: none; cursor: pointer;">
            Send
        </button>
    </form>
    
    <hr>
    <p><a href="suggestions.php">← Back to AI Assistant</a></p>
</body>
</html>
