# QuntecHub · Project File Management System

[![PHP 8.0+](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php)](https://www.php.net/)
[![MySQL 5.6+](https://img.shields.io/badge/MySQL-5.6%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)

> 🌐 **Project by [Quntec (群星云)](https://cloud.quntec.cn)** · Developed by 小伍
>
> 🚀 Built on **FileHub** — a lightweight PHP file management foundation

A lightweight, single-user project file management system built with native PHP and MySQL. No frameworks, no Composer — just upload and run. Perfect for individuals or small teams who need a self-hosted document hub with version control.

> Language:
[English](./README.md) | [简体中文](./README.zh.md)

> English Development README of Baota Panel：[README_Deployment.md](README_Deployment.md)

> Chinese Development README of Baota Panel：[README_Deployment.zh.md](README_Deployment.zh.md)
---

## About QuntecHub

QuntecHub is a community-driven fork of the **FileHub** project, maintained by the Quntec (群星云) team. We add features, polish the UI, and provide deployment support for Chinese users — especially those using the BT Panel (宝塔面板).

- **Organization**: Quntec / 群星云
- **Lead Developer**: 小伍
- **Support**: [cloud.quntec.cn](https://cloud.quntec.cn) — register and submit a support ticket
- **Most code generated with AI assistance**

---

## Features

- 📁 Project-based organization — organize files into projects and nested folders
- 📝 File version history — automatic versioning when uploading files with the same name; revert anytime
- 👁️ Online preview — images, PDF, text, video, audio, Word (.docx), Excel (.xlsx) — all viewable in the browser
- 🏷️ Tags & notes — tag files with custom labels and add notes; search across names, notes, and tags
- 🔍 Global search — find files by name, note content, or tag name
- 📊 Dashboard — overview of projects, recent files, and storage usage
- 📱 Responsive UI — built with Bootstrap 5, works on desktop and mobile
- 🔐 Secure by design — PDO prepared statements, CSRF protection, path traversal prevention
- 🚀 Web installer — fill in DB credentials and admin account, done in 30 seconds

## Online Preview Support

| Type | Formats | How |
|------|---------|-----|
| Image | jpg, png, gif, webp, bmp, svg | Native `<img>` |
| PDF | pdf | Native `<iframe>` |
| Text | txt, md, csv, json, xml, yml, log… | Fetch + `<pre>` |
| Video | mp4, webm, ogg | Native `<video>` |
| Audio | mp3, wav, m4a | Native `<audio>` |
| Word | docx | [docx-preview](https://github.com/VolodymyrBaydalka/docxjs) (client-side) |
| Excel | xlsx, xls | [SheetJS](https://sheetjs.com/) (client-side) |
| Other | — | Download to view |

## Requirements

- **PHP** 8.0 or higher (with `pdo_mysql`, `mbstring` extensions)
- **MySQL** 5.6+ / MariaDB 10.0+
- **Web server**: Apache (with `mod_rewrite`) or Nginx
- Browser: any modern browser (Chrome, Firefox, Safari, Edge)

## Installation

### 1. Download

Download the latest release zip and extract it to your web server's document root.

### 2. Set web root (important!)

Point your web server's document root to the `public/` directory. This keeps `app/`, `config/`, and other sensitive directories outside the web-accessible path.

### 3. URL rewriting

**Nginx:**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Prevent PHP execution in uploads
location ~* ^/uploads/.*\.(php|phtml|phar|pht|php3|php4|php5|php7)$ {
    deny all;
}
```

**Apache:** Already included in `public/.htaccess` — just make sure `mod_rewrite` is enabled.

### 4. Create a database

Create an empty MySQL database (and a database user with full privileges on it).

### 5. Set permissions

Make these directories writable by the web server (`www-data` on Debian/Ubuntu, `www` on BT Panel):
```
config/          # for writing config.php during install
public/uploads/  # for uploaded files
```

### 6. Run the installer

Open your browser and navigate to your domain. You'll be redirected to the install wizard. Fill in:
- Database host, port, name, username, password
- Admin username and password (min 6 chars)

Click **Install** — the system will create tables, set up the admin account, and write `config/config.php`.

### 7. Done

Log in with your admin credentials and start using QuntecHub!

> 💡 **BT Panel (宝塔面板) users** — check out the step-by-step guide with screenshots: [README_部署.md](README_部署.md) (Chinese)

## Directory Structure

```
quntechub/
├── app/
│   ├── Controllers/    # Request handlers (10 controllers)
│   ├── Models/         # Data access layer (6 models)
│   ├── Views/          # PHP templates (10 views)
│   ├── core/           # Framework core (Router, Model, Controller, Database, helpers)
│   ├── bootstrap.php   # App bootstrap
│   └── routes.php      # Route definitions
├── config/
│   └── config.sample.php
├── public/             # ← Web server document root
│   ├── uploads/        # Uploaded files (stored by project/year/month)
│   ├── .htaccess
│   └── index.php       # Entry point
├── install.sql         # Database schema
├── LICENSE             # MIT License
├── README.md           # This file
└── README_部署.md      # Chinese BT Panel deployment guide
```

## How Versioning Works

When you upload a file that has the same name as an existing file in the same folder:
1. The current file is automatically archived as a new version in `file_versions`
2. The newly uploaded file becomes the current version
3. You can view all versions, download any version, or revert to any previous version
4. When you revert, the current version is also archived (so you can "undo" a revert)

## Security

- All database queries use PDO prepared statements — no SQL injection
- All user output is escaped with `htmlspecialchars()` — no XSS
- CSRF tokens on all POST forms
- Upload filenames are sanitized; path traversal is prevented
- Uploads directory blocks PHP execution via `.htaccess` / Nginx rule
- Admin passwords are hashed with `password_hash()` (bcrypt)
- Sessions use `httponly` and `samesite=Lax` cookie flags

## Configuration

Edit `config/config.php` after installation:

```php
return [
    'db' => [
        'host'    => 'localhost',
        'port'    => 3306,
        'name'    => 'quntechub',
        'user'    => 'your_db_user',
        'pass'    => 'your_db_pass',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name'       => 'QuntecHub 项目文件管理系统',
        'upload_dir' => '/path/to/public/uploads',
        'max_size'   => 0,        // 0 = unlimited (PHP limits still apply)
        'allow_ext'  => [],       // [] = allow all extensions
    ],
    'installed' => true,
];
```

## Development

No build step required. Just edit PHP files and refresh.

```bash
# Run locally with PHP's built-in server
php -S 127.0.0.1:8090 -t public public/index.php
```

## Contributing

Contributions are welcome! Feel free to:
- Report bugs or suggest features by opening an issue
- Submit pull requests with improvements
- Translate the UI into more languages

### Roadmap Ideas
- [ ] Multi-user support with role-based access
- [ ] File sharing via public links
- [ ] Recycle bin (soft delete)
- [ ] Bulk operations (batch delete, batch tag)
- [ ] Drag & drop upload
- [ ] Dark mode
- [ ] WebDAV support

## Support

If you encounter issues, you can:
1. Open an issue on GitHub
2. Visit [cloud.quntec.cn](https://cloud.quntec.cn), register an account, and submit a technical support ticket

## License

MIT License — see [LICENSE](LICENSE) for details.

Built on **FileHub** — a lightweight PHP file management system. Modified and maintained by the Quntec team.

---

Made with ❤️ by the Quntec / 群星云 team.
