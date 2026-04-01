<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ListingModel;
use App\Models\OrderModel;
use Exception;

class OrderController extends BaseController
{
    private function isLogged(): bool
    {
        return $this->session->has('logged');
    }

    //create a new order from the modal form
    public function create()
    {
        if (!$this->isLogged()) {
            return redirect()->to('/')->with('alert', 'Devi essere loggato per inserire un ordine.');
        }

        $isin     = trim((string) $this->request->getPost('isin'));
        $mic      = trim((string) $this->request->getPost('mic'));
        $quantity = (int) $this->request->getPost('quantity');
        $price    = $this->request->getPost('price');
        $type     = trim((string) $this->request->getPost('type'));

        //get ticker from listings table using isin + mic
        $listingModel = model(ListingModel::class);
        $listing = $listingModel->where('isin', $isin)->where('mic', $mic)->where('active', 1)->first();

        if (!$listing) {
            return redirect()->back()->with('alert', 'Quotazione non trovata per la borsa selezionata.')->with('alert_type', 'danger');
        }

        if ($quantity < 1) {
            return redirect()->back()->with('alert', 'La quantità deve essere almeno 1.')->with('alert_type', 'danger');
        }

        if (!in_array($type, ['BUY', 'SELL'], true)) {
            return redirect()->back()->with('alert', 'Tipo ordine non valido.')->with('alert_type', 'danger');
        }

        $data = [
            'isin'     => $isin,
            'ticker'   => $listing['ticker'],
            'mic'      => $mic,
            'quantity' => $quantity,
            'price'    => ($price !== '' && $price !== null) ? (float) $price : null,
            'type'     => $type,
            'id_user'  => $this->session->get('user_id'),
            'active'   => 1,
        ];

        $ok = model(OrderModel::class)->insertOrder($data);

        return $ok
            ? redirect()->back()->with('alert', 'Ordine inserito con successo.')
            : redirect()->back()->with('alert', 'Impossibile inserire l\'ordine.')->with('alert_type', 'danger');
    }
}
