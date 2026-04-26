<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CompanyModel;
use App\Models\EducationModuleModel;
use App\Models\NewsModel;
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
            $data = [
                'userCount' => $modelUser->countUsersByRole(2),
                'companyCount' => model(CompanyModel::class)->countActive(),
                'moduleCount' => model(EducationModuleModel::class)->countActive(),
                'ordersToday' => $orderModel->countTodayOrders(),
                'recentOrders' => $orderModel->findRecentForDashboard(3),
                'recentNews' => model(NewsModel::class)->findRecentForDashboard(3),
            ];
            echo view('templates/header');
            echo view('pages/admins/viewDashboard', $data);
            echo view('templates/footer');
            return;
        }
        return redirect()->to('/');
    }
}
