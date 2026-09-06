<?php
class SearchController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $q = trim($_GET['q'] ?? '');
        $results = $q !== '' ? FileModel::search($q) : [];
        $this->view('search', [
            'title'   => '搜索 · QuntecHub',
            'q'       => $q,
            'results' => $results,
            'tags'    => Tag::allUsed(),
        ]);
    }
}