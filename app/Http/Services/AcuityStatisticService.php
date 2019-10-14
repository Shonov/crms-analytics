<?php
/**
 * Created by PhpStorm.
 * User: Виталий Шонов
 * Date: 29.05.2019
 * Time: 19:31
 */

namespace App\Http\Services;


use App\AcuityAppointment;
use App\AcuityScheduledAppoinment;
use Carbon\Carbon;

class AcuityStatisticService
{
    public $locations = [];
    protected $acuity;

    public function __construct()
    {
        $this->locations = config('acuity.' . env('PROJECT_NAME') . '.locations');

        $this->acuity = new \AcuityScheduling([
            'userId' => env('ACUITY_USER_ID'),
            'apiKey' => env('ACUITY_API_KEY'),
        ]);
    }

    public function getStatistic($startDate, $endDate)
    {
        if ($startDate === null) {
            $startDate = Carbon::yesterday()->addWeeks(-1);
        } else {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate, 3);
        }

        if ($endDate === null) {
            $endDate = Carbon::yesterday();
        } else {
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate, 3);
        }

        dump($startDate, $endDate);

        for ($date = $startDate; $date <= $endDate; $date = $date->addDay()) {
            $this->getApiServiceStatistics($date->format('Y-m-d'));
        }

        $this->getScheduledAppointments();

        if (Carbon::now()->day === 1) {
            $this->getScheduledAppointments(Carbon::now());
        }

        return true;
    }

    private function getApiServiceStatistics($yesterday = null)
    {
        if (!isset($yesterday)) {
            $yesterday = Carbon::yesterday()->format('Y-m-d');
        }

        foreach ($this->locations as $locationCode => $location) {
            foreach ($location as $type => $typeId) {
                $acuityAppointments = [
                    'completed_count' => 0,
                    'no_show_count' => 0,
                    'cancelled_count' => 0,
                ];

                $appointments = $this->acuity->request('/appointments', [
                    'query' => [
                        'minDate' => $yesterday,
                        'maxDate' => $yesterday,
                        'appointmentTypeID' => $typeId,
                        'showall' => true,
                    ],
                ]);

                foreach ($appointments as $appointment) {
                    if ($appointment['canceled'] === false) {
                        $acuityAppointments['completed_count']++;
                    } else if ($appointment['noShow'] === true) {
                        $acuityAppointments['no_show_count']++;
                    } else {
                        $acuityAppointments['cancelled_count']++;
                    }
                }

                (new AcuityAppointment())->updateOrCreate(
                    [
                        'location' => $locationCode,
                        'type' => $type,
                        'date' => $yesterday,
                    ],
                    $acuityAppointments
                );
            }
        }

        return response()->json(['code' => 200], 200);
    }

    private function getScheduledAppointments($today = null)
    {
        if ($today === null) {
            $today = Carbon::now();
        }

        $scheduledAppointments = 0;
        foreach ($this->locations as $locationCode => $location) {
            foreach ($location as $type => $typeId) {
                if ($type !== 'trial') {
                    continue;
                }

                $scheduledAppointments += count($this->acuity->request('/appointments', [
                    'query' => [
                        'max' => 100000,
                        'minDate' => $today->startOfMonth()->format('Y-m-d'),
                        'maxDate' => $today->endOfMonth()->format('Y-m-d'),
                        'appointmentTypeID' => $typeId,
                        'showall' => true,
                    ],
                ]));
            }
        }

        $acuityScheduledAppoinment = new AcuityScheduledAppoinment();
        $acuityScheduledAppoinment->updateOrCreate(
            ['month' => $today->startOfMonth()->format('Y-m-d')],
            ['count' => $scheduledAppointments]
        );

        return true;
    }

    public function getAll($startDay = null, $lastDay = null, $location = null)
    {
        $query = AcuityAppointment::query();

        $queryGenerator = [[
            'condition' => $startDay !== null,
            'column' => 'date',
            'operator' => '>=',
            'value' => $startDay,
        ], [
            'condition' => $lastDay !== null,
            'column' => 'date',
            'operator' => '<=',
            'value' => $lastDay,
        ], [
            'condition' => $location !== null,
            'column' => 'location',
            'operator' => '=',
            'value' => $location,
        ]];

        foreach ($queryGenerator as $queryCondition) {
            if (!$queryCondition['condition']) {
                continue;
            }
            $query = $query->where($queryCondition['column'], $queryCondition['operator'], $queryCondition['value']);
        }

        $data = $query->get();

        $scheduledAppointments = (new AcuityScheduledAppoinment)->where('month', Carbon::now()->startOfMonth()->format('Y-m-d'))->first();

        $result = [
            'all' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'no_show' => 0,
            'scheduledAppointments' => $scheduledAppointments->count,
            'table' => [
                'trial' => [
                    'type' => 'Trial',
                    'all' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'no_show' => 0,
                ],
                'membership' => [
                    'type' => 'Membership',
                    'all' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'no_show' => 0,
                ],
                'home_sessions' => [
                    'type' => 'Home sessions',
                    'all' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'no_show' => 0,
                ]
            ],
            'trials' => [],
            'memberships' => [],
            'home_sessions' => [],
        ];

        foreach ($data as $record) {
            $result['table'][$record->type]['completed'] += $record->completed_count;
            $result['table'][$record->type]['cancelled'] += $record->cancelled_count;
            $result['table'][$record->type]['no_show'] += $record->no_show_count;

            if ($record->location === AcuityAppointment::$HOME_SESSION_LOCATION) {
                $result['table'][AcuityAppointment::$HOME_SESSION_LOCATION]['completed'] += $record->completed_count;
                $result['table'][AcuityAppointment::$HOME_SESSION_LOCATION]['cancelled'] += $record->cancelled_count;
                $result['table'][AcuityAppointment::$HOME_SESSION_LOCATION]['no_show'] += $record->no_show_count;
            }
        }

        foreach ($result['table'] as $key => $tableRecord) {
            $result['table'][$key]['all'] = $result['table'][$key]['completed'] + $result['table'][$key]['cancelled'] + $result['table'][$key]['no_show'];
            if ($key === AcuityAppointment::$HOME_SESSION_LOCATION) {
                continue;
            }
            $result['all'] += $result['table'][$key]['all'];
            $result['completed'] += $result['table'][$key]['completed'];
            $result['cancelled'] += $result['table'][$key]['cancelled'];
            $result['no_show'] += $result['table'][$key]['no_show'];
        }

        foreach ($data as $record) {
            $selector = 'trials';

            if ($record->type === AcuityAppointment::$MEMBERSHIP_SESSION_TYPE) {
                $selector = 'memberships';
            }

            if (!isset($result[$selector][$record->date])) {
                $result[$selector][$record->date] = [
                    'completed' => 0,
                    'cancelled' => 0,
                    'no_show' => 0,
                    'date' => $record->date
                ];
            }

            $result[$selector][$record->date]['completed'] += $record->completed_count;
            $result[$selector][$record->date]['cancelled'] += $record->cancelled_count;
            $result[$selector][$record->date]['no_show'] += $record->no_show_count;
        }


        foreach ($data as $record) {
            if ($record->location !== AcuityAppointment::$HOME_SESSION_LOCATION) {
                continue;
            }

            $selector = 'home_sessions';


            if (!isset($result[$selector][$record->date])) {
                $result[$selector][$record->date] = [
                    'completed' => 0,
                    'cancelled' => 0,
                    'no_show' => 0,
                    'date' => $record->date
                ];
            }

            $result[$selector][$record->date]['completed'] += $record->completed_count;
            $result[$selector][$record->date]['cancelled'] += $record->cancelled_count;
            $result[$selector][$record->date]['no_show'] += $record->no_show_count;
        }

        foreach ($data as $record) {
            $selector = 'all_sessions';


            if (!isset($result[$selector][$record->date])) {
                $result[$selector][$record->date] = [
                    'completed' => 0,
                    'cancelled' => 0,
                    'no_show' => 0,
                    'date' => $record->date
                ];
            }

            $result[$selector][$record->date]['completed'] += $record->completed_count;
            $result[$selector][$record->date]['cancelled'] += $record->cancelled_count;
            $result[$selector][$record->date]['no_show'] += $record->no_show_count;
        }

        return $result;
    }
}
