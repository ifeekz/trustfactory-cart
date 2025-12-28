# Virtual Hosts Setup

This setup closely mirrors a real production environment.

> **Note:** WampServer is a free and open-source web server that runs on Windows and Linux. It is available for download from [WampServer](https://www.wampserver.com/en/).

## [WINDOWS] WampServer + Virtual Hosts

Install WampServer and configure virtual hosts for `trustfactory-cart.test`.

### 🧩 Step 1: Create Virtual Hosts in WampServer

1. Open **WampServer**
2. Click the **Wamp icon** in the system tray
3. Go to:

    ```ngnx
    Your Virtual Hosts → Virtual Hosts Management
    ```

4. Click **Add a Virtual Host**
    - Name of the Virtual Host: `trustfactory-cart.test`
    - Complete absolute path: `C:\wamp64\www\trustfactory-cart\public`
    - Click *Start the creation of the VirtualHost*
    - Restart Apache

🧩 Step 2: Verify Virtual Hosts

Open your browser and visit `http://trustfactory-cart.test:8080`


If configured correctly:

`trustfactory-cart.test:8080` should load the app

## 🔐 Notes on Authentication & CORS

- Communication is established using session-based authentication
- Laravel Sanctum is configured to treat `trustfactory-cart.test:8080` as a stateful domain
- Cookies are shared securely across requests
- CORS is configured to allow credentials

Relevant backend environment configuration:

```env
APP_URL=http://trustfactory-cart.test:8080
SANCTUM_STATEFUL_DOMAINS=trustfactory-cart.test
SESSION_DOMAIN=.test
```

## [Linux / macOS] LAMP + Virtual Hosts

On Linux or macOS, the project can be run using **Apache/Nginx virtual hosts** or **Laravel Valet**.

### 📁 Project Paths (Example)

Adjust paths based on where you cloned the repository.

```text
/home/your-user/trustfactory-cart/public
```

(macOS example: `/Users/your-user/...`)

### 🧩 Option A: Apache Virtual Hosts

**1️⃣ Configure Apache Virtual Hosts**

Edit your Apache virtual hosts file:

- **Ubuntu / Debian**

    ```bash
    /etc/apache2/sites-available/trustfactory-cart.conf
    ```

- **macOS (Homebrew Apache)**

    ```swift
    /usr/local/etc/httpd/extra/httpd-vhosts.conf
    ```

Add the following:

```apache
<VirtualHost *:80>
    ServerName limitorder.test
    DocumentRoot "/path/to/trustfactory-cart/public"

    <Directory "/path/to/trustfactory-cart/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Enable required Apache modules:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

(macOS Homebrew users may need `brew services restart httpd`)

### 🧩 Option B: Laravel Valet (macOS / Linux)

If you are using **Laravel Valet**, setup is simpler.

**1️⃣ Link the backend**

```bash
valet link trustfactory-cart
```

This will expose: `http://trustfactory-cart.test`

### 🧩 Step 2: Update Hosts File

Edit your hosts file:

```bash
sudo nano /etc/hosts
```

Add:

```bash
127.0.0.1 trustfactory-cart.test
```

Save and exit.

## ⚠️ Common Issues

- **403 or 419** errors usually indicate:
    - missing CSRF cookie
    - incorrect `SANCTUM_STATEFUL_DOMAINS`
    - incorrect `SESSION_DOMAIN`

- **CORS errors** usually indicate:
    - frontend domain not whitelisted
    - missing `withCredentials: true` on frontend requests

Restart Apache after any virtual host or configuration change.

### 🧠 Why This Setup

Using separate virtual hosts:

- Mirrors real-world deployments
- Makes CORS and authentication behavior explicit
- Avoids `localhost` edge cases
- Improves confidence in session-based auth correctness
