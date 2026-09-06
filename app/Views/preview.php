<?php
$fid = (int) $file['id'];
$raw = base_url('file/'.$fid.'/raw');
?>
<div class="d-flex align-items-center mb-3 gap-2 flex-wrap">
  <a class="btn btn-link text-decoration-none p-0 me-2" href="<?= base_url('file/'.$fid) ?>"><i class="bi bi-arrow-left"></i> 返回</a>
  <i class="<?= file_icon($file['extension']) ?> fh-file-icon"></i>
  <span class="fw-semibold"><?= e($file['name']) ?></span>
  <a class="btn btn-outline-secondary btn-sm ms-auto" href="<?= base_url('file/'.$fid.'/download') ?>"><i class="bi bi-download"></i> 下载</a>
</div>

<div class="card p-3">
  <?php switch ($ptype):
    case 'image': ?>
      <div class="text-center"><img src="<?= $raw ?>" alt="" class="img-fluid" style="max-height:80vh"></div>
      <?php break; ?>
    <?php case 'pdf': ?>
      <iframe src="<?= $raw ?>" style="width:100%;height:80vh;border:0"></iframe>
      <?php break; ?>
    <?php case 'video': ?>
      <div class="text-center"><video src="<?= $raw ?>" controls style="max-width:100%;max-height:80vh"></video></div>
      <?php break; ?>
    <?php case 'audio': ?>
      <div class="text-center py-5"><audio src="<?= $raw ?>" controls></audio></div>
      <?php break; ?>
    <?php case 'text': ?>
      <pre id="textPreview" class="bg-light p-3 rounded" style="white-space:pre-wrap;word-break:break-word;max-height:80vh;overflow:auto">加载中…</pre>
      <script>
      fetch('<?= $raw ?>').then(r=>r.text()).then(t=>{
        document.getElementById('textPreview').textContent=t;
      }).catch(()=>{document.getElementById('textPreview').textContent='读取失败';});
      </script>
      <?php break; ?>
    <?php case 'docx': ?>
      <div id="docxContainer" class="p-2">Word 文档加载中…</div>
      <script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.0/dist/docx-preview.min.js"></script>
      <script>
      (function(){
        var c=document.getElementById('docxContainer');
        c.innerHTML='<div class="text-muted">加载中…</div>';
        fetch('<?= $raw ?>').then(function(r){return r.blob();}).then(function(b){
          window.docx.renderAsync(b, c, null, {className:'docx', inWrapper:true}).catch(function(){c.textContent='该 Word 文档无法在线预览，请下载查看。';});
        }).catch(function(){c.textContent='加载失败';});
      })();
      </script>
      <?php break; ?>
    <?php case 'xlsx': ?>
      <div class="table-responsive"><div id="sheet">表格加载中…</div></div>
      <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
      <script>
      fetch('<?= $raw ?>').then(function(r){return r.arrayBuffer();}).then(function(ab){
        var wb=XLSX.read(new Uint8Array(ab), {type:'array'});
        var el=document.getElementById('sheet');
        el.innerHTML='';
        var sel=document.createElement('select'); el.appendChild(sel);
        wb.SheetNames.forEach(function(n,i){
          var o=document.createElement('option'); o.value=n; o.textContent=n; if(i===0)o.selected=true; sel.appendChild(o);
        });
        function render(){el.querySelectorAll('table').forEach(t=>t.remove());var html=XLSX.utils.sheet_to_html(wb.Sheets[sel.value]);var w=document.createElement('div');w.innerHTML=html;w.querySelector('table').className='table table-sm table-bordered';el.appendChild(w);}
        sel.addEventListener('change',render);render();
      }).catch(function(){document.getElementById('sheet').textContent='加载失败';});
      </script>
      <?php break; ?>
    <?php default: ?>
      <div class="text-center text-muted py-5">
        <i class="bi bi-file-earmark-lock fs-1"></i>
        <p class="mt-3">该格式（<?= e(strtoupper($file['extension'])) ?>）暂不支持在线预览。</p>
        <a class="btn btn-primary" href="<?= base_url('file/'.$fid.'/download') ?>">下载到本地查看</a>
      </div>
  <?php endswitch; ?>
</div>
