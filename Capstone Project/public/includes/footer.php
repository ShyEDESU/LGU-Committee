<?php
// Handle path prefixing and root path determination
// Use variables already defined in header.php if they exist
if (!isset($footerPathPrefix)) {
    $currentDir = dirname($_SERVER['PHP_SELF']);
    if (strpos($currentDir, '/pages/') !== false) {
        $footerPathPrefix = '../../';
        $rootPath = '../../../';
    } elseif (strpos($currentDir, '/public') !== false) {
        $footerPathPrefix = '';
        $rootPath = '../';
    } else {
        $footerPathPrefix = 'public/';
        $rootPath = './';
    }
}

// Fetch system settings for branding
require_once __DIR__ . '/../../app/helpers/SystemSettingsHelper.php';
$settings = getSystemSettings();
$themeColor = $settings['theme_color'] ?? '#dc2626';
$systemLogo = $settings['lgu_logo_path'] ?? 'assets/images/logo.png';
?>

<!-- Redesigned System Footer -->
<footer class="bg-slate-950 text-slate-300 py-16 border-t border-slate-800 font-sans">
    <div class="max-w-[1600px] mx-auto px-4 md:px-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-16 mb-12">
            <!-- Branding & About -->
            <div class="space-y-6">
                <div class="flex items-center space-x-4">
                    <img src="<?php echo $footerPathPrefix . $systemLogo; ?>" alt="Logo"
                        class="w-16 h-16 object-contain">
                    <div>
                        <h4 class="text-white font-black text-xl tracking-tight uppercase">City of Valenzuela</h4>
                        <p class="text-[<?php echo $themeColor; ?>] text-xs font-bold uppercase tracking-widest"
                            style="color: <?php echo $themeColor; ?>;">Legislative Office</p>
                    </div>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                    A centralized platform for managing the legislative committees of the City Government of Valenzuela — tracking members, meetings, documents, reports, and ordinances in one system.
                </p>
            </div>

            <!-- Quick Links -->
            <div class="md:pl-12">
                <h4 class="text-white font-bold text-sm mb-8 uppercase tracking-[0.2em]">Quick Links</h4>
                <ul class="space-y-4 text-sm font-semibold">
                    <li><a href="<?php echo $rootPath; ?>public/dashboard.php" class="hover:text-white transition-colors">Dashboard</a></li>
                    <li><a href="<?php echo $rootPath; ?>public/pages/committee-profiles/index.php" class="hover:text-white transition-colors">Committees</a></li>
                    <li><a href="<?php echo $rootPath; ?>public/pages/committee-meetings/index.php" class="hover:text-white transition-colors">Meetings</a></li>
                    <li><a href="<?php echo $rootPath; ?>public/pages/committee-reports/index.php" class="hover:text-white transition-colors">Committee Reports</a></li>
                    <li><a href="<?php echo $rootPath; ?>public/pages/committee-profiles/index.php" class="hover:text-white transition-colors">Ordinances</a></li>
                </ul>
            </div>

            <!-- Contact Information -->
            <div>
                <h4 class="text-white font-bold text-sm mb-8 uppercase tracking-[0.2em]">Contact Information</h4>
                <ul class="space-y-6 text-sm font-semibold">
                    <li class="flex items-start space-x-4">
                        <i class="bi bi-geo-alt text-xl" style="color: <?php echo $themeColor; ?>;"></i>
                        <span class="text-slate-400">Valenzuela City Hall, MacArthur Highway,<br>Valenzuela City, Metro
                            Manila</span>
                    </li>
                    <li class="flex items-center space-x-4">
                        <i class="bi bi-telephone text-xl" style="color: <?php echo $themeColor; ?>;"></i>
                        <span class="text-slate-400">(02) 8352-1000</span>
                    </li>
                    <li class="flex items-center space-x-4">
                        <i class="bi bi-envelope text-xl" style="color: <?php echo $themeColor; ?>;"></i>
                        <span class="text-slate-400">legislative@valenzuela.gov.ph</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Copyright Bar -->
        <div class="pt-8 border-t border-slate-900 text-center">
            <p class="text-slate-600 text-[10px] font-bold uppercase tracking-[0.3em]">
                &copy; <?php echo date('Y'); ?> City Government of Valenzuela. All Rights Reserved.
            </p>
        </div>
    </div>
</footer>

