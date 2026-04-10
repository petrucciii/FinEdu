<?php

namespace App\Models;

use CodeIgniter\Model;

class BoardModel extends Model
{
    //ci4 attributi per query builder
    protected $table = 'companies_board';
    protected $primaryKey = 'isin'; //ci4 non accetta array coma pk
    protected $returnType = 'array';
    protected $allowedFields = [
        'isin',
        'member_id',
        'role',
    ];

    //timestamp automatici
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'last_update';

    //elenco membri cda di una società
    public function findBoardPerCompany(string $isin)
    {
        try {
            return $this->db->table($this->table)
                ->select('companies_board.isin, companies_board.member_id, companies_board.role, board_members.full_name, board_members.picture_path')
                ->join('board_members', 'board_members.member_id = companies_board.member_id')
                ->where('companies_board.isin', trim($isin))
                ->where('board_members.active', 1) //solo quelli non eliminati
                ->orderBy('board_members.full_name', 'ASC')
                ->get()
                ->getResultArray();
        } catch (\Throwable $th) {
            return false;
        }
    }

    //inserimento riga querybuilder
    public function insertRow(array $row): bool
    {
        try {
            return $this->db->table($this->table)->insert($row);
        } catch (\Throwable $th) {
            return false;
        }

    }

    //aggiornamento con chiave composta isin+member_id
    public function updateRow(string $isin, int $memberId, array $row): bool
    {
        try {
            return $this->db->table($this->table)
                ->where('isin', $isin)
                ->where('member_id', $memberId)
                ->update($row);
        } catch (\Throwable $th) {
            return false;
        }
    }

    //eliminazione fisica dato che non ha senso tenere storico dei membri cda di una società
    public function deleteRow(string $isin, int $memberId): bool
    {
        try {
            return (bool) $this->db->table($this->table)
                ->where('isin', $isin)
                ->where('member_id', $memberId)
                ->delete();
        } catch (\Throwable $th) {
            return false;
        }
    }
}
