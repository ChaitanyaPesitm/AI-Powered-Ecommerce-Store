<?php
// public/support.php - Full-Page AI Support Interface
require_once __DIR__ . '/../partials/header.php';
?>

<style>
  .support-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
  }

  .support-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: var(--radius-lg);
    padding: 3rem 2rem;
    text-align: center;
    color: white;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-lg);
  }

  .support-hero h1 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 1rem;
  }

  .support-hero p {
    font-size: 1.2rem;
    opacity: 0.95;
    max-width: 600px;
    margin: 0 auto;
  }

  .support-grid {
    display: grid;
    grid-template-columns: 1fr 2fr;
    gap: 2rem;
    margin-bottom: 3rem;
  }

  .support-sidebar {
    background: white;
    border-radius: var(--radius-lg);
    padding: 2rem;
    box-shadow: var(--shadow);
    height: fit-content;
  }

  .support-sidebar h3 {
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
    color: var(--text-primary);
  }

  .support-links {
    list-style: none;
    padding: 0;
  }

  .support-links li {
    margin-bottom: 1rem;
  }

  .support-links a {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--bg-secondary);
    border-radius: var(--radius);
    text-decoration: none;
    color: var(--text-primary);
    transition: all 0.3s ease;
  }

  .support-links a:hover {
    background: var(--primary);
    color: white;
    transform: translateX(5px);
  }

  .support-links i {
    font-size: 1.2rem;
    width: 24px;
    text-align: center;
  }

  .support-chat-area {
    background: white;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 600px;
  }

  .support-chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .support-chat-avatar {
    width: 50px;
    height: 50px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
  }

  .support-chat-info h3 {
    margin: 0;
    font-size: 1.3rem;
  }

  .support-chat-info p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.9rem;
  }

  .support-messages {
    flex: 1;
    overflow-y: auto;
    padding: 2rem;
    background: var(--bg-secondary);
  }

  .support-input-area {
    padding: 1.5rem;
    background: white;
    border-top: 1px solid var(--gray-200);
    display: flex;
    gap: 1rem;
  }

  .support-input-area input {
    flex: 1;
    padding: 0.75rem 1.25rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-full);
    font-size: 1rem;
    transition: all 0.3s ease;
  }

  .support-input-area input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
  }

  .support-input-area button {
    padding: 0.75rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: var(--radius-full);
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .support-input-area button:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
  }

  .support-input-area button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
  }

  .support-faqs {
    background: white;
    border-radius: var(--radius-lg);
    padding: 2rem;
    box-shadow: var(--shadow);
    margin-bottom: 2rem;
  }

  .support-faqs h2 {
    font-size: 1.8rem;
    margin-bottom: 1.5rem;
    color: var(--text-primary);
  }

  .faq-item {
    border-bottom: 1px solid var(--gray-200);
    padding: 1.5rem 0;
  }

  .faq-item:last-child {
    border-bottom: none;
  }

  .faq-question {
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--primary);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .faq-answer {
    color: var(--text-secondary);
    line-height: 1.6;
  }

  @media (max-width: 768px) {
    .support-grid {
      grid-template-columns: 1fr;
    }

    .support-hero h1 {
      font-size: 1.8rem;
    }

    .support-chat-area {
      height: 500px;
    }
  }
</style>

