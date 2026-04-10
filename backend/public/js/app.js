const API_BASE = '/api';
const translations = {
    id: {
        drafts: 'Drafts',
        media: 'Media',
        albums: 'Albums',
        cart: 'Cart',
        profile: 'Profile',
        refresh: 'Refresh',
        no_drafts: 'Belum ada draft',
        all_categories: 'Semua Kategori',
        all_types: 'Semua Tipe',
        photo: 'Foto',
        video: 'Video',
        audio: 'Audio',
        document: 'Dokumen',
        search_placeholder: 'Cari media...',
        no_media: 'Tidak ada media',
        no_albums: 'Tidak ada album',
        empty_cart: 'Keranjang kosong',
        total: 'Total:',
        checkout: 'Checkout',
        clear_cart: 'Kosongkan',
        wishlist: 'Wishlist',
        my_purchases: 'Pembelian',
        support: 'Support',
        analytics: 'Analitik',
        purchases: 'Pembelian',
        loading: 'Memuat...',
        publish: 'Publish',
        delete: 'Hapus',
        add_cart: 'Masukkan Keranjang',
        add_wishlist: 'Wishlist',
        buy_now: 'Beli Sekarang',
        edit: 'Edit',
        price: 'Harga',
        category: 'Kategori',
        title: 'Judul',
        description: 'Deskripsi',
        type_message: 'Ketik pesan...',
        send: 'Kirim',
        save: 'Simpan',
        cancel: 'Batal',
        back: 'Kembali',
        rating: 'Rating',
        review: 'Ulasan',
        write_review: 'Tulis Ulasan',
        sold: 'Terjual',
        views: 'Dilihat',
        free: 'Gratis',
        select_all: 'Pilih Semua',
        create_album: 'Buat Album',
        album_created: 'Album berhasil dibuat',
        payment_success: 'Pembayaran berhasil',
        payment_pending: 'Menunggu pembayaran',
        error: 'Terjadi kesalahan',
        notifications: 'Notifikasi',
        no_notifications: 'Tidak ada notifikasi',
        mark_read: 'Tandai dibaca',
        mark_all_read: 'Tandai semua dibaca',
        clear_all: 'Hapus semua',
        share: 'Bagikan',
        copy_link: 'Salin Link',
        share_telegram: 'Bagikan ke Telegram',
        share_whatsapp: 'Bagikan ke WhatsApp',
        share_twitter: 'Bagikan ke Twitter',
        share_facebook: 'Bagikan ke Facebook',
        badges: 'Lencana',
        achievements: 'Pencapaian',
        leaderboard: 'Papan Peringkat',
        earned: 'Diperoleh',
        progress: 'Progres',
    },
    en: {
        drafts: 'Drafts',
        media: 'Media',
        albums: 'Albums',
        cart: 'Cart',
        profile: 'Profile',
        refresh: 'Refresh',
        no_drafts: 'No drafts yet',
        all_categories: 'All Categories',
        all_types: 'All Types',
        photo: 'Photo',
        video: 'Video',
        audio: 'Audio',
        document: 'Document',
        search_placeholder: 'Search media...',
        no_media: 'No media found',
        no_albums: 'No albums found',
        empty_cart: 'Cart is empty',
        total: 'Total:',
        checkout: 'Checkout',
        clear_cart: 'Clear Cart',
        wishlist: 'Wishlist',
        my_purchases: 'Purchases',
        support: 'Support',
        analytics: 'Analytics',
        purchases: 'Purchases',
        loading: 'Loading...',
        publish: 'Publish',
        delete: 'Delete',
        add_cart: 'Add to Cart',
        add_wishlist: 'Wishlist',
        buy_now: 'Buy Now',
        edit: 'Edit',
        price: 'Price',
        category: 'Category',
        title: 'Title',
        description: 'Description',
        type_message: 'Type your message...',
        send: 'Send',
        save: 'Save',
        cancel: 'Cancel',
        back: 'Back',
        rating: 'Rating',
        review: 'Review',
        write_review: 'Write Review',
        sold: 'Sold',
        views: 'Views',
        free: 'Free',
        select_all: 'Select All',
        create_album: 'Create Album',
        album_created: 'Album created successfully',
        payment_success: 'Payment successful',
        payment_pending: 'Payment pending',
        error: 'An error occurred',
        notifications: 'Notifications',
        no_notifications: 'No notifications',
        mark_read: 'Mark as read',
        mark_all_read: 'Mark all as read',
        clear_all: 'Clear all',
        share: 'Share',
        copy_link: 'Copy Link',
        share_telegram: 'Share on Telegram',
        share_whatsapp: 'Share on WhatsApp',
        share_twitter: 'Share on Twitter',
        share_facebook: 'Share on Facebook',
        badges: 'Badges',
        achievements: 'Achievements',
        leaderboard: 'Leaderboard',
        earned: 'Earned',
        progress: 'Progress',
    }
};

