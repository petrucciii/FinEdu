<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

class OrderModel extends Model
{
    protected $table      = 'orders';
    protected $primaryKey = 'order_id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'isin',
        'ticker',
        'mic',
        'quantity',
        'price',
        'type',      // BUY / SELL
        'id_user',
        'active',
    ];

    //automatic update and creation timestamp
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    //insert a new order returning boolean
    public function insertOrder(array $data): bool
    {
        try {
            return $this->db->table($this->table)->insert($data);
        } catch (Exception $e) {
            return false;
        }
    }

    //get all active orders for a given user, joining listings/exchanges for label
    public function findOrdersByUser(int $userId): array
    {
        try {
            return $this->db->table($this->table)
                ->select('orders.*, companies.name as company_name, exchanges.short_name as exchange_label, exchanges.currency_code')
                ->join('companies', 'companies.isin = orders.isin', 'left')
                ->join('exchanges', 'exchanges.mic = orders.mic', 'left')
                ->where('orders.id_user', $userId)
                ->where('orders.active', 1)
                ->orderBy('orders.created_at', 'DESC')
                ->get()
                ->getResultArray();
        } catch (Exception $e) {
            return [];
        }
    }

    //logical delete returning boolean
    public function deleteOrder(int $orderId): bool
    {
        try {
            return $this->update($orderId, ['active' => 0]) ? true : false;
        } catch (Exception $e) {
            return false;
        }
    }
}
