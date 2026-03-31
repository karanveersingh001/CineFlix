# 🎬 Cinflix — Self-Hosted OTT Platform

A fully featured Netflix-style streaming frontend for your **Jellyfin** media server.

---

## ✨ Features

- 🔐 **Authentication** — Login with Jellyfin credentials, persistent sessions
- 🏠 **Home Dashboard** — Hero carousel, Recently Added, Movies, TV Shows, Continue Watching
- 🎬 **Movies & Shows** — Browse all library content with genre/sort filters
- 🔍 **Live Search** — Debounced search across your entire library
- ▶️ **Video Player** — Full-featured HTML5 player with keyboard shortcuts, seek, volume, fullscreen, speed control
- ❤️ **My List / Favorites** — Add/remove from favorites, persisted to Jellyfin
- 📱 **PWA** — Installable app, service worker, offline fallback
- 🌙 **Dark Theme** — Beautiful dark OTT UI with glassmorphism effects
- ⚡ **Performance** — Lazy loading, skeleton loaders, debounced search, caching

---

## 📋 Requirements

- PHP 7.4+ with cURL and session support
- Apache with `mod_rewrite` enabled (or Nginx with equivalent config)
- Access to a Jellyfin server (v10.8+)

---

## 🚀 Installation

### 1. Clone / Upload Files

```bash
# Upload the /cinflix folder to your web root
/var/www/html/cinflix/
```

### 2. Configure Jellyfin URL

Edit `config.php` and update:

```php
define('JELLYFIN_URL', 'https://your-jellyfin-server:port');
```

### 3. Set File Permissions

```bash
chmod 755 /var/www/html/cinflix
chmod 644 /var/www/html/cinflix/*.php
```

### 4. Enable Apache mod_rewrite

```bash
sudo a2enmod rewrite
sudo systemctl reload apache2
```

### 5. Access Cinflix

Navigate to: `https://yourdomain.com/cinflix/`

Sign in with your **Jellyfin username and password**.

---

## 📂 File Structure

```
/cinflix
├── index.php              # Entry point / router
├── config.php             # Jellyfin URL, session config
├── manifest.json          # PWA manifest
├── sw.js                  # Service Worker
├── .htaccess              # Apache rewrite + security
│
├── /api
│   ├── auth.php           # Login / logout / session
│   ├── media.php          # Media, search, favorites API
│   └── jellyfin.php       # cURL wrapper for Jellyfin
│
├── /components
│   └── layout.php         # HTML shell + navbar
│
├── /pages
│   ├── login.php          # Login page
│   ├── home.php           # Home with hero carousel
│   ├── movies.php         # Browse movies
│   ├── shows.php          # Browse TV shows
│   ├── detail.php         # Item detail + episodes
│   ├── player.php         # Video player
│   ├── search.php         # Search page
│   ├── favorites.php      # My List
│   ├── profile.php        # User profile
│   └── 404.php            # Not found
│
└── /assets
    ├── /css
    │   └── cinflix.css    # Custom styles
    ├── /js
    │   ├── api.js         # JS API client
    │   ├── ui.js          # UI helpers (cards, toasts, favs)
    │   ├── player.js      # Video player logic
    │   └── app.js         # Nav, search, PWA
    └── /images
        ├── icon-192.png   # PWA icon
        └── icon-512.png   # PWA icon
```

---

## ⌨️ Player Keyboard Shortcuts

| Key              | Action             |
|------------------|--------------------|
| `Space` / `K`    | Play / Pause       |
| `←` / `→`        | Skip ±10 seconds   |
| `↑` / `↓`        | Volume ±10%        |
| `M`              | Toggle mute        |
| `F`              | Toggle fullscreen  |

---

## 🔒 Security Notes

- All PHP inputs are sanitized with `htmlspecialchars` + `strip_tags`
- Session cookies use `HttpOnly` + `SameSite=Lax`
- Jellyfin tokens stored server-side in PHP session, never exposed in HTML source
- HTTPS recommended — uncomment the redirect in `.htaccess`
- `config.php` is protected from direct access via `.htaccess`

---

## 🔧 Nginx Config (Alternative)

```nginx
location /cinflix/ {
    try_files $uri $uri/ /cinflix/index.php?$query_string;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 📊 PWA Analytics

Basic page view analytics are stored in `localStorage` under the key `cf_analytics`. View them in browser dev tools:

```javascript
JSON.parse(localStorage.getItem('cf_analytics'))
```

---

## 🎨 Customization

- **Colors**: Edit CSS variables in `assets/css/cinflix.css` (`:root` block)
- **Brand name**: Change `APP_NAME` in `config.php`
- **Jellyfin URL**: Change `JELLYFIN_URL` in `config.php`

---

## 📝 License

MIT — Personal use. Cinflix connects to your own Jellyfin server and does not distribute any media.
