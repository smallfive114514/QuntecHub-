# QuntecHub · 项目文件管理系统

[![PHP 8.0+](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php)](https://www.php.net/)
[![MySQL 5.6+](https://img.shields.io/badge/MySQL-5.6%2B-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com/)

> 🌐 **由 [Quntec（群星云）](https://cloud.quntec.cn) 出品** · 开发者：小伍
>
> 🚀 基于 **FileHub** 构建 —— 一个轻量级 PHP 文件管理基础

一个使用原生 PHP 和 MySQL 构建的轻量级、单用户项目文件管理系统。无需框架，无需 Composer —— 上传即可运行。非常适合需要具备版本控制的自托管文档中心的个人或小团队。

> 语言：
> [English](./README.md) | 简体中文

> 宝塔面板中文部署 README：[README_部署.md](README_部署.md)

---

## 关于 QuntecHub

QuntecHub 是 **FileHub** 项目的社区驱动分支，由 Quntec（群星云）团队维护。我们添加功能、优化 UI，并为中文用户——尤其是使用宝塔面板的用户——提供部署支持。

- **组织**：Quntec / 群星云
- **主要开发者**：小伍
- **支持**：[cloud.quntec.cn](https://cloud.quntec.cn) —— 注册并提交技术支持工单
- **大部分代码由 AI 辅助生成**

---

## 功能特性

- 📁 基于项目的组织方式 —— 将文件组织到项目和嵌套文件夹中
- 📝 文件版本历史 —— 上传同名文件时自动创建版本；可随时回滚
- 👁️ 在线预览 —— 图片、PDF、文本、视频、音频、Word（.docx）、Excel（.xlsx）—— 均可在浏览器中查看
- 🏷️ 标签与备注 —— 使用自定义标签标记文件并添加备注；可跨名称、备注和标签搜索
- 🔍 全局搜索 —— 按文件名、备注内容或标签名查找文件
- 📊 仪表盘 —— 项目、最近文件和存储使用情况概览
- 📱 响应式 UI —— 使用 Bootstrap 5 构建，适用于桌面端和移动端
- 🔐 安全设计 —— PDO 预处理语句、CSRF 防护、路径遍历防护
- 🚀 Web 安装程序 —— 填写数据库凭据和管理员账户，30 秒完成

## 在线预览支持

| 类型 | 格式 | 实现方式 |
|------|------|----------|
| 图片 | jpg, png, gif, webp, bmp, svg | 原生 `<img>` |
| PDF | pdf | 原生 `<iframe>` |
| 文本 | txt, md, csv, json, xml, yml, log… | Fetch + `<pre>` |
| 视频 | mp4, webm, ogg | 原生 `<video>` |
| 音频 | mp3, wav, m4a | 原生 `<audio>` |
| Word | docx | [docx-preview](https://github.com/VolodymyrBaydalka/docxjs)（客户端） |
| Excel | xlsx, xls | [SheetJS](https://sheetjs.com/)（客户端） |
| 其他 | — | 下载后查看 |

## 环境要求

- **PHP** 8.0 或更高版本（需启用 `pdo_mysql`、`mbstring` 扩展）
- **MySQL** 5.6+ / MariaDB 10.0+
- **Web 服务器**：Apache（需启用 `mod_rewrite`）或 Nginx
- 浏览器：任意现代浏览器（Chrome、Firefox、Safari、Edge）

## 安装

### 1. 下载

下载最新发行版 zip 并解压到 Web 服务器的文档根目录。

### 2. 设置网站根目录（重要！）

将 Web 服务器的文档根目录指向 `public/` 目录。这样可以将 `app/`、`config/` 等敏感目录置于 Web 可访问路径之外。

### 3. URL 重写

**Nginx：**
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# 阻止 uploads 目录中的 PHP 执行
location ~* ^/uploads/.*\.(php|phtml|phar|pht|php3|php4|php5|php7)$ {
    deny all;
}
```

**Apache：** 已包含在 `public/.htaccess` 中 —— 只需确保已启用 `mod_rewrite`。

### 4. 创建数据库

创建一个空的 MySQL 数据库（以及一个对该数据库拥有完整权限的数据库用户）。

### 5. 设置权限

使以下目录可被 Web 服务器写入（Debian/Ubuntu 上为 `www-data`，宝塔面板上为 `www`）：
```
config/          # 用于安装期间写入 config.php
public/uploads/  # 用于存放上传文件
```

### 6. 运行安装程序

打开浏览器并访问你的域名。你会被重定向到安装向导。填写：
- 数据库主机、端口、名称、用户名、密码
- 管理员用户名和密码（至少 6 个字符）

点击 **安装** —— 系统将创建数据表、设置管理员账户，并写入 `config/config.php`。

### 7. 完成

使用管理员凭据登录，开始使用 QuntecHub！

> 💡 **宝塔面板用户** —— 查看带截图的逐步指南：[README_部署.md](README_部署.md)（中文）

## 目录结构

```
quntechub/
├── app/
│   ├── Controllers/    # 请求处理器（10 个控制器）
│   ├── Models/         # 数据访问层（6 个模型）
│   ├── Views/          # PHP 模板（10 个视图）
│   ├── core/           # 框架核心（Router、Model、Controller、Database、helpers）
│   ├── bootstrap.php   # 应用引导文件
│   └── routes.php      # 路由定义
├── config/
│   └── config.sample.php
├── public/             # ← Web 服务器文档根目录
│   ├── uploads/        # 上传文件（按项目/年/月存储）
│   ├── .htaccess
│   └── index.php       # 入口文件
├── install.sql         # 数据库结构
├── LICENSE             # MIT 许可证
├── README.md           # 英文 README
├── README.zh.md        # 本文件（中文 README）
└── README_部署.md      # 宝塔面板中文部署指南
```

## 版本控制如何工作

当你上传的文件与同一文件夹中已有文件同名时：
1. 当前文件会自动归档为新版本，存入 `file_versions`
2. 新上传的文件成为当前版本
3. 你可以查看所有版本、下载任意版本，或回滚到任意历史版本
4. 回滚时，当前版本也会被归档（因此你可以“撤销”回滚）

## 安全性

- 所有数据库查询均使用 PDO 预处理语句 —— 无 SQL 注入
- 所有用户输出均使用 `htmlspecialchars()` 转义 —— 无 XSS
- 所有 POST 表单均包含 CSRF 令牌
- 上传文件名会被清理；防止路径遍历
- 上传目录通过 `.htaccess` / Nginx 规则阻止 PHP 执行
- 管理员密码使用 `password_hash()`（bcrypt）哈希存储
- Session 使用 `httponly` 和 `samesite=Lax` Cookie 标志

## 配置

安装后编辑 `config/config.php`：

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
        'max_size'   => 0,        // 0 = 不限制（仍受 PHP 限制）
        'allow_ext'  => [],       // [] = 允许所有扩展名
    ],
    'installed' => true,
];
```

## 开发

无需构建步骤。只需编辑 PHP 文件并刷新。

```bash
# 使用 PHP 内置服务器在本地运行
php -S 127.0.0.1:8090 -t public public/index.php
```

## 贡献

欢迎贡献！你可以：
- 通过提交 issue 报告 bug 或建议功能
- 提交包含改进的 pull request
- 将 UI 翻译成更多语言

### 路线图构想
- [ ] 支持多用户和基于角色的访问控制
- [ ] 通过公开链接分享文件
- [ ] 回收站（软删除）
- [ ] 批量操作（批量删除、批量打标签）
- [ ] 拖拽上传
- [ ] 深色模式
- [ ] WebDAV 支持

## 支持

如果遇到问题，你可以：
1. 在 GitHub 上提交 issue
2. 访问 [cloud.quntec.cn](https://cloud.quntec.cn)，注册账户并提交技术支持工单

## 许可证

MIT 许可证 —— 详见 [LICENSE](LICENSE)。

基于 **FileHub** 构建 —— 一个轻量级 PHP 文件管理系统。由 Quntec 团队修改和维护。

---

由 Quntec / 群星云团队用 ❤️ 制作。