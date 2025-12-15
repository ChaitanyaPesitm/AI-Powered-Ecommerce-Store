// The Seventh Com AI Copilot - Enhanced Chat System
(function(){
  const launch = document.getElementById('ai-chat-launcher');
  const panel  = document.getElementById('ai-chat-panel');
  const close  = document.getElementById('ai-close');
  const box    = document.getElementById('ai-messages');
  const input  = document.getElementById('ai-input');
  const send   = document.getElementById('ai-send');

  if(!launch || !panel) return;

  let isFirstOpen = true;
  let isTyping = false;

  const show = () => { 
    panel.classList.remove('ai-hidden'); 
    setTimeout(()=>input.focus(), 100); 
    
    // Show welcome message on first open
    if (isFirstOpen && box.children.length === 0) {
      isFirstOpen = false;
      setTimeout(() => {
        addBotMsgWithTyping("👋 Hi there! Welcome to The Seventh Com Support 💬\n\nI can help you track, cancel, or refund your order — what would you like to do today?");
      }, 500);
    }
  };
  
  const hide = () => panel.classList.add('ai-hidden');

  launch.addEventListener('click', (e)=>{ e.preventDefault(); show(); });
  close.addEventListener('click', (e)=>{ e.preventDefault(); hide(); });

  // Also open when any element with class "ask-suggest" is clicked
  document.addEventListener('click', (e)=>{
    const a = e.target.closest('.ask-suggest');
    if (a) { e.preventDefault(); show(); }
  });

  // Add message with avatar
  function addMsg(text, cls, showAvatar = true){
    const msgWrapper = document.createElement('div');
    msgWrapper.className = 'ai-msg-wrapper ' + cls + '-wrapper';
    
    if (showAvatar) {
      const avatar = document.createElement('div');
      avatar.className = 'ai-avatar';
      if (cls === 'ai-bot') {
        avatar.innerHTML = '<i class="fas fa-robot"></i>';
      } else {
        avatar.innerHTML = '<i class="fas fa-user"></i>';
      }
      msgWrapper.appendChild(avatar);
    }
    
    const div = document.createElement('div');
    div.className = 'ai-msg ' + cls;
    div.textContent = text;
    msgWrapper.appendChild(div);
    
    box.appendChild(msgWrapper);
    box.scrollTop = box.scrollHeight;
    
    return div;
  }

  // Typing indicator
  function showTypingIndicator() {
    const indicator = document.createElement('div');
    indicator.className = 'ai-typing-indicator';
    indicator.id = 'typing-indicator';
    indicator.innerHTML = `
      <div class="ai-avatar"><i class="fas fa-robot"></i></div>
      <div class="ai-typing-dots">
        <span></span><span></span><span></span>
      </div>
    `;
    box.appendChild(indicator);
    box.scrollTop = box.scrollHeight;
  }

  function hideTypingIndicator() {
    const indicator = document.getElementById('typing-indicator');
    if (indicator) indicator.remove();
  }

  // Typing animation for bot messages
  async function addBotMsgWithTyping(text) {
    showTypingIndicator();
    
    // Simulate realistic typing delay
    await new Promise(resolve => setTimeout(resolve, 800 + Math.random() * 400));
    
    hideTypingIndicator();
    addMsg(text, 'ai-bot');
  }

  // Main AI request function
  async function askAI(text){
    if (isTyping) return;
    
    addMsg(text, 'ai-user');
    input.value = '';
    send.disabled = true;
    isTyping = true;

    try {
      const endpoint = window.AI_ENDPOINT || panel.dataset.endpoint || '/ecommerce/public/ai-chat-api.php';
      
      showTypingIndicator();
      
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text })
      });
      
      const data = await res.json();
      
      // Add small delay for natural feel
      await new Promise(resolve => setTimeout(resolve, 500));
      
      hideTypingIndicator();
      addMsg(data.reply || 'Sorry, I could not get a reply. 😅', 'ai-bot');
      
    } catch (e) {
      hideTypingIndicator();
      addMsg('Network error. Please try again or contact our support team. 📞', 'ai-bot');
      console.error('AI Chat Error:', e);
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

  // Quick action buttons
  function addQuickActions() {
    const actions = [
      { text: '📦 Track Order', message: 'I want to track my order' },
      { text: '💸 Request Refund', message: 'I need help with a refund' },
      { text: '❌ Cancel Order', message: 'I want to cancel my order' },
      { text: '🛍️ Product Info', message: 'I have a question about a product' }
    ];
    
    const quickActionsDiv = document.createElement('div');
    quickActionsDiv.className = 'ai-quick-actions';
    quickActionsDiv.innerHTML = '<p class="ai-quick-label">Quick Actions:</p>';
    
    actions.forEach(action => {
      const btn = document.createElement('button');
      btn.className = 'ai-quick-btn';
      btn.textContent = action.text;
      btn.onclick = () => {
        input.value = action.message;
        sendNow();
      };
      quickActionsDiv.appendChild(btn);
    });
    
    box.appendChild(quickActionsDiv);
  }

  // Event listeners
  input.addEventListener('keydown', (e)=>{ 
    if(e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendNow();
    }
  });
  
  send.addEventListener('click', (e)=>{ e.preventDefault(); sendNow(); });

  // Add quick actions on load if chat is empty
  if (box.children.length === 0) {
    setTimeout(addQuickActions, 100);
  }
})();
