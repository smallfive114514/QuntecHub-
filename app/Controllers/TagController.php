<?php
class TagController extends Controller
{
    public function attach(int $id): void
    {
        $this->requireAuth();
        $this->requireCsrf();
        $file = FileModel::find($id);
        if (!$file) {
            redirect('');
        }
        $raw = trim($_POST['tags'] ?? '');
        $names = preg_split('/[,，]/', $raw);
        Tag::sync($id, $names ?: []);
        flash('success', '标签已更新。');
        redirect('file/'.$id);
    }
}