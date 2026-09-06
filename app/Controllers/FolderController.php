<?php
class FolderController extends ProjectController
{
    public function show(int $id): void
    {
        $this->requireAuth();
        $folder = Folder::find($id);
        if (!$folder) {
            flash('error', '文件夹不存在。');
            redirect('');
        }
        $project = Project::find($folder['project_id']);
        if (!$project) {
            redirect('');
        }
        $this->renderBrowse($project, (int) $folder['id'], $folder);
    }

    public function store(): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $projectId = (int) ($_POST['project_id'] ?? 0);
        $parentId  = (int) ($_POST['folder_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($projectId <= 0 || $name === '') {
            flash('error', '参数错误。');
            redirect('');
        }
        if (!Project::find($projectId)) {
            redirect('');
        }
        Folder::create([
            'project_id' => $projectId,
            'parent_id'  => $parentId,
            'name'       => safe_name($name),
        ]);
        flash('success', '文件夹已创建。');
        redirect($parentId > 0 ? 'folder/'.$parentId : 'project/'.$projectId);
    }

    public function update(int $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $folder = Folder::find($id);
        if (!$folder) {
            redirect('');
        }
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            Folder::update($id, ['name' => safe_name($name)]);
        }
        flash('success', '文件夹已重命名。');
        redirect('folder/'.$id);
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $folder = Folder::find($id);
        if (!$folder) {
            redirect('');
        }
        $projectId = (int) $folder['project_id'];
        $this->deleteFolderRecursive($id);
        flash('success', '文件夹已删除。');
        redirect('project/'.$projectId);
    }

    private function deleteFolderRecursive(int $folderId): void
    {
        $files = Database::run('SELECT id, storage_path FROM files WHERE folder_id = ?', [$folderId])->fetchAll();
        foreach ($files as $f) {
            $rel = $f['storage_path'];
            $abs = rtrim(upload_dir(), '/').'/'.ltrim($rel, '/');
            if ($rel !== '' && is_file($abs)) {
                @unlink($abs);
            }
            Database::run('DELETE FROM file_versions WHERE file_id = ?', [$f['id']]);
            Database::run('DELETE FROM file_tags WHERE file_id = ?', [$f['id']]);
            Database::run('DELETE FROM files WHERE id = ?', [$f['id']]);
        }
        $children = Database::run('SELECT id FROM folders WHERE parent_id = ?', [$folderId])->fetchAll();
        foreach ($children as $c) {
            $this->deleteFolderRecursive((int) $c['id']);
        }
        Folder::delete($folderId);
    }
}