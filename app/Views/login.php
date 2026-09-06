<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>登录 · QuntecHub</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f4f5f9}.card{border:none;border-radius:14px}.brand{font-weight:700;letter-spacing:.5px;color:#4B3FE3}</style>
</head>
<body class="py-5">
<div class="container" style="max-width:400px">
  <div class="text-center mb-4">
    <div class="brand fs-3"><i class="bi bi-cloud"></i> QuntecHub</div>
    <div class="text-muted small">项目文件管理系统</div>
  </div>
  <div class="card shadow-sm p-4">
    <?php if (!empty($error)): ?><div class="alert alert-danger py-2"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= base_url('login') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($_GET['next'] ?? '') ?>">
      <label class="form-label small">用户名</label>
      <input class="form-control mb-3" name="username" autofocus required>
      <label class="form-label small">密码</label>
      <input class="form-control mb-4" type="password" name="password" required>
      <button class="btn btn-primary w-100">登录</button>
    </form>
  </div>
</div>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</body>
</html>
