<?php

namespace App\Http\Controllers;

use App\Http\Services\AcuityStatisticService;
use App\Http\Services\AmocrmStatisticService;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    public $acuityStatisticService;
    public $amoStatisticService;

    public function __construct()
    {
        $this->amoStatisticService = new AmocrmStatisticService();
        $this->acuityStatisticService = new AcuityStatisticService();
    }

    public function runCron(Request $request)
    {
        $startDate = $request->get('startDate', null);
        $endDate = $request->get('endDate', null);

        return [
            'amo' => $this->amoStatisticService->getStatistic($startDate, $endDate),
            'acuity' => $this->acuityStatisticService->getStatistic($startDate, $endDate),
        ];
    }

    public function getAcuity(Request $request)
    {
        $startDay = $request->get('start_day');
        $lastDay = $request->get('last_day');
        $location = $request->get('location');

        return [
            'data' => $this->acuityStatisticService->getAll($startDay, $lastDay, $location),
            'status' => true,
        ];
    }

    public function getAmoLeads(Request $request)
    {
        $startDay = $request->get('start_day');
        $lastDay = $request->get('last_day');

        return [
            'data' => $this->amoStatisticService->getAll($startDay, $lastDay),
            'status' => true,
        ];
    }
}
