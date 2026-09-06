<?php
class FileController extends Controller
{
    /** 文件详情 */
    public function show(int $id): void
    {
        $this->requireAuth();
        $file = FileModel::find($id);
        if (!$file) {
            flash('error', '文件不存在。');
            redirect('');
        }
        $project = Project::find($file['project_id']);
        $tags = FileModel::tags($id);
        $versions = Version::of($id);
        $this->view('file_detail', [
            'title'        => e($file['name']).' · QuntecHub',
            'file'         => $file,
            'project'      => $project,
            'tags'         => $tags,
            'versions'     => $versions,
            'ptype'        => preview_type($file['extension']),
            'folderOptions'=> $this->folderOptions((int) $file['project_id']),
        ]);
    }

    /** 上传（支持多文件、同名自动归档为版本） */
    public function upload(): void
    {
        $this->requireAuth();
        $this->requireCsrf();

        $projectId = (int) ($_POST['project_id'] ?? 0);
        $folderId  = (int) ($_POST['folder_id'] ?? 0);
        if (!Project::find($projectId)) {
            flash('error', '项目不存在。');
            redirect('');
        }
        if ($folderId > 0) {
            $folder = Folder::find($folderId);
            if (!$folder || (int) $folder['project_id'] !== $projectId) {
                $folderId = 0;
            }
        }

        $files = $_FILES['files'] ?? [];
        if (empty($files['name'])) {
            flash('error', '没有选择文件。');
            $this->back();
        }

        $allow = config('app.allow_ext');
        $maxSize = (int) config('app.max_size');
        $count = count((array) $files['name']);
        $ok = 0;

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            $orig  = $files['name'][$i];
            $tmp   = $files['tmp_name'][$i];
            $size  = (int) $files['size'][$i];
            $ext   = ext_of($orig);
            $mime  = (string) ($files['type'][$i]);

            if (!empty($allow) && !in_array($ext, $allow, true)) {
                continue;
            }
            if ($maxSize > 0 && $size > $maxSize) {
                continue;
            }

            $rel = make_storage_path($projectId, $orig);
            ensure_dir($rel);
            $abs = rtrim(upload_dir(), '/').'/'.$rel;

            if (!@move_uploaded_file($tmp, $abs)) {
                continue;
            }

            $existing = FileModel::sameName($projectId, $folderId, $orig);
            if ($existing) {
                Version::create([
                    'file_id'       => $existing['id'],
                    'version_no'    => Version::nextNo($existing['id']),
                    'size'          => $existing['size'],
                    'storage_path' => $existing['storage_path'],
                    'remark'        => '自动归档',
                ]);
                FileModel::update($existing['id'], [
                    'extension'     => $ext,
                    'mime'           => $mime,
                    'size'           => $size,
                    'storage_path'  => $rel,
                ]);
                $ok++;
            } else {
                $fid = FileModel::create([
                    'project_id'    => $projectId,
                    'folder_id'     => $folderId,
                    'name'          => safe_name($orig),
                    'extension'     => $ext,
                    'mime'          => $mime,
                    'size'          => $size,
                    'storage_path' => $rel,
                    'note'          => '',
                ]);
                Version::create([
                    'file_id'       => $fid,
                    'version_no'    => 1,
                    'size'          => $size,
                    'storage_path' => $rel,
                    'remark'        => '初始版本',
                ]);
                $ok++;
            }
        }

