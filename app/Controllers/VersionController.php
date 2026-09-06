<?php
class VersionController extends Controller
{
    public function index(int $id): void
    {
        $this->requireAuth();
        $file = FileModel::find($id);
        if (!$file) {
            flash('error', '文件不存在。');
            redirect('');
        }
        $project = Project::find($file['project_id']);
        $versions = Version::of($id);
        $this->view('versions', [
            'title'    => '版本历史 · '.e($file['name']),
            'file'     => $file,
            'project'  => $project,
            'versions' => $versions,
        ]);
    }

    public function uploadNew(int $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $file = FileModel::find($id);
        if (!$file) {
            redirect('');
        }
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            flash('error', '请选择文件。');
            redirect('file/'.$id.'/versions');
        }
        $orig = $_FILES['file']['name'];
        $size = (int) $_FILES['file']['size'];
        $ext  = ext_of($orig);
        $mime = (string) $_FILES['file']['type'];

        $rel = make_storage_path((int) $file['project_id'], $orig);
        ensure_dir($rel);
        $abs = rtrim(upload_dir(), '/').'/'.$rel;
        if (!@move_uploaded_file($_FILES['file']['tmp_name'], $abs)) {
            flash('error', '保存文件失败，请检查目录权限。');
            redirect('file/'.$id.'/versions');
        }
        Version::create([
            'file_id'       => $id,
            'version_no'    => Version::nextNo($id),
            'size'          => $file['size'],
            'storage_path' => $file['storage_path'],
            'remark'        => '更新前版本',
        ]);
        FileModel::update($id, [
            'size'          => $size,
            'extension'    => $ext,
            'mime'          => $mime,
            'storage_path' => $rel,
        ]);
        flash('success', '已上传新版本。');
        redirect('file/'.$id.'/versions');
    }

    public function revert(int $id, int $vid): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $file = FileModel::find($id);
        if (!$file) {
            redirect('');
        }
        $ver = Version::find($vid);
        if (!$ver || (int) $ver['file_id'] !== $id) {
            flash('error', '版本不存在。');
            redirect('file/'.$id.'/versions');
        }
        Version::create([
            'file_id'       => $id,
            'version_no'    => Version::nextNo($id),
            'size'          => $file['size'],
            'storage_path' => $file['storage_path'],
            'remark'        => '回退前版本',
        ]);
        FileModel::update($id, [
            'size'          => $ver['size'],
            'storage_path' => $ver['storage_path'],
        ]);
        flash('success', '已回退到该版本。');
        redirect('file/'.$id.'/versions');
    }

    public function download(int $id, int $vid): void
    {
        $this->requireAuth();
        $file = FileModel::find($id);
        if (!$file) {
            http_response_code(404);
            exit('文件不存在');
        }
        $ver = Version::find($vid);
        if (!$ver || (int) $ver['file_id'] !== $id) {
            http_response_code(404);
            exit('版本不存在');
        }
        $abs = rtrim(upload_dir(), '/').'/'.ltrim($ver['storage_path'], '/');
        if (!is_file($abs)) {
            http_response_code(404);
            exit('文件不存在');
        }
        $name = $file['name'];
        header('Content-Type: '.($file['mime'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename*=UTF-8\'\''.rawurlencode($name));
        header('Content-Length: '.filesize($abs));
        if (ob_get_level()) ob_end_clean();
        readfile($abs);
        exit;
    }
}