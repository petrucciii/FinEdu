<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\CompletedLessonModel;
use App\Models\EducationModuleModel;
use App\Models\NewsModel;

class Home extends BaseController
{
    public function index()
    {
        //home page con moduli, progressi, ultime notizie
        $moduleModel = model(EducationModuleModel::class);
        $companyModel = model(CompanyModel::class);
        $userId = $this->session->has('logged') ? (int) $this->session->get('user_id') : 0;

        //se l'utente non e loggato i completamenti sono a zero
        $modules = $moduleModel->findProgressForUser($userId);
        foreach ($modules as &$module) {
            $lessonCount = (int) ($module['lesson_count'] ?? 0);
            $completedCount = (int) ($module['completed_count'] ?? 0);
            $module['progress_percent'] = $lessonCount > 0 ? (int) round(($completedCount / $lessonCount) * 100) : 0;
        }
        unset($module);

        $recentCompletion = [];
        if ($userId > 0) {
            $recentCompletion = model(CompletedLessonModel::class)->recentCompletionsForUser($userId, 1, 7);
        }

        $data = [
            'modules' => $modules,
            'companyCount' => $companyModel->countActive(),
            'moduleCount' => $moduleModel->countActive(),
            'latestCompany' => $companyModel->findLatestActive(),//ultimo
            'latestModule' => $moduleModel->findLatestActive(),//ultimo
            'recentCompletion' => $recentCompletion[0] ?? null,
            'latestNews' => model(NewsModel::class)->findLatestPublic(4),//ultime 4 notizie pubbliche
        ];
        echo view('templates/header', $data);
        echo view('pages/viewHome', $data);
        echo view('templates/footer', $data);
    }
}
