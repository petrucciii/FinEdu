<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\OrderModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        //caricamento pagina dashboard con attivita recenti
        $modelUser = model(UserModel::class);
        if ($this->session->has('logged') && $this->session->get('role_id') == 1) {
            $orderModel = model(OrderModel::class);
            $db = db_connect();
            $data = [
                'userCount' => $modelUser->countUsersByRole(2),
                'companyCount' => model(CompanyModel::class)->where('active', 1)->countAllResults(),
                'moduleCount' => (int) $db->table('modules')->where('active', 1)->countAllResults(),
                'ordersToday' => $orderModel->countTodayOrders(),
                'recentOrders' => $orderModel->findRecentForDashboard(3),
                'recentNews' => $orderModel->findRecentNewsForDashboard(3),
            ];
            echo view('templates/header');
            echo view('pages/admins/viewDashboard', $data);
            echo view('templates/footer');
            return;
        }
        return redirect()->to('/');
    }
}