<?php
// Test page for AI Copilot
require_once __DIR__ . '/../partials/header.php';
?>

<div style="max-width: 800px; margin: 3rem auto; padding: 2rem;">
  <div style="background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem;">
    <h2 style="color: #667eea; margin-bottom: 1rem;">🤖 AI Copilot Test Page</h2>
    <p>Welcome to the AI Copilot testing interface for The Seventh Com!</p>
    
    <h3 style="margin-top: 2rem; color: #764ba2;">✅ Features Implemented:</h3>
    <ul style="list-style: none; padding: 0;">
      <li style="padding: 0.75rem; margin: 0.5rem 0; background: #f9fafb; border-radius: 0.5rem;">
        <i class="fas fa-check-circle" style="color: #10b981;"></i> Floating chat launcher (bottom-right)
      </li>
      <li style="padding: 0.75rem; margin: 0.5rem 0; background: #f9fafb; border-radius: 0.5rem;">
        <i class="fas fa-check-circle" style="color: #10b981;"></i> Typing animation with dots
      </li>
      <li style="padding: 0.75rem; margin: 0.5rem 0; background: #f9fafb; border-radius: 0.5rem;">
        <i class="fas fa-check-circle" style="color: #10b981;"></i> Session memory (context awareness)
      </li>
      <li style="padding: 0.75rem; margin: 0.5rem 0; background: #f9fafb; border-radius: 0.5rem;">
        <i class="fas fa-check-circle" style="color: #10b981;"></i> Quick action buttons
      </li>
      <li style="padding: 0.75rem; margin: 0.5rem 0; background: #f9fafb; border-radius: 0.5rem;">
        <i class="fas fa-check-circle" style="color: #10b981;"></i> Mobile responsive design
      </li>
      <li style="padding: 0.75rem; margin: 0.5rem 0; background: #f9fafb; border-radius: 0.5rem;">
        <i class="fas fa-check-circle" style="color: #10b981;"></i> Full-page support interface
      </li>
    </ul>

    <h3 style="margin-top: 2rem; color: #764ba2;">🧪 Test Actions:</h3>
    <div style="margin-top: 1rem;">
      <button onclick="document.getElementById('ai-chat-launcher').click()" 
              style="padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 50px; font-weight: 600; cursor: pointer; margin: 0.5rem;">
        Open Chat Widget
      </button>
      <a href="<?= base_url('public/support.php') ?>" 
         style="display: inline-block; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 50px; text-decoration: none; font-weight: 600; margin: 0.5rem;">
        Visit Support Page
      </a>
    </div>

    <h3 style="margin-top: 2rem; color: #764ba2;">💬 Sample Queries to Test:</h3>
    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 0.5rem; margin-top: 1rem;">
      <div style="background: white; padding: 1rem; border-radius: 0.5rem; margin: 0.5rem 0; border-left: 4px solid #667eea;">
        <strong>Order Tracking:</strong> "I want to track my order #1234"
      </div>
      <div style="background: white; padding: 1rem; border-radius: 0.5rem; margin: 0.5rem 0; border-left: 4px solid #667eea;">
        <strong>Refund Request:</strong> "I need a refund for order #5678"
      </div>
      <div style="background: white; padding: 1rem; border-radius: 0.5rem; margin: 0.5rem 0; border-left: 4px solid #667eea;">
        <strong>Order Cancellation:</strong> "Can I cancel my order?"
      </div>
      <div style="background: white; padding: 1rem; border-radius: 0.5rem; margin: 0.5rem 0; border-left: 4px solid #667eea;">
        <strong>Product Info:</strong> "What payment methods do you accept?"
      </div>
      <div style="background: white; padding: 1rem; border-radius: 0.5rem; margin: 0.5rem 0; border-left: 4px solid #667eea;">
        <strong>General Help:</strong> "How long does delivery take?"
      </div>
    </div>

    <h3 style="margin-top: 2rem; color: #764ba2;">📋 Technical Details:</h3>
    <ul style="line-height: 1.8;">
      <li><strong>Backend:</strong> PHP with Groq API (Llama 3.3 70B)</li>
      <li><strong>Frontend:</strong> Vanilla JavaScript (no dependencies)</li>
      <li><strong>Styling:</strong> Custom CSS with animations</li>
      <li><strong>API Endpoint:</strong> /public/ai-chat-api.php</li>
      <li><strong>Session Storage:</strong> PHP sessions for chat history</li>
      <li><strong>Response Time:</strong> ~1-3 seconds</li>
    </ul>

    <div style="margin-top: 2rem; padding: 1rem; background: #fef3c7; border-radius: 0.5rem; border-left: 4px solid #f59e0b;">
      <strong>⚠️ Note:</strong> Make sure your XAMPP server is running and the Groq API key is configured in config/ai.php
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
