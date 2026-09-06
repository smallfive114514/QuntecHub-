<?php
class ProjectController extends Controller
{
    public function show(int $id): void
    {
        $this->requireAuth();
        $project = Project::find($id);
        if (!$project) {
            flash('error', '项目不存在。');
            redirect('');
        }
        $this->renderBrowse($project, 0, null);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            flash('error', '项目名称不能为空。');
            redirect('');
        }
        Project::create([
            'name'        => safe_name($name),
            'description' => mb_substr(trim($_POST['description'] ?? ''), 0, 500),
            'color'       => preg_match('/^#[0-9a-fA-F]{3,8}$/', $_POST['color'] ?? '') ? $_POST['color'] : '#6f6fff',
            'sort'        => 0,
        ]);
        flash('success', '项目已创建。');
        redirect('');
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $project = Project::find($id);
        if (!$project) {
            redirect('');
        }
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            Project::update($id, [
                'name'        => safe_name($name),
                'description' => mb_substr(trim($_POST['description'] ?? ''), 0, 500),
                'color'       => preg_match('/^#[0-9a-fA-F]{3,8}$/', $_POST['color'] ?? '') ? $_POST['color'] : '#6f6fff',
            ]);
        }
        flash('success', '项目已更新。');
        redirect('project/'.$id);
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $project = Project::find($id);
        if (!$project) {
            redirect('');
        }
        $files = Database::run('SELECT id, storage_path FROM files WHERE project_id = ?', [$id])->fetchAll();
        foreach ($files as $f) {
            $this->removeDiskFile($f['storage_path']);
            $vers = Database::run('SELECT storage_path FROM file_versions WHERE file_id = ?', [$f['id']])->fetchAll();
            foreach ($vers as $v) {
                $this->removeDiskFile($v['storage_path']);
            }
            Database::run('DELETE FROM file_versions WHERE file_id = ?', [$f['id']]);
            Database::run('DELETE FROM file_tags WHERE file_id = ?', [$f['id']]);
        }
        Database::run('DELETE FROM files WHERE project_id = ?', [$id]);
        Database::run('DELETE FROM folders WHERE project_id = ?', [$id]);
        Project::delete($id);
        $dir = rtrim(upload_dir(), '/').'/projects/'.$id;
        if (is_dir($dir)) {
            $this->rrmdir($dir);
        }
        flash('success', '项目已删除。');
        redirect('');
    }

    protected function renderBrowse(array $project, int $folderId, ?array $currentFolder): void
    {
        $folders = Folder::children((int) $project['id'], $folderId);
        $files   = FileModel::inFolder((int) $project['id'], $folderId);
        $breadcrumb = $currentFolder ? Folder::breadcrumb((int) $currentFolder['id']) : [];

        $this->view('browse', [
            'title'         => e($project['name']).' · QuntecHub',
            'project'       => $project,
            'currentFolder' => $currentFolder,
            'folderId'      => $folderId,
            'folders'       => $folders,
            'files'         => $files,
            'breadcrumb'    => $breadcrumb,
        ]);
    }

    private function removeDiskFile(string $rel): void
    {
        $abs = rtrim(upload_dir(), '/').'/'.ltrim($rel, '/');
        if ($rel !== '' && is_file($abs)) {
            @unlink($abs);
        }
    }

    private function rrmdir(string $dir): void
    {
        foreach (glob($dir.'/*') ?: [] as $f) {
            is_dir($f) ? $this->rrmdir($f) : @unlink($f);
        }
        @rmdir($dir);
    }
}