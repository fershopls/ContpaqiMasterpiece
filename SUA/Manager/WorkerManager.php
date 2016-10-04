<?php

namespace SUA\Manager;

use lib\Reporter\ReporterInterface;

// Query
use SUA\Query\DatabaseWorkerById;
use SUA\Query\DatabasePeriodDates;
use SUA\Query\DatabaseWorkerMovementDates;

class WorkerManager extends ReporterInterface {

    public function logic ($date_beg, $date_end)
    {
        $period_ids = $this->query(DatabasePeriodDates::class)
            ->execute([
                'date_beg' => $date_beg,
                'date_end' => $date_end,
            ]);

        $workers_row = array();
        foreach ($period_ids as $db_slug => $row)
        {
            $movement_ids = $this->query(DatabaseWorkerMovementDates::class)
                ->injectDbs([$db_slug])
                ->execute([
                    'list_ids' => implode(',', $row)
                ])[$db_slug];

            $workers_row [$db_slug] = $this->query(DatabaseWorkerById::class)
                ->injectDbs([$db_slug])
                ->execute([
                    'list_ids' => implode(',', $movement_ids),
                ])[$db_slug];
        }

        return $workers_row;
    }

    public function dump()
    {

    }

}