        if ($ok > 0) {
            flash('success', '已上传 '.$ok.' 个文件。');
        } else {
            flash('error', '上传失败，请检查文件大小/类型限制及宝塔 PHP 上传上限。');
        }
        $this->back();
    }

    /** 重命名 / 备注 */
    public function update(int $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $file = FileModel::find($id);
        if (!$file) {
            redirect('');
        }
        $data = [];
        $name = trim($_POST['name'] ?? '');
        if ($name !== '' && $name !== $file['name']) {
            $data['name'] = safe_name($name);
        }
        $note = trim($_POST['note'] ?? '');
        $data['note'] = mb_substr($note, 0, 1000);
        if ($data) {
            FileModel::update($id, $data);
        }
        flash('success', '已保存。');
        redirect('file/'.$id);
    }

    /** 移动文件 */
    public function move(int $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $file = FileModel::find($id);
        if (!$file) {
            redirect('');
        }
        $targetFolder = (int) ($_POST['folder_id'] ?? 0);
        $targetProject = (int) ($_POST['project_id'] ?? 0);

        if ($targetFolder > 0) {
            $f = Folder::find($targetFolder);
            if (!$f) {
                flash('error', '目标文件夹不存在。');
                redirect('file/'.$id);
            }
            FileModel::update($id, ['project_id' => $f['project_id'], 'folder_id' => $targetFolder]);
            flash('success', '已移动。');
            redirect('folder/'.$targetFolder);
        } elseif ($targetProject > 0) {
            if (!Project::find($targetProject)) {
                flash('error', '目标项目不存在。');
                redirect('file/'.$id);
            }
            FileModel::update($id, ['project_id' => $targetProject, 'folder_id' => 0]);
            flash('success', '已移动到项目根目录。');
            redirect('project/'.$targetProject);
        }
        redirect('file/'.$id);
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $file = FileModel::find($id);
        if (!$file) {
            redirect('');
        }
        $projectId = (int) $file['project_id'];
        $this->removeDiskFile($file['storage_path']);
        $vers = Database::run('SELECT storage_path FROM file_versions WHERE file_id = ?', [$id])->fetchAll();
        foreach ($vers as $v) {
            $this->removeDiskFile($v['storage_path']);
        }
        Database::run('DELETE FROM file_versions WHERE file_id = ?', [$id]);
        Database::run('DELETE FROM file_tags WHERE file_id = ?', [$id]);
        FileModel::delete($id);
        flash('success', '文件已删除。');
        redirect('project/'.$projectId);
    }

    /** 下载（附件） */
    public function download(int $id): void
    {
        $this->requireAuth();
        $file = FileModel::find($id);
        if (!$file) {
            http_response_code(404);
            exit('文件不存在');
        }
        $this->send($file, (bool) ($_GET['inline'] ?? false));
    }

    /** 原始流（用于预览 src） */
    public function raw(int $id): void
    {
        $this->requireAuth();
        $file = FileModel::find($id);
        if (!$file) {
            http_response_code(404);
            exit;
        }
        $this->send($file, true);
    }

    /** 在线预览页面 */
    public function preview(int $id): void
    {
        $this->requireAuth();
        $file = FileModel::find($id);
        if (!$file) {
            flash('error', '文件不存在。');
            redirect('');
        }
        $project = Project::find($file['project_id']);
        $this->view('preview', [
            'title'   => '预览 · '.e($file['name']),
            'file'    => $file,
            'project' => $project,
            'ptype'   => preview_type($file['extension']),
        ]);
    }

    private function send(array $file, bool $inline): void
    {
        $abs = rtrim(upload_dir(), '/').'/'.ltrim($file['storage_path'], '/');
        if (!is_file($abs)) {
            http_response_code(404);
            exit('文件不存在');
        }
        $disp = ($inline ? 'inline' : 'attachment').'; filename*=UTF-8\'\''.rawurlencode($file['name']);
        header('Content-Description: File Transfer');
        header('Content-Type: '.($file['mime'] ?: 'application/octet-stream'));
        header('Content-Disposition: '.$disp);
        header('Content-Length: '.filesize($abs));
        header('Cache-Control: private, max-age=0');
        if (ob_get_level()) {
            ob_end_clean();
        }
        $fp = fopen($abs, 'rb');
        while (!feof($fp) && connection_status() === CONNECTION_NORMAL) {
            echo fread($fp, 1024 * 1024);
            flush();
        }
        fclose($fp);
        exit;
    }

    private function removeDiskFile(string $rel): void
    {
        $abs = rtrim(upload_dir(), '/').'/'.ltrim($rel, '/');
        if ($rel !== '' && is_file($abs)) {
            @unlink($abs);
        }
    }

    private function back(): void
    {
        $back = $_POST['_back'] ?? ($_SERVER['HTTP_REFERER'] ?? '');
        if (is_string($back) && $back !== '') {
            header('Location: '.$back);
            exit;
        }
        redirect('');
    }

    private function folderOptions(int $projectId): array
    {
        $rows = Database::run('SELECT id, parent_id, name FROM folders WHERE project_id = ? ORDER BY name', [$projectId])->fetchAll();
        $byParent = [];
        foreach ($rows as $r) {
            $byParent[(int) $r['parent_id']][] = $r;
        }
        $out = [];
        $walk = function (int $parentId, string $prefix) use (&$walk, &$byParent, &$out): void {
            foreach ($byParent[$parentId] ?? [] as $r) {
                $out[] = ['id' => (int) $r['id'], 'label' => $prefix.$r['name']];
                $walk((int) $r['id'], $prefix.$r['name'].' / ');
            }
        };
        $walk(0, '');
        return $out;
    }
}