// Dashboard.js
const chatBox = document.getElementById('chatBox');
const chatForm = document.getElementById('chatForm');
const messageInput = document.getElementById('messageInput');


let currentConversationId = null;

document.getElementById('userIcon').addEventListener('click', () => {
  const panel = document.getElementById('userPanel');
  panel.style.display = (panel.style.display === 'block') ? 'none' : 'block';
});

window.addEventListener('click', function(e) {
  const icon = document.getElementById('userIcon');
  const panel = document.getElementById('userPanel');
  if (!icon.contains(e.target)) {
    panel.style.display = 'none';
  }
});





chatForm.addEventListener('submit', async (e) => {
  e.preventDefault();
  const message = messageInput.value.trim();
  if (!message) return;

  addMessage(message, 'user');
  messageInput.value = '';

  const res = await fetch('../chat/chat_handler.php', {
    method: 'POST',
    body: new URLSearchParams({ 
      message,
      conversation_id: currentConversationId
    })
  });

  const reply = await res.text();
  addMessage(reply, 'bot');
});

document.getElementById('new-convo-btn').addEventListener('click', function () {
  fetch('../chat/create_conversation.php', {
    method: 'POST'
  })
  .then(response => response.json())
  .then(data => {
    console.log(data); // debug
    if (data.conversation_id) {
      currentConversationId = data.conversation_id;
      document.getElementById('activeConversationId').value = data.conversation_id;
      loadUserConversations();
      loadConversationHistory(data.conversation_id);
    } else {
      alert('Erreur lors de la création de la conversation');
    }
  });
});

function addMessage(text, sender) {
  const msg = document.createElement("div");
  msg.className = sender === 'user' ? "message user" : "message bot";
  msg.innerHTML = `
      <div class="avatar ${sender}"></div>
      <div class="text">${text}</div>
  `;
  chatBox.appendChild(msg);
  chatBox.scrollTop = chatBox.scrollHeight;
}

messageInput.addEventListener("keypress", (e) => {
  if (e.key === "Enter") {
    chatForm.dispatchEvent(new Event('submit'));
  }
});

