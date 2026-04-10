<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BotKonten - Media Marketplace</title>
    <link rel="stylesheet" href="/css/styles.css">
    <script src="https://telegram.org/js/telegram-web-app.js"></script>
</head>
<body>
    <div id="app">
        <header class="header">
            <h1>BotKonten</h1>
            <div class="header-actions">
                <button id="notification-btn" class="icon-btn" title="Notifications">
                    <span>🔔</span>
                    <span class="notification-badge" id="notification-badge" style="display:none;"></span>
                </button>
                <button id="theme-toggle" class="icon-btn" title="Toggle Theme">
                    <span id="theme-icon">🌙</span>
                </button>
                <button id="lang-toggle" class="icon-btn" title="Language">
                    <span id="lang-text">ID</span>
                </button>
            </div>
        </header>

        <nav class="tab-nav">
            <button class="tab-btn active" data-tab="drafts" id="tab-drafts">Drafts</button>
            <button class="tab-btn" data-tab="media" id="tab-media">Media</button>
            <button class="tab-btn" data-tab="albums" id="tab-albums">Albums</button>
            <button class="tab-btn" data-tab="cart" id="tab-cart">Cart</button>
            <button class="tab-btn" data-tab="profile" id="tab-profile">Profile</button>
            <button class="tab-btn" data-tab="notifications" id="tab-notifications">Notifications</button>
            <button class="tab-btn" data-tab="badges" id="tab-badges">Badges</button>
        </nav>

        <main class="main-content">
            <section id="drafts-section" class="tab-content active">
                <div class="section-header">
                    <h2 data-i18n="drafts">Drafts</h2>
                    <button class="btn btn-primary" id="refresh-drafts" data-i18n="refresh">Refresh</button>
                </div>
                <div id="drafts-list" class="grid-list"></div>
                <div class="empty-state" id="drafts-empty" style="display:none;">
                    <p data-i18n="no_drafts">No drafts yet</p>
                </div>
            </section>

            <section id="media-section" class="tab-content">
                <div class="section-header">
                    <h2 data-i18n="media">Media</h2>
                    <div class="filter-group">
                        <select id="media-filter-category" class="filter-select">
                            <option value="" data-i18n="all_categories">All Categories</option>
                        </select>
                        <select id="media-filter-type" class="filter-select">
                            <option value="" data-i18n="all_types">All Types</option>
                            <option value="photo" data-i18n="photo">Photo</option>
                            <option value="video" data-i18n="video">Video</option>
                            <option value="audio" data-i18n="audio">Audio</option>
                            <option value="document" data-i18n="document">Document</option>
                        </select>
                    </div>
                </div>
                <div class="search-bar">
                    <input type="text" id="media-search" placeholder="Search media..." data-i18n-placeholder="search_placeholder">
                </div>
                <div id="media-list" class="grid-list"></div>
                <div class="empty-state" id="media-empty" style="display:none;">
                    <p data-i18n="no_media">No media found</p>
                </div>
            </section>

            <section id="albums-section" class="tab-content">
                <div class="section-header">
                    <h2 data-i18n="albums">Albums</h2>
                </div>
                <div id="albums-list" class="grid-list"></div>
                <div class="empty-state" id="albums-empty" style="display:none;">
                    <p data-i18n="no_albums">No albums found</p>
                </div>
            </section>

            <section id="cart-section" class="tab-content">
                <div class="section-header">
                    <h2 data-i18n="cart">Cart</h2>
                    <button class="btn btn-danger" id="clear-cart" data-i18n="clear_cart">Clear Cart</button>
                </div>
                <div id="cart-items"></div>
                <div class="cart-summary">
                    <div class="cart-total">
                        <span data-i18n="total">Total:</span>
                        <span id="cart-total-amount">Rp 0</span>
                    </div>
                    <button class="btn btn-primary" id="checkout-btn" data-i18n="checkout">Checkout</button>
                </div>
                <div class="empty-state" id="cart-empty" style="display:none;">
                    <p data-i18n="empty_cart">Cart is empty</p>
                </div>
            </section>

            <section id="profile-section" class="tab-content">
                <div class="profile-header">
                    <div class="avatar" id="profile-avatar">👤</div>
                    <h3 id="profile-name">User</h3>
                    <p id="profile-id"></p>
                </div>
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-value" id="stat-media">0</span>
                        <span class="stat-label" data-i18n="media">Media</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value" id="stat-albums">0</span>
                        <span class="stat-label" data-i18n="albums">Albums</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-value" id="stat-purchases">0</span>
                        <span class="stat-label" data-i18n="purchases">Purchases</span>
                    </div>
                </div>
                <div class="profile-menu">
                    <button class="menu-item" id="btn-wishlist">
                        <span>❤️</span> <span data-i18n="wishlist">Wishlist</span>
                    </button>
                    <button class="menu-item" id="btn-purchases">
                        <span>📦</span> <span data-i18n="my_purchases">My Purchases</span>
                    </button>
                    <button class="menu-item" id="btn-support">
                        <span>💬</span> <span data-i18n="support">Support</span>
                    </button>
                    <button class="menu-item" id="btn-analytics">
                        <span>📊</span> <span data-i18n="analytics">Analytics</span>
                    </button>
                </div>
            </section>

            <section id="detail-section" class="tab-content">
                <button class="btn btn-back" id="back-btn">← Back</button>
                <div id="detail-content"></div>
            </section>

            <section id="wishlist-section" class="tab-content">
                <div class="section-header">
                    <h2 data-i18n="wishlist">Wishlist</h2>
                </div>
                <div id="wishlist-items"></div>
            </section>

            <section id="purchases-section" class="tab-content">
                <div class="section-header">
                    <h2 data-i18n="my_purchases">My Purchases</h2>
                </div>
                <div id="purchases-items"></div>
            </section>

            <section id="support-section" class="tab-content">
                <div class="section-header">
                    <h2 data-i18n="support">Support</h2>
                </div>
                <div id="support-messages"></div>
                <div class="support-input">
                    <input type="text" id="support-message" placeholder="Type your message..." data-i18n-placeholder="type_message">
                    <button class="btn btn-primary" id="send-support" data-i18n="send">Send</button>
                </div>
            </section>

            <section id="analytics-section" class="tab-content">
                <div class="section-header">
                    <h2 data-i18n="analytics">Analytics</h2>
                </div>
                <div id="analytics-content"></div>
            </section>

            <section id="notifications-section" class="tab-content">
                <div class="section-header">
                    <h2 data-i18n="notifications">Notifications</h2>
                    <div class="notification-actions">
                        <button class="btn btn-secondary btn-sm" id="mark-all-read" data-i18n="mark_all_read">Mark all as read</button>
                        <button class="btn btn-danger btn-sm" id="clear-notifications" data-i18n="clear_all">Clear all</button>
                    </div>
                </div>
                <div id="notifications-list"></div>
                <div class="empty-state" id="notifications-empty" style="display:none;">
                    <p data-i18n="no_notifications">No notifications</p>
                </div>
            </section>

            <section id="badges-section" class="tab-content">
                <div class="section-header">
                    <h2 data-i18n="badges">Badges</h2>
                    <div class="badge-actions">
                        <button class="btn btn-primary btn-sm" id="check-achievements" data-i18n="achievements">Check Achievements</button>
                        <button class="btn btn-secondary btn-sm" id="show-leaderboard" data-i18n="leaderboard">Leaderboard</button>
                    </div>
                </div>
                <div id="badges-list"></div>
            </section>
        </main>

        <div id="loading" class="loading" style="display:none;">
            <div class="spinner"></div>
            <p data-i18n="loading">Loading...</p>
        </div>

        <div id="toast" class="toast" style="display:none;"></div>
    </div>

    <script src="/js/app.js"></script>
</body>
</html>