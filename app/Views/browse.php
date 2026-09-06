<?php
$pid = (int)$project['id'];
$here = $currentFolder ? 'folder/'.$currentFolder['id'] : 'project/'.$pid;
?>
<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb mb-0">
    <li class="breadcrumb-item"><a href="<?= base_url('') ?>">首页</a></li>
    <li class="breadcrumb-item"><a href="<?= base_url('project/'.$pid) ?>"><?= e($project['name']) ?></a></li>
    <?php foreach ($breadcrumb as $b): ?>
      <li class="breadcrumb-item"><a href="<?= base_url('folder/'.$b['id']) ?>"><?= e($b['name']) ?></a></li>
    <?php endforeach; ?>
    <?php if ($currentFolder): ?>
      <li class="breadcrumb-item active" aria-current="page"><?= e($currentFolder['name']) ?></li>
    <?php endif; ?>
  </ol>
</nav>

<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
  <div class="btn-group">
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="bi bi-upload"></i> 上传文件</button>
    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#folderModal"><i class="bi bi-folder-plus"></i> 新建文件夹</button>
  </div>
  <?php if ($currentFolder): ?>
    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#renameFolderModal"><i class="bi bi-pencil"></i> 重命名</button>
    <form method="post" action="<?= base_url('folder/'.$currentFolder['id'].'/delete') ?>" class="d-inline" onsubmit="return confirm('删除文件夹将连同其中所有文件与子文件夹一起删除，确定？')">
      <?= csrf_field() ?>
      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> 删除文件夹</button>
    </form>
  <?php else: ?>
    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editProjectModal"><i class="bi bi-gear"></i> 编辑项目</button>
    <form method="post" action="<?= base_url('project/'.$pid.'/delete') ?>" class="d-inline" onsubmit="return confirm('删除项目将删除其下所有文件，确定？')">
      <?= csrf_field() ?>
      <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> 删除项目</button>
    </form>
  <?php endif; ?>
</div>

<?php if (!$folders && !$files): ?>
  <div class="card p-5 text-center text-muted">这里是空的，点「上传文件」或「新建文件夹」开始整理吧。</div>
<?php endif; ?>

<?php if ($folders): ?>
  <h6 class="text-muted mt-2">文件夹</h6>
  <div class="row g-2 mb-3">
    <?php foreach ($folders as $f): ?>
      <div class="col-6 col-md-3 col-lg-2">
        <a class="card p-3 text-decoration-none text-reset d-block" href="<?= base_url('folder/'.$f['id']) ?>">
          <i class="bi bi-folder-fill text-warning fs-4"></i>
          <div class="small text-truncate"><?= e($f['name']) ?></div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($files): ?>
  <h6 class="text-muted">文件</h6>
  <div class="card">
    <table class="table table-hover mb-0 align-middle">
      <tbody>
      <?php foreach ($files as $f):
        $pt = preview_type($f['extension']); ?>
        <tr>
          <td style="width:42px"><i class="<?= file_icon($f['extension']) ?> fh-file-icon"></i></td>
          <td>
            <a class="text-decoration-none" href="<?= base_url('file/'.$f['id']) ?>"><?= e($f['name']) ?></a>
            <?php if ($f['note']): ?><div class="text-muted small text-truncate" style="max-width:380px"><?= e($f['note']) ?></div><?php endif; ?>
          </td>
          <td class="text-muted small d-none d-md-table-cell"><?= format_bytes($f['size']) ?></td>
          <td class="text-muted small d-none d-lg-table-cell"><?= time_ago(strtotime($f['updated_at'])) ?></td>
          <td class="text-end">
            <?php if ($pt !== 'download'): ?>
              <a class="btn btn-sm btn-outline-primary" href="<?= base_url('file/'.$f['id'].'/preview') ?>" title="预览"><i class="bi bi-eye"></i></a>
            <?php endif; ?>
            <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('file/'.$f['id'].'/download') ?>" title="下载"><i class="bi bi-download"></i></a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= base_url('file/upload') ?>" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="project_id" value="<?= $pid ?>">
      <input type="hidden" name="folder_id" value="<?= (int)$folderId ?>">
      <div class="modal-header"><h6 class="modal-title">上传文件</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input class="form-control" type="file" name="files[]" multiple required>
        <div class="text-muted small mt-2">可多选。同名文件会被作为新版本归档（保留历史版本可回退）。</div>
      </div>
      <div class="modal-footer"><button class="btn btn-primary">上传</button></div>
    </form>
  </div>
</div>

<div class="modal fade" id="folderModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= base_url('folder') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="project_id" value="<?= $pid ?>">
      <input type="hidden" name="folder_id" value="<?= (int)$folderId ?>">
      <div class="modal-header"><h6 class="modal-title">新建文件夹</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body"><label class="form-label small">名称</label><input class="form-control" name="name" required></div>
      <div class="modal-footer"><button class="btn btn-primary">创建</button></div>
    </form>
  </div>
</div>

<?php if ($currentFolder): ?>
<div class="modal fade" id="renameFolderModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= base_url('folder/'.$currentFolder['id']) ?>">
      <?= csrf_field() ?>
      <div class="modal-header"><h6 class="modal-title">重命名文件夹</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body"><label class="form-label small">名称</label><input class="form-control" name="name" value="<?= e($currentFolder['name']) ?>" required></div>
      <div class="modal-footer"><button class="btn btn-primary">保存</button></div>
    </form>
  </div>
</div>
<?php else: ?>
<div class="modal fade" id="editProjectModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= base_url('project/'.$pid) ?>">
      <?= csrf_field() ?>
      <div class="modal-header"><h6 class="modal-title">编辑项目</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <label class="form-label small">名称</label><input class="form-control mb-3" name="name" value="<?= e($project['name']) ?>" required>
        <label class="form-label small">描述</label><input class="form-control mb-3" name="description" value="<?= e($project['description']) ?>">
        <label class="form-label small">颜色</label><input type="color" class="form-control form-control-color" name="color" value="<?= e($project['color']) ?>">
      </div>
      <div class="modal-footer"><button class="btn btn-primary">保存</button></div>
    </form>
  </div>
</div>
<?php endif; ?>