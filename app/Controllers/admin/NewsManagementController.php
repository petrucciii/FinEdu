<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\CompanyNewsModel;
use App\Models\NewspaperModel;
use App\Models\NewsModel;

//controller admin per la gestione delle notizie (CRUD).
//tutte le azioni richiedono ruolo admin (role_id = 1)
class NewsManagementController extends BaseController
{
    //verifica che l'utente loggato sia admin
    private function isAdmin(): bool
    {
        return $this->session->has('logged') && (int) $this->session->get('role_id') === 1;
    }

    //pagina principale gestione news: carica le fonti e le aziende per i modal di add/edit
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

    //endpoint ajax per la ricerca paginata delle news.
    //restituisce json con array news + dati paginazione
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

    //endpoint ajax per il dettaglio di una singola news.
    //usato dal modal di modifica per caricare tutti i dati della news.
    //il body viene estratto separatamente perche e un BLOB e va convertito a stringa.
    //restituisce anche gli isin collegati e la lista fonti per il select
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

        //separa il body dal resto dei dati perche il BLOB va gestito a parte.
        //il model gia converte il blob in stringa tramite convertBlobToString
        $body = $news['body'] ?? '';
        unset($news['body']);

        return $this->response->setJSON([
            'news' => $news,
            'body' => $body,
            'linked_isins' => $isins,
            'newspapers' => $newspapers,
        ]);
    }

    //crea una nuova news. valida i campi obbligatori, inserisce la riga nella tabella news,
    //poi collega fino a 3 societa tramite la tabella companies_news
    public function create()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        //regole di validazione per i campi del form
        $rules = [
            'headline' => 'required|max_length[255]',
            'subtitle' => 'required|max_length[255]',
            'author' => 'required|max_length[50]',
            'newspaper_id' => 'required|is_natural_no_zero',
            'body' => 'required', //il body e HTML da quill, salvato come BLOB
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
                'body' => $this->request->getPost('body'), //HTML formattato da quill
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

        //raccoglie fino a 3 isin collegati, rimuove vuoti e duplicati,
        //poi aggiorna la tabella ponte companies_news
        $isins = array_filter([
            trim((string) $this->request->getPost('isin1')),
            trim((string) $this->request->getPost('isin2')),
            trim((string) $this->request->getPost('isin3')),
        ]);
        $isins = array_values(array_unique($isins));

        model(CompanyNewsModel::class)->replaceLinks((int) $newsId, $isins, $this->session->get('user_id'));

        return redirect()->back()->with('alert', 'News pubblicata.');
    }

    //aggiorna una news esistente. stessa logica del create ma con update
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
            'body' => $this->request->getPost('body'), //HTML formattato da quill
            'author' => trim((string) $this->request->getPost('author')),
            'id_user' => $this->session->get('user_id'),
        ]);

        //aggiorna i collegamenti con le societa
        $isins = array_filter([
            trim((string) $this->request->getPost('isin1')),
            trim((string) $this->request->getPost('isin2')),
            trim((string) $this->request->getPost('isin3')),
        ]);
        $isins = array_values(array_unique($isins));

        model(CompanyNewsModel::class)->replaceLinks($newsId, $isins, $this->session->get('user_id'));

        return redirect()->back()->with('alert', 'News aggiornata.');
    }

    //soft delete: disattiva la news impostando active=0
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