let state = {
    user: null,
    token: localStorage.getItem('token'),
    theme: localStorage.getItem('theme') || 'light',
    language: localStorage.getItem('language') || 'id',
    cart: JSON.parse(localStorage.getItem('cart') || '[]'),
    drafts: [],
    media: [],
    albums: [],
    wishlist: [],
    purchases: [],
    notifications: [],
    unreadNotifications: 0,
    badges: [],
    currentTab: 'drafts',
    selectedDrafts: [],
};

const tg = window.Telegram.WebApp;
tg.ready();

function t(key) {
    return translations[state.language][key] || key;
}

function showLoading() {
    document.getElementById('loading').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loading').style.display = 'none';
}

function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.style.display = 'block';
    setTimeout(() => toast.style.display = 'none', 3000);
}

async function apiCall(endpoint, options = {}) {
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    };
    
    if (state.token) {
        headers['Authorization'] = `Bearer ${state.token}`;
    }

    try {
        const response = await fetch(`${API_BASE}${endpoint}`, {
            ...options,
            headers: { ...headers, ...options.headers },
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Request failed');
        }

        return data;
    } catch (error) {
        showToast(error.message);
        throw error;
    }
}

function saveState() {
    localStorage.setItem('token', state.token || '');
    localStorage.setItem('theme', state.theme);
    localStorage.setItem('language', state.language);
    localStorage.setItem('cart', JSON.stringify(state.cart));
}

function applyTheme() {
    document.documentElement.setAttribute('data-theme', state.theme);
    document.getElementById('theme-icon').textContent = state.theme === 'dark' ? '☀️' : '🌙';
}

function applyLanguage() {
    document.getElementById('lang-text').textContent = state.language.toUpperCase();
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        el.textContent = t(key);
    });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
        const key = el.getAttribute('data-i18n-placeholder');
        el.placeholder = t(key);
    });
}

function switchTab(tabName) {
    state.currentTab = tabName;
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabName);
    });
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.toggle('active', content.id === `${tabName}-section`);
    });
}

async function initAuth() {
    if (state.token) {
        try {
            const data = await apiCall('/user/profile');
            state.user = data.user;
            updateProfileUI();
            return true;
        } catch (e) {
            state.token = null;
        }
    }

    const initData = tg.initData;
    let telegram_id = null;
    
    if (initData) {
        const params = new URLSearchParams(initData);
        telegram_id = params.get('user') ? JSON.parse(params.get('user')).id : null;
    }

    try {
        const data = await apiCall('/auth/register', {
            method: 'POST',
            body: JSON.stringify({ 
                telegram_id,
                name: tg.initDataUnsafe?.user?.first_name || 'User'
            })
        });
        state.token = data.token;
        state.user = data.user;
        saveState();
        updateProfileUI();
    } catch (e) {
        console.error('Auth failed:', e);
    }
}

function updateProfileUI() {
    if (state.user) {
        document.getElementById('profile-name').textContent = state.user.name || 'User';
        document.getElementById('profile-id').textContent = state.user.anonymous_id || '';
        document.getElementById('profile-avatar').textContent = state.user.name ? state.user.name[0].toUpperCase() : '👤';
    }
}

async function loadDrafts() {
    try {
        const data = await apiCall('/drafts');
        state.drafts = data.drafts;
        renderDrafts();
    } catch (e) {
        console.error('Failed to load drafts:', e);
    }
}

function renderDrafts() {
    const container = document.getElementById('drafts-list');
    const empty = document.getElementById('drafts-empty');
    
    if (state.drafts.length === 0) {
        container.innerHTML = '';
        empty.style.display = 'block';
        return;
    }
    
    empty.style.display = 'none';
    container.innerHTML = state.drafts.map(draft => `
        <div class="card">
            <div class="card-media" style="display:flex;align-items:center;justify-content:center;font-size:40px;">
                ${getTypeIcon(draft.type)}
            </div>
            <div class="card-body">
                <div class="card-title">${draft.caption || draft.type}</div>
                <div class="card-meta">${draft.type}</div>
                <div class="card-price">${draft.price > 0 ? formatPrice(draft.price) : t('free')}</div>
                <div class="card-actions">
                    <button class="btn btn-primary btn-sm" onclick="publishDraft(${draft.id})">${t('publish')}</button>
                    <button class="btn btn-danger btn-sm" onclick="deleteDraft(${draft.id})">${t('delete')}</button>
                </div>
            </div>
        </div>
    `).join('');
}

