# QuntecHub · BT Panel Deployment Guide

Beginner-friendly, all GUI operations — no command line needed.
Server environment: **BT Panel 13.0.0 + PHP 8.0 + MySQL 5.6**.

**Project**: Quntec / 群星云
**Developer**: 小伍
**Support**: Visit [cloud.quntec.cn](https://cloud.quntec.cn), register an account, and submit a technical support ticket.

> Language:
[English](./README.md) | [简体中文](./README.zh.md)

---

## Step 1: Install Software Environment

In the BT Panel "Software Store" (软件商店), install:

- **Nginx** (any 1.20+ version — usually pre-installed)
- **PHP 8.0**
- **MySQL 5.6** (search "MySQL" in the software store, pick version 5.6)

> If PHP 8.0 isn't installed, go to **Software Store → PHP** and install version 8.0.

---

## Step 2: Create a Website

1. In the left sidebar, click **Website (网站)** → **Add site (添加站点)**.
2. **Domain**: enter your domain (use your server IP if you don't have a domain yet).
3. **Root directory**: keep the default `/www/wwwroot/your-domain` for now.
4. **PHP version**: select **PHP 8.0**.
5. **Database**: **skip it for now** (we'll create it separately so you can easily remember the credentials). Click **Submit (提交)**.

---

## Step 3: Upload Source Code

1. Upload `quntechub.zip` to your site's root directory (use BT Panel **Files (文件)** → navigate to `/www/wwwroot/your-domain` → upload).
2. Right-click the zip file → **Extract (解压)** to the current directory. After extraction, you should see `public`, `app`, `config`, `install.sql`, and other folders in the root.
3. Keep all extracted files at the site root (the `public` folder should be directly in the root directory).

---

## Step 4: Set the Running Directory (Important!)

1. Go to **Website (网站)** → your site → **Settings (设置)** → **Website directory (网站目录)**.
2. Change **Running directory (运行目录)** to `/public` and save.

   - This prevents sensitive directories like `config` and `app` from being directly accessed from the internet — much more secure.

---

## Step 5: Set URL Rewrite Rules (Nginx)

Go to **Website (网站)** → your site → **Settings (设置)** → **URL rewrite (伪静态)**, paste the following content, and save:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Block PHP execution inside the uploads directory
location ~* ^/uploads/.*\.(php|phtml|phar|pht|php3|php4|php5|php7)$ {
    deny all;
}
```

> If you're using Apache instead of Nginx, you can skip this step — the included `.htaccess` file in the root directory takes care of it automatically.

---

## Step 6: Create a Database

1. In BT Panel, go to **Database (数据库)** → **Add database (添加数据库)**.
2. **Database name**: anything you like, e.g. `quntechub`.
3. **Username & password**: **write these down** — you'll need them during installation.
4. **Access permission**: local only is fine.
5. Click **Submit (提交)**. The database will be created automatically (the installer also tries to create it as a fallback).

---

## Step 7: Set Directory Permissions

In BT Panel **Files (文件)**, navigate to your site root:

1. Right-click the `config` folder → **Permissions (权限)** → set to `755`, set owner to `www`.
2. Right-click the `public/uploads` folder → **Permissions (权限)** → set to `755`, set owner to `www` (required for file uploads).

> If you're not sure, just set both directories to `755` with owner `www`.

---

## Step 8: Increase Upload Limits (for videos / large files)

In BT Panel, go to **Software Store → PHP 8.0 → Settings → Configuration modification (配置修改)**:

- Change `upload_max_filesize` to `512M` (or larger)
- Change `post_max_size` to `512M`
- Change `max_execution_time` to `300`

Save and restart PHP.

---

## Step 9: Run the Install Wizard

1. Open your browser and go to `http://your-domain/` — you'll be automatically redirected to the install page.
2. Fill in the credentials from Step 6: database host (usually `localhost`), port `3306`, database name, username, password.
3. Set an admin username and password (password must be at least 6 characters).
4. Click **Install (开始安装)**. The system will automatically create tables and write the config file.
5. When finished, you'll be redirected to the login page — log in with your admin credentials and start using QuntecHub!

---

## FAQ

**Q: The page shows 500 / blank when I open the domain?**
A: Check that PHP version is 8.0, the running directory is set to `/public`, and the URL rewrite rules are pasted correctly.

**Q: The install page says "failed to write config"?**
A: Set the `config` directory permissions to `755` with owner `www`.

**Q: Large file uploads fail?**
A: Increase PHP upload limits as described in Step 8.

**Q: I want to switch to a different database?**
A: Delete `config/config.php` and revisit your domain to re-run the installer.

**Q: I forgot my admin password?**
A: Use BT Panel's phpMyAdmin to open the `quntechub` database, then update `password_hash` in the `admin` table with a new bcrypt hash. Alternatively, delete `config/config.php` and reinstall (this will reset your account).