<!-- 🏛️ LEGISLATIVE ASSISTANT AI HELPBOT WIDGET -->
<div id="helpbot-widget" class="fixed bottom-6 right-6 z-50 font-sans">
    <!-- Chat Button -->
    <button id="helpbot-toggle-btn" 
            class="w-14 h-14 bg-red-600 hover:bg-red-700 text-white rounded-full flex items-center justify-center shadow-2xl hover:scale-105 active:scale-95 transition-all duration-200 focus:outline-none relative group">
        <i class="bi bi-chat-dots-fill text-2xl group-hover:rotate-6 transition-transform"></i>
        <span class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 border-2 border-white rounded-full animate-pulse"></span>
    </button>

    <!-- Chat Box -->
    <div id="helpbot-chatbox" 
         class="flex flex-col absolute bottom-20 right-0 w-[380px] h-[500px] bg-white/95 dark:bg-gray-800/95 backdrop-blur-md rounded-2xl shadow-2xl border border-gray-200/80 dark:border-gray-700/80 overflow-hidden transition-all duration-300 transform scale-95 opacity-0 pointer-events-none origin-bottom-right">
        
        <!-- Header -->
        <div class="bg-red-600 text-white p-4 flex items-center justify-between shadow-md">
            <div class="flex items-center space-x-3">
                <div class="bg-white/20 p-2 rounded-xl">
                    <i class="bi bi-bank text-xl"></i>
                </div>
                <div>
                    <h4 class="font-bold text-sm leading-tight">Legislative Assistant</h4>
                    <p class="text-[10px] text-red-200 font-semibold flex items-center gap-1">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-ping"></span> Valenzuela LGU Bot
                    </p>
                </div>
            </div>
            <button id="helpbot-close-btn" class="text-white/80 hover:text-white hover:scale-110 transition-transform">
                <i class="bi bi-x-lg text-lg"></i>
            </button>
        </div>

        <!-- Messages Stream -->
        <div id="helpbot-messages" class="flex-1 p-4 overflow-y-auto space-y-4 scrollbar-thin scrollbar-thumb-gray-200">
            <!-- Welcome message -->
            <div class="flex items-start gap-2.5 max-w-[85%]">
                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400 flex-shrink-0">
                    <i class="bi bi-bank"></i>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200 p-3 rounded-2xl rounded-tl-none text-xs leading-relaxed shadow-sm">
                    Hello! I am your <strong>Valenzuela Legislative Assistant</strong>. 🏛️<br><br>
                    Ask me anything about:
                    <ul class="list-disc list-inside mt-2 space-y-1 font-semibold">
                        <li>Valenzuela Committees</li>
                        <li>Drafting & Signing Reports</li>
                        <li>Scheduling emergency meetings</li>
                        <li>Tracking task deadlines</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Suggestions Bar -->
        <div class="px-4 py-2 bg-gray-50 dark:bg-gray-700/20 border-t border-gray-100 dark:border-gray-800 flex gap-1.5 overflow-x-auto whitespace-nowrap scrollbar-none">
            <button onclick="sendSuggested('What committees are in Valenzuela?')" class="text-[10px] bg-white dark:bg-gray-800 hover:bg-gray-100 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 px-2.5 py-1 rounded-full font-semibold transition-all">List Committees</button>
            <button onclick="sendSuggested('How do I draft a report?')" class="text-[10px] bg-white dark:bg-gray-800 hover:bg-gray-100 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 px-2.5 py-1 rounded-full font-semibold transition-all">Drafting Reports</button>
            <button onclick="sendSuggested('How to request emergency meeting?')" class="text-[10px] bg-white dark:bg-gray-800 hover:bg-gray-100 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 px-2.5 py-1 rounded-full font-semibold transition-all">Emergency Meetings</button>
        </div>

        <!-- Input Box -->
        <div class="p-3 bg-white dark:bg-gray-800 border-t border-gray-200/60 dark:border-gray-700/60 flex items-center gap-2">
            <input type="text" id="helpbot-input" 
                   placeholder="Ask a question..." 
                   class="flex-1 px-4 py-2 text-xs border border-gray-200 dark:border-gray-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white">
            <button id="helpbot-send-btn" 
                    class="w-8 h-8 bg-red-600 hover:bg-red-700 text-white rounded-xl flex items-center justify-center shadow-md active:scale-95 transition-all">
                <i class="bi bi-send-fill text-sm"></i>
            </button>
        </div>
    </div>
</div>