async function publishDraft(id) {
    showLoading();
    try {
        await apiCall(`/drafts/${id}/publish`, { method: 'POST' });
        showToast('Draft published!');
        loadDrafts();
        loadMedia();
    } catch (e) {
        console.error(e);
    }
    hideLoading();
}

async function deleteDraft(id) {
    showLoading();
    try {
        await apiCall(`/drafts/${id}`, { method: 'DELETE' });
        showToast('Draft deleted');
        loadDrafts();
    } catch (e) {
        console.error(e);
    }
    hideLoading();
}

async function loadMedia(filters = {}) {
    showLoading();
    try {
        const params = new URLSearchParams(filters).toString();
        const data = await apiCall(`/media?${params}`);
        state.media = data.data;
        renderMedia();
    } catch (e) {
        console.error('Failed to load media:', e);
    }
    hideLoading();
}

function renderMedia() {
    const container = document.getElementById('media-list');
    const empty = document.getElementById('media-empty');
    
    if (state.media.length === 0) {
        container.innerHTML = '';
        empty.style.display = 'block';
        return;
    }
    
    empty.style.display = 'none';
    container.innerHTML = state.media.map(media => `
        <div class="card" onclick="showMediaDetail('${media.unique_id}')">
            <div class="card-media" style="display:flex;align-items:center;justify-content:center;font-size:40px;">
                ${getTypeIcon(media.type)}
            </div>
            <div class="card-body">
                <div class="card-title">${media.caption || media.type}</div>
                <div class="card-meta">${media.type} • ${media.category || '-'}</div>
                <div class="card-price">${media.price > 0 ? formatPrice(media.price) : t('free')}</div>
                <div class="card-actions">
                    <button class="btn btn-primary btn-sm" onclick="event.stopPropagation();addToCart('media', ${media.id})">${t('add_cart')}</button>
                    <button class="btn btn-secondary btn-sm" onclick="event.stopPropagation();addToWishlist('media', ${media.id})">❤️</button>
                </div>
            </div>
        </div>
    `).join('');
}

async function showMediaDetail(id) {
    showLoading();
    try {
        const data = await apiCall(`/media/${id}`);
        const media = data.media;
        
        document.getElementById('detail-content').innerHTML = `
            <div class="card" style="margin-top:16px;">
                <div class="card-media" style="height:200px;display:flex;align-items:center;justify-content:center;font-size:80px;">
                    ${getTypeIcon(media.type)}
                </div>
                <div class="card-body">
                    <h3>${media.caption || media.type}</h3>
                    <p class="card-meta">${media.type} • ${media.category || '-'} • Rating: ${data.average_rating} (${data.review_count} reviews)</p>
                    <div class="card-price">${media.price > 0 ? formatPrice(media.price) : t('free')}</div>
                    <div class="card-actions">
                        <button class="btn btn-primary" onclick="addToCart('media', ${media.id})">${t('add_cart')}</button>
                        <button class="btn btn-success" onclick="buyNow('media', ${media.id})">${t('buy_now')}</button>
                    </div>
                </div>
            </div>
            <div class="card" style="margin-top:16px;">
                <div class="card-body">
                    <h4>${t('write_review')}</h4>
                    <div class="rating" id="review-rating">
                        ${[1,2,3,4,5].map(i => `<span class="rating-star" onclick="setRating(${i})">★</span>`).join('')}
                    </div>
                    <textarea id="review-comment" placeholder="Your review..." style="width:100%;margin-top:8px;padding:8px;border-radius:8px;"></textarea>
                    <button class="btn btn-primary" style="margin-top:8px;" onclick="submitReview('media', ${media.id})">${t('send')}</button>
                </div>
            </div>
        `;
        switchTab('detail');
    } catch (e) {
        console.error(e);
    }
    hideLoading();
}

