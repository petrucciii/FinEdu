<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\CompanyNewsModel;
use App\Models\NewspaperModel;
use App\Models\NewsModel;

class NewsManagementController extends BaseController
{
    private function isAdmin(): bool
    {
        return $this->session->has('logged') && (int) $this->session->get('role_id') === 1;
    }


    public function index()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }


        $data = [
            'newspapers' => model(NewspaperModel::class)->listActive(),
            'companies' => model(CompanyModel::class)->where('active', 1)->orderBy('name', 'ASC')->findAll(),
            'adminSection' => true,
        ];

        echo view('templates/header');
        echo view('pages/admins/viewNewsManagement', $data);
        echo view('templates/footer');
    }

    public function search($query = '')
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $page = (int) ($this->request->getGet('page') ?? 1);
        $result = model(NewsModel::class)->searchAndPaginate((string) $query, $page);

        return $this->response->setJSON([
            'news' => $result['news'],
            'pagination' => $result['pager'],
        ]);
    }

    public function detail($newsId)
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $news = model(NewsModel::class)->findDetailForAdmin((int) $newsId);
        if (!$news) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        $isins = model(CompanyNewsModel::class)->getIsinsForNews((int) $newsId);
        $newspapers = model(NewspaperModel::class)->listActive();

        $body = $news['body'] ?? '';
        unset($news['body']);

        return $this->response->setJSON([
            'news' => $news,
            'body' => is_string($body) ? $body : (string) $body,
            'linked_isins' => $isins,
            'newspapers' => $newspapers,
        ]);
    }

    public function create()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $rules = [
            'headline' => 'required|max_length[255]',
            'subtitle' => 'required|max_length[255]',
            'author' => 'required|max_length[50]',
            'newspaper_id' => 'required|is_natural_no_zero',
            'body' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('alert', 'Dati non validi per la news.');
        }

        $newsModel = model(NewsModel::class);
        if (
            !$newsModel->insert([
                'newspaper_id' => (int) $this->request->getPost('newspaper_id'),
                'headline' => trim((string) $this->request->getPost('headline')),
                'subtitle' => trim((string) $this->request->getPost('subtitle')),
                'body' => $this->request->getPost('body'),
                'author' => trim((string) $this->request->getPost('author')),
                'date' => date('Y-m-d H:i:s'),
                'id_user' => $this->session->get('user_id'),
                'active' => 1,
            ])
        ) {
            return redirect()->back()->with('alert', 'Errore inserimento news.');
        }

        $newsId = (int) $newsModel->getInsertID();
        if ($newsId < 1) {
            return redirect()->back()->with('alert', 'Errore inserimento news.');
        }

        $isins = array_filter([
            trim((string) $this->request->getPost('isin1')),
            trim((string) $this->request->getPost('isin2')),
            trim((string) $this->request->getPost('isin3')),
        ]);
        $isins = array_values(array_unique($isins));

        model(CompanyNewsModel::class)->replaceLinks((int) $newsId, $isins, $this->session->get('user_id'));

        return redirect()->back()->with('alert', 'News pubblicata.');
    }

    public function update()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $newsId = (int) $this->request->getPost('news_id');
        if ($newsId < 1) {
            return redirect()->back()->with('alert', 'News non valida.');
        }

        $rules = [
            'headline' => 'required|max_length[255]',
            'subtitle' => 'required|max_length[255]',
            'author' => 'required|max_length[50]',
            'newspaper_id' => 'required|is_natural_no_zero',
            'body' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('alert', 'Dati non validi.');
        }

        $newsModel = model(NewsModel::class);
        $newsModel->update($newsId, [
            'newspaper_id' => (int) $this->request->getPost('newspaper_id'),
            'headline' => trim((string) $this->request->getPost('headline')),
            'subtitle' => trim((string) $this->request->getPost('subtitle')),
            'body' => $this->request->getPost('body'),
            'author' => trim((string) $this->request->getPost('author')),
            'id_user' => $this->session->get('user_id'),
        ]);

        $isins = array_filter([
            trim((string) $this->request->getPost('isin1')),
            trim((string) $this->request->getPost('isin2')),
            trim((string) $this->request->getPost('isin3')),
        ]);
        $isins = array_values(array_unique($isins));

        model(CompanyNewsModel::class)->replaceLinks($newsId, $isins, $this->session->get('user_id'));

        return redirect()->back()->with('alert', 'News aggiornata.');
    }

    public function delete()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $newsId = (int) $this->request->getPost('news_id');
        if ($newsId < 1) {
            return redirect()->back()->with('alert', 'Richiesta non valida.');
        }

        model(NewsModel::class)->update($newsId, ['active' => 0, 'id_user' => $this->session->get('user_id')]);

        return redirect()->back()->with('alert', 'News archiviata.');
    }
}