<style>
    /* Hide scrollbars for suggestions */
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }

    /* Custom Scrollbar for Chat Stream */
    #helpbot-messages::-webkit-scrollbar {
        width: 6px;
    }
    #helpbot-messages::-webkit-scrollbar-track {
        background: transparent;
    }
    #helpbot-messages::-webkit-scrollbar-thumb {
        background: rgba(156, 163, 175, 0.5); /* gray-400 with opacity */
        border-radius: 9999px;
    }
    #helpbot-messages::-webkit-scrollbar-thumb:hover {
        background: rgba(156, 163, 175, 0.8);
    }
    /* Ensure messages area always has correct height for scrolling */
    #helpbot-chatbox {
        display: flex !important;
        flex-direction: column !important;
    }
    #helpbot-messages {
        flex: 1 1 0%;
        min-height: 0;          /* critical — allows flex children to shrink below content size */
        overflow-y: auto !important;
        scrollbar-width: thin;  /* Firefox */
        scrollbar-color: rgba(156, 163, 175, 0.5) transparent; /* Firefox */
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('helpbot-toggle-btn');
    const closeBtn = document.getElementById('helpbot-close-btn');
    const chatBox = document.getElementById('helpbot-chatbox');
    const input = document.getElementById('helpbot-input');
    const sendBtn = document.getElementById('helpbot-send-btn');
    const messagesStream = document.getElementById('helpbot-messages');

    // Simple path resolver for public/api/chat.php
    const endpoint = window.CMS_ROOT ? window.CMS_ROOT + 'public/api/chat.php' : '../../api/chat.php';

    let chatOpen = false;

    // Toggle Chatbox
    toggleBtn.addEventListener('click', () => {
        chatOpen ? closeChatbox() : openChatbox();
    });

    closeBtn.addEventListener('click', closeChatbox);

    function openChatbox() {
        chatOpen = true;
        chatBox.classList.remove('scale-95', 'opacity-0', 'pointer-events-none');
        chatBox.classList.add('scale-100', 'opacity-100');
        setTimeout(() => input.focus(), 300);
    }

    function closeChatbox() {
        chatOpen = false;
        chatBox.classList.remove('scale-100', 'opacity-100');
        chatBox.classList.add('scale-95', 'opacity-0', 'pointer-events-none');
    }

    // Handle Send Action
    sendBtn.addEventListener('click', submitMessage);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') submitMessage();
    });

    // Helper for suggestions
    window.sendSuggested = function(text) {
        input.value = text;
        submitMessage();
    };

    function submitMessage() {
        const query = input.value.trim();
        if (!query) return;

        // Clear input
        input.value = '';

        // 1. Append User Message
        appendMessage(query, 'user');

        // 2. Append Typing Indicator
        const typingId = appendTypingIndicator();

        // 3. Send to API
        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: query })
        })
        .then(res => res.json())
        .then(data => {
            removeTypingIndicator(typingId);
            appendMessage(data.response, 'assistant');
        })
        .catch(err => {
            console.error(err);
            removeTypingIndicator(typingId);
            appendMessage("Sorry, I'm having trouble connecting to my knowledge base right now. Please try again.", 'assistant');
        });
    }

    function appendMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = sender === 'user' 
            ? 'flex items-start gap-2.5 justify-end max-w-[85%] ml-auto' 
            : 'flex items-start gap-2.5 max-w-[85%] animate-fade-in';

        // Convert simple markdown elements like bolding and linebreaks
        const formattedText = text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>')
            .replace(/### (.*?)(<br>|$)/g, '<h5 class="font-bold text-sm text-red-600 dark:text-red-400 mt-1 mb-1">$1</h5>');

        if (sender === 'user') {
            messageDiv.innerHTML = `
                <div class="bg-red-600 text-white p-3 rounded-2xl rounded-tr-none text-xs leading-relaxed shadow-md">
                    ${formattedText}
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400 flex-shrink-0">
                    <i class="bi bi-bank"></i>
                </div>
                <div class="bg-gray-100 dark:bg-gray-700/50 text-gray-800 dark:text-gray-200 p-3 rounded-2xl rounded-tl-none text-xs leading-relaxed shadow-sm">
                    ${formattedText}
                </div>
            `;
        }

        messagesStream.appendChild(messageDiv);
        messagesStream.scrollTop = messagesStream.scrollHeight;
    }

    function appendTypingIndicator() {
        const id = 'typing-' + Date.now();
        const indicator = document.createElement('div');
        indicator.id = id;
        indicator.className = 'flex items-start gap-2.5 max-w-[85%] animate-pulse';
        indicator.innerHTML = `
            <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400 flex-shrink-0">
                <i class="bi bi-bank animate-bounce"></i>
            </div>
            <div class="bg-gray-100 dark:bg-gray-700/50 text-gray-500 p-3 rounded-2xl rounded-tl-none text-xs">
                Drafting response...
            </div>
        `;
        messagesStream.appendChild(indicator);
        messagesStream.scrollTop = messagesStream.scrollHeight;
        return id;
    }

    function removeTypingIndicator(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }
});
</script>