async function loadAlbums() {
    try {
        const data = await apiCall('/albums');
        state.albums = data.data;
        renderAlbums();
    } catch (e) {
        console.error('Failed to load albums:', e);
    }
}

function renderAlbums() {
    const container = document.getElementById('albums-list');
    const empty = document.getElementById('albums-empty');
    
    if (state.albums.length === 0) {
        container.innerHTML = '';
        empty.style.display = 'block';
        return;
    }
    
    empty.style.display = 'none';
    container.innerHTML = state.albums.map(album => `
        <div class="card" onclick="showAlbumDetail('${album.unique_id}')">
            <div class="card-media" style="display:flex;align-items:center;justify-content:center;font-size:40px;">
                📁
            </div>
            <div class="card-body">
                <div class="card-title">${album.title || 'Album'}</div>
                <div class="card-meta">${album.media?.length || 0} items</div>
                <div class="card-price">${album.price > 0 ? formatPrice(album.price) : t('free')}</div>
                <div class="card-actions">
                    <button class="btn btn-primary btn-sm" onclick="event.stopPropagation();addToCart('album', ${album.id})">${t('add_cart')}</button>
                </div>
            </div>
        </div>
    `).join('');
}

function renderCart() {
    const container = document.getElementById('cart-items');
    const empty = document.getElementById('cart-empty');
    
    if (state.cart.length === 0) {
        container.innerHTML = '';
        empty.style.display = 'block';
        return;
    }
    
    empty.style.display = 'none';
    
    let total = 0;
    container.innerHTML = state.cart.map((item, index) => {
        total += item.price || 0;
        return `
            <div class="card" style="margin-bottom:12px;">
                <div class="card-body" style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <strong>${item.type}: ${item.id}</strong>
                        <div class="card-price">${item.price > 0 ? formatPrice(item.price) : t('free')}</div>
                    </div>
                    <button class="btn btn-danger btn-sm" onclick="removeFromCart(${index})">X</button>
                </div>
            </div>
        `;
    }).join('');
    
    document.getElementById('cart-total-amount').textContent = formatPrice(total);
}

function addToCart(type, id) {
    const item = state.cart.find(c => c.type === type && c.id === id);
    if (item) {
        showToast('Already in cart');
        return;
    }
    
    state.cart.push({ type, id, price: 0 });
    saveState();
    renderCart();
    showToast('Added to cart');
}

function removeFromCart(index) {
    state.cart.splice(index, 1);
    saveState();
    renderCart();
}

function addToWishlist(type, id) {
    apiCall('/wishlist', {
        method: 'POST',
        body: JSON.stringify({ [type === 'media' ? 'media_id' : 'album_id']: id })
    }).then(() => {
        showToast('Added to wishlist');
    }).catch(e => console.error(e));
}

async function checkout() {
    if (state.cart.length === 0) return;
    
    showLoading();
    try {
        for (const item of state.cart) {
            await apiCall('/payment/checkout', {
                method: 'POST',
                body: JSON.stringify({
                    [item.type + '_id']: item.id,
                    payment_method: 'simulation'
                })
            });
        }
        
        state.cart = [];
        saveState();
        renderCart();
        showToast(t('payment_success'));
    } catch (e) {
        console.error(e);
    }
    hideLoading();
}

function buyNow(type, id) {
    addToCart(type, id);
    switchTab('cart');
}

function setRating(value) {
    document.querySelectorAll('.rating-star').forEach((star, i) => {
        star.classList.toggle('active', i < value);
    });
    window.selectedRating = value;
}

function submitReview(type, id) {
    const comment = document.getElementById('review-comment').value;
    const rating = window.selectedRating || 5;
    
    apiCall('/reviews', {
        method: 'POST',
        body: JSON.stringify({
            [type + '_id']: id,
            rating,
            comment
        })
    }).then(() => {
        showToast('Review submitted!');
        window.selectedRating = null;
    }).catch(e => console.error(e));
}

function getTypeIcon(type) {
    const icons = { photo: '📷', video: '🎬', audio: '🎵', document: '📄' };
    return icons[type] || '📁';
}

function formatPrice(amount) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(amount);
}

// Notification functions
async function loadNotifications() {
    try {
        const data = await apiCall('/notifications');
        state.notifications = data.notifications;
        state.unreadNotifications = data.unread_count;
        updateNotificationBadge();
        renderNotifications();
    } catch (e) {
        console.error('Failed to load notifications:', e);
    }
}