<div class="support-container">
  <!-- Hero Section -->
  <div class="support-hero" data-aos="fade-up">
    <h1>🤖 The Seventh Com AI Support</h1>
    <p>Get instant help with orders, returns, and product questions. Our AI assistant is here 24/7!</p>
  </div>

  <!-- Main Support Grid -->
  <div class="support-grid">
    <!-- Sidebar -->
    <div class="support-sidebar" data-aos="fade-right">
      <h3>Quick Links</h3>
      <ul class="support-links">
        <li>
          <a href="<?= base_url('public/orders.php') ?>">
            <i class="fas fa-box"></i>
            <span>My Orders</span>
          </a>
        </li>
        <li>
          <a href="<?= base_url('public/track-order.php') ?>">
            <i class="fas fa-shipping-fast"></i>
            <span>Track Order</span>
          </a>
        </li>
        <li>
          <a href="<?= base_url('public/cart.php') ?>">
            <i class="fas fa-shopping-cart"></i>
            <span>My Cart</span>
          </a>
        </li>
        <li>
          <a href="<?= base_url('public/wishlist.php') ?>">
            <i class="fas fa-heart"></i>
            <span>Wishlist</span>
          </a>
        </li>
        <li>
          <a href="<?= base_url('public/privacy.php') ?>">
            <i class="fas fa-shield-alt"></i>
            <span>Privacy Policy</span>
          </a>
        </li>
        <li>
          <a href="<?= base_url('public/terms.php') ?>">
            <i class="fas fa-file-contract"></i>
            <span>Terms & Conditions</span>
          </a>
        </li>
      </ul>

      <div style="margin-top: 2rem; padding: 1rem; background: var(--bg-secondary); border-radius: var(--radius);">
        <h4 style="margin-bottom: 0.5rem;">Need Human Help?</h4>
        <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 0.75rem;">
          Contact our support team
        </p>
        <p style="font-size: 0.9rem;">
          <i class="fas fa-envelope"></i> support@theseventhcom.com<br>
          <i class="fas fa-phone"></i> +91 98765 43210
        </p>
      </div>
    </div>

    <!-- Chat Area -->
    <div class="support-chat-area" data-aos="fade-left">
      <div class="support-chat-header">
        <div class="support-chat-avatar">
          <i class="fas fa-robot"></i>
        </div>
        <div class="support-chat-info">
          <h3>The Seventh Com AI Assistant</h3>
          <p><span style="color: #4ade80;">●</span> Online - Ready to help!</p>
        </div>
      </div>

      <div id="ai-messages" class="support-messages"></div>

      <div class="support-input-area">
        <input 
          type="text" 
          id="ai-input" 
          placeholder="Type your question here..."
          autocomplete="off"
        />
        <button id="ai-send">
          <i class="fas fa-paper-plane"></i> Send
        </button>
      </div>
    </div>
  </div>

  <!-- FAQs Section -->
  <div class="support-faqs" data-aos="fade-up">
    <h2>📚 Frequently Asked Questions</h2>
    
    <div class="faq-item">
      <div class="faq-question">
        <i class="fas fa-question-circle"></i>
        How do I track my order?
      </div>
      <div class="faq-answer">
        You can track your order by visiting the "My Orders" page or by asking our AI assistant for your order status. Just provide your Order ID!
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">
        <i class="fas fa-question-circle"></i>
        What is your return policy?
      </div>
      <div class="faq-answer">
        We offer a 7-day return policy for most products. Items must be unused and in original packaging. Contact our AI assistant to initiate a return.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">
        <i class="fas fa-question-circle"></i>
        How long does delivery take?
      </div>
      <div class="faq-answer">
        Standard delivery takes 3-5 business days. Express delivery is available for 1-2 days delivery in select areas.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">
        <i class="fas fa-question-circle"></i>
        What payment methods do you accept?
      </div>
      <div class="faq-answer">
        We accept Credit/Debit Cards, UPI, Net Banking, and Cash on Delivery (COD) for eligible orders.
      </div>
    </div>

    <div class="faq-item">
      <div class="faq-question">
        <i class="fas fa-question-circle"></i>
        How do I cancel my order?
      </div>
      <div class="faq-answer">
        Orders can be cancelled before they are shipped. Visit "My Orders" or ask our AI assistant to help you cancel your order.
      </div>
    </div>
  </div>
</div>

