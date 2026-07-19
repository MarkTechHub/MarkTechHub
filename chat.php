<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userEmail = $_SESSION['user_email'] ?? '';
$conn = db_connect();

$messages = [];
$stmt = $conn->prepare('SELECT sender, content, created_at FROM messages WHERE user_id = ? ORDER BY created_at ASC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$stmt->bind_result($sender, $content, $created_at);
while ($stmt->fetch()) {
    $messages[] = ['sender' => $sender, 'content' => $content, 'created_at' => $created_at];
}
$stmt->close();

$unreadCount = 0;
$stmt2 = $conn->prepare('SELECT COUNT(*) FROM messages WHERE user_id = ? AND sender = "admin" AND is_read_by_user = 0');
$stmt2->bind_param('i', $userId);
$stmt2->execute();
$stmt2->bind_result($unreadCount);
$stmt2->fetch();
$stmt2->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Chat | MarkTechHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .chat-page { min-height: 100vh; padding: 24px 0 32px; background: linear-gradient(180deg, #061228 0%, #020712 100%); display: flex; align-items: center; justify-content: center; }
        .chat-app { width: 100%; max-width: 980px; padding: 0 16px; }
        .chat-panel { display: grid; gap: 0; background: rgba(7, 19, 35, 0.95); border: 1px solid rgba(255,255,255,0.08); border-radius: 32px; box-shadow: 0 38px 90px rgba(0,0,0,0.35); overflow: hidden; min-height: calc(100vh - 64px); }
        .chat-header { display: flex; align-items: center; justify-content: space-between; padding: 24px 28px; gap: 18px; background: rgba(7,18,33,0.96); border-bottom: 1px solid rgba(255,255,255,0.08); }
        .chat-title { display: flex; align-items: center; gap: 14px; }
        .chat-avatar { width: 56px; height: 56px; border-radius: 50%; background: #ff7e1b; color: #0f172a; display: grid; place-items: center; font-weight: 800; font-size: 1.2rem; box-shadow: 0 15px 30px rgba(255,126,27,0.25); }
        .chat-header h1 { margin: 0; font-size: clamp(1.9rem, 3vw, 2.6rem); }
        .chat-header p { margin: 4px 0 0; color: rgba(226,232,240,0.75); font-size: 0.98rem; }
        .chat-notification { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,126,27,0.16); padding: 10px 16px; border-radius: 999px; color: #fffbeb; font-weight: 700; }
        .chat-box { display: flex; flex-direction: column; flex: 1; background: rgba(7,15,29,0.98); }
        .chat-history { display: flex; flex-direction: column; gap: 16px; flex: 1; overflow-y: auto; padding: 24px; background: linear-gradient(180deg, rgba(6,12,24,0.8), rgba(7,14,25,0.98)); }
        .message { padding: 14px 18px; border-radius: 22px; line-height: 1.6; max-width: 72%; word-break: break-word; position: relative; box-shadow: 0 10px 24px rgba(0,0,0,0.18); }
        .message .sender { display: block; margin-bottom: 6px; font-size: 0.88rem; font-weight: 700; color: rgba(255,255,255,0.82); }
        .message.user { background: #ff7e1b; color: #0f172a; align-self: flex-end; border-bottom-right-radius: 10px; border-bottom-left-radius: 22px; border-top-left-radius: 22px; border-top-right-radius: 22px; margin-left: auto; }
        .message.admin { background: rgba(255,255,255,0.08); color: #e5e7eb; align-self: flex-start; border-bottom-left-radius: 10px; border-bottom-right-radius: 22px; border-top-right-radius: 22px; border-top-left-radius: 22px; margin-right: auto; }
        .message.admin::before { content: ''; position: absolute; left: -8px; bottom: 10px; width: 0; height: 0; border-top: 8px solid transparent; border-right: 8px solid rgba(255,255,255,0.08); border-bottom: 8px solid transparent; }
        .message.user::after { content: ''; position: absolute; right: -8px; bottom: 10px; width: 0; height: 0; border-top: 8px solid transparent; border-left: 8px solid #ff7e1b; border-bottom: 8px solid transparent; }
        .chat-input-row { display: flex; flex-wrap: wrap; gap: 12px; padding: 18px 24px 24px; background: rgba(7,12,24,0.9); border-top: 1px solid rgba(255,255,255,0.08); }
        .chat-input-row input { flex: 1; min-height: 54px; padding: 16px 18px; border-radius: 28px; border: 1px solid rgba(255,255,255,0.16); background: rgba(255,255,255,0.08); color: #0f172a; }
        .chat-input-row button { min-height: 54px; padding: 16px 22px; border-radius: 28px; background: #ff7e1b; border: none; color: #0f172a; font-weight: 700; transition: transform 150ms ease; display: inline-flex; align-items: center; justify-content: center; gap: 10px; }
        .chat-input-row button:hover { transform: translateY(-1px); }
        .chat-input-row button i { font-size: 1rem; }
        .chat-footer { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-top: 4px; }
        .chat-footer a { color: #f8fafc; text-decoration: none; }
        @media (max-width: 760px) { .chat-input-row { flex-wrap: nowrap; } }
        @media (max-width: 640px) { .chat-header { flex-direction: column; align-items: flex-start; } .chat-history { padding: 18px; } .message { max-width: 100%; } .chat-input-row { padding: 16px 18px 20px; gap: 10px; } }
    </style>
</head>
<body>
    <header>
        <div class="logo"><img src="image2/MY LOGO.png" alt="MarkTechHub logo" class="logo"></div>
        <div class="menu-toggle" onclick="toggleMenu()">&#9776;</div>
        <nav>
            <ul id="nav-links">
                <li><a href="index.html" data-key="home">home</a></li>
                <li><a href="about.html" data-key="about">about us</a></li>
                <li><a href="port.html" data-key="portfolio">Portfolio</a></li>
                <li><a href="chat.php"><i class="fas fa-comments"></i> Chat</a></li>
                <li><a href="contact.html" data-key="contact">Contact</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="chat-page">
        <div class="chat-app">
            <div class="chat-panel">
                <div class="chat-header">
                    <div class="chat-title">
                        <div class="chat-avatar">MH</div>
                        <div>
                            <div class="chat-icon"><i class="fas fa-comments"></i></div>
                            <h1>Chat yako</h1>
                            <p>Ujumbe wako unahifadhiwa kwa akaunti yako pekee.</p>
                        </div>
                    </div>
                    <div class="chat-notification"><i class="fas fa-bell"></i> Ujumbe mpya: <span id="notificationCount"><?= (int)$unreadCount ?></span></div>
                </div>

                <div class="chat-box">
                    <div id="chatHistory" class="chat-history">
                        <?php foreach ($messages as $msg): ?>
                            <div class="message <?= $msg['sender'] === 'admin' ? 'admin' : 'user' ?>">
                                <span class="sender"><?= $msg['sender'] === 'admin' ? 'Mteja' : 'Mimi' ?></span>
                                <?= htmlspecialchars($msg['content']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="chat-input-row">
                        <input id="chatInput" type="text" placeholder="Andika ujumbe wako hapa..." aria-label="Chat message">
                        <button id="sendChatBtn"><i class="fas fa-paper-plane"></i> Tuma</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleMenu() {
            document.getElementById('nav-links').classList.toggle('show');
        }

        document.addEventListener('click', function (e) {
            const nav = document.getElementById('nav-links');
            const toggle = document.querySelector('.menu-toggle');
            if (!toggle.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('show');
            }
        });

        const chatHistory = document.getElementById('chatHistory');
        const chatInput = document.getElementById('chatInput');
        const sendChatBtn = document.getElementById('sendChatBtn');

        function appendMessage(text, sender, label = '') {
            const message = document.createElement('div');
            message.className = `message ${sender}`;
            if (label) {
                const senderLabel = document.createElement('span');
                senderLabel.className = 'sender';
                senderLabel.textContent = label;
                message.appendChild(senderLabel);
            }
            const messageText = document.createElement('span');
            messageText.textContent = text;
            message.appendChild(messageText);
            chatHistory.appendChild(message);
            chatHistory.scrollTop = chatHistory.scrollHeight;
        }

        async function sendChat() {
            const text = chatInput.value.trim();
            if (!text) return;
            appendMessage(text, 'user', 'Mimi');
            chatInput.value = '';

            try {
                const res = await fetch('send_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ content: text })
                });

                const data = await res.json();
                if (data.success) {
                    appendMessage('Ujumbe umehifadhiwa. Tutakuambia hivi hivi.', 'admin', 'Mteja');
                } else {
                    appendMessage('Samahani, tatizo limetokea. Jaribu tena.', 'admin', 'Mteja');
                }
            } catch (error) {
                appendMessage('Samahani, tatizo limetokea. Jaribu tena.', 'admin', 'Mteja');
            }
        }

        sendChatBtn.addEventListener('click', sendChat);
        chatInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') sendChat(); });

        async function pollChat() {
            try {
                const res = await fetch('chat_poll.php');
                if (!res.ok) return;
                const data = await res.json();
                if (!data.success) return;

                const notificationCount = document.getElementById('notificationCount');
                if (notificationCount) {
                    notificationCount.textContent = data.unreadCount;
                }

                const currentMessages = Array.from(chatHistory.querySelectorAll('.message')).map(el => el.textContent.trim());
                const newMessages = data.messages.map(msg => (msg.sender === 'admin' ? 'Mteja ' : 'Mimi ') + msg.content);

                if (newMessages.length !== currentMessages.length || newMessages.some((msg, idx) => msg !== currentMessages[idx])) {
                    chatHistory.innerHTML = '';
                    data.messages.forEach(msg => {
                        appendMessage(msg.content, msg.sender === 'admin' ? 'admin' : 'user', msg.sender === 'admin' ? 'Mteja' : 'Mimi');
                    });
                }
            } catch (error) {
                console.error('Poll failed', error);
            }
        }

        setInterval(pollChat, 8000);
    </script>
</body>
</html>
