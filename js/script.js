// Cyberpunk 2077 Themed Streaming Site JavaScript

// DOM Elements
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');
const blogPostsContainer = document.getElementById('blog-posts');

// Mobile Navigation
if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navMenu.classList.toggle('active');
    });

    // Close mobile menu when clicking on a link
    document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
        hamburger.classList.remove('active');
        navMenu.classList.remove('active');
    }));
}

// Smooth Scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Twitch Chat API Integration
class TwitchChatAPI {
    constructor() {
        this.socket = null;
        this.isConnected = false;
        this.channelName = 'lutficyber'; // Twitch kullanıcı adınız
        this.clientId = 'YOUR_TWITCH_CLIENT_ID'; // Twitch Client ID
        this.accessToken = 'YOUR_TWITCH_ACCESS_TOKEN'; // OAuth Token
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 3;
        this.chatMessages = [];
        this.maxMessages = 50;
        this.chatApiUrl = 'https://api.twitch.tv/helix/chat/settings';
        this.chatMessagesUrl = 'https://api.twitch.tv/helix/chat/messages';
    }

    // WebSocket bağlantısı ile Twitch IRC'ye bağlan
    // Get chat settings using Twitch Helix API
    async getChatSettings() {
        try {
            const response = await fetch(`${this.chatApiUrl}?broadcaster_id=${await this.getUserId()}`, {
                headers: {
                    'Client-ID': this.clientId,
                    'Authorization': `Bearer ${this.accessToken}`,
                    'Content-Type': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`Chat API Error: ${response.status}`);
            }

            const data = await response.json();
            return data.data[0] || null;
        } catch (error) {
            console.error('Chat Settings API Error:', error);
            return null;
        }
    }

    // Get user ID for API calls
    async getUserId() {
        try {
            const response = await fetch(`https://api.twitch.tv/helix/users?login=${this.channelName}`, {
                headers: {
                    'Client-ID': this.clientId,
                    'Authorization': `Bearer ${this.accessToken}`
                }
            });

            if (!response.ok) {
                throw new Error(`User API Error: ${response.status}`);
            }

            const data = await response.json();
            return data.data[0]?.id || null;
        } catch (error) {
            console.error('User ID API Error:', error);
            return null;
        }
    }

    // Send chat message using Helix API
    async sendChatMessageAPI(message) {
        try {
            const userId = await this.getUserId();
            if (!userId) {
                throw new Error('User ID bulunamadı');
            }

            const response = await fetch(this.chatMessagesUrl, {
                method: 'POST',
                headers: {
                    'Client-ID': this.clientId,
                    'Authorization': `Bearer ${this.accessToken}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    broadcaster_id: userId,
                    sender_id: userId,
                    message: message
                })
            });

            if (!response.ok) {
                throw new Error(`Message API Error: ${response.status}`);
            }

            return true;
        } catch (error) {
            console.error('Send Message API Error:', error);
            throw error;
        }
    }

    connectToChat() {
        try {
            this.socket = new WebSocket('wss://irc-ws.chat.twitch.tv:443');
            
            this.socket.onopen = () => {
                console.log('Twitch Chat bağlantısı kuruldu');
                this.authenticate();
            };

            this.socket.onmessage = (event) => {
                this.handleMessage(event.data);
            };

            this.socket.onclose = () => {
                console.log('Twitch Chat bağlantısı kapandı');
                this.isConnected = false;
                this.attemptReconnect();
            };

            this.socket.onerror = (error) => {
                console.error('Twitch Chat hatası:', error);
            };

        } catch (error) {
            console.error('WebSocket bağlantı hatası:', error);
            this.showChatError();
        }
    }

    authenticate() {
        // Twitch IRC kimlik doğrulama
        this.socket.send('CAP REQ :twitch.tv/membership twitch.tv/tags twitch.tv/commands');
        this.socket.send(`PASS oauth:${this.accessToken}`);
        this.socket.send(`NICK ${this.channelName}`);
        this.socket.send(`JOIN #${this.channelName}`);
        this.isConnected = true;
        this.reconnectAttempts = 0;
    }

    handleMessage(data) {
        const lines = data.split('\r\n');
        
        lines.forEach(line => {
            if (line.includes('PRIVMSG')) {
                this.parsePrivateMessage(line);
            } else if (line.startsWith('PING')) {
                // PONG yanıtı gönder
                this.socket.send('PONG :tmi.twitch.tv');
            }
        });
    }

    parsePrivateMessage(line) {
        try {
            // Twitch IRC mesaj formatını parse et
            const tagsPart = line.split(' :')[0];
            const messagePart = line.split(' :').slice(2).join(' :');
            const username = line.split('!')[0].split(':')[1];
            
            // Kullanıcı rengini tags'den çıkar
            const colorMatch = tagsPart.match(/color=([^;]*)/);
            const userColor = colorMatch ? colorMatch[1] : '#00FFFF';
            
            // Subscriber, moderator gibi badge'leri çıkar
            const badgesMatch = tagsPart.match(/badges=([^;]*)/);
            const badges = badgesMatch ? badgesMatch[1].split(',') : [];
            
            const chatMessage = {
                id: Date.now() + Math.random(),
                username: username,
                message: messagePart,
                color: userColor || '#00FFFF',
                badges: badges,
                timestamp: new Date()
            };

            this.addChatMessage(chatMessage);
        } catch (error) {
            console.error('Mesaj parse hatası:', error);
        }
    }

    addChatMessage(message) {
        this.chatMessages.push(message);
        
        // Maksimum mesaj sayısını kontrol et
        if (this.chatMessages.length > this.maxMessages) {
            this.chatMessages.shift();
        }
        
        this.renderChatMessage(message);
    }

    renderChatMessage(message) {
        const chatContainer = document.getElementById('twitch-chat-messages');
        if (!chatContainer) return;

        const messageElement = document.createElement('div');
        messageElement.className = 'chat-message';
        messageElement.innerHTML = `
            <div class="chat-message-content">
                <span class="chat-username" style="color: ${message.color}">
                    ${this.renderBadges(message.badges)}${message.username}:
                </span>
                <span class="chat-text">${this.escapeHtml(message.message)}</span>
            </div>
            <div class="chat-timestamp">${this.formatTime(message.timestamp)}</div>
        `;

        chatContainer.appendChild(messageElement);
        
        // Otomatik scroll
        chatContainer.scrollTop = chatContainer.scrollHeight;
        
        // Eski mesajları temizle
        while (chatContainer.children.length > this.maxMessages) {
            chatContainer.removeChild(chatContainer.firstChild);
        }
    }

    renderBadges(badges) {
        if (!badges || badges.length === 0) return '';
        
        let badgeHtml = '';
        badges.forEach(badge => {
            if (badge.includes('moderator')) {
                badgeHtml += '<span class="chat-badge mod">MOD</span>';
            } else if (badge.includes('subscriber')) {
                badgeHtml += '<span class="chat-badge sub">SUB</span>';
            } else if (badge.includes('vip')) {
                badgeHtml += '<span class="chat-badge vip">VIP</span>';
            }
        });
        return badgeHtml;
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatTime(date) {
        return date.toLocaleTimeString('tr-TR', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }

    attemptReconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Yeniden bağlanma denemesi ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);
            setTimeout(() => {
                this.connectToChat();
            }, 5000 * this.reconnectAttempts);
        } else {
            this.showChatError();
        }
    }

    showChatError() {
        const chatContainer = document.getElementById('twitch-chat-messages');
        if (chatContainer) {
            chatContainer.innerHTML = `
                <div class="chat-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Chat bağlantısı kurulamadı</p>
                    <button onclick="twitchChat.connectToChat()" class="cyber-btn-sm">
                        Tekrar Dene
                    </button>
                </div>
            `;
        }
    }

    disconnect() {
        if (this.socket) {
            this.socket.close();
            this.isConnected = false;
        }
    }
}

// Twitch API Integration
class TwitchAPI {
    constructor() {
        this.clientId = 'YOUR_TWITCH_CLIENT_ID'; // Replace with your Twitch Client ID
        this.channelName = 'lutficyber'; // Twitch kullanıcı adınız
        this.accessToken = 'YOUR_TWITCH_ACCESS_TOKEN'; // Replace with your access token
    }

    async getStreamInfo() {
        try {
            const response = await fetch(`https://api.twitch.tv/helix/streams?user_login=${this.channelName}`, {
                headers: {
                    'Client-ID': this.clientId,
                    'Authorization': `Bearer ${this.accessToken}`
                }
            });
            
            if (!response.ok) {
                throw new Error('Twitch API request failed');
            }
            
            const data = await response.json();
            return data.data[0] || null;
        } catch (error) {
            console.error('Twitch API Error:', error);
            return null;
        }
    }

    async updateStreamStatus() {
        const streamInfo = await this.getStreamInfo();
        const statusElement = document.getElementById('twitch-status');
        const viewersElement = document.getElementById('twitch-viewers');
        const titleElement = document.getElementById('twitch-title');
        const statusDot = document.querySelector('.twitch-panel .status-dot');

        if (streamInfo) {
            // Stream is live
            if (statusElement) statusElement.textContent = 'CANLI';
            if (viewersElement) viewersElement.textContent = streamInfo.viewer_count.toLocaleString();
            if (titleElement) titleElement.textContent = streamInfo.title;
            if (statusDot) {
                statusDot.classList.add('live');
                statusDot.style.background = 'var(--neon-green)';
            }
        } else {
            // Stream is offline
            if (statusElement) statusElement.textContent = 'ÇEVRİMDIŞI';
            if (viewersElement) viewersElement.textContent = '0';
            if (titleElement) titleElement.textContent = 'Yayın şu anda çevrimdışı';
            if (statusDot) {
                statusDot.classList.remove('live');
                statusDot.style.background = 'var(--text-muted)';
            }
        }
    }
}

// Kick API Integration (Note: Kick doesn't have a public API yet)
class KickAPI {
    constructor() {
        this.channelName = 'YOUR_KICK_USERNAME'; // Replace with your Kick username
    }

    // Simulated Kick API - Replace with actual API when available
    async getStreamInfo() {
        // This is a placeholder since Kick doesn't have a public API yet
        // You might need to use web scraping or wait for official API
        return {
            isLive: Math.random() > 0.5, // Random for demo
            viewers: Math.floor(Math.random() * 1000),
            title: 'Cyberpunk 2077 Gameplay - Night City Adventures'
        };
    }

    async updateStreamStatus() {
        const streamInfo = await this.getStreamInfo();
        const statusElement = document.getElementById('kick-status');
        const viewersElement = document.getElementById('kick-viewers');
        const titleElement = document.getElementById('kick-title');
        const statusDot = document.querySelector('.kick-panel .status-dot');

        if (streamInfo.isLive) {
            // Stream is live
            if (statusElement) statusElement.textContent = 'CANLI';
            if (viewersElement) viewersElement.textContent = streamInfo.viewers.toLocaleString();
            if (titleElement) titleElement.textContent = streamInfo.title;
            if (statusDot) {
                statusDot.classList.add('live');
                statusDot.style.background = 'var(--neon-green)';
            }
        } else {
            // Stream is offline
            if (statusElement) statusElement.textContent = 'ÇEVRİMDIŞI';
            if (viewersElement) viewersElement.textContent = '0';
            if (titleElement) titleElement.textContent = 'Yayın şu anda çevrimdışı';
            if (statusDot) {
                statusDot.classList.remove('live');
                statusDot.style.background = 'var(--text-muted)';
            }
        }
    }
}

// Blog Posts Management
class BlogManager {
    constructor() {
        this.posts = [];
    }

    async loadBlogPosts() {
        try {
            const response = await fetch('api/get_posts.php');
            if (response.ok) {
                this.posts = await response.json();
                this.renderBlogPosts();
            } else {
                // Fallback to demo posts if API is not available
                this.loadDemoPosts();
            }
        } catch (error) {
            console.error('Blog API Error:', error);
            this.loadDemoPosts();
        }
    }

    loadDemoPosts() {
        this.posts = [
            {
                id: 1,
                title: 'Cyberpunk 2077: Phantom Liberty İncelemesi',
                excerpt: 'CD Projekt RED\'in uzun zamandır beklenen genişleme paketi sonunda burada. Phantom Liberty ile Night City\'de yeni maceralar...',
                category: 'Oyun',
                date: '2024-01-15',
                image: null
            },
            {
                id: 2,
                title: 'Blade Runner 2049: Cyberpunk Sinemasının Zirvesi',
                excerpt: 'Denis Villeneuve\'in yönettiği bu bilim kurgu başyapıtı, orijinal Blade Runner\'ın mirasını nasıl sürdürüyor?',
                category: 'Film',
                date: '2024-01-10',
                image: null
            },
            {
                id: 3,
                title: 'Neuromancer: Cyberpunk Edebiyatının Babası',
                excerpt: 'William Gibson\'ın 1984 tarihli romanı cyberpunk türünün temellerini nasıl attı ve günümüze nasıl etki ediyor?',
                category: 'Kitap',
                date: '2024-01-05',
                image: null
            }
        ];
        this.renderBlogPosts();
    }

    renderBlogPosts() {
        if (!blogPostsContainer) return;

        blogPostsContainer.innerHTML = '';
        
        this.posts.slice(0, 3).forEach(post => {
            const postElement = this.createPostElement(post);
            blogPostsContainer.appendChild(postElement);
        });
    }

    createPostElement(post) {
        const postDiv = document.createElement('div');
        postDiv.className = 'blog-post';
        
        const categoryColors = {
            'Oyun': 'var(--neon-cyan)',
            'Film': 'var(--neon-pink)',
            'Kitap': 'var(--neon-yellow)',
            'Dizi': 'var(--neon-purple)'
        };

        postDiv.innerHTML = `
            <div class="blog-post-image">
                <i class="fas fa-gamepad"></i>
            </div>
            <div class="blog-post-content">
                <span class="blog-post-category" style="background: ${categoryColors[post.category] || 'var(--neon-cyan)'}">
                    ${post.category}
                </span>
                <h3 class="blog-post-title">${post.title}</h3>
                <p class="blog-post-excerpt">${post.excerpt}</p>
                <div class="blog-post-meta">
                    <span class="post-date">${this.formatDate(post.date)}</span>
                    <a href="post.php?id=${post.id}" class="read-more">Devamını Oku</a>
                </div>
            </div>
        `;

        return postDiv;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('tr-TR', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }
}

// Cyber Effects
class CyberEffects {
    constructor() {
        this.initGlitchEffect();
        this.initParticles();
    }

    initGlitchEffect() {
        const glitchElements = document.querySelectorAll('.neon-text');
        
        glitchElements.forEach(element => {
            element.addEventListener('mouseenter', () => {
                element.classList.add('glitch');
                element.setAttribute('data-text', element.textContent);
                
                setTimeout(() => {
                    element.classList.remove('glitch');
                }, 500);
            });
        });
    }

    initParticles() {
        // Create floating particles effect
        const particleContainer = document.createElement('div');
        particleContainer.className = 'particles';
        particleContainer.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        `;
        
        document.body.appendChild(particleContainer);
        
        for (let i = 0; i < 50; i++) {
            this.createParticle(particleContainer);
        }
    }

    createParticle(container) {
        const particle = document.createElement('div');
        particle.style.cssText = `
            position: absolute;
            width: 2px;
            height: 2px;
            background: var(--neon-cyan);
            border-radius: 50%;
            opacity: 0.7;
            animation: float ${Math.random() * 10 + 5}s linear infinite;
            left: ${Math.random() * 100}%;
            top: ${Math.random() * 100}%;
            box-shadow: 0 0 6px var(--neon-cyan);
        `;
        
        container.appendChild(particle);
        
        // Remove particle after animation
        setTimeout(() => {
            if (particle.parentNode) {
                particle.parentNode.removeChild(particle);
            }
        }, 15000);
    }
}

// Notification System
class NotificationSystem {
    constructor() {
        this.createNotificationContainer();
    }

    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 10000;
            max-width: 300px;
        `;
        document.body.appendChild(container);
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const colors = {
            info: 'var(--neon-cyan)',
            success: 'var(--neon-green)',
            warning: 'var(--neon-yellow)',
            error: 'var(--neon-pink)'
        };
        
        notification.style.cssText = `
            background: var(--accent-bg);
            border: 2px solid ${colors[type]};
            border-radius: 5px;
            padding: 1rem;
            margin-bottom: 10px;
            color: var(--text-primary);
            box-shadow: 0 0 15px ${colors[type]};
            animation: slideIn 0.3s ease-out;
            position: relative;
            overflow: hidden;
        `;
        
        notification.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-info-circle" style="color: ${colors[type]};"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.parentElement.remove()" style="
                    background: none;
                    border: none;
                    color: var(--text-secondary);
                    cursor: pointer;
                    margin-left: auto;
                    font-size: 1.2rem;
                ">×</button>
            </div>
        `;
        
        document.getElementById('notification-container').appendChild(notification);
        
        // Auto remove after duration
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }
        }, duration);
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize APIs
    const twitchAPI = new TwitchAPI();
    const kickAPI = new KickAPI();
    const blogManager = new BlogManager();
    const cyberEffects = new CyberEffects();
    const notifications = new NotificationSystem();
    
    // Initialize Twitch Chat
    window.twitchChat = new TwitchChatAPI();
    window.notifications = notifications;
    
    // Load initial data
    blogManager.loadBlogPosts();
    
    // Update stream status
    twitchAPI.updateStreamStatus();
    kickAPI.updateStreamStatus();
    
    // Update stream status every 30 seconds
    setInterval(() => {
        twitchAPI.updateStreamStatus();
        kickAPI.updateStreamStatus();
    }, 30000);
    
    // Initialize stats counter animation
    initStatsAnimation();
    
    // Initialize scroll animations
    initScrollAnimations();
});

// Twitch Chat Functions
function toggleTwitchChat() {
    const connectBtn = document.getElementById('chat-connect-btn');
    const chatInput = document.getElementById('chat-input');
    const sendBtn = document.getElementById('chat-send-btn');
    
    if (!window.twitchChat.isConnected) {
        // Connect to chat
        connectBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Bağlanıyor...';
        connectBtn.disabled = true;
        
        // Önce chat ayarlarını kontrol et
        window.twitchChat.getChatSettings()
            .then(settings => {
                if (settings) {
                    console.log('Chat ayarları:', settings);
                    window.notifications.show('Chat ayarları alındı', 'info');
                }
                
                // WebSocket bağlantısını başlat
                return window.twitchChat.connectToChat();
            })
            .then(() => {
                // Bağlantı başarılı
                setTimeout(() => {
                    connectBtn.innerHTML = '<i class="fas fa-unlink"></i> Bağlantıyı Kes';
                    connectBtn.disabled = false;
                    chatInput.disabled = false;
                    sendBtn.disabled = false;
                    
                    // Clear welcome message
                    const chatMessages = document.getElementById('twitch-chat-messages');
                    chatMessages.innerHTML = '<div class="chat-status">Chat\'e bağlandı! Mesajlar yükleniyor...</div>';
                    
                    window.notifications.show('Twitch Chat\'e başarıyla bağlandı!', 'success');
                }, 2000);
            })
            .catch(error => {
                connectBtn.innerHTML = '<i class="fas fa-plug"></i> Bağlan';
                connectBtn.disabled = false;
                
                // API hatası varsa kullanıcıya bildir
                if (error.message.includes('401')) {
                    window.notifications.show('OAuth token gerekli. Lütfen API anahtarlarınızı kontrol edin.', 'error');
                } else {
                    window.notifications.show('Chat bağlantısı başarısız: ' + error.message, 'error');
                }
            });
            
    } else {
        // Disconnect from chat
        window.twitchChat.disconnect();
        connectBtn.innerHTML = '<i class="fas fa-plug"></i> Bağlan';
        chatInput.disabled = true;
        sendBtn.disabled = true;
        
        const chatMessages = document.getElementById('twitch-chat-messages');
        chatMessages.innerHTML = `
            <div class="chat-welcome">
                <i class="fab fa-twitch"></i>
                <h4>Twitch Chat</h4>
                <p>Chat bağlantısı kesildi</p>
            </div>
        `;
        
        window.notifications.show('Chat bağlantısı kesildi', 'info');
    }
}

function clearTwitchChat() {
    const chatMessages = document.getElementById('twitch-chat-messages');
    if (window.twitchChat.isConnected) {
        chatMessages.innerHTML = '<div class="chat-status">Chat temizlendi</div>';
        window.twitchChat.chatMessages = [];
    }
}

function sendChatMessage() {
    const chatInput = document.getElementById('chat-input');
    const message = chatInput.value.trim();
    
    if (message && window.twitchChat.isConnected) {
        // Helix API kullanarak mesaj gönder
        window.twitchChat.sendChatMessageAPI(message)
            .then(() => {
                chatInput.value = '';
                
                // Kendi mesajımızı chat'e ekle
                const ownMessage = {
                    id: Date.now(),
                    username: window.twitchChat.channelName,
                    message: message,
                    color: '#00FFFF',
                    badges: ['broadcaster'],
                    timestamp: new Date()
                };
                window.twitchChat.addChatMessage(ownMessage);
                
                window.notifications.show('Mesaj gönderildi!', 'success');
            })
            .catch(error => {
                // API başarısız olursa WebSocket'i dene
                try {
                    window.twitchChat.socket.send(`PRIVMSG #${window.twitchChat.channelName} :${message}`);
                    chatInput.value = '';
                    
                    const ownMessage = {
                        id: Date.now(),
                        username: window.twitchChat.channelName,
                        message: message,
                        color: '#00FFFF',
                        badges: [],
                        timestamp: new Date()
                    };
                    window.twitchChat.addChatMessage(ownMessage);
                    
                } catch (wsError) {
                    window.notifications.show('Mesaj gönderilemedi: ' + error.message, 'error');
                }
            });
    }
}

// Enter key support for chat input
document.addEventListener('keypress', function(e) {
    if (e.target.id === 'chat-input' && e.key === 'Enter') {
        sendChatMessage();
    }
});

// Stream View Mode Functions
function setViewMode(mode) {
    const container = document.getElementById('streams-container');
    const buttons = document.querySelectorAll('.view-mode-buttons .cyber-btn-sm');
    
    // Remove all mode classes
    container.className = 'streams-container';
    buttons.forEach(btn => btn.classList.remove('active'));
    
    // Add new mode class
    container.classList.add(mode);
    
    // Update active button
    const activeButton = document.getElementById(`${mode.replace('-', '-')}-view`) || 
                        document.getElementById(`cinema-${mode.split('-')[1]}`);
    if (activeButton) {
        activeButton.classList.add('active');
    }
    
    // Handle cinema mode
    if (mode.includes('cinema')) {
        document.body.style.overflow = 'hidden';
        
        // Show close button
        let closeButton = document.querySelector('.cinema-close');
        if (!closeButton) {
            closeButton = document.createElement('button');
            closeButton.className = 'cinema-close';
            closeButton.innerHTML = '<i class="fas fa-times"></i>';
            closeButton.onclick = () => setViewMode('normal');
            document.body.appendChild(closeButton);
        }
        closeButton.style.display = 'flex';
    } else {
        document.body.style.overflow = 'auto';
        const closeButton = document.querySelector('.cinema-close');
        if (closeButton) {
            closeButton.style.display = 'none';
        }
    }
}
    
    // Set up periodic updates
    setInterval(() => {
        twitchAPI.updateStreamStatus();
        kickAPI.updateStreamStatus();
    }, 60000); // Update every minute
    
    // Welcome notification
    setTimeout(() => {
        notifications.show('Su Ascend\'e hoş geldiniz! Yayınlar ve blog güncellemeleri için takipte kalın.', 'info');
    }, 2000);
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) rotate(360deg);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
});

// Export for global access
window.SuAscend = {
    TwitchAPI,
    KickAPI,
    BlogManager,
    CyberEffects,
    NotificationSystem
};