<!-- Use same AI chat script but with different container -->
<script>
  // Override the default chat panel to use support page elements
  window.AI_ENDPOINT = '<?= base_url('public/ai-chat-api.php') ?>';
  
  (function(){
    const box = document.getElementById('ai-messages');
    const input = document.getElementById('ai-input');
    const send = document.getElementById('ai-send');
    
    if (!box || !input || !send) return;
    
    let isTyping = false;
    let isFirstLoad = true;

    function addMsg(text, cls, action = null){
      const msgWrapper = document.createElement('div');
      msgWrapper.className = 'ai-msg-wrapper ' + cls + '-wrapper';
      msgWrapper.style.cssText = 'display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: flex-start;';
      
      const avatar = document.createElement('div');
      avatar.className = 'ai-avatar';
      avatar.style.cssText = 'width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.2rem;';
      
      if (cls === 'ai-bot') {
        avatar.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        avatar.style.color = 'white';
        avatar.innerHTML = '<i class="fas fa-robot"></i>';
      } else {
        avatar.style.background = 'var(--gray-200)';
        avatar.style.color = 'var(--gray-600)';
        avatar.innerHTML = '<i class="fas fa-user"></i>';
      }
      
      const div = document.createElement('div');
      div.className = 'ai-msg ' + cls;
      div.style.cssText = 'padding: 0.75rem 1.25rem; border-radius: var(--radius); max-width: 70%; line-height: 1.6;';
      
      if (cls === 'ai-bot') {
        div.style.background = 'white';
        div.style.color = 'var(--text-primary)';
        div.style.boxShadow = 'var(--shadow-sm)';
      } else {
        div.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
        div.style.color = 'white';
        msgWrapper.style.flexDirection = 'row-reverse';
      }
      
      // Convert newlines to breaks
      div.innerHTML = text.replace(/\n/g, '<br>');
      
      // Render Action Button if present
      if (action && cls === 'ai-bot') {
          const btn = document.createElement('a');
          btn.style.cssText = 'display: inline-block; margin-top: 10px; padding: 8px 16px; background: #4ade80; color: #064e3b; text-decoration: none; border-radius: 20px; font-weight: 600; font-size: 0.9rem; transition: transform 0.2s;';
          btn.onmouseover = () => btn.style.transform = 'scale(1.05)';
          btn.onmouseout = () => btn.style.transform = 'scale(1)';
          
          if (action.action === 'track_order') {
              btn.href = 'track-order.php?order_id=' + action.order_id;
              btn.innerHTML = '<i class="fas fa-shipping-fast"></i> Track Order #' + action.order_id;
          } else if (action.action === 'return_order') {
              btn.href = 'return-order.php?id=' + action.order_id;
              btn.innerHTML = '<i class="fas fa-undo"></i> Return Order #' + action.order_id;
          } else if (action.action === 'cancel_order') {
              // Create a form for cancellation to handle POST request safely
              const form = document.createElement('form');
              form.method = 'POST';
              form.action = 'cancel_order.php';
              form.style.display = 'inline';
              
              const inputId = document.createElement('input');
              inputId.type = 'hidden';
              inputId.name = 'id';
              inputId.value = action.order_id;
              
              const inputCsrf = document.createElement('input');
              inputCsrf.type = 'hidden';
              inputCsrf.name = 'csrf_token';
              inputCsrf.value = '<?= $_SESSION['csrf_token'] ?? '' ?>'; // Inject CSRF token
              
              const submitBtn = document.createElement('button');
              submitBtn.innerHTML = '<i class="fas fa-times-circle"></i> Cancel Order #' + action.order_id;
              submitBtn.style.cssText = 'margin-top: 10px; padding: 8px 16px; background: #ef4444; color: white; border: none; border-radius: 20px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: transform 0.2s;';
              submitBtn.onmouseover = () => submitBtn.style.transform = 'scale(1.05)';
              submitBtn.onmouseout = () => submitBtn.style.transform = 'scale(1)';
              
              form.appendChild(inputId);
              form.appendChild(inputCsrf);
              form.appendChild(submitBtn);
              
              div.appendChild(document.createElement('br'));
              div.appendChild(form);
              
              // Skip the default link appending
              btn.href = ''; 
          } else if (action.action === 'contact_support') {
              btn.href = 'mailto:support@theseventhcom.com';
              btn.innerHTML = '<i class="fas fa-envelope"></i> Email Support';
          }
          
          if (btn.href) div.appendChild(document.createElement('br'));
          if (btn.href) div.appendChild(btn);
      }
      
      msgWrapper.appendChild(avatar);
      msgWrapper.appendChild(div);
      box.appendChild(msgWrapper);
      box.scrollTop = box.scrollHeight;
    }

    function showTypingIndicator() {
      const indicator = document.createElement('div');
      indicator.id = 'typing-indicator';
      indicator.style.cssText = 'display: flex; gap: 0.75rem; margin-bottom: 1rem; align-items: center;';
      indicator.innerHTML = `
        <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
          <i class="fas fa-robot"></i>
        </div>
        <div style="padding: 0.75rem 1.25rem; background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm);">
          <div class="ai-typing-dots" style="display: flex; gap: 0.25rem;">
            <span style="width: 8px; height: 8px; background: var(--gray-400); border-radius: 50%; animation: typing 1.4s infinite;"></span>
            <span style="width: 8px; height: 8px; background: var(--gray-400); border-radius: 50%; animation: typing 1.4s infinite 0.2s;"></span>
            <span style="width: 8px; height: 8px; background: var(--gray-400); border-radius: 50%; animation: typing 1.4s infinite 0.4s;"></span>
          </div>
        </div>
      `;
      box.appendChild(indicator);
      box.scrollTop = box.scrollHeight;
    }

    function hideTypingIndicator() {
      const indicator = document.getElementById('typing-indicator');
      if (indicator) indicator.remove();
    }

    async function askAI(text){
      if (isTyping) return;
      
      addMsg(text, 'ai-user');
      input.value = '';
      send.disabled = true;
      isTyping = true;

      try {
        showTypingIndicator();
        
        const res = await fetch(window.AI_ENDPOINT, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ 
              message: text,
              support_mode: true // Enable Support Persona
          })
        });
        
        const data = await res.json();
        await new Promise(resolve => setTimeout(resolve, 500));
        
        hideTypingIndicator();
        addMsg(data.reply || 'Sorry, I could not get a reply. 😅', 'ai-bot', data.action);
        
      } catch (e) {
        hideTypingIndicator();
        addMsg('Network error. Please try again or contact our support team. 📞', 'ai-bot');
      } finally {
        send.disabled = false;
        isTyping = false;
        input.focus();
      }
    }

    function sendNow(){
      const t = input.value.trim();
      if(!t || isTyping) return;
      askAI(t);
    }

    input.addEventListener('keydown', (e)=>{ 
      if(e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendNow();
      }
    });
    
    send.addEventListener('click', sendNow);

    // Welcome message
    if (isFirstLoad) {
      isFirstLoad = false;
      setTimeout(() => {
        addMsg("👋 Hi there! Welcome to The Seventh Com Support 💬\n\nI can check your order status, help with returns, or answer any questions. How can I help?", 'ai-bot');
      }, 500);
    }
  })();
</script>

<style>
  @keyframes typing {
    0%, 60%, 100% { transform: translateY(0); }
    30% { transform: translateY(-10px); }
  }
</style>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