function updateNotificationBadge() {
    const badge = document.getElementById('notification-badge');
    if (badge) {
        badge.textContent = state.unreadNotifications;
        badge.style.display = state.unreadNotifications > 0 ? 'inline' : 'none';
    }
}

async function markNotificationRead(id) {
    try {
        await apiCall(`/notifications/${id}/read`, { method: 'POST' });
        state.unreadNotifications = Math.max(0, state.unreadNotifications - 1);
        updateNotificationBadge();
        loadNotifications();
    } catch (e) {
        console.error(e);
    }
}

async function markAllNotificationsRead() {
    try {
        await apiCall('/notifications/read-all', { method: 'POST' });
        state.unreadNotifications = 0;
        updateNotificationBadge();
        loadNotifications();
    } catch (e) {
        console.error(e);
    }
}

async function clearAllNotifications() {
    try {
        await apiCall('/notifications/clear', { method: 'POST' });
        state.notifications = [];
        state.unreadNotifications = 0;
        updateNotificationBadge();
        loadNotifications();
    } catch (e) {
        console.error(e);
    }
}

// Share functions
async function shareItem(type, id) {
    try {
        const data = await apiCall(`/share/${type}/${id}`);
        showShareModal(data);
    } catch (e) {
        console.error('Failed to generate share link:', e);
    }
}

function showShareModal(shareData) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${t('share')}</h3>
                <button class="close-btn" onclick="this.closest('.modal').remove()">×</button>
            </div>
            <div class="modal-body">
                <div class="share-links">
                    <button class="share-btn telegram" onclick="window.open('${shareData.social_links.telegram}', '_blank')">
                        📱 ${t('share_telegram')}
                    </button>
                    <button class="share-btn whatsapp" onclick="window.open('${shareData.social_links.whatsapp}', '_blank')">
                        💬 ${t('share_whatsapp')}
                    </button>
                    <button class="share-btn twitter" onclick="window.open('${shareData.social_links.twitter}', '_blank')">
                        🐦 ${t('share_twitter')}
                    </button>
                    <button class="share-btn facebook" onclick="window.open('${shareData.social_links.facebook}', '_blank')">
                        📘 ${t('share_facebook')}
                    </button>
                </div>
                <div class="share-link">
                    <input type="text" value="${shareData.url}" readonly id="share-url">
                    <button class="btn btn-primary" onclick="copyShareLink()">${t('copy_link')}</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function copyShareLink() {
    const input = document.getElementById('share-url');
    input.select();
    document.execCommand('copy');
    showToast('Link copied to clipboard!');
}

// Badge functions
async function loadBadges() {
    try {
        const data = await apiCall('/badges/user');
        state.badges = data.badges;
        renderBadges();
    } catch (e) {
        console.error('Failed to load badges:', e);
    }
}

function renderBadges() {
    const container = document.getElementById('badges-list');
    if (!container) return;

    container.innerHTML = state.badges.map(badge => `
        <div class="badge-item ${badge.pivot ? 'earned' : 'locked'}">
            <div class="badge-icon">${badge.icon}</div>
            <div class="badge-info">
                <h4>${badge.name}</h4>
                <p>${badge.description}</p>
                ${badge.pivot ? `<small>${t('earned')} ${new Date(badge.pivot.earned_at).toLocaleDateString()}</small>` : ''}
            </div>
        </div>
    `).join('');
}

async function showLeaderboard() {
    try {
        const data = await apiCall('/badges/leaderboard');
        showLeaderboardModal(data.leaderboard, data.type);
    } catch (e) {
        console.error('Failed to load leaderboard:', e);
    }
}

