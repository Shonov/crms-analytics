<?php
/**
 * Created by PhpStorm.
 * User: Виталий Шонов
 * Date: 06.06.2019
 * Time: 13:05
 */

namespace App\Http\Services;


use App\AmoCrmLeadStatistic;
use App\AmocrmLostAndWonLeads;
use App\AmoCrmOperator;
use App\AmoCrmTaskStatistic;
use App\Helpers\Amocrm;
use App\Contracts\Facades\Logger;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AmocrmStatisticService
{
    protected $amo;
    protected $config;

    public function __construct()
    {
        $this->config = config('amocrm.' . env('PROJECT_NAME') . '.statistic_collection');
        $this->amo = new Amocrm();
    }

    public function getStatistic($startDate = null, $endDate = null)
    {
        if ($startDate === null) {
            $startDate = Carbon::yesterday();
        } else {
            $startDate = Carbon::createFromFormat('Y-m-d', $startDate, 3);
        }

        if ($endDate === null) {
            $endDate = Carbon::yesterday();
        } else {
            $endDate = Carbon::createFromFormat('Y-m-d', $endDate, 3);
        }

        for ($currentDay = $startDate->copy(); $currentDay <= $endDate; $currentDay = $currentDay->addDay()) {
            $this->createWonAndLostLeadsStatistic($currentDay);
            $this->createNewLeadsStatistic($currentDay);
            $this->createTasksStatistic($currentDay);
        }

        return response()->json(['code' => 200], 200);
    }

    public function createWonAndLostLeadsStatistic(Carbon $day)
    {
        $statistic = new AmocrmLostAndWonLeads();
        $wonLeads = $this->amo->getLeads([
            'status' => '142',
            'filter' => [
                'date_modify' => [
                    'from' => $day->copy()->startOfMonth()->format('Y-m-d H:i:sP'),
                    'to' => $day->copy()->endOfMonth()->format('Y-m-d H:i:sP'),
                ]
            ]
        ]);

        $lostLeads = $this->amo->getLeads([
            'status' => '143',
            'filter' => [
                'date_modify' => [
                    'from' => $day->copy()->startOfMonth()->format('Y-m-d H:i:sP'),
                    'to' => $day->copy()->endOfMonth()->format('Y-m-d H:i:sP'),
                ]
            ]
        ]);

        $statistic->updateOrCreate(
            ['month' => $day->copy()->startOfMonth()->format('Y-m-d')],
            [
                'won' => count($wonLeads),
                'lost' => count($lostLeads),
            ]
        );

        dump(AmocrmLostAndWonLeads::all()->toArray());
    }

    public function createNewLeadsStatistic(Carbon $day)
    {
        $leads = $this->amo->getLeads([
            'filter' => [
                'date_create' => [
                    'from' => $day->copy()->format('Y-m-d H:i:sP'),
                    'to' => $day->copy()->addDay()->format('Y-m-d H:i:sP'),
                ]
            ]
        ]);

        $sources = [
            'unknown' => 0
        ];

        foreach ($leads as $lead) {

            if ($lead->pipeline_id !== $this->config['pipeline_id']) {
                continue;
            }

            $source = $this->amo->getCustomFieldValue($this->config['utm_source_field'], $lead->custom_fields);

            if ($source === null) {
                $sources['unknown']++;
                continue;
            }

            if (!isset($sources[$source])) {
                $sources[$source] = 0;
            }
            $sources[$source]++;
        }

        foreach ($sources as $source => $count) {
            $statistic = new AmoCrmLeadStatistic();
            $statistic->updateOrCreate(
                ['type' => $source, 'date' => $day->format('Y-m-d')],
                ['count' => $count]);
        }

        dump(AmoCrmLeadStatistic::all()->toArray());
    }


    public function createTasksStatistic(Carbon $day)
    {
        $offset = 0;
        $limit_rows = 500;
        $operators = array_flip(AmoCrmOperator::query()->pluck('id')->all());

        foreach ($operators as &$operator) {
            $operator = [];
        }
        unset($operator);

        $filters = [
            'new' => [
                'date_create' => [
                    'from' => $day->copy()->format('Y-m-d H:i:sP'),
                    'to' => $day->copy()->addDay()->format('Y-m-d H:i:sP'),
                ],
            ],
            'not_closed' => [
                'status' => [0],
            ],
            'closed' => [
                'date_modify' => [
                    'from' => $day->copy()->format('Y-m-d H:i:sP'),
                    'to' => $day->copy()->addDay()->format('Y-m-d H:i:sP'),
                ],
                'status' => [1],
            ],
        ];

        foreach ($filters as $filter) {
            do {
                $type = array_search($filter, $filters, true);

                $tasks = $this->amo->getTasks([
                    'limit_rows' => $limit_rows,
                    'limit_offset' => $offset,
                    'filter' => $filter
                ]);

                if ($tasks === null) {
                    foreach ($operators as &$operator) {
                        $operator[$type] = 0;
                    }
                    unset($operator);

                    $offset = 0;
                    break;
                }
                if ($type === 'not_closed') {
                    foreach ($tasks as $task) {
                        if (!array_key_exists($type, $operators[$task->responsible_user_id])) {
                            $operators[$task->responsible_user_id][$type] = 0;
                        }

                        if ($task->complete_till_at < strtotime($day->copy()->format('Y-m-d H:i:sP'))) {
                            $operators[$task->responsible_user_id][$type]++;
                        }
                    }
                } else {
                    foreach ($tasks as $task) {
                        if (!array_key_exists($type, $operators[$task->responsible_user_id])) {
                            $operators[$task->responsible_user_id][$type] = 0;
                        }
                        $operators[$task->responsible_user_id][$type]++;
                    }
                }

                if ($tasks === null || count($tasks) < $limit_rows) {
                    $offset = 0;
                    break;
                }

                $offset += $limit_rows + 1;
            } while (count($tasks) === $limit_rows);
        }

        dump($operators);

        foreach ($operators as $operatorId => $data) {
            (new AmoCrmTaskStatistic())->updateOrCreate(
                [
                    'date' => $day->format('Y-m-d'),
                    'operator_id' => $operatorId,
                ],
                $data
            );
        }
    }

    public function getAll($startDay, $lastDay)
    {
        $query = AmoCrmLeadStatistic::query();

        $queryGenerator = $this->getQueryConditions($startDay, $lastDay);

        foreach ($queryGenerator as $queryCondition) {
            if (!$queryCondition['condition']) {
                continue;
            }
            $query = $query->where($queryCondition['column'], $queryCondition['operator'], $queryCondition['value']);
        }

        $data = $query->get();

        $result = [
            'table' => [],
            'daily_statistic' => [],
            'all_statistic' => [],
            'won_and_lost' => [
                'won' => 0,
                'lost' => 0,
            ],
            'tasks' => [],
            'operators' => [],
        ];

        foreach ($data as $statistic) {
            if (!isset($result['table'][$statistic->type])) {
                $result['table'][$statistic->type] = 0;
            }
            $result['table'][$statistic->type] += $statistic->count;
        }

        arsort($result['table']);

        foreach ($data as $statistic) {
            if (!isset($result['daily_statistic'][$statistic->date])) {
                $result['daily_statistic'][$statistic->date] = [
                    'date' => $statistic->date
                ];
            }

            if (!isset($result['daily_statistic'][$statistic->date][$statistic->type])) {
                $result['daily_statistic'][$statistic->date][$statistic->type] = 0;
            }

            $result['daily_statistic'][$statistic->date][$statistic->type] += $statistic->count;
        }

        foreach ($data as $statistic) {
            if (!isset($result['all_statistic'][$statistic->date])) {
                $result['all_statistic'][$statistic->date] = [
                    'count' => 0,
                    'date' => $statistic->date,
                ];
            }

            $result['all_statistic'][$statistic->date]['count'] += $statistic->count;
        }

        $query = AmocrmLostAndWonLeads::query();

        $queryGenerator = $this->getQueryConditions((new Carbon($startDay))->startOfMonth(), (new Carbon($startDay))->endOfMonth(), 'month');

        foreach ($queryGenerator as $queryCondition) {
            if (!$queryCondition['condition']) {
                continue;
            }
            $query = $query->where($queryCondition['column'], $queryCondition['operator'], $queryCondition['value']);
        }


        $data = $query->get();

        foreach ($data as $record) {
            $result['won_and_lost']['won'] += $record->won;
            $result['won_and_lost']['lost'] += $record->lost;
        }

        $operators = AmoCrmOperator::all();
        $result['operators'] = $operators->toArray();

        $result['tasks']['all'] = [];

        foreach ($operators as $operator) {
            $result['tasks']['graph'][$operator->id] = $operator->getTasks($startDay, $lastDay)->toArray();
            foreach ($result['tasks']['graph'][$operator->id] as $task) {
                foreach ($task as $key => $value) {
                    if ($key !== 'operator_id' && $key !== 'date') {
                        if (!array_key_exists($key, $result['tasks']['all'])) {
                            $result['tasks']['all'][$key] = $value;
                        } else {
                            $result['tasks']['all'][$key] += $value;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function getQueryConditions($startDay = null, $lastDay = null, $column = 'date')
    {
        return [[
            'condition' => $startDay !== null,
            'column' => $column,
            'operator' => '>=',
            'value' => $startDay,
        ], [
            'condition' => $lastDay !== null,
            'column' => $column,
            'operator' => '<=',
            'value' => $lastDay,
        ]];
    }
}
