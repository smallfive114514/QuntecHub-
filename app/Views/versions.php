<?php
$fid = (int) $file['id'];
?>
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="<?= base_url('') ?>">首页</a></li>
    <?php if ($project): ?>
      <li class="breadcrumb-item"><a href="<?= base_url('project/'.(int)$project['id']) ?>"><?= e($project['name']) ?></a></li>
    <?php endif; ?>
    <li class="breadcrumb-item"><a href="<?= base_url('file/'.$fid) ?>"><?= e($file['name']) ?></a></li>
    <li class="breadcrumb-item active">版本历史</li>
  </ol>
</nav>

<div class="d-flex align-items-center mb-3">
  <h5 class="mb-0">版本历史</h5>
  <button class="btn btn-sm btn-primary ms-3" data-bs-toggle="modal" data-bs-target="#newVersion"><i class="bi bi-upload"></i> 上传新版本</button>
</div>

<div class="card mb-3">
  <div class="card-body d-flex align-items-center">
    <i class="<?= file_icon($file['extension']) ?> fh-file-icon me-3"></i>
    <div>
      <div class="fw-semibold"><?= e($file['name']) ?></div>
      <div class="text-muted small">当前版本 · <?= format_bytes($file['size']) ?> · <?= time_ago(strtotime($file['updated_at'])) ?></div>
    </div>
    <div class="ms-auto d-flex gap-2">
      <a class="btn btn-sm btn-primary" href="<?= base_url('file/'.$fid.'/preview') ?>">预览</a>
      <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('file/'.$fid.'/download') ?>">下载</a>
    </div>
  </div>
</div>

<?php if (!$versions): ?>
  <div class="card p-4 text-center text-muted small">暂无历史版本</div>
<?php else: ?>
  <div class="card">
    <table class="table table-hover align-middle mb-0">
      <thead><tr><th>版本</th><th>大小</th><th>备注</th><th class="d-none d-md-table-cell">时间</th><th class="text-end">操作</th></tr></thead>
      <tbody>
      <?php foreach ($versions as $v): ?>
        <tr>
          <td>#<?= (int)$v['version_no'] ?></td>
          <td class="text-muted small"><?= format_bytes($v['size']) ?></td>
          <td class="small"><?= e($v['remark']) ?></td>
          <td class="text-muted small d-none d-md-table-cell"><?= date('Y-m-d H:i', strtotime($v['created_at'])) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('file/'.$fid.'/version/'.$v['id'].'/download') ?>">下载</a>
            <form method="post" action="<?= base_url('file/'.$fid.'/version/'.$v['id'].'/revert') ?>" class="d-inline" onsubmit="return confirm('回退后当前版本会被归档为历史版本，确定？')">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-outline-primary">回退</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<div class="modal fade" id="newVersion" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= base_url('file/'.$fid.'/version') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="modal-header"><h6 class="modal-title">上传新版本</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input class="form-control" type="file" name="file" required>
        <div class="text-muted small mt-2">新版本会替换当前文件，当前版本自动归档为历史版本，可随时回退。</div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary">上传</button></div>
    </form>
  </div>
</div>
