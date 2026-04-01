<?php

namespace App\Models;

use CodeIgniter\Model;

//i Model tabella `companies_board` (CdA): PK (isin, member_id)
class BoardModel extends Model
{
    protected $table = 'companies_board';
    protected $primaryKey = 'isin';
    protected $returnType = 'array';
    protected $allowedFields = [
        'isin',
        'member_id',
        'role',
    ];

    public function findBoardPerCompany(string $isin): array
    {
        return $this->db->table($this->table)
            ->select('companies_board.isin, companies_board.member_id, companies_board.role, board_members.full_name, board_members.picture_path')
            ->join('board_members', 'board_members.member_id = companies_board.member_id')
            ->where('companies_board.isin', trim($isin))
            ->where('board_members.active', 1)
            ->orderBy('board_members.full_name', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function insertRow(array $row): bool
    {
        return $this->db->table($this->table)->insert($row);
    }

    public function updateRow(string $isin, int $memberId, array $row): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('isin', $isin)
            ->where('member_id', $memberId)
            ->update($row);
    }

    public function deleteRow(string $isin, int $memberId): bool
    {
        return (bool) $this->db->table($this->table)
            ->where('isin', $isin)
            ->where('member_id', $memberId)
            ->delete();
    }
}
