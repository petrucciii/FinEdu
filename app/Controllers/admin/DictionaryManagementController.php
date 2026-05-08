<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DictionaryManagementController extends BaseController
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

        $items = [];
        foreach ($this->dictionaries() as $key => $config) {
            $builder = db_connect()->table($config['table'])
                ->where('active', 1)
                ->orderBy($config['order'], 'ASC');
            $items[$key] = $builder->get()->getResultArray();
        }

        echo view('templates/header');
        echo view('pages/admins/viewDictionaryManagement', [
            'dictionaries' => $this->dictionaries(),
            'items' => $items,
            'adminSection' => true,
        ]);
        echo view('templates/footer');
    }

    public function create(string $dictionary)
    {
        //creazione generica solo per dizionari dichiarati nella configurazione locale
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $config = $this->dictionaryConfig($dictionary);
        if (!$config) {
            return redirect()->back()->with('alert', 'Dizionario non valido.')->with('alert_type', 'danger');
        }

        $payload = $this->payloadFromPost($config, true);
        if ($payload === null) {
            return redirect()->back()
                ->with('alert', 'Compila i campi obbligatori.')
                ->with('alert_type', 'danger')
                ->with('dictionary', $dictionary);
        }

        $payload['id_user'] = $this->session->get('user_id');
        $payload['active'] = 1;

        if ($this->activeDuplicateExists($config, $payload)) {
            return redirect()->back()
                ->with('alert', 'Voce gia presente tra i record attivi.')
                ->with('alert_type', 'danger')
                ->with('dictionary', $dictionary);
        }

        try {
            db_connect()->table($config['table'])->insert($payload);
            return redirect()->back()
                ->with('alert', 'Voce inserita.')
                ->with('alert_type', 'success')
                ->with('dictionary', $dictionary);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('alert', 'Inserimento non riuscito.')
                ->with('alert_type', 'danger')
                ->with('dictionary', $dictionary);
        }
    }

    public function update(string $dictionary)
    {
        //aggiornamento generico: la chiave primaria non viene mai modificata
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $config = $this->dictionaryConfig($dictionary);
        $id = $this->request->getPost('id');
        if (!$config || $id === null || $id === '') {
            return redirect()->back()
                ->with('alert', 'Dati non validi.')
                ->with('alert_type', 'danger')
                ->with('dictionary', $dictionary);
        }

        $payload = $this->payloadFromPost($config, false);
        if ($payload === null) {
            return redirect()->back()
                ->with('alert', 'Compila i campi obbligatori.')
                ->with('alert_type', 'danger')
                ->with('dictionary', $dictionary);
        }

        $payload['id_user'] = $this->session->get('user_id');
        $payload['last_update'] = date('Y-m-d H:i:s');

        if ($this->activeDuplicateExists($config, $payload, $id)) {
            return redirect()->back()
                ->with('alert', 'Voce gia presente tra i record attivi.')
                ->with('alert_type', 'danger')
                ->with('dictionary', $dictionary);
        }

        try {
            db_connect()->table($config['table'])
                ->where($config['pk'], $id)
                ->update($payload);

            return redirect()->back()
                ->with('alert', 'Voce aggiornata.')
                ->with('alert_type', 'success')
                ->with('dictionary', $dictionary);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('alert', 'Aggiornamento non riuscito.')
                ->with('alert_type', 'danger')
                ->with('dictionary', $dictionary);
        }
    }

    public function delete(string $dictionary)
    {
        //soft delete consentito solo se non ci sono dipendenze operative
        if (!$this->isAdmin()) {
            return redirect()->to('/');
        }

        $config = $this->dictionaryConfig($dictionary);
        $id = $this->request->getPost('id');
        if (!$config || $id === null || $id === '') {
            return redirect()->back()
                ->with('alert', 'Dati non validi.')
                ->with('alert_type', 'danger')
                ->with('dictionary', $dictionary);
        }

        $dependency = $this->firstDependency($config, $id);
        if ($dependency !== null) {
            return redirect()->back()
                ->with('alert', 'Impossibile eliminare: esistono record collegati in ' . $dependency . '.')
                ->with('alert_type', 'danger')
                ->with('dictionary', $dictionary);
        }

        try {
            db_connect()->table($config['table'])
                ->where($config['pk'], $id)
                ->update([
                    'active' => 0,
                    'id_user' => $this->session->get('user_id'),
                    'last_update' => date('Y-m-d H:i:s'),
                ]);

            return redirect()->back()
                ->with('alert', 'Voce disattivata.')
                ->with('alert_type', 'success')
                ->with('dictionary', $dictionary);
        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('alert', 'Eliminazione non riuscita.')
                ->with('alert_type', 'danger')
                ->with('dictionary', $dictionary);
        }
    }

    private function dictionaryConfig(string $key): ?array
    {
        $all = $this->dictionaries();
        return $all[$key] ?? null;
    }

    private function payloadFromPost(array $config, bool $isCreate): ?array
    {
        $payload = [];
        foreach ($config['fields'] as $field) {
            $name = $field['name'];
            if (!$isCreate && $name === $config['pk']) {
                continue;
            }

            $value = trim((string) $this->request->getPost($name));
            if (($field['required'] ?? true) && $value === '') {
                return null;
            }

            if (($field['type'] ?? 'text') === 'int') {
                $payload[$name] = (int) $value;
            } else {
                $payload[$name] = $value;
            }
        }

        return $payload;
    }

    private function activeDuplicateExists(array $config, array $payload, $excludeId = null): bool
    {
        //controllo case insensitive solo sui record ancora attivi
        $field = $config['unique_field'] ?? null;
        if ($field === null || !array_key_exists($field, $payload)) {
            return false;
        }

        $value = trim((string) $payload[$field]);
        if ($value === '') {
            return false;
        }
        $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);

        $builder = db_connect()->table($config['table'])
            ->where('active', 1)
            ->where('LOWER(' . $field . ') =', $value);

        if ($excludeId !== null && $excludeId !== '') {
            $builder->where($config['pk'] . ' !=', $excludeId);
        }

        return (int) $builder->countAllResults() > 0;
    }

    private function firstDependency(array $config, $id): ?string
    {
        //controlla le tabelle operative prima del soft delete
        foreach ($config['dependencies'] as $dependency) {
            $count = (int) db_connect()->table($dependency['table'])
                ->where($dependency['field'], $id)
                ->countAllResults();
            if ($count > 0) {
                return $dependency['table'];
            }
        }

        return null;
    }

    private function dictionaries(): array
    {
        /*
         * Sono inclusi solo tabelle dizionario semplici con active/id_user.
         * companies_shareholders resta nella scheda societa perche e una relazione
         * operativa tra azienda e firm, non una tabella dizionario autonoma.
         */
        return [
            'roles' => [
                'title' => 'Ruoli',
                'table' => 'roles',
                'pk' => 'role_id',
                'order' => 'role',
                'unique_field' => 'role',
                'fields' => [
                    ['name' => 'role', 'label' => 'Ruolo', 'maxlength' => 50],
                ],
                'dependencies' => [
                    ['table' => 'users', 'field' => 'role_id'],
                ],
            ],
            'levels' => [
                'title' => 'Livelli',
                'table' => 'levels',
                'pk' => 'level_id',
                'order' => 'level',
                'unique_field' => 'level',
                'fields' => [
                    ['name' => 'level', 'label' => 'Livello', 'maxlength' => 20],
                ],
                'dependencies' => [
                    ['table' => 'users', 'field' => 'level_id'],
                ],
            ],
            'firms' => [
                'title' => 'Firms',
                'table' => 'firms',
                'pk' => 'firm_id',
                'order' => 'firm_name',
                'unique_field' => 'firm_name',
                'fields' => [
                    ['name' => 'firm_name', 'label' => 'Nome firm', 'maxlength' => 50],
                ],
                'dependencies' => [
                    ['table' => 'companies_shareholders', 'field' => 'firm_id'],
                    ['table' => 'analysts_consensus', 'field' => 'firm_id'],
                ],
            ],
            'ratings' => [
                'title' => 'Rating',
                'table' => 'ratings',
                'pk' => 'rating_id',
                'order' => 'rating',
                'unique_field' => 'rating',
                'fields' => [
                    ['name' => 'rating', 'label' => 'Rating', 'maxlength' => 40],
                ],
                'dependencies' => [
                    ['table' => 'analysts_consensus', 'field' => 'rating_id'],
                ],
            ],
            'newspapers' => [
                'title' => 'Fonti news',
                'table' => 'newspapers',
                'pk' => 'newspaper_id',
                'order' => 'newspaper',
                'unique_field' => 'newspaper',
                'fields' => [
                    ['name' => 'newspaper', 'label' => 'Fonte', 'maxlength' => 50],
                ],
                'dependencies' => [
                    ['table' => 'news', 'field' => 'newspaper_id'],
                ],
            ],
            'countries' => [
                'title' => 'Paesi',
                'table' => 'countries',
                'pk' => 'country_code',
                'order' => 'country',
                'unique_field' => 'country',
                'fields' => [
                    ['name' => 'country_code', 'label' => 'Codice', 'maxlength' => 2],
                    ['name' => 'country', 'label' => 'Paese', 'maxlength' => 50],
                ],
                'dependencies' => [
                    ['table' => 'companies', 'field' => 'country_code'],
                    ['table' => 'exchanges', 'field' => 'country_code'],
                ],
            ],
            'currencies' => [
                'title' => 'Valute',
                'table' => 'currencies',
                'pk' => 'currency_code',
                'order' => 'currency_code',
                'unique_field' => 'description',
                'fields' => [
                    ['name' => 'currency_code', 'label' => 'Codice', 'maxlength' => 3],
                    ['name' => 'description', 'label' => 'Descrizione', 'maxlength' => 10],
                    ['name' => 'symbol', 'label' => 'Simbolo', 'maxlength' => 1],
                ],
                'dependencies' => [
                    ['table' => 'exchanges', 'field' => 'currency_code'],
                    ['table' => 'data', 'field' => 'currency_code'],
                ],
            ],
            'sectors' => [
                'title' => 'Settori',
                'table' => 'sectors',
                'pk' => 'ea_code',
                'order' => 'description',
                'unique_field' => 'description',
                'fields' => [
                    ['name' => 'ea_code', 'label' => 'Codice EA', 'type' => 'int'],
                    ['name' => 'description', 'label' => 'Descrizione', 'maxlength' => 200],
                ],
                'dependencies' => [
                    ['table' => 'companies', 'field' => 'ea_code'],
                ],
            ],
            'data_type' => [
                'title' => 'Tipi dato',
                'table' => 'data_type',
                'pk' => 'type_id',
                'order' => 'name',
                'unique_field' => 'name',
                'fields' => [
                    ['name' => 'type', 'label' => 'Tipo', 'maxlength' => 1],
                    ['name' => 'name', 'label' => 'Nome', 'maxlength' => 20],
                ],
                'dependencies' => [
                    ['table' => 'data', 'field' => 'type_id'],
                ],
            ],
        ];
    }
}