function showLeaderboardModal(leaderboard, type) {
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${t('leaderboard')} - ${type}</h3>
                <button class="close-btn" onclick="this.closest('.modal').remove()">×</button>
            </div>
            <div class="modal-body">
                ${leaderboard.map((user, index) => `
                    <div class="leaderboard-item ${index < 3 ? 'top-' + (index + 1) : ''}">
                        <div class="rank">#${index + 1}</div>
                        <div class="user-info">
                            <div class="user-name">${user.name}</div>
                            <div class="user-stats">${getLeaderboardStat(user, type)}</div>
                        </div>
                    </div>
                `).join('')}
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function getLeaderboardStat(user, type) {
    switch (type) {
        case 'sales':
            return `${user.sales_count || 0} sales`;
        case 'rating':
            return `Rating: ${user.avg_rating || 0}`;
        case 'media_count':
            return `${user.media_count || 0} media items`;
        default:
            return '';
    }
}

async function checkAchievements() {
    try {
        const data = await apiCall('/badges/check', { method: 'POST' });
        if (data.earned_badges.length > 0) {
            showAchievementModal(data.earned_badges);
            loadBadges();
        }
    } catch (e) {
        console.error('Failed to check achievements:', e);
    }
}

function showAchievementModal(badges) {
    const modal = document.createElement('div');
    modal.className = 'modal achievement-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>🎉 ${t('achievements')}</h3>
            </div>
            <div class="modal-body">
                ${badges.map(badge => `
                    <div class="achievement-item">
                        <div class="badge-icon">${badge.icon}</div>
                        <div class="achievement-info">
                            <h4>${badge.name}</h4>
                            <p>${badge.description}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="this.closest('.modal').remove()">OK</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function renderNotifications() {
    const container = document.getElementById('notifications-list');
    const empty = document.getElementById('notifications-empty');

    if (state.notifications.length === 0) {
        container.innerHTML = '';
        empty.style.display = 'block';
        return;
    }

    empty.style.display = 'none';
    container.innerHTML = state.notifications.map(notification => `
        <div class="notification-item ${!notification.read_at ? 'unread' : ''}" onclick="markNotificationRead(${notification.id})">
            <div class="notification-content">
                <div class="notification-title">${notification.title}</div>
                <div class="notification-message">${notification.message}</div>
                <div class="notification-time">${new Date(notification.created_at).toLocaleString()}</div>
            </div>
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', () => {
    applyTheme();
    applyLanguage();
    
    document.getElementById('theme-toggle').addEventListener('click', () => {
        state.theme = state.theme === 'light' ? 'dark' : 'light';
        saveState();
        applyTheme();
    });
    
    document.getElementById('lang-toggle').addEventListener('click', () => {
        state.language = state.language === 'id' ? 'en' : 'id';
        saveState();
        applyLanguage();
    });
    
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.dataset.tab));
    });
    
    document.getElementById('refresh-drafts').addEventListener('click', loadDrafts);
    document.getElementById('clear-cart').addEventListener('click', () => { state.cart = []; saveState(); renderCart(); });
    document.getElementById('checkout-btn').addEventListener('click', checkout);
    document.getElementById('back-btn').addEventListener('click', () => switchTab(state.currentTab));

    // Notification events
    document.getElementById('notification-btn').addEventListener('click', () => {
        switchTab('notifications');
        loadNotifications(); // Refresh notifications when tab is clicked
    });
    document.getElementById('tab-notifications').addEventListener('click', () => {
        loadNotifications(); // Refresh when notifications tab is clicked
    });
    document.getElementById('mark-all-read').addEventListener('click', markAllNotificationsRead);
    document.getElementById('clear-notifications').addEventListener('click', clearAllNotifications);

    // Badge events
    document.getElementById('tab-badges').addEventListener('click', () => {
        loadBadges(); // Refresh badges when tab is clicked
    });
    document.getElementById('check-achievements').addEventListener('click', checkAchievements);
    document.getElementById('show-leaderboard').addEventListener('click', showLeaderboard);
    
    document.getElementById('media-search').addEventListener('input', (e) => {
        loadMedia({ search: e.target.value });
    });
    
    document.getElementById('media-filter-category').addEventListener('change', (e) => {
        loadMedia({ category: e.target.value });
    });
    
    document.getElementById('media-filter-type').addEventListener('change', (e) => {
        loadMedia({ type: e.target.value });
    });
    
    initAuth().then(() => {
        loadDrafts();
        loadMedia();
        loadAlbums();
        loadNotifications();
        loadBadges();
        renderCart();
        // Update notification badge periodically
        setInterval(() => {
            apiCall('/notifications/unread').then(data => {
                state.unreadNotifications = data.count;
                updateNotificationBadge();
            }).catch(() => {});
        }, 30000); // Check every 30 seconds
    });
});

window.publishDraft = publishDraft;
window.deleteDraft = deleteDraft;
window.showMediaDetail = showMediaDetail;
window.showAlbumDetail = (id) => { /* Implement album detail */ };
window.addToCart = addToCart;
window.addToWishlist = addToWishlist;
window.removeFromCart = removeFromCart;
window.buyNow = buyNow;
window.setRating = setRating;
window.submitReview = submitReview;
window.shareItem = shareItem;
window.markNotificationRead = markNotificationRead;