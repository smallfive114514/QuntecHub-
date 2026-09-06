<?php
?>
<h4 class="mb-3">搜索<?= $q !== '' ? '：'.e($q) : '' ?></h4>

<?php if ($tags): ?>
  <div class="mb-3">
    <span class="text-muted small me-2">常用标签：</span>
    <?php foreach ($tags as $t): if ($t['cnt']<=0) continue; ?>
      <a class="badge text-bg-light border text-decoration-none me-1" href="<?= base_url('search?q='.urlencode($t['name'])) ?>"><?= e($t['name']) ?> <span class="text-muted"><?= (int)$t['cnt'] ?></span></a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($q !== '' && !$results): ?>
  <div class="card p-4 text-center text-muted">没有找到匹配“<?= e($q) ?>”的文件</div>
<?php elseif ($results): ?>
  <div class="card">
    <table class="table table-hover align-middle mb-0">
      <tbody>
      <?php foreach ($results as $f): ?>
        <tr>
          <td style="width:42px"><i class="<?= file_icon($f['extension']) ?> fh-file-icon"></i></td>
          <td><a class="text-decoration-none" href="<?= base_url('file/'.$f['id']) ?>"><?= e($f['name']) ?></a>
            <?php if ($f['note']): ?><div class="text-muted small text-truncate" style="max-width:380px"><?= e($f['note']) ?></div><?php endif; ?>
          </td>
          <td class="text-muted small d-none d-md-table-cell"><?= format_bytes($f['size']) ?></td>
          <td class="text-muted small d-none d-lg-table-cell"><?= time_ago(strtotime($f['created_at'])